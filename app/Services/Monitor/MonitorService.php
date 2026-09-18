<?php

namespace App\Services\Monitor;

use App\Models\Monitor\VideoMonitorEvent;
use App\Models\Monitor\VideoMonitorRule;
use App\Models\Video\VideoOption;
use App\Services\Admin\System\SysScheduleService;
use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use App\Support\Utils\ServerStats;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class MonitorService
{
    public const HEARTBEAT_DEAD_SEC = 300;

    /** @var array<string, string> */
    public const OPTION_DEFAULTS = [
        'monitor_enabled' => '1',
        'monitor_slow_ms' => '1000',
        'monitor_retain_min_days' => '3',
        'monitor_retain_hour_days' => '90',
        'monitor_access_cc' => '120',
    ];

    /** @var array<string, string> */
    public const METRIC_LABELS = [
        'sys.load1' => '1 分钟负载',
        'sys.load5' => '5 分钟负载',
        'sys.load15' => '15 分钟负载',
        'sys.cpu.pct' => 'CPU %',
        'sys.mem.used_pct' => '内存 %',
        'sys.disk.used_pct' => '磁盘 %',
        'php.memory_pct' => 'PHP 内存 %',
        'http.req' => '请求数',
        'http.2xx' => '2xx',
        'http.3xx' => '3xx',
        'http.4xx' => '4xx',
        'http.5xx' => '5xx',
        'http.slow' => '慢请求',
    ];

    /** @var list<string> */
    public const SERIES_KEYS = [
        'sys.load1', 'sys.load5', 'sys.load15', 'sys.cpu.pct',
        'sys.mem.used_pct', 'sys.disk.used_pct', 'php.memory_pct',
        'http.req', 'http.2xx', 'http.4xx', 'http.5xx', 'http.slow',
    ];

    /** @var list<array{id: string, label: string, keys: list<string>}> */
    public const SERIES_GROUPS = [
        ['id' => 'load', 'label' => '系统负载', 'keys' => ['sys.load1', 'sys.load5', 'sys.load15']],
        ['id' => 'cpu', 'label' => 'CPU', 'keys' => ['sys.cpu.pct']],
        ['id' => 'mem', 'label' => '内存与磁盘', 'keys' => ['sys.mem.used_pct', 'sys.disk.used_pct', 'php.memory_pct']],
        ['id' => 'req', 'label' => '请求量', 'keys' => ['http.req']],
        ['id' => 'slow', 'label' => '慢请求', 'keys' => ['http.slow']],
        ['id' => 'http', 'label' => '响应状态码', 'keys' => ['http.2xx', 'http.4xx', 'http.5xx']],
    ];

    public static function option(string $key, mixed $default = ''): mixed
    {
        $all = self::options();

        return $all[$key] ?? $default;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $defaults = self::OPTION_DEFAULTS;
        try {
            if (! Schema::hasTable('video_options')) {
                return $defaults;
            }
            $rows = VideoOption::query()->whereIn('k', array_keys($defaults))->pluck('v', 'k')->all();
            foreach ($defaults as $k => $v) {
                if (! array_key_exists($k, $rows) || $rows[$k] === null || $rows[$k] === '') {
                    $rows[$k] = $v;
                } else {
                    $rows[$k] = (string) $rows[$k];
                }
            }

            return $rows;
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public static function enabled(): bool
    {
        return (int) self::option('monitor_enabled', '1') === 1;
    }

    public static function slowMs(): int
    {
        return max(1, (int) self::option('monitor_slow_ms', 1000));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function pageBoard(string $desk, array $query = []): array
    {
        $opts = self::options();
        $heartbeat = (int) MonitorState::getNum('cron_heartbeat', 0);
        $age = $heartbeat > 0 ? time() - $heartbeat : 0;
        $dead = $heartbeat < 1 || $age > self::HEARTBEAT_DEAD_SEC;
        $cronLine = app(SysScheduleService::class)->pageBoard()['cron_line'] ?? '';
        $skipped = $this->capabilityMap();
        $events = $this->eventRows();
        $firingCount = 0;
        foreach ($events as $event) {
            if ((int) ($event['status'] ?? 0) === 1) {
                $firingCount++;
            }
        }
        $eventStatus = (int) ($query['status'] ?? 0);
        if (in_array($eventStatus, [1, 2, 3], true)) {
            $events = array_values(array_filter(
                $events,
                static fn (array $row): bool => (int) ($row['status'] ?? 0) === $eventStatus
            ));
        } else {
            $eventStatus = 0;
        }
        $accessCc = (int) ($opts['monitor_access_cc'] ?? 120);
        $access = MonitorAccess::lastDay($accessCc);
        $accessHigh = 0;
        foreach ($access as $row) {
            if (! empty($row['high'])) {
                $accessHigh++;
            }
        }
        $accessOnly = (string) ($query['only'] ?? 'high') !== 'all';
        if ($accessOnly) {
            $access = array_values(array_filter($access, static fn (array $row): bool => ! empty($row['high'])));
        }
        $heartbeatText = $heartbeat > 0 ? date('Y-m-d H:i:s', $heartbeat) : '还没跑过';

        return [
            'desk' => $desk,
            'opts' => $opts,
            'heartbeat' => $heartbeat,
            'heartbeat_text' => $heartbeatText,
            'heartbeat_dead' => $dead,
            'snapshot' => $this->snapshotItems($dead, $heartbeatText),
            'cron_line' => $cronLine,
            'skipped' => $skipped,
            'skipped_lines' => $this->skippedLines(),
            'rules' => $this->ruleRows(),
            'events' => $events,
            'event_status' => $eventStatus,
            'firing_count' => $firingCount,
            'access' => $access,
            'access_cc' => $accessCc,
            'access_high' => $accessHigh,
            'access_only' => $accessOnly,
        ];
    }

    /** @return array<string, mixed> */
    public function seriesPayload(string $range): array
    {
        $windows = [
            '1h' => 3600,
            '6h' => 21600,
            '24h' => 86400,
            '72h' => 259200,
            '7d' => 604800,
            '30d' => 2592000,
        ];
        if (! isset($windows[$range])) {
            $range = '1h';
        }
        $to = time();
        $from = $to - $windows[$range];
        $res = MonitorStore::fetchSeries(self::SERIES_KEYS, $from, $to, 'auto');
        $step = $res['granularity'] === 'hour' ? 3600 : 60;
        $groups = [];
        foreach (self::SERIES_GROUPS as $group) {
            $series = [];
            foreach ($group['keys'] as $key) {
                $pts = $res['series'][$key] ?? [];
                if ($pts === []) {
                    continue;
                }
                $points = [];
                foreach ($pts as $ts => $v) {
                    $points[] = [(int) $ts, (float) $v];
                }
                usort($points, static fn (array $a, array $b): int => $a[0] <=> $b[0]);
                $lastPt = $points === [] ? null : $points[array_key_last($points)];
                $series[] = [
                    'key' => $key,
                    'label' => self::METRIC_LABELS[$key] ?? $key,
                    'points' => $points,
                    'last' => $lastPt === null ? null : (float) $lastPt[1],
                ];
            }
            $groups[] = [
                'id' => $group['id'],
                'label' => $group['label'],
                'series' => $series,
                'empty_reason' => $this->groupEmptyReason($group),
            ];
        }

        return [
            'range' => $range,
            'step' => $step,
            'granularity' => $res['granularity'],
            'skipped' => array_keys($this->capabilityMap()),
            'groups' => $groups,
            'series' => $this->flatSeries($groups),
        ];
    }

    /** @return array<string, mixed> */
    public function livePayload(): array
    {
        $now = time();
        $from = $now - 3600;
        $keys = ['http.req', 'http.5xx', 'http.slow', 'sys.load1', 'sys.mem.used_pct', 'sys.disk.used_pct', 'php.memory_pct'];
        $res = MonitorStore::fetchSeries($keys, $from, $now, 'min');
        $sum5 = static function (array $series) use ($now): ?float {
            $n = 0.0;
            $has = false;
            foreach ($series as $ts => $v) {
                if ((int) $ts >= $now - 300) {
                    $has = true;
                    $n += (float) $v;
                }
            }

            return $has ? $n : null;
        };
        $last = static function (array $series): ?float {
            if ($series === []) {
                return null;
            }
            ksort($series);
            $v = end($series);

            return $v === false ? null : (float) $v;
        };
        $req5 = $sum5($res['series']['http.req'] ?? []);
        $err5 = $sum5($res['series']['http.5xx'] ?? []);
        $slow5 = $sum5($res['series']['http.slow'] ?? []);

        return [
            'ts' => $now,
            'qps' => $req5 === null ? null : round($req5 / 300, 2),
            'req5' => $req5 === null ? null : (int) round($req5),
            'err5' => $err5 === null ? null : (int) round($err5),
            'slow5' => $slow5 === null ? null : (int) round($slow5),
            'load' => $last($res['series']['sys.load1'] ?? []),
            'mem' => $last($res['series']['sys.mem.used_pct'] ?? []),
            'disk' => $last($res['series']['sys.disk.used_pct'] ?? []),
            'php_mem' => $last($res['series']['php.memory_pct'] ?? []),
        ];
    }

    /** @param  array<string, mixed>  $input */
    public function saveSettings(array $input): array
    {
        if (! $this->optionsReady()) {
            return Result::fail('还没有 video_options 表');
        }
        $enabled = ! empty($input['monitor_enabled']) ? '1' : '0';
        $slow = max(100, min(60000, (int) ($input['monitor_slow_ms'] ?? 1000)));
        $minDays = max(1, min(14, (int) ($input['monitor_retain_min_days'] ?? 3)));
        $hourDays = max(7, min(730, (int) ($input['monitor_retain_hour_days'] ?? 90)));
        $cc = max(10, min(10000, (int) ($input['monitor_access_cc'] ?? 120)));
        $now = time();
        $rows = [
            'monitor_enabled' => $enabled,
            'monitor_slow_ms' => (string) $slow,
            'monitor_retain_min_days' => (string) $minDays,
            'monitor_retain_hour_days' => (string) $hourDays,
            'monitor_access_cc' => (string) $cc,
        ];
        foreach ($rows as $k => $v) {
            VideoOption::query()->updateOrCreate(['k' => $k], ['v' => $v, 'updated_at' => $now]);
        }
        Cache::forget(\App\Services\Video\VideoSettingService::CACHE_KEY);

        return AdminOpLog::ifOk(Result::success([], '已保存'), 'update', '改了运行监控设置');
    }

    /** @param  array<string, mixed>  $input */
    public function saveRule(array $input): array
    {
        if (! $this->rulesReady()) {
            return Result::fail('还没有规则表');
        }
        $id = (int) ($input['id'] ?? 0);
        $row = null;
        if ($id > 0) {
            $row = VideoMonitorRule::query()->find($id);
            if ($row === null) {
                return Result::fail('规则不存在');
            }
            if ($this->blankInput($input, 'name')) {
                $input['name'] = (string) $row->name;
            }
            if ($this->blankInput($input, 'metric_key')) {
                $input['metric_key'] = (string) $row->metric_key;
            }
            foreach (['agg', 'op', 'window_min', 'threshold', 'for_min', 'silence_min', 'hint'] as $field) {
                if ($this->blankInput($input, $field)) {
                    $input[$field] = $row->{$field};
                }
            }
        }
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            return Result::fail('请填写名称');
        }
        $metric = trim((string) ($input['metric_key'] ?? ''));
        if ($metric === '' || ! preg_match('#^[A-Za-z0-9_.\-|/]{1,64}$#', $metric)) {
            return Result::fail('指标键不对');
        }
        $agg = (string) ($input['agg'] ?? 'avg');
        if (! in_array($agg, ['avg', 'max', 'sum', 'last'], true)) {
            $agg = 'avg';
        }
        $op = (string) ($input['op'] ?? 'gt');
        if (! in_array($op, ['gt', 'gte', 'lt', 'lte'], true)) {
            $op = 'gt';
        }
        $data = [
            'name' => mb_substr($name, 0, 100),
            'metric_key' => $metric,
            'agg' => $agg,
            'window_min' => max(1, min(1440, (int) ($input['window_min'] ?? 5))),
            'op' => $op,
            'threshold' => (float) ($input['threshold'] ?? 0),
            'for_min' => max(0, min(1440, (int) ($input['for_min'] ?? 0))),
            'silence_min' => max(0, min(10080, (int) ($input['silence_min'] ?? 30))),
            'hint' => mb_substr(trim((string) ($input['hint'] ?? '')), 0, 255),
        ];
        if ($id > 0 && $row !== null) {
            $row->fill($data)->save();

            return AdminOpLog::ifOk(Result::success(['id' => $id], '已保存'), 'update', '改了告警规则 '.$data['name']);
        }
        $data['status'] = 0;
        $new = VideoMonitorRule::query()->create($data);

        return AdminOpLog::ifOk(Result::success(['id' => (int) $new->id], '已加上，默认停用'), 'add', '加了告警规则 '.$name);
    }

    public function updateRuleStatus(int $id, int $status): array
    {
        if ($id < 1) {
            return Result::fail('参数错误');
        }
        $row = VideoMonitorRule::query()->find($id);
        if ($row === null) {
            return Result::fail('规则不存在');
        }
        $row->status = $status ? 1 : 0;
        $row->save();

        return AdminOpLog::ifOk(
            Result::success([], $row->status ? '已启用' : '已停用'),
            'update',
            ($row->status ? '启用了告警规则 ' : '停用了告警规则 ').$row->name
        );
    }

    public function testRule(int $id): array
    {
        $row = VideoMonitorRule::query()->find($id);
        if ($row === null) {
            return Result::fail('规则不存在');
        }
        $res = AlertEngine::evaluate($row->toArray());
        if ($res === null) {
            return Result::success([
                'hit' => false,
                'missing' => true,
            ], '现在没有样本，不能判断会不会触发');
        }

        return Result::success([
            'hit' => $res['hit'],
            'value' => $res['value'],
            'summary' => $res['summary'],
            'missing' => false,
        ], $res['hit'] ? '按当前样本会触发（没有外发通知）' : '按当前样本不会触发');
    }

    public function ackEvent(int $id): array
    {
        if ($id < 1) {
            return Result::fail('参数错误');
        }
        $row = VideoMonitorEvent::query()->find($id);
        if ($row === null) {
            return Result::fail('事件不存在');
        }
        if ((int) $row->status === 2) {
            return Result::fail('已经恢复了');
        }
        $row->status = 3;
        $row->acked_at = time();
        $row->save();

        return AdminOpLog::ifOk(Result::success([], '已确认'), 'update', '确认了监控事件 #'.$id);
    }

    /** @return list<array<string, mixed>> */
    private function ruleRows(): array
    {
        if (! $this->rulesReady()) {
            return [];
        }
        $out = [];
        foreach (VideoMonitorRule::query()->orderBy('id')->get() as $row) {
            $item = $row->toArray();
            $key = (string) $row->metric_key;
            $item['metric_label'] = self::METRIC_LABELS[$key] ?? $key;
            $item['status_text'] = ((int) $row->status === 1) ? '开着' : '停用';
            $item['agg_text'] = match ((string) $row->agg) {
                'max' => '最大',
                'sum' => '求和',
                'last' => '最新',
                default => '平均',
            };
            $item['op_text'] = match ((string) $row->op) {
                'gte' => '≥',
                'lt' => '<',
                'lte' => '≤',
                default => '>',
            };
            $out[] = $item;
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    private function eventRows(): array
    {
        try {
            if (! Schema::hasTable('video_monitor_events')) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }
        $names = VideoMonitorRule::query()->pluck('name', 'id');
        $out = [];
        foreach (VideoMonitorEvent::query()->orderByDesc('id')->limit(80)->get() as $row) {
            $item = $row->toArray();
            $item['rule_name'] = (string) ($names[$row->rule_id] ?? ('#'.$row->rule_id));
            $item['status_text'] = match ((int) $row->status) {
                2 => '已恢复',
                3 => '已确认',
                default => '触发中',
            };
            $item['opened_text'] = $row->opened_at > 0 ? date('Y-m-d H:i', (int) $row->opened_at) : '';
            $out[] = $item;
        }

        return $out;
    }

    /** @return array<string, string> */
    private function capabilityMap(): array
    {
        $raw = MonitorState::getVal('capabilities', '');
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $key => $reason) {
            if (! is_string($key) && ! is_int($key)) {
                continue;
            }
            $out[(string) $key] = is_scalar($reason) ? (string) $reason : '';
        }

        return $out;
    }

    /**
     * @return list<array{k: string, v: string}>
     */
    private function snapshotItems(bool $dead, string $heartbeatText): array
    {
        $items = [
            ['k' => '心跳', 'v' => $dead ? '采集未跑' : $heartbeatText],
            ['k' => 'PHP', 'v' => PHP_VERSION],
            ['k' => '系统', 'v' => PHP_OS_FAMILY === 'Windows' ? 'Windows' : PHP_OS_FAMILY],
        ];
        $path = realpath(base_path()) ?: base_path();
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);
        if (is_numeric($total) && is_numeric($free) && (float) $total > 0) {
            $usedPct = 100.0 * (((float) $total - max(0.0, (float) $free)) / (float) $total);
            $items[] = ['k' => '磁盘', 'v' => number_format($usedPct, 1, '.', '').'%'];
        }
        try {
            $ram = ServerStats::snapshot()['ram'] ?? [];
            if (! empty($ram['ok'])) {
                $items[] = ['k' => '内存', 'v' => number_format((float) ($ram['percent'] ?? 0), 1, '.', '').'%'];
            }
        } catch (\Throwable) {
        }

        return $items;
    }

    private function skipLine(string $key, string $reason): string
    {
        $label = match ($key) {
            'runtime' => '请求计数',
            'sys.load1', 'sys.load5', 'sys.load15' => '负载',
            'sys.cpu.pct' => 'CPU',
            'sys.mem.used_pct' => '内存',
            'sys.disk.used_pct' => '磁盘',
            'php.memory_pct' => 'PHP 内存',
            default => self::METRIC_LABELS[$key] ?? $key,
        };
        $reason = trim($reason);

        return $reason === '' ? $label : ($label.'：'.$reason);
    }

    /**
     * @param  array{id?: string, keys?: list<string>}  $group
     */
    private function groupEmptyReason(array $group): string
    {
        $caps = $this->capabilityMap();
        foreach ($group['keys'] ?? [] as $key) {
            if (isset($caps[$key])) {
                return $this->skipLine((string) $key, (string) $caps[$key]);
            }
        }
        $id = (string) ($group['id'] ?? '');
        if (in_array($id, ['req', 'slow', 'http'], true) && isset($caps['runtime'])) {
            return $this->skipLine('runtime', (string) $caps['runtime']);
        }

        return '';
    }

    /** @return list<string> */
    private function skippedLines(): array
    {
        $lines = [];
        foreach ($this->capabilityMap() as $key => $reason) {
            $lines[] = $this->skipLine((string) $key, (string) $reason);
        }

        return array_values(array_unique($lines));
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return list<array<string, mixed>>
     */
    private function flatSeries(array $groups): array
    {
        $out = [];
        foreach ($groups as $group) {
            foreach ($group['series'] ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $pts = $item['points'] ?? [];
                if (! is_array($pts) || $pts === []) {
                    continue;
                }
                $out[] = $item;
            }
        }

        return $out;
    }

    /** @param  array<string, mixed>  $input */
    private function blankInput(array $input, string $key): bool
    {
        if (! array_key_exists($key, $input)) {
            return true;
        }
        $v = $input[$key];
        if ($v === null) {
            return true;
        }
        if (is_string($v) && trim($v) === '') {
            return true;
        }

        return false;
    }

    private function optionsReady(): bool
    {
        try {
            return Schema::hasTable('video_options');
        } catch (\Throwable) {
            return false;
        }
    }

    private function rulesReady(): bool
    {
        try {
            return Schema::hasTable('video_monitor_rules');
        } catch (\Throwable) {
            return false;
        }
    }
}

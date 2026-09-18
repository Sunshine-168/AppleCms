<?php

namespace App\Services\Monitor;

use App\Models\Monitor\VideoMonitorEvent;
use App\Models\Monitor\VideoMonitorRule;
use Illuminate\Support\Facades\Schema;

class AlertEngine
{
    public const SEQUENCE_BREAK_SEC = 180;

    /**
     * @return array{evaluated:int,fired:int,resolved:int,skipped:int}
     */
    public static function run(int $now = 0): array
    {
        $now = $now > 0 ? $now : time();
        $stat = ['evaluated' => 0, 'fired' => 0, 'resolved' => 0, 'skipped' => 0];
        if (! self::ready()) {
            return $stat;
        }
        $rules = VideoMonitorRule::query()->where('status', 1)->orderBy('id')->get();
        foreach ($rules as $rule) {
            try {
                $stat['evaluated']++;
                $row = $rule->toArray();
                $res = self::evaluate($row, $now);
                if ($res === null) {
                    $stat['skipped']++;
                    continue;
                }
                if ($res['hit']) {
                    self::handleHit($row, $res, $now, $stat);
                } else {
                    self::handleClear($row, $res, $now, $stat);
                }
            } catch (\Throwable) {
            }
        }

        return $stat;
    }

    /**
     * @param  array<string, mixed>  $rule
     * @return array{hit:bool,value:float,summary:string}|null
     */
    public static function evaluate(array $rule, int $now = 0): ?array
    {
        $now = $now > 0 ? $now : time();
        $window = max(1, (int) ($rule['window_min'] ?? 5));
        $from = $now - ($window * 60);
        $metric = (string) ($rule['metric_key'] ?? '');
        $agg = (string) ($rule['agg'] ?? 'avg');
        if ($metric === '') {
            return null;
        }
        $fetched = MonitorStore::fetchSeries([$metric], $from, $now, 'min');
        $series = $fetched['series'][$metric] ?? [];
        $value = MonitorStore::aggregate($series, $agg);
        if ($value === null) {
            return null;
        }
        $threshold = (float) ($rule['threshold'] ?? 0);
        $op = (string) ($rule['op'] ?? 'gt');

        return [
            'hit' => self::compare($value, $op, $threshold),
            'value' => $value,
            'summary' => sprintf(
                '%s(%s, %dm) = %s',
                $agg,
                $metric,
                $window,
                rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.')
            ),
        ];
    }

    public static function compare(float $value, string $op, float $threshold): bool
    {
        return match ($op) {
            'gte' => $value >= $threshold,
            'lt' => $value < $threshold,
            'lte' => $value <= $threshold,
            default => $value > $threshold,
        };
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    public static function fingerprint(array $rule): string
    {
        return md5((int) ($rule['id'] ?? 0).'|'.(string) ($rule['metric_key'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $rule
     * @param  array{hit:bool,value:float,summary:string}  $res
     * @param  array{evaluated:int,fired:int,resolved:int,skipped:int}  $stat
     */
    private static function handleHit(array $rule, array $res, int $now, array &$stat): void
    {
        $ruleId = (int) ($rule['id'] ?? 0);
        $pendingKey = 'alert.pending.'.$ruleId;
        $raw = MonitorState::getVal($pendingKey, '');
        $state = $raw !== '' ? json_decode($raw, true) : null;
        if (! is_array($state)) {
            $state = ['since' => 0, 'last_hit' => 0];
        }
        $since = (int) ($state['since'] ?? 0);
        $lastHit = (int) ($state['last_hit'] ?? 0);
        if ($since === 0 || ($now - $lastHit) > self::SEQUENCE_BREAK_SEC) {
            $since = $now;
        }
        MonitorState::set($pendingKey, $now, json_encode(['since' => $since, 'last_hit' => $now]) ?: '');
        $forSec = max(0, (int) ($rule['for_min'] ?? 0)) * 60;
        if (($now - $since) < $forSec) {
            return;
        }
        self::fire($rule, $res, $since, $now, $stat);
    }

    /**
     * @param  array<string, mixed>  $rule
     * @param  array{hit:bool,value:float,summary:string}  $res
     * @param  array{evaluated:int,fired:int,resolved:int,skipped:int}  $stat
     */
    private static function handleClear(array $rule, array $res, int $now, array &$stat): void
    {
        $ruleId = (int) ($rule['id'] ?? 0);
        MonitorState::purgeByPrefix('alert.pending.'.$ruleId);
        $active = self::activeEvent($rule);
        if ($active === null) {
            return;
        }
        $active->status = 2;
        $active->closed_at = $now;
        $active->message = mb_substr((string) $res['summary'], 0, 500);
        $active->value = $res['value'];
        $active->save();
        $stat['resolved']++;
    }

    /**
     * @param  array<string, mixed>  $rule
     * @param  array{hit:bool,value:float,summary:string}  $res
     * @param  array{evaluated:int,fired:int,resolved:int,skipped:int}  $stat
     */
    private static function fire(array $rule, array $res, int $since, int $now, array &$stat): void
    {
        $fp = self::fingerprint($rule);
        $active = self::activeEvent($rule);
        if ($active !== null) {
            $silenceSec = max(0, (int) ($rule['silence_min'] ?? 0)) * 60;
            $active->value = $res['value'];
            $active->message = mb_substr((string) $res['summary'], 0, 500);
            $active->save();
            if ($silenceSec > 0 && $active->opened_at > 0 && ($now - (int) $active->opened_at) < $silenceSec) {
                return;
            }

            return;
        }
        VideoMonitorEvent::query()->create([
            'rule_id' => (int) ($rule['id'] ?? 0),
            'fingerprint' => $fp,
            'status' => 1,
            'message' => mb_substr((string) ($rule['name'] ?? '').' '.$res['summary'], 0, 500),
            'value' => $res['value'],
            'opened_at' => $since > 0 ? $since : $now,
            'closed_at' => 0,
            'acked_at' => 0,
        ]);
        $stat['fired']++;
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    private static function activeEvent(array $rule): ?VideoMonitorEvent
    {
        $fp = self::fingerprint($rule);

        return VideoMonitorEvent::query()
            ->where('fingerprint', $fp)
            ->where('status', 1)
            ->orderByDesc('id')
            ->first();
    }

    private static function ready(): bool
    {
        try {
            return Schema::hasTable('video_monitor_rules') && Schema::hasTable('video_monitor_events');
        } catch (\Throwable) {
            return false;
        }
    }
}

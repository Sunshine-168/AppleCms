<?php


namespace App\Services\Admin\System;

use App\Models\System\SysScheduleLogModel;
use App\Models\System\SysScheduleModel;
use App\Support\Plugins\PluginHost;
use App\Support\Utils\Result;
use Cron\CronExpression;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class SysScheduleService
{

    public SysScheduleModel $sysScheduleModel;

    public function __construct()
    {
        $this->sysScheduleModel = new SysScheduleModel();
    }

    /** @return array<string, string> */
    public function cronPresets(): array
    {
        return [
            '* * * * *' => '每分钟',
            '0 * * * *' => '每小时',
            '0 */3 * * *' => '每 3 小时',
            '0 */6 * * *' => '每 6 小时',
            '0 4 * * *' => '每天凌晨 4 点',
            '0 3 * * *' => '每天凌晨 3 点',
            '0 2 * * *' => '每天凌晨 2 点',
            '0 6 * * *' => '每天早上 6 点',
        ];
    }

    /**
     * @return list<array{key:string,label:string,hint:string,type:string,command:string,params:string,cron:string}>
     */
    public function jobPresets(): array
    {
        $jobs = [
            [
                'key' => 'baidu',
                'label' => '百度推送',
                'hint' => '推已发布地址，要先在搜索推送里填 Token',
                'type' => 'artisan',
                'command' => 'video:baidu-push',
                'params' => '--limit=50',
                'cron' => '0 4 * * *',
            ],
            [
                'key' => 'html',
                'label' => '写出静态页',
                'hint' => '生成 public/html，磁盘静态页要先打开',
                'type' => 'artisan',
                'command' => 'video:html-make',
                'params' => '',
                'cron' => '0 3 * * *',
            ],
            [
                'key' => 'backup',
                'label' => '每天备份库',
                'hint' => '导出一份到磁盘，只留最近几份。本机 sqlite 是复制文件',
                'type' => 'artisan',
                'command' => 'video:db-backup',
                'params' => '--keep=7',
                'cron' => '0 3 * * *',
            ],
        ];
        foreach ($this->pluginJobs() as $job) {
            $jobs[] = [
                'key' => 'plugin-'.$job['plugin'].'-'.$job['id'],
                'label' => (string) $job['label'],
                'hint' => ((string) ($job['plugin_label'] ?? '') !== '' ? $job['plugin_label'].' · ' : '').(string) ($job['hint'] ?? ''),
                'type' => 'artisan',
                'command' => 'plugin:run',
                'params' => (string) $job['params'],
                'cron' => (string) ($job['cron'] ?? '0 4 * * *'),
            ];
        }

        return $jobs;
    }

    /**
     * @return array<string, string>
     */
    public function artisanCommands(): array
    {
        $cmds = [
            'video:baidu-push' => '百度推送',
            'video:html-make' => '写出静态页',
            'video:collect' => '采集入库（要填源 ID）',
            'video:collect-due' => '到期采集',
            'video:publish-due' => '到期上架',
            'video:hits-reset' => '人气日清',
            'stats:prune' => '访问统计清理',
            'video:db-backup' => '备份数据库',
            'monitor:tick' => '监控采集',
        ];
        foreach ($this->pluginJobs() as $job) {
            $cmds['plugin:run '.$job['params']] = $job['label'].'（'.$job['plugin_label'].'）';
        }

        return $cmds;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pluginJobs(): array
    {
        try {
            return app(PluginHost::class)->scheduleJobs();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function pageBoard(): array
    {
        $php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
        $artisan = base_path('artisan');
        $cronLine = '* * * * * '.$php.' '.$artisan.' schedule:run';
        $collectN = 0;
        try {
            if (Schema::hasTable('video_collect_tasks')) {
                $collectN = (int) \App\Models\Video\VideoCollectTask::query()->count();
            }
        } catch (\Throwable) {
            $collectN = 0;
        }
        $rows = [];
        try {
            if (Schema::hasTable('sys_schedule')) {
                $rows = SysScheduleModel::query()->orderByDesc('sort')->orderByDesc('id')->limit(100)->get()->toArray();
            }
        } catch (\Throwable) {
            $rows = [];
        }
        $tasks = [];
        $usedCommands = [];
        $idleOn = 0;
        foreach ($rows as $row) {
            $item = $this->presentTask($row);
            $tasks[] = $item;
            $cmd = (string) ($item['command'] ?? '');
            $usedCommands[$cmd] = true;
            if ($cmd === 'plugin:run') {
                $usedCommands[trim($cmd.' '.($item['params_text'] ?? ''))] = true;
            }
            if (! empty($item['on']) && (int) ($row['last_run_time'] ?? 0) < 1) {
                $idleOn++;
            }
        }
        $this->attachRecentLogs($tasks);
        $presets = [];
        foreach ($this->jobPresets() as $preset) {
            $usedKey = $preset['command'] === 'plugin:run'
                ? trim($preset['command'].' '.($preset['params'] ?? ''))
                : (string) $preset['command'];
            $preset['added'] = isset($usedCommands[$usedKey]);
            $presets[] = $preset;
        }

        return [
            'cron_line' => $cronLine,
            'php_bin' => $php,
            'collect_n' => $collectN,
            'tasks' => $tasks,
            'idle_on' => $idleOn,
            'presets' => $presets,
            'cron_presets' => $this->cronPresets(),
            'collect_note' => $collectN > 0 ? ('定时采集里已有 '.$collectN.' 条') : '',
            'builtins' => [
                ['label' => '到期采集', 'when' => '每分钟', 'hint' => '跑「定时采集」里到期的任务', 'url' => '/admin/video/collect_tasks'],
                ['label' => '到期上架', 'when' => '每分钟', 'hint' => '到点把定时发布的片子上架', 'url' => ''],
                ['label' => '人气日清', 'when' => '每天 00:05', 'hint' => '把今日人气归零', 'url' => ''],
                ['label' => '访问统计清理', 'when' => '每天 03:20', 'hint' => '删过期统计', 'url' => '/admin/stats'],
                ['label' => '监控', 'when' => '每分钟', 'hint' => '采指标、评估告警。曲线在系统里的监控。', 'url' => '/admin/system/runtime'],
            ],
            'artisan_cmds' => $this->artisanCommands(),
            'ui' => [
                'title' => '定时任务',
                'collect' => '定时采集',
                'push' => '搜索推送',
                'cache' => '缓存',
                'lead' => '采集片子用左边那栏。备份、推送、插件任务（数据统计这类）加在下面。本机 artisan serve 不会执行。服务器要每分钟跑下面这条。',
                'install' => '先装计划',
                'install_hint' => 'Linux / 宝塔把这一行放进 crontab。Windows 用任务计划每分钟启动同一条。改完任务不用重装这一行。',
                'copy' => '复制',
                'copied' => '已复制',
                'del_confirm' => '删除这条任务？',
                'make' => '静态生成',
                'idle' => '条开着的任务还从没跑过。多半是还没装上面这条计划。可以先点「立刻跑」试一次。',
                'builtin' => '系统自带',
                'builtin_hint' => '代码里写死的，不用在这再加一条。装好计划就会跑。成败和耗时不记在下面这份记录里。',
                'look' => '去看看',
                'custom' => '自己加的',
                'custom_hint' => '每天推百度、写出静态页、备份库可以加在这里。启用中的插件若声明了定时任务，也会出现在上面。按资源站采片子请点「采集片子」。',
                'add_custom' => '自定义',
                'empty' => '还没有自己加的任务。',
                'empty_hint' => '系统自带的不用加。要每天推百度，点上面的按钮。',
                'on' => '开着',
                'off' => '已停',
                'last_fail' => '上次失败',
                'next' => '下次',
                'ran' => '上次',
                'recent' => '最近几次',
                'no_duration' => '升级前只记下了时间，没有耗时。再跑一次就会有。',
                'ok' => '成功',
                'fail' => '失败',
                'output' => '输出',
                'run' => '立刻跑',
                'stop' => '停用',
                'start' => '启用',
                'edit' => '改',
                'delete' => '删除',
                'form_add' => '加一条',
                'name' => '名称',
                'name_ph' => '例如 每天推百度',
                'kind' => '要跑什么',
                'kind_artisan' => '本站命令 / 插件任务',
                'kind_http' => '访问一个网址',
                'kind_shell' => '服务器命令（高级）',
                'cmd' => '命令',
                'params' => '参数',
                'params_ph' => '可空，例如 --limit=50 或采集源 ID',
                'url' => '网址',
                'http_hint' => '只发 GET。要 http 或 https。',
                'shell_hint' => '填错可能把站点搞停。能用本站命令就别写这个。',
                'every' => '多久跑一次',
                'cron_custom' => '自定义',
                'keep_on' => '保存后开着',
                'save' => '保存',
                'cancel' => '取消',
                'added_prefix' => '已有',
                'add_prefix' => '加',
            ],
        ];
    }

    /**
     * 获取任务列表
     */
    public function getScheduleLists(string $name, string $type, mixed $status, int $limit): array
    {

        if ($limit < 1)
        {
            $limit = 50;
        }

        $where = [];

        if ($name = trim($name))
        {
            $where[] = ['name', 'like', '%'.$name.'%'];
        }

        if ($type = trim($type))
        {
            $where[] = ['type', '=', $type];
        }

        if ($status !== '' && $status !== null)
        {
            $where[] = ['status', '=', (int)$status];
        }

        $data = $this->sysScheduleModel->paginates(
            $where,
            '*',
            $limit,
            ['sort' => 'desc', 'id' => 'desc']
        );

        foreach ($data['data'] as &$item)
        {
            $item = $this->presentTask($item);
        }
        unset($item);

        return Result::success($data);
    }


    /**
     * 保存任务
     */
    public function saveSchedule(
        int $id,
        string $name,
        string $code,
        string $type,
        string $command,
        string $params,
        string $cronExpression,
        string $timezone,
        int $status,
        int $withoutOverlapping,
        int $onOneServer,
        int $runInMaintenance,
        int $timeout,
        int $maxAttempts,
        string $remark,
        int $sort
    ): array {

        $name = trim($name);
        if ($name === '')
        {
            return Result::fail('请输入任务名称');
        }

        if (!in_array($type, ['artisan', 'shell', 'http'], true))
        {
            return Result::fail('任务类型不正确');
        }

        $command = trim($command);
        if ($command === '')
        {
            return Result::fail('请输入执行内容');
        }

        $checked = $this->normalizeJob($type, $command, $params);
        if ((int) ($checked['code'] ?? 1) !== 0) {
            return $checked;
        }
        $command = (string) ($checked['data']['command'] ?? $command);
        $params = (string) ($checked['data']['params'] ?? $params);
        $type = (string) ($checked['data']['type'] ?? $type);

        if (mb_strlen($command) > 2000)
        {
            return Result::fail('执行内容过长');
        }

        $cronExpression = trim($cronExpression);
        if ($cronExpression === '') {
            $cronExpression = '0 4 * * *';
        }

        try {
            if (! CronExpression::isValidExpression($cronExpression)) {
                return Result::fail('cron表达式格式错误');
            }
        } catch (\Throwable) {
            return Result::fail('cron表达式格式错误');
        }

        if ($timezone === '' || !in_array($timezone, timezone_identifiers_list()))
        {
            $timezone = 'Asia/Shanghai';
        }

        $timeout = max(0, min($timeout, 3600));
        $maxAttempts = max(1, min($maxAttempts, 50));
        $sort = max(0, (int)$sort);

        $time = time();

        $nextRunTime = 0;

        try {

            $cron = new CronExpression($cronExpression);

            $nextRunTime = $cron->getNextRunDate()->getTimestamp();

        } catch (\Throwable) {
        }

        $update = [

            'name' => $name,
            'code' => trim($code),
            'type' => $type,
            'command' => $command,
            'params' => (string)$params,
            'cron_expression' => $cronExpression,
            'timezone' => $timezone,
            'status' => $status ? 1 : 0,
            'without_overlapping' => $withoutOverlapping ? 1 : 0,
            'on_one_server' => $onOneServer ? 1 : 0,
            'run_in_maintenance' => $runInMaintenance ? 1 : 0,
            'timeout' => $timeout,
            'max_attempts' => $maxAttempts,
            'next_run_time' => $nextRunTime,
            'remark' => trim($remark),
            'sort' => $sort,
            'update_time' => $time,
            'update_at' => date('Y-m-d H:i:s', $time),

        ];

        $existsWhere = [['name', '=', $name]];

        if ($id > 0) {
            $existsWhere[] = ['id', '<>', $id];
        }

        if ($this->sysScheduleModel->existsBy($existsWhere))
        {
            return Result::fail('任务名称已存在');
        }

        if ($id > 0)
        {

            $ok = $this->sysScheduleModel->updateById($id, $update);

            if (!$ok) {
                return Result::fail('保存失败');
            }

            return Result::success(['id' => $id], '保存成功');
        }

        $insert = $update;

        $insert['create_time'] = $time;
        $insert['create_at'] = date('Y-m-d H:i:s', $time);

        $newId = $this->sysScheduleModel->insertsGetId($insert);

        if (!$newId) {
            return Result::fail('保存失败');
        }

        return Result::success(['id' => $newId], '保存成功');
    }


    /**
     * 删除任务
     */
    public function deleteSchedule(int $id): array
    {

        if ($id <= 0)
        {
            return Result::fail('参数错误');
        }

        $row = $this->sysScheduleModel->findById($id);

        if (!$row)
        {
            return Result::fail('任务不存在');
        }

        $ok = $this->sysScheduleModel->deleteById($id);
        if ($ok) {
            $this->forgetRunLogs($id);
        }

        return $ok
            ? Result::success([], '删除成功')
            : Result::fail('删除失败');
    }


    /**
     * 修改状态
     */
    public function updateScheduleStatus(int $id, int $status): array
    {

        if ($id <= 0) {
            return Result::fail('参数错误');
        }

        $row = $this->sysScheduleModel->findById($id);
        if ($row === []) {
            return Result::fail('任务不存在');
        }

        $ok = $this->sysScheduleModel->updateById($id, [

            'status' => $status ? 1 : 0,
            'update_time' => time(),
            'update_at' => date('Y-m-d H:i:s')

        ]);

        return $ok
            ? Result::success([], $status ? '已开着，到点会跑' : '已停，到点不跑')
            : Result::fail('没能改状态');
    }


    /**
     * 立即执行任务
     */
    public function runScheduleOnce(int $id): array
    {

        $task = $this->sysScheduleModel->findById($id);

        if (!$task) {
            return Result::fail('任务不存在');
        }

        $lock = null;

        if ($task['without_overlapping'])
        {

            $lock = Cache::lock('schedule_lock_' . $id, 600);

            if (!$lock->get()) {
                return Result::fail('任务正在执行中');
            }
        }

        $status = 2;
        $error = '';
        $output = '';
        $started = microtime(true);

        try {

            if ($task['type'] === 'artisan')
            {

                $output = $this->runArtisanCommand((string) $task['command'], (string) ($task['params'] ?? ''));

            } elseif ($task['type'] === 'shell')
            {

                $output = $this->runShellCommand($task['command'], $task['timeout']);

            } elseif ($task['type'] === 'http')
            {

                $output = $this->runHttpRequest($task['command'], $task['timeout']);

            }

            $status = 1;

        } catch (\Throwable $e) {

            $error = $e->getMessage();

            Log::error('schedule run error', [
                'task' => $task['name'],
                'error' => $error
            ]);
        }

        if ($lock) {
            $lock->release();
        }

        $ended = microtime(true);
        $durationMs = max(0, (int) round(($ended - $started) * 1000));
        $ranAt = (int) $started;

        $nextRun = 0;

        try {

            $cron = new CronExpression($task['cron_expression']);

            $nextRun = $cron->getNextRunDate()->getTimestamp();

        } catch (\Throwable) {
        }

        $patch = [

            'last_run_time' => $ranAt,
            'next_run_time' => $nextRun,
            'update_time' => time(),
            'update_at' => date('Y-m-d H:i:s')

        ];
        try {
            if (Schema::hasColumn('sys_schedule', 'last_status')) {
                $patch['last_status'] = $status;
            }
            if (Schema::hasColumn('sys_schedule', 'last_error')) {
                $patch['last_error'] = mb_substr($error, 0, 255);
            }
            if (Schema::hasColumn('sys_schedule', 'last_duration_ms')) {
                $patch['last_duration_ms'] = $durationMs;
            }
        } catch (\Throwable) {
        }

        $this->sysScheduleModel->updateById($id, $patch);
        $this->recordRunLog($id, $status, $durationMs, $output, $error);

        return $status === 1
            ? Result::success([
                'output' => $output,
                'duration_ms' => $durationMs,
                'duration_text' => $this->durationText($durationMs),
            ], '跑完了，'.$this->durationText($durationMs))
            : Result::fail($error !== '' ? $error : '执行失败', [
                'duration_ms' => $durationMs,
                'duration_text' => $this->durationText($durationMs),
            ]);
    }



    /**
     * 执行Artisan命令
     * @param string $command
     * @param string $params
     * @return string
     */
    private function runArtisanCommand(string $command, string $params = ''): string
    {
        [$name, $args] = $this->parseArtisan($command, $params);
        $deny = $this->artisanDenied($name, $command, $params);
        if ($deny !== '') {
            throw new \RuntimeException($deny);
        }
        if ($name === 'plugin:run') {
            $args = $this->pluginRunArgs($command, $params);
        }

        $code = Artisan::call($name, $args);

        if ($code !== 0) {
            throw new \RuntimeException(trim(Artisan::output()) ?: '命令返回 '.$code);
        }

        return Artisan::output();
    }

    /**
     * 执行Shell命令
     * @param string $command
     * @param int $timeout
     * @return string
     */
    private function runShellCommand(string $command, int $timeout): string
    {

        $process = Process::fromShellCommandline($command);

        $process->setTimeout($timeout > 0 ? $timeout : null);

        $process->run();

        if (!$process->isSuccessful()) {

            throw new \RuntimeException(
                $process->getErrorOutput() ?: $process->getOutput()
            );
        }

        return $process->getOutput();
    }

    /**
     * 执行HTTP请求
     * @param string $url
     * @param int $timeout
     * @return string
     */
    private function runHttpRequest(string $url, int $timeout): string
    {

        $url = trim($url);
        if (! preg_match('#^https?://#i', $url)) {
            throw new \RuntimeException('只接受 http 或 https 地址');
        }

        $resp = Http::timeout($timeout ?: 30)
            ->acceptJson()
            ->get($url);

        if (!$resp->successful()) {
            throw new \RuntimeException('HTTP错误: ' . $resp->status());
        }

        return $resp->body();
    }

    /** @param  array<string, mixed>  $row */
    public function presentTask(array $row): array
    {
        $id = (int) ($row['id'] ?? 0);
        $type = (string) ($row['type'] ?? 'artisan');
        $cron = trim((string) ($row['cron_expression'] ?? ''));
        $status = (int) ($row['status'] ?? 0);
        $lastAt = (int) ($row['last_run_time'] ?? 0);
        $lastStatus = (int) ($row['last_status'] ?? 0);
        $lastErr = trim((string) ($row['last_error'] ?? ''));
        $durationMs = (int) ($row['last_duration_ms'] ?? 0);
        $logged = $lastStatus === 1 || $lastStatus === 2;
        $presets = $this->cronPresets();
        $row['id'] = $id;
        $row['type'] = $type;
        $row['type_label'] = match ($type) {
            'http' => '访问网址',
            'shell' => '服务器命令',
            default => '本站命令',
        };
        $row['cron_label'] = $presets[$cron] ?? ($cron !== '' ? $cron : '未设周期');
        $row['status'] = $status;
        $row['on'] = $status === 1;
        $row['last_run_text'] = $lastAt > 0 ? date('Y-m-d H:i', $lastAt) : '还没跑过';
        $row['last_ok'] = $lastStatus === 1;
        $row['last_fail'] = $lastStatus === 2;
        $row['last_status_text'] = $lastAt < 1 ? '还没跑过' : ($lastStatus === 1 ? '成功' : ($lastStatus === 2 ? '失败' : '没记下成败'));
        $row['last_error'] = $lastErr;
        $row['duration_ms'] = $durationMs;
        $row['duration_text'] = $logged ? $this->durationText($durationMs) : '';
        $row['next_run_text'] = $this->nextRunText($cron, $status);
        $row['next_text'] = $row['next_run_text'];
        $row['last_text'] = $this->lastRunSummary($lastAt, $lastStatus, $durationMs);
        $row['legacy_run'] = $lastAt > 0 && ! $logged;
        $row['logs'] = [];
        $row['command_label'] = $this->commandLabel($type, (string) ($row['command'] ?? ''), (string) ($row['params'] ?? ''));
        $row['cron'] = $cron;
        $row['params_text'] = trim((string) ($row['params'] ?? ''));

        return $row;
    }

    /**
     * @return array{code:int,msg:string,data?:array{type:string,command:string,params:string}}
     */
    protected function normalizeJob(string $type, string $command, string $params): array
    {
        $command = trim($command);
        $params = trim($params);
        if ($type === 'artisan') {
            $command = (string) preg_replace('/^(php\s+)?artisan\s+/i', '', $command);
            [$name, $args] = $this->parseArtisan($command, $params);
            $deny = $this->artisanDenied($name, $command, $params);
            if ($deny !== '') {
                return Result::fail($deny);
            }
            if ($name === 'plugin:run') {
                $run = $this->pluginRunArgs($command, $params);

                return Result::success([
                    'type' => 'artisan',
                    'command' => 'plugin:run',
                    'params' => $run['plugin'].' '.$run['job'],
                ]);
            }
            $command = $name;
            $params = $this->formatArtisanParams($args);

            return Result::success(['type' => 'artisan', 'command' => $command, 'params' => $params]);
        }
        if ($type === 'http') {
            if (! preg_match('#^https?://#i', $command)) {
                return Result::fail('网址要以 http:// 或 https:// 开头');
            }

            return Result::success(['type' => 'http', 'command' => $command, 'params' => '']);
        }
        if ($type === 'shell') {
            if ($command === '') {
                return Result::fail('请填写要在服务器上跑的命令');
            }

            return Result::success(['type' => 'shell', 'command' => $command, 'params' => $params]);
        }

        return Result::fail('任务类型不正确');
    }

    protected function artisanDenied(string $name, string $command = '', string $params = ''): string
    {
        $name = trim($name);
        if ($name === '') {
            return '请选择要跑的本站命令';
        }
        if ($name === 'plugin:run') {
            try {
                $this->pluginRunArgs($command !== '' ? $command : $name, $params);
            } catch (\Throwable $e) {
                return $e->getMessage();
            }

            return '';
        }
        $allow = [
            'video:baidu-push', 'video:collect-due', 'video:publish-due',
            'video:hits-reset', 'video:html-make', 'video:collect', 'stats:prune',
            'video:db-backup', 'monitor:tick',
        ];
        if (in_array($name, $allow, true)) {
            return '';
        }
        $deny = ['migrate', 'db:', 'tinker', 'env', 'down', 'up', 'serve', 'key:', 'make:', 'queue:', 'vendor:', 'package:', 'inspire', 'test', 'plugin:'];
        foreach ($deny as $prefix) {
            if ($name === rtrim($prefix, ':') || str_starts_with($name, $prefix)) {
                return '这条命令不能当定时任务跑';
            }
        }

        return '只接受本站命令，或已启用插件在 plugin.json 里声明的任务';
    }

    /**
     * @return array{plugin:string,job:string}
     */
    protected function pluginRunArgs(string $command, string $params): array
    {
        $line = trim($command.' '.$params);
        $line = (string) preg_replace('/^(php\s+)?artisan\s+/i', '', $line);
        $parts = preg_split('/\s+/', $line) ?: [];
        if (($parts[0] ?? '') === 'plugin:run') {
            array_shift($parts);
        }
        $plugin = strtolower(trim((string) ($parts[0] ?? '')));
        $job = strtolower(trim((string) ($parts[1] ?? '')));
        if ($plugin === '' || $job === '') {
            throw new \RuntimeException('插件任务要写成 plugin:run 插件id 任务id');
        }
        foreach ($this->pluginJobs() as $row) {
            if (($row['plugin'] ?? '') === $plugin && ($row['id'] ?? '') === $job) {
                return ['plugin' => $plugin, 'job' => $job];
            }
        }

        throw new \RuntimeException('没有这条插件任务，或插件没启用');
    }

    /**
     * @return array{0:string,1:array<string|int, mixed>}
     */
    protected function parseArtisan(string $command, string $params): array
    {
        $line = trim($command.' '.$params);
        $line = (string) preg_replace('/^(php\s+)?artisan\s+/i', '', $line);
        $parts = preg_split('/\s+/', $line) ?: [];
        $name = (string) array_shift($parts);
        $args = [];
        $count = count($parts);
        for ($i = 0; $i < $count; $i++) {
            $p = (string) $parts[$i];
            if (str_starts_with($p, '--')) {
                $eq = strpos($p, '=');
                if ($eq !== false) {
                    $args[substr($p, 0, $eq)] = substr($p, $eq + 1);
                    continue;
                }
                $next = $parts[$i + 1] ?? null;
                if (is_string($next) && $next !== '' && ! str_starts_with($next, '-')) {
                    $args[$p] = $next;
                    $i++;
                } else {
                    $args[$p] = true;
                }
                continue;
            }
            if (! isset($args['id'])) {
                $args['id'] = $p;
            }
        }

        return [$name, $args];
    }

    /** @param  array<string|int, mixed>  $args */
    protected function formatArtisanParams(array $args): string
    {
        $out = [];
        foreach ($args as $k => $v) {
            if (is_int($k)) {
                $out[] = (string) $v;
                continue;
            }
            if ($k === 'id') {
                $out[] = (string) $v;
                continue;
            }
            if ($v === true) {
                $out[] = (string) $k;
                continue;
            }
            $out[] = $k.'='.(string) $v;
        }

        return implode(' ', $out);
    }

    /**
     * @param  list<array<string, mixed>>  $tasks
     */
    protected function attachRecentLogs(array &$tasks): void
    {
        $ids = [];
        foreach ($tasks as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        if ($ids === []) {
            return;
        }
        try {
            if (! Schema::hasTable('sys_schedule_log')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }
        $rows = SysScheduleLogModel::query()
            ->whereIn('schedule_id', $ids)
            ->orderByDesc('id')
            ->limit(800)
            ->get()
            ->toArray();
        $map = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['schedule_id'] ?? 0);
            if ($sid < 1) {
                continue;
            }
            if (count($map[$sid] ?? []) >= 8) {
                continue;
            }
            $map[$sid][] = $this->presentLog($row);
        }
        foreach ($tasks as &$task) {
            $task['logs'] = $map[(int) ($task['id'] ?? 0)] ?? [];
        }
        unset($task);
    }

    /** @param  array<string, mixed>  $row */
    protected function presentLog(array $row): array
    {
        $status = (int) ($row['status'] ?? 0);
        $ms = (int) ($row['duration_ms'] ?? 0);
        $at = (int) ($row['create_time'] ?? 0);
        $ok = $status === 1;

        return [
            'ok' => $ok,
            'status_text' => $ok ? '成功' : '失败',
            'duration_ms' => $ms,
            'duration_text' => $this->durationText($ms),
            'time_text' => $at > 0 ? date('Y-m-d H:i:s', $at) : '',
            'output' => trim((string) ($row['output'] ?? '')),
            'error' => trim((string) ($row['error'] ?? '')),
        ];
    }

    protected function lastRunSummary(int $lastAt, int $lastStatus, int $durationMs): string
    {
        if ($lastAt < 1) {
            return '还没跑过';
        }
        $when = date('Y-m-d H:i', $lastAt);
        if ($lastStatus !== 1 && $lastStatus !== 2) {
            return '上次跑过 · '.$when.' · 没记下成败和耗时';
        }
        $ok = $lastStatus === 1 ? '成功' : '失败';

        return '上次'.$ok.' · '.$this->durationText($durationMs).' · '.$when;
    }

    protected function durationText(int $ms): string
    {
        $ms = max(0, $ms);
        if ($ms < 100) {
            return '不到 0.1 秒';
        }
        if ($ms < 1000) {
            return '用了 '.$ms.' 毫秒';
        }
        if ($ms < 60000) {
            $sec = round($ms / 1000, 1);
            $text = rtrim(rtrim(number_format($sec, 1, '.', ''), '0'), '.');

            return '用了 '.$text.' 秒';
        }
        $m = intdiv($ms, 60000);
        $s = (int) round(($ms % 60000) / 1000);
        if ($s < 1) {
            return '用了 '.$m.' 分钟';
        }

        return '用了 '.$m.' 分 '.$s.' 秒';
    }

    protected function recordRunLog(int $scheduleId, int $status, int $durationMs, string $output, string $error): void
    {
        if ($scheduleId < 1) {
            return;
        }
        try {
            if (! Schema::hasTable('sys_schedule_log')) {
                return;
            }
            $output = trim(preg_replace('/\s+/u', ' ', $output) ?? $output);
            SysScheduleLogModel::query()->insert([
                'schedule_id' => $scheduleId,
                'status' => $status === 1 ? 1 : 2,
                'duration_ms' => max(0, $durationMs),
                'output' => mb_substr($output, 0, 500),
                'error' => mb_substr($error, 0, 255),
                'create_time' => time(),
            ]);
            $keep = SysScheduleLogModel::query()
                ->where('schedule_id', $scheduleId)
                ->orderByDesc('id')
                ->skip(40)
                ->take(200)
                ->pluck('id');
            if ($keep->isNotEmpty()) {
                SysScheduleLogModel::query()->whereIn('id', $keep)->delete();
            }
        } catch (\Throwable) {
        }
    }

    protected function forgetRunLogs(int $scheduleId): void
    {
        try {
            if (Schema::hasTable('sys_schedule_log')) {
                SysScheduleLogModel::query()->where('schedule_id', $scheduleId)->delete();
            }
        } catch (\Throwable) {
        }
    }

    protected function nextRunText(string $cron, int $status): string
    {
        if ($status !== 1) {
            return '已停';
        }
        $cron = trim($cron);
        if ($cron === '') {
            return '未设周期';
        }
        try {
            return (new CronExpression($cron))->getNextRunDate()->format('Y-m-d H:i');
        } catch (\Throwable) {
            return '周期无效';
        }
    }

    protected function commandLabel(string $type, string $command, string $params): string
    {
        $command = trim($command);
        $params = trim($params);
        if ($type === 'http') {
            return $command;
        }
        if ($type === 'shell') {
            return $command;
        }
        foreach ($this->jobPresets() as $preset) {
            $presetParams = trim((string) ($preset['params'] ?? ''));
            if ($preset['command'] !== $command) {
                continue;
            }
            if ($command !== 'plugin:run' || $presetParams === $params) {
                return $preset['label'];
            }
        }
        $map = [
            'video:baidu-push' => '百度推送',
            'video:html-make' => '写出静态页',
            'video:collect-due' => '到期采集',
            'video:publish-due' => '到期上架',
            'video:hits-reset' => '人气日清',
            'video:collect' => '采集入库',
            'stats:prune' => '访问统计清理',
            'video:db-backup' => '备份数据库',
            'monitor:tick' => '监控采集',
        ];

        $label = $map[$command] ?? $command;

        return $params !== '' ? $label.' '.$params : $label;
    }

}


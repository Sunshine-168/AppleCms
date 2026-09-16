<?php
namespace app\common\util;

use think\Db;

/**
 * 服务器性能指标采集器。
 *
 * 铁律：每一项独立 try/catch，任何一项失败都不 fatal、不影响其他项。
 * 拿不到的指标直接不写行（稀疏即语义），并在 skipped 里给出原因，
 * 后台据此隐藏对应图表，而不是画一条恒为 0 的假曲线。
 *
 * shell_exec 不是主路径（monitor.allow_shell 默认 '0'）：
 * 太多主机禁用它，而且它是 RCE 攻击面。
 */
class MonitorCollector
{
    /**
     * @return array ['metrics' => [['k'=>..,'t'=>..,'v'=>..]], 'skipped' => ['php.fpm' => '原因']]
     */
    public static function collect()
    {
        $metrics = [];
        $skipped = [];

        // 桶目录写不进去 = 请求埋点全部静默失效。
        // 这是最需要被看见的一种故障：它不会报错，只会让监控安静地变成摆设。
        if (!self::runtimeWritable()) {
            $skipped['runtime'] = 'RUNTIME_PATH/monitor is not writable by the web process — '
                . 'request metrics and abnormal-access tracking are silently disabled. '
                . 'Check ownership (a CLI script run as root may have created it).';
        }

        self::collectCpu($metrics, $skipped);
        self::collectLoad($metrics, $skipped);
        self::collectMemory($metrics, $skipped);
        self::collectDisk($metrics, $skipped);
        self::collectUptime($metrics, $skipped);
        self::collectFpm($metrics, $skipped);
        self::collectOpcache($metrics, $skipped);
        self::collectDb($metrics, $skipped);

        return ['metrics' => $metrics, 'skipped' => $skipped];
    }

    /**
     * 能力探测结果，供后台「能力面板」展示。
     *
     * @return array
     */
    public static function capabilities()
    {
        $caps = [];
        $caps['proc_stat']   = @is_readable('/proc/stat');
        $caps['cgroup_v2']   = @is_readable('/sys/fs/cgroup/cpu.stat');
        $caps['cgroup_v1']   = @is_readable('/sys/fs/cgroup/cpuacct/cpuacct.usage');
        $caps['loadavg']     = function_exists('sys_getloadavg');
        $caps['meminfo']     = @is_readable('/proc/meminfo');
        $caps['uptime']      = @is_readable('/proc/uptime');
        $caps['fpm']         = function_exists('fpm_get_status');
        $caps['opcache']     = function_exists('opcache_get_status');
        $caps['disk']        = function_exists('disk_free_space');
        $caps['cpu_scope']   = self::cpuScope();
        $caps['runtime_writable'] = self::runtimeWritable();
        return $caps;
    }

    /**
     * 分钟桶目录是否可写。
     *
     * ★ 为什么必须单独探测并上报 ★
     * 请求路径上的埋点是 fail-open 的：任何异常都被 catch 吞掉，
     * 绝不能因为监控自己出问题就把网站弄坏。代价是 —— 一旦
     * runtime/monitor 因为权限问题写不进去（典型场景：站长以 root 跑过
     * CLI 脚本，把目录建成了 root 所有，而 web 进程是 www-data），
     * 埋点就会安静地什么都不做：没有报错、没有日志、后台看起来一切正常，
     * 但监控实际上已经瞎了。
     *
     * 「看起来在保护你、其实没有」是最危险的失败模式。
     * 所以必须主动探测并把它摆到站长眼前。
     *
     * @return bool
     */
    public static function runtimeWritable()
    {
        try {
            $dir = rtrim(RUNTIME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'monitor';

            // ★ 探测必须零副作用，绝不能自己去 mkdir ★
            // 采集器会以 CLI（可能是 root）被调用，一旦在这里把目录建出来，
            // 目录的属主就是 root —— 而真正需要写它的 web 进程是 www-data，
            // 于是这个「用来发现权限问题」的探测，自己制造了那个权限问题。
            // 目录还不存在时，只判断父目录能不能建；由真正需要它的进程去创建。
            if (!is_dir($dir)) {
                return is_writable(rtrim(RUNTIME_PATH, '/\\'));
            }
            return is_writable($dir);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * CPU 采集范围：容器里 /proc/stat 是宿主的，优先用 cgroup。
     *
     * @return string 'cgroup2' | 'cgroup1' | 'host' | 'none'
     */
    private static function cpuScope()
    {
        if (@is_readable('/sys/fs/cgroup/cpu.stat')) {
            return 'cgroup2';
        }
        if (@is_readable('/sys/fs/cgroup/cpuacct/cpuacct.usage')) {
            return 'cgroup1';
        }
        if (@is_readable('/proc/stat')) {
            return 'host';
        }
        return 'none';
    }

    // ------------------------------------------------------------------
    // CPU：绝不 sleep(1)
    // ------------------------------------------------------------------

    /**
     * cron 跑在 FPM worker 上。sleep(1) 取两次 /proc/stat 会占住一个 worker 整整一秒，
     * 每分钟一次，在高负载时是自伤。
     *
     * 正确做法：把上次的采样存进 mac_monitor_state，用 delta 算。
     * 这不但零阻塞，还更准 —— 得到的是「过去整整一分钟的平均 CPU」，
     * 而不是「某一秒的瞬时值」。首次没有 prev，跳过一次。
     *
     * @param array $metrics
     * @param array $skipped
     * @return void
     */
    private static function collectCpu(array &$metrics, array &$skipped)
    {
        try {
            $scope = self::cpuScope();
            if ($scope === 'none') {
                $skipped['sys.cpu'] = 'no /proc/stat and no cgroup cpu accounting';
                return;
            }

            $now = microtime(true);
            $cur = null;

            if ($scope === 'host') {
                $raw = @file_get_contents('/proc/stat');
                $cur = self::parseProcStat($raw);
                if ($cur === null) {
                    $skipped['sys.cpu'] = '/proc/stat unparsable';
                    return;
                }
                $cur['mode'] = 'jiffies';
            } else {
                $usec = self::readCgroupCpuUsec($scope);
                if ($usec === null) {
                    $skipped['sys.cpu'] = 'cgroup cpu usage unreadable';
                    return;
                }
                $cur = ['mode' => 'usec', 'usage_usec' => $usec, 'wall' => $now];
            }

            $prevRaw = MonitorState::getVal('cpu.prev', '');
            $prev = $prevRaw !== '' ? json_decode($prevRaw, true) : null;
            MonitorState::set('cpu.prev', intval($now), json_encode($cur));

            if (!is_array($prev) || !isset($prev['mode']) || $prev['mode'] !== $cur['mode']) {
                $skipped['sys.cpu'] = 'first sample, need one more interval';
                return;
            }

            $pct = self::cpuPctFromDelta($prev, $cur);
            if ($pct === null) {
                $skipped['sys.cpu'] = 'delta not positive (clock or counter reset)';
                return;
            }

            $metrics[] = ['k' => 'sys.cpu.pct', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $pct];
            if ($scope === 'host' && self::looksContainerized()) {
                // 值仍然采集，但标注它是宿主级的，后台会打标签
                $skipped['sys.cpu.scope'] = 'host-level (containerized, no cgroup cpu.stat)';
            }
        } catch (\Throwable $e) {
            $skipped['sys.cpu'] = 'error: ' . $e->getMessage();
        }
    }

    /**
     * @param string $scope
     * @return float|null 微秒累计值
     */
    private static function readCgroupCpuUsec($scope)
    {
        if ($scope === 'cgroup2') {
            $raw = @file_get_contents('/sys/fs/cgroup/cpu.stat');
            if ($raw === false) {
                return null;
            }
            if (preg_match('/^usage_usec\s+(\d+)/m', $raw, $m)) {
                return floatval($m[1]);
            }
            return null;
        }
        // cgroup v1: cpuacct.usage 单位是纳秒
        $raw = @file_get_contents('/sys/fs/cgroup/cpuacct/cpuacct.usage');
        if ($raw === false) {
            return null;
        }
        $ns = trim($raw);
        if (!preg_match('/^\d+$/', $ns)) {
            return null;
        }
        return floatval($ns) / 1000.0;
    }

    /**
     * @return bool
     */
    private static function looksContainerized()
    {
        return @is_file('/.dockerenv') || @is_dir('/sys/fs/cgroup/memory');
    }

    /**
     * 解析 /proc/stat 的第一行 cpu 累计 jiffies。纯函数，供单测。
     *
     * @param string $content
     * @return array|null ['total'=>int,'idle'=>int]
     */
    public static function parseProcStat($content)
    {
        if (!is_string($content) || $content === '') {
            return null;
        }
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (strpos($line, 'cpu ') !== 0) {
                continue;
            }
            $parts = preg_split('/\s+/', trim($line));
            array_shift($parts); // 去掉 'cpu'
            if (count($parts) < 5) {
                return null;
            }
            $total = 0;
            foreach ($parts as $p) {
                if ($p === '' || !is_numeric($p)) {
                    continue;
                }
                $total += intval($p);
            }
            // idle = idle(索引3) + iowait(索引4)
            $idle = intval($parts[3]) + intval($parts[4]);
            if ($total <= 0) {
                return null;
            }
            return ['total' => $total, 'idle' => $idle];
        }
        return null;
    }

    /**
     * 从两次采样算 CPU 使用率。纯函数，供单测。
     *
     * @param array $prev
     * @param array $cur
     * @return float|null null = delta 非正（时钟回拨或计数器重置）
     */
    public static function cpuPctFromDelta(array $prev, array $cur)
    {
        $mode = isset($cur['mode']) ? $cur['mode'] : 'jiffies';

        if ($mode === 'usec') {
            if (!isset($prev['usage_usec'], $prev['wall'], $cur['usage_usec'], $cur['wall'])) {
                return null;
            }
            $wallDelta = floatval($cur['wall']) - floatval($prev['wall']);
            $usageDelta = floatval($cur['usage_usec']) - floatval($prev['usage_usec']);
            if ($wallDelta <= 0 || $usageDelta < 0) {
                return null;
            }
            $ncpu = self::cpuCount();
            $pct = ($usageDelta / ($wallDelta * 1000000.0 * $ncpu)) * 100.0;
            return round(min(100.0, max(0.0, $pct)), 2);
        }

        if (!isset($prev['total'], $prev['idle'], $cur['total'], $cur['idle'])) {
            return null;
        }
        $totalDelta = intval($cur['total']) - intval($prev['total']);
        $idleDelta = intval($cur['idle']) - intval($prev['idle']);
        if ($totalDelta <= 0) {
            return null;
        }
        $pct = 100.0 * (1.0 - ($idleDelta / $totalDelta));
        return round(min(100.0, max(0.0, $pct)), 2);
    }

    /**
     * @return int
     */
    private static function cpuCount()
    {
        $raw = @file_get_contents('/proc/cpuinfo');
        if (is_string($raw) && $raw !== '') {
            $n = preg_match_all('/^processor\s*:/m', $raw);
            if ($n > 0) {
                return $n;
            }
        }
        return 1;
    }

    // ------------------------------------------------------------------
    // Load / Memory / Disk / Uptime
    // ------------------------------------------------------------------

    private static function collectLoad(array &$metrics, array &$skipped)
    {
        try {
            if (!function_exists('sys_getloadavg')) {
                $skipped['sys.load'] = 'sys_getloadavg() unavailable';
                return;
            }
            $load = sys_getloadavg();
            if (!is_array($load) || count($load) < 3) {
                $skipped['sys.load'] = 'sys_getloadavg() returned unexpected value';
                return;
            }
            $metrics[] = ['k' => 'sys.load1', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round(floatval($load[0]), 2)];
            $metrics[] = ['k' => 'sys.load5', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round(floatval($load[1]), 2)];
            $metrics[] = ['k' => 'sys.load15', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round(floatval($load[2]), 2)];
        } catch (\Throwable $e) {
            $skipped['sys.load'] = 'error: ' . $e->getMessage();
        }
    }

    /**
     * 解析 /proc/meminfo。纯函数，供单测。
     *
     * @param string $content
     * @return array|null ['total_kb'=>int,'avail_kb'=>int,'swap_total_kb'=>int,'swap_free_kb'=>int]
     */
    public static function parseMemInfo($content)
    {
        if (!is_string($content) || $content === '') {
            return null;
        }
        $want = [
            'MemTotal'     => 'total_kb',
            'MemAvailable' => 'avail_kb',
            'MemFree'      => 'free_kb',
            'SwapTotal'    => 'swap_total_kb',
            'SwapFree'     => 'swap_free_kb',
        ];
        $out = [];
        foreach (explode("\n", $content) as $line) {
            $pos = strpos($line, ':');
            if ($pos === false) {
                continue;
            }
            $name = trim(substr($line, 0, $pos));
            if (!isset($want[$name])) {
                continue;
            }
            if (preg_match('/(\d+)/', substr($line, $pos + 1), $m)) {
                $out[$want[$name]] = intval($m[1]);
            }
        }
        if (empty($out['total_kb'])) {
            return null;
        }
        // 老内核没有 MemAvailable，退回 MemFree
        if (!isset($out['avail_kb']) && isset($out['free_kb'])) {
            $out['avail_kb'] = $out['free_kb'];
        }
        if (!isset($out['avail_kb'])) {
            return null;
        }
        return $out;
    }

    private static function collectMemory(array &$metrics, array &$skipped)
    {
        try {
            $raw = @file_get_contents('/proc/meminfo');
            $mem = self::parseMemInfo($raw);
            if ($mem === null) {
                $skipped['sys.mem'] = '/proc/meminfo unreadable or unparsable';
                return;
            }
            $total = $mem['total_kb'];
            $avail = $mem['avail_kb'];
            $usedPct = $total > 0 ? (100.0 * ($total - $avail) / $total) : 0.0;

            $metrics[] = ['k' => 'sys.mem.used_pct', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round($usedPct, 2)];
            $metrics[] = ['k' => 'sys.mem.avail_mb', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round($avail / 1024.0, 2)];

            if (!empty($mem['swap_total_kb'])) {
                $st = $mem['swap_total_kb'];
                $sf = isset($mem['swap_free_kb']) ? $mem['swap_free_kb'] : $st;
                $swapPct = 100.0 * ($st - $sf) / $st;
                $metrics[] = ['k' => 'sys.swap.used_pct', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round($swapPct, 2)];
            }
        } catch (\Throwable $e) {
            $skipped['sys.mem'] = 'error: ' . $e->getMessage();
        }
    }

    private static function collectDisk(array &$metrics, array &$skipped)
    {
        try {
            if (!function_exists('disk_free_space')) {
                $skipped['sys.disk'] = 'disk_free_space() unavailable';
                return;
            }
            foreach (self::diskMounts() as $mount) {
                $free = @disk_free_space($mount);
                $total = @disk_total_space($mount);
                if ($free === false || $total === false || $total <= 0) {
                    $skipped['sys.disk|' . $mount] = 'unreadable (open_basedir?)';
                    continue;
                }
                $usedPct = 100.0 * ($total - $free) / $total;
                $metrics[] = [
                    'k' => 'sys.disk.used_pct|' . $mount,
                    't' => MonitorMetric::TYPE_GAUGE,
                    'v' => round($usedPct, 2),
                ];
                $metrics[] = [
                    'k' => 'sys.disk.free_mb|' . $mount,
                    't' => MonitorMetric::TYPE_GAUGE,
                    'v' => round($free / 1048576.0, 2),
                ];
            }
        } catch (\Throwable $e) {
            $skipped['sys.disk'] = 'error: ' . $e->getMessage();
        }
    }

    /**
     * @return array
     */
    private static function diskMounts()
    {
        $mounts = ['/'];
        $cfg = self::config();
        $extra = isset($cfg['disk_mounts']) ? trim((string)$cfg['disk_mounts']) : '';
        if ($extra !== '') {
            foreach (explode(',', $extra) as $one) {
                $one = trim($one);
                // 只接受绝对路径，且必须真实存在，避免站长填错把 skipped 刷屏
                if ($one !== '' && strpos($one, '/') === 0 && @is_dir($one)) {
                    $mounts[] = $one;
                }
            }
        }
        return array_values(array_unique($mounts));
    }

    private static function collectUptime(array &$metrics, array &$skipped)
    {
        try {
            $raw = @file_get_contents('/proc/uptime');
            if ($raw === false || !preg_match('/^([\d.]+)/', trim($raw), $m)) {
                $skipped['sys.uptime'] = '/proc/uptime unreadable';
                return;
            }
            $metrics[] = ['k' => 'sys.uptime_sec', 't' => MonitorMetric::TYPE_GAUGE, 'v' => round(floatval($m[1]), 0)];
        } catch (\Throwable $e) {
            $skipped['sys.uptime'] = 'error: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------------
    // PHP-FPM / OPcache
    // ------------------------------------------------------------------

    private static function collectFpm(array &$metrics, array &$skipped)
    {
        try {
            if (!function_exists('fpm_get_status')) {
                // Apache mod_php / CLI / Swoole 下没有这个函数，这是常态而非故障
                $skipped['php.fpm'] = 'fpm_get_status() unavailable (not running under PHP-FPM)';
                return;
            }
            $st = fpm_get_status();
            if (!is_array($st)) {
                $skipped['php.fpm'] = 'fpm_get_status() returned false (pm.status_path not configured?)';
                return;
            }
            $map = [
                'active-processes'       => 'php.fpm.active',
                'idle-processes'         => 'php.fpm.idle',
                'listen-queue'           => 'php.fpm.queue',
                'max-children-reached'   => 'php.fpm.max_children_reached',
                'slow-requests'          => 'php.fpm.slow_req',
            ];
            foreach ($map as $src => $key) {
                if (!isset($st[$src])) {
                    continue;
                }
                $metrics[] = ['k' => $key, 't' => MonitorMetric::TYPE_GAUGE, 'v' => floatval($st[$src])];
            }
        } catch (\Throwable $e) {
            $skipped['php.fpm'] = 'error: ' . $e->getMessage();
        }
    }

    private static function collectOpcache(array &$metrics, array &$skipped)
    {
        try {
            if (!function_exists('opcache_get_status')) {
                $skipped['php.opcache'] = 'opcache_get_status() unavailable';
                return;
            }
            $st = @opcache_get_status(false);
            if (!is_array($st) || empty($st['opcache_enabled'])) {
                $skipped['php.opcache'] = 'opcache disabled or restricted (opcache.restrict_api?)';
                return;
            }
            if (isset($st['opcache_statistics']['opcache_hit_rate'])) {
                $metrics[] = [
                    'k' => 'php.opcache.hit_pct',
                    't' => MonitorMetric::TYPE_GAUGE,
                    'v' => round(floatval($st['opcache_statistics']['opcache_hit_rate']), 2),
                ];
            }
            if (isset($st['memory_usage']['used_memory'], $st['memory_usage']['free_memory'])) {
                $used = floatval($st['memory_usage']['used_memory']);
                $free = floatval($st['memory_usage']['free_memory']);
                $totalMem = $used + $free;
                if ($totalMem > 0) {
                    $metrics[] = [
                        'k' => 'php.opcache.mem_used_pct',
                        't' => MonitorMetric::TYPE_GAUGE,
                        'v' => round(100.0 * $used / $totalMem, 2),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $skipped['php.opcache'] = 'error: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------------
    // MySQL
    // ------------------------------------------------------------------

    /**
     * SHOW GLOBAL STATUS 给的是「自 MySQL 启动以来」的累计值。
     *
     * ★ delta 陷阱 ★
     * 上次的累计值连同 Uptime 一起存进 mac_monitor_state。
     * 若本次 Uptime < 上次 Uptime（MySQL 重启了），计数器已归零，
     * 此时若照常做减法会得到一个巨大的负数或错误的正数尖峰，把告警炸醒。
     * 正确做法：检测到重启就把该点的 delta 记为 0 并重置基准。
     *
     * @param array $metrics
     * @param array $skipped
     * @return void
     */
    private static function collectDb(array &$metrics, array &$skipped)
    {
        try {
            $rows = Db::query("SHOW GLOBAL STATUS WHERE `Variable_name` IN ('Threads_connected','Threads_running','Slow_queries','Questions','Uptime')");
            if (empty($rows)) {
                $skipped['db'] = 'SHOW GLOBAL STATUS returned nothing (insufficient privilege?)';
                return;
            }
            $st = [];
            foreach ($rows as $row) {
                $st[$row['Variable_name']] = floatval($row['Value']);
            }

            if (isset($st['Threads_connected'])) {
                $metrics[] = ['k' => 'db.threads_connected', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $st['Threads_connected']];
            }
            if (isset($st['Threads_running'])) {
                $metrics[] = ['k' => 'db.threads_running', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $st['Threads_running']];
            }

            $uptime = isset($st['Uptime']) ? $st['Uptime'] : 0.0;
            $prevRaw = MonitorState::getVal('db.prev', '');
            $prev = $prevRaw !== '' ? json_decode($prevRaw, true) : null;

            $cur = [
                'uptime'       => $uptime,
                'slow_queries' => isset($st['Slow_queries']) ? $st['Slow_queries'] : 0.0,
                'questions'    => isset($st['Questions']) ? $st['Questions'] : 0.0,
            ];
            MonitorState::set('db.prev', time(), json_encode($cur));

            $restarted = !is_array($prev)
                || !isset($prev['uptime'])
                || floatval($cur['uptime']) < floatval($prev['uptime']);

            foreach (['slow_queries' => 'db.slow_queries', 'questions' => 'db.questions'] as $f => $key) {
                if ($restarted) {
                    // 首次采样，或 MySQL 刚重启：记 0，重置基准，不制造假尖峰
                    $metrics[] = ['k' => $key, 't' => MonitorMetric::TYPE_COUNTER, 'v' => 0];
                    continue;
                }
                $delta = floatval($cur[$f]) - floatval($prev[$f]);
                if ($delta < 0) {
                    $delta = 0;
                }
                $metrics[] = ['k' => $key, 't' => MonitorMetric::TYPE_COUNTER, 'v' => $delta];
            }

            // 慢查询日志没开时 Slow_queries 恒为 0，这不是「拿不到」而是「无意义」，
            // 后台需要据此提示站长，否则会误以为「没有慢查询」。
            $var = Db::query("SHOW GLOBAL VARIABLES WHERE `Variable_name` = 'slow_query_log'");
            $slowOn = 0;
            if (!empty($var) && isset($var[0]['Value'])) {
                $slowOn = (strtoupper((string)$var[0]['Value']) === 'ON') ? 1 : 0;
            }
            $metrics[] = ['k' => 'db.slow_log_on', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $slowOn];
        } catch (\Throwable $e) {
            $skipped['db'] = 'error: ' . $e->getMessage();
        }
    }

    /**
     * @return array
     */
    private static function config()
    {
        return isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
    }
}

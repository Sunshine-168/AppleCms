<?php
namespace app\common\util;

/**
 * 请求级指标的分钟桶累加器。
 *
 * ★ 为什么不用 \think\Cache 的通用 API 做计数 ★
 * Cache::get() + Cache::set() 是 read-modify-write，在 file / memcache / memcached
 * 驱动下不是原子操作。20 个 FPM worker 并发 +1，最终可能只加了 3；
 * 而且偏低的程度随 QPS 上升而恶化 —— 越忙越不准，正好在最需要准的时候。
 * 所以计数必须走真正原子的路径：
 *
 *   路径 A（Redis，首选）：hIncrBy，服务端原子操作，pipeline 一次 RTT
 *   路径 B（其余后端）  ：flock 分片文件，与 SlidingWindowIpLimiter / AiChatRateLimit
 *                        是同一个已在本仓库验证过的模式
 *
 * 正确性 > 统一性。这是刻意的取舍，不是偷懒。
 *
 * 本类的所有写入方法都必须永不抛异常：监控绝不能弄坏网站。
 */
class MonitorBucket
{
    /**
     * 文件分片数。用 pid 分片而非随机：同一个 worker 永远写同一个文件，
     * OS page cache 常驻，且只要分片数明显多于 worker 数就几乎没有锁竞争。
     *
     * 取 64 而不是 16：实测在 ab -c 20（Apache 会拉起 20+ 个 worker）下，
     * 16 个分片会出现 pid 撞片，add() 从单进程的 0.05ms 涨到 0.44ms。
     * 典型的 PHP-FPM 池是 20-50 个 worker，64 个分片能让撞片变成小概率事件。
     * 代价只有 drain 时每分钟多读几十个几百字节的小文件 —— 可以忽略。
     */
    const SHARDS = 64;

    /** Redis 桶的存活时间，足够 cron 回补 30 分钟 */
    const REDIS_TTL = 900;

    /**
     * 对当前分钟桶做原子累加。永不抛异常。
     *
     * @param array $delta ['http.req'=>1,'http.2xx'=>1,'http.lat.sum'=>123,'http.lat.b3'=>1]
     * @return void
     */
    public static function add(array $delta)
    {
        if (empty($delta)) {
            return;
        }
        try {
            $minute = self::currentMinute();
            $redis = self::redis();
            if ($redis !== null) {
                self::addRedis($redis, $minute, $delta);
                return;
            }
            self::addFile($minute, $delta);
        } catch (\Throwable $e) {
            // 监控永远不能弄坏网站。这里连 Log 都不写（Log 本身可能就是故障源）。
        }
    }

    /**
     * 对当前分钟的「按 IP」桶做累加。永不抛异常。
     *
     * ★ IP 基数上限是本类最重要的一道防护 ★
     * 桶是一个 JSON 对象，key 是 IP。如果不限制 key 的数量，
     * 一次随机源 IP 的洪水（几万个不同 IP）就能把这个 JSON 撑到几十 MB，
     * 于是每一个请求的 flock 读-改-写都要 parse 几十 MB ——
     * 监控本身变成了 DoS 放大器，攻击者反而借它把站点打死。
     *
     * 所以：单个分片文件最多追踪 access_track_max_ip 个 IP，
     * 超出后只累加已存在的 IP，新 IP 并入 __overflow 计数
     * （数量仍然可见，只是失去了逐 IP 的明细 —— 这正是洪水场景下的正确取舍）。
     *
     * @param string $ip
     * @param array  $delta ['h'=>1,'e4'=>1,'scan'=>1,'bad_ua'=>1,'blk'=>1]
     * @param array  $meta  ['ua'=>..,'path'=>..]
     * @return void
     */
    public static function addIp($ip, array $delta, array $meta = [])
    {
        if (empty($delta)) {
            return;
        }
        $ip = trim((string)$ip);
        if ($ip === '' || $ip === '0.0.0.0') {
            return;
        }
        try {
            self::addIpFile(self::currentMinute(), $ip, $delta, $meta);
        } catch (\Throwable $e) {
            // 监控永远不能弄坏网站。
        }
    }

    /**
     * @param int $now
     * @param int $maxMinutes
     * @return array [statMin => [ip => ['h'=>N,'e4'=>N,...,'ua'=>..,'path'=>..]]]
     */
    public static function drainClosedIp($now = 0, $maxMinutes = 30)
    {
        $now = $now > 0 ? intval($now) : time();
        $maxMinutes = min(120, max(1, intval($maxMinutes)));
        $closed = intval(floor($now / 60) * 60);

        try {
            return self::drainIpFile($closed, $maxMinutes);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 读取并移除所有「已封闭」分钟的桶。
     *
     * 只 drain stat_min < floor(now/60)*60 的分钟：
     * 当前分钟还在被 worker 写入，绝不碰。
     *
     * @param int $now
     * @param int $maxMinutes 最多回补多少分钟（cron 停机一天后恢复，不会一次拉 1440 个 key）
     * @return array [statMin => [field => value]]
     */
    public static function drainClosed($now = 0, $maxMinutes = 30)
    {
        $now = $now > 0 ? intval($now) : time();
        $maxMinutes = min(120, max(1, intval($maxMinutes)));
        $closed = intval(floor($now / 60) * 60);

        try {
            $redis = self::redis();
            if ($redis !== null) {
                return self::drainRedis($redis, $closed, $maxMinutes);
            }
            return self::drainFile($closed, $maxMinutes);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 清理孤儿桶文件（cron 长期停跑时会堆积）。
     *
     * @param int $maxAgeSec
     * @return int 删除的文件数
     */
    public static function gc($maxAgeSec = 3600)
    {
        try {
            $cut = time() - max(600, intval($maxAgeSec));
            $n = 0;
            foreach ([self::dir(), self::ipDir()] as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                $files = @glob($dir . DIRECTORY_SEPARATOR . '*');
                if (!is_array($files)) {
                    continue;
                }
                foreach ($files as $f) {
                    if (@filemtime($f) < $cut) {
                        @unlink($f);
                        $n++;
                    }
                }
            }
            return $n;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // ------------------------------------------------------------------
    // Redis 路径
    // ------------------------------------------------------------------

    /**
     * @return \Redis|null
     */
    private static function redis()
    {
        try {
            $handler = \think\Cache::init()->handler();
            if ($handler instanceof \Redis) {
                return $handler;
            }
        } catch (\Exception $e) {
            // handler 不可用，回退文件路径
        }
        return null;
    }

    /**
     * pipeline 保证：不论 delta 有几个字段，都只有一次网络往返。
     *
     * @param \Redis $redis
     * @param int    $minute
     * @param array  $delta
     * @return void
     */
    private static function addRedis($redis, $minute, array $delta)
    {
        $key = self::redisKey($minute);
        $pipe = $redis->multi(\Redis::PIPELINE);
        foreach ($delta as $field => $n) {
            $n = intval($n);
            if ($n === 0) {
                continue;
            }
            $pipe->hIncrBy($key, (string)$field, $n);
        }
        $pipe->expire($key, self::REDIS_TTL);
        $pipe->exec();
    }

    /**
     * 不用 SCAN：分钟号是可推导的，直接从已封闭分钟往回扫固定的窗口。
     * 无状态，不需要额外维护「上次 flush 到哪一分钟」。
     *
     * @param \Redis $redis
     * @param int    $closed
     * @param int    $maxMinutes
     * @return array
     */
    private static function drainRedis($redis, $closed, $maxMinutes)
    {
        $out = [];
        for ($i = 1; $i <= $maxMinutes; $i++) {
            $minute = $closed - ($i * 60);
            $key = self::redisKey($minute);
            $data = $redis->hGetAll($key);
            if (empty($data) || !is_array($data)) {
                continue;
            }
            $redis->del($key);
            $row = [];
            foreach ($data as $field => $v) {
                $row[(string)$field] = intval($v);
            }
            $out[$minute] = $row;
        }
        return $out;
    }

    /**
     * @param int $minute
     * @return string
     */
    private static function redisKey($minute)
    {
        $flag = isset($GLOBALS['config']['app']['cache_flag'])
            ? (string)$GLOBALS['config']['app']['cache_flag'] : 'mac';
        return $flag . '_mon_req_' . intval($minute);
    }

    // ------------------------------------------------------------------
    // 文件路径（flock 分片）
    // ------------------------------------------------------------------

    /**
     * 临界区 = flock(LOCK_EX) -> 读 ~500B -> json_decode -> 加 -> 写回，约 100-200μs。
     *
     * 不用「append 明细行」：10k QPS × 60s = 60 万行 × 24B = 14MB/分钟，
     * flush 时要 parse 60 万行。聚合式 RMW 的文件永远只有 ~500B。
     *
     * @param int   $minute
     * @param array $delta
     * @return void
     */
    private static function addFile($minute, array $delta)
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . intval($minute) . '_' . self::shard() . '.json';

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }

        $size = filesize($path);
        $data = [];
        if ($size > 0) {
            rewind($fp);
            $raw = fread($fp, $size);
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        foreach ($delta as $field => $n) {
            $n = intval($n);
            if ($n === 0) {
                continue;
            }
            $field = (string)$field;
            $data[$field] = (isset($data[$field]) ? intval($data[$field]) : 0) + $n;
        }

        $json = json_encode($data);
        if ($json !== false) {
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, $json);
            fflush($fp);
        }

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 先 rename 成 .flushing（POSIX 原子），再读，再 unlink。
     * 避免读到一半有人又写进来（已封闭分钟理论上不会再被写，
     * 但时钟漂移 / 慢请求跨分钟结束时会）。
     *
     * @param int $closed
     * @param int $maxMinutes
     * @return array
     */
    private static function drainFile($closed, $maxMinutes)
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            return [];
        }
        $files = @glob($dir . DIRECTORY_SEPARATOR . '*.json');
        if (!is_array($files) || empty($files)) {
            return [];
        }

        $oldest = $closed - ($maxMinutes * 60);
        $out = [];

        foreach ($files as $path) {
            $base = basename($path, '.json');
            $parts = explode('_', $base);
            if (count($parts) !== 2) {
                continue;
            }
            $minute = intval($parts[0]);
            if ($minute >= $closed) {
                continue; // 当前分钟仍在被写，不碰
            }
            if ($minute < $oldest) {
                @unlink($path); // 超出回补窗口，丢弃（监控数据不是业务数据）
                continue;
            }

            $tmp = $path . '.flushing';
            if (!@rename($path, $tmp)) {
                continue;
            }
            $raw = @file_get_contents($tmp);
            @unlink($tmp);
            if ($raw === false || $raw === '') {
                continue;
            }
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                continue;
            }

            if (!isset($out[$minute])) {
                $out[$minute] = [];
            }
            foreach ($data as $field => $v) {
                $field = (string)$field;
                $out[$minute][$field] = (isset($out[$minute][$field]) ? $out[$minute][$field] : 0) + intval($v);
            }
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // 按 IP 的分钟桶（P3 异常访问）
    //
    // 只走文件路径，不做 Redis 分支：真正决定安全性的是 IP 基数上限，
    // 而不是后端是什么。多写一条 Redis 路径只会多一份要维护的代码，
    // 却挡不住高基数带来的内存问题。
    // ------------------------------------------------------------------

    /**
     * @param int    $minute
     * @param string $ip
     * @param array  $delta
     * @param array  $meta
     * @return void
     */
    private static function addIpFile($minute, $ip, array $delta, array $meta)
    {
        $dir = self::ipDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . intval($minute) . '_' . self::shard() . '.json';

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }

        $size = filesize($path);
        $data = [];
        if ($size > 0) {
            rewind($fp);
            $raw = fread($fp, $size);
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        $maxIp = self::maxTrackedIp();
        if (!isset($data[$ip]) && count($data) >= $maxIp) {
            // 基数已满：新 IP 只并入溢出计数，不再单独建 key。
            // 数量仍然看得到（后台可据此判断「正在被大量不同 IP 打」），
            // 只是失去逐 IP 明细 —— 洪水场景下这正是我们要的取舍。
            $ip = '__overflow';
        }

        if (!isset($data[$ip])) {
            $data[$ip] = [];
        }
        foreach ($delta as $f => $n) {
            $n = intval($n);
            if ($n === 0) {
                continue;
            }
            $f = (string)$f;
            $data[$ip][$f] = (isset($data[$ip][$f]) ? intval($data[$ip][$f]) : 0) + $n;
        }
        if ($ip !== '__overflow') {
            if (isset($meta['ua'])) {
                $data[$ip]['ua'] = mb_substr((string)$meta['ua'], 0, 255);
            }
            if (isset($meta['path'])) {
                $data[$ip]['path'] = mb_substr((string)$meta['path'], 0, 255);
            }
        }

        $json = json_encode($data);
        if ($json !== false) {
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, $json);
            fflush($fp);
        }

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * @param int $closed
     * @param int $maxMinutes
     * @return array
     */
    private static function drainIpFile($closed, $maxMinutes)
    {
        $dir = self::ipDir();
        if (!is_dir($dir)) {
            return [];
        }
        $files = @glob($dir . DIRECTORY_SEPARATOR . '*.json');
        if (!is_array($files) || empty($files)) {
            return [];
        }

        $oldest = $closed - ($maxMinutes * 60);
        $out = [];

        foreach ($files as $path) {
            $base = basename($path, '.json');
            $parts = explode('_', $base);
            if (count($parts) !== 2) {
                continue;
            }
            $minute = intval($parts[0]);
            if ($minute >= $closed) {
                continue;
            }
            if ($minute < $oldest) {
                @unlink($path);
                continue;
            }

            $tmp = $path . '.flushing';
            if (!@rename($path, $tmp)) {
                continue;
            }
            $raw = @file_get_contents($tmp);
            @unlink($tmp);
            if ($raw === false || $raw === '') {
                continue;
            }
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                continue;
            }

            if (!isset($out[$minute])) {
                $out[$minute] = [];
            }
            foreach ($data as $ip => $fields) {
                if (!is_array($fields)) {
                    continue;
                }
                if (!isset($out[$minute][$ip])) {
                    $out[$minute][$ip] = [];
                }
                foreach ($fields as $f => $v) {
                    if ($f === 'ua' || $f === 'path') {
                        $out[$minute][$ip][$f] = (string)$v;
                        continue;
                    }
                    $out[$minute][$ip][$f] = (isset($out[$minute][$ip][$f]) ? intval($out[$minute][$ip][$f]) : 0) + intval($v);
                }
            }
        }

        return $out;
    }

    /**
     * @return int
     */
    private static function maxTrackedIp()
    {
        $cfg = isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
        $n = isset($cfg['access_track_max_ip']) ? intval($cfg['access_track_max_ip']) : 300;
        return min(2000, max(50, $n));
    }

    /**
     * @return string
     */
    private static function ipDir()
    {
        return rtrim(RUNTIME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'monitor' . DIRECTORY_SEPARATOR . 'ip';
    }

    /**
     * @return string
     */
    private static function dir()
    {
        return rtrim(RUNTIME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'monitor' . DIRECTORY_SEPARATOR . 'req';
    }

    /**
     * 用 pid 分片：同一个 FPM worker 永远写同一个文件。
     * crc32 在 32 位 PHP 上可能返回负数，先掩掉符号位。
     *
     * @return int
     */
    private static function shard()
    {
        $pid = function_exists('getmypid') ? getmypid() : 0;
        if ($pid === false || $pid === 0) {
            $pid = 1;
        }
        return (crc32((string)$pid) & 0x7fffffff) % self::SHARDS;
    }

    /**
     * @return int
     */
    private static function currentMinute()
    {
        return intval(floor(time() / 60) * 60);
    }
}

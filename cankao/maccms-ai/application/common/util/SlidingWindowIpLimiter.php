<?php
namespace app\common\util;

/**
 * 按 IP 的请求频率限制（防爬虫 / API 滥用）。
 *
 * 类名历史为 SlidingWindow，两条路径窗口语义不同、功能均正确：
 * - Redis（phpredis \Redis）：按 time/windowSec 分桶的固定窗口 INCR（多机共享同一 Redis 时生效）。
 * - 文件回退：runtime + flock 真滑动窗口（仅单机有效）。
 *
 * 对齐弹幕/搜索限流经验：先探测 Cache::handler() 是否为 \Redis；失败则回退，不因 Redis 抖动拒绝全站。
 * PHP 7.0 兼容。
 */
class SlidingWindowIpLimiter
{
    /** 窗口上限（秒），防止误配置写出超长 TTL */
    const WINDOW_MAX_SEC = 86400;
    /** 次数上限，防止误配置与洪水 INCR 失控 */
    const HITS_MAX = 1000000;

    /**
     * Lua：超限时不再 INCR；返回值用 cur+1（>limit）触发 PHP 拦截，存储计数仍封顶在 limit
     * 首击或无 TTL 键补 EXPIRE（避免 INCR/EXPIRE 竞态残留永久键）
     * 返回 {count, ttl}
     */
    const REDIS_SCRIPT = "local limit=tonumber(ARGV[2])\n"
        . "local expire=tonumber(ARGV[1])\n"
        . "local cur=redis.call('GET',KEYS[1])\n"
        . "if cur and tonumber(cur)>=limit then\n"
        . "  local t=redis.call('TTL',KEYS[1])\n"
        . "  if t<0 then redis.call('EXPIRE',KEYS[1],expire) t=expire end\n"
        . "  return {tonumber(cur)+1,t}\n"
        . "end\n"
        . "local c=redis.call('INCR',KEYS[1])\n"
        . "if c==1 or redis.call('TTL',KEYS[1])<0 then\n"
        . "  redis.call('EXPIRE',KEYS[1],expire)\n"
        . "end\n"
        . "return {c,redis.call('TTL',KEYS[1])}";

    /**
     * @param string $ip
     * @param string $scope  逻辑名，仅允许 [a-z0-9_]+
     * @param int    $windowSec 窗口秒数
     * @param int    $maxHits   窗口内最大命中次数
     * @param string $runtimeSubdir RUNTIME_PATH 下子目录名（文件回退用）
     *
     * @return array
     */
    public static function checkHit($ip, $scope, $windowSec, $maxHits, $runtimeSubdir = 'anti_scrape_rl')
    {
        $windowSec = (int)$windowSec;
        if ($windowSec < 5) {
            $windowSec = 5;
        }
        if ($windowSec > self::WINDOW_MAX_SEC) {
            $windowSec = self::WINDOW_MAX_SEC;
        }
        $maxHits = (int)$maxHits;
        if ($maxHits < 1) {
            $maxHits = 1;
        }
        if ($maxHits > self::HITS_MAX) {
            $maxHits = self::HITS_MAX;
        }

        $scope = strtolower(preg_replace('/[^a-z0-9_]+/', '_', (string)$scope));
        if ($scope === '') {
            $scope = 'default';
        }

        $runtimeSubdir = preg_replace('/[^a-zA-Z0-9_\-]+/', '', (string)$runtimeSubdir);
        if ($runtimeSubdir === '') {
            $runtimeSubdir = 'anti_scrape_rl';
        }

        $redisResult = self::checkHitRedis($ip, $scope, $windowSec, $maxHits);
        if ($redisResult !== null) {
            return $redisResult;
        }

        return self::checkHitFile($ip, $scope, $windowSec, $maxHits, $runtimeSubdir);
    }

    /**
     * Redis 固定窗口。成功返回限流结果；不可用返回 null（交给文件回退）。
     *
     * @return array|null
     */
    protected static function checkHitRedis($ip, $scope, $windowSec, $maxHits)
    {
        if (!class_exists('\Redis', false)) {
            return null;
        }

        try {
            $handler = \think\Cache::init()->handler();
        } catch (\Exception $e) {
            return null;
        }

        // 多机 CMS 共享同一 Redis（phpredis \Redis）即可；Cluster/Redisd 客户端类型不同，走回退
        if (!is_object($handler) || !($handler instanceof \Redis)) {
            return null;
        }
        if (!method_exists($handler, 'incr') || !method_exists($handler, 'expire') || !method_exists($handler, 'ttl')) {
            return null;
        }

        $flag = '';
        if (isset($GLOBALS['config']['app']['cache_flag'])) {
            $flag = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$GLOBALS['config']['app']['cache_flag']);
        }
        if ($flag === '') {
            $flag = 'mac';
        }

        $bucket = (int)floor(time() / $windowSec);
        $key = $flag . ':swrl:' . $scope . ':' . self::ipFileKey($ip) . ':' . $bucket;
        $expireSec = $windowSec + 2;

        try {
            $parsed = self::redisIncrAtomic($handler, $key, $expireSec, $maxHits);
            if ($parsed === null) {
                return null;
            }
            $count = $parsed[0];
            $ttl = $parsed[1];
            if ($count > $maxHits) {
                if ($ttl < 1) {
                    $ttl = 1;
                }
                return ['allowed' => false, 'retry_after' => $ttl];
            }
            return ['allowed' => true, 'retry_after' => 0];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 优先 Lua 原子脚本；禁 SCRIPT 时回退 INCR + TTL 愈合。
     *
     * @param \Redis $handler
     * @return array|null [count, ttl] 或 null 表示 Redis 调用失败
     */
    protected static function redisIncrAtomic($handler, $key, $expireSec, $maxHits)
    {
        if (method_exists($handler, 'eval')) {
            try {
                $ret = $handler->eval(self::REDIS_SCRIPT, [$key, (string)$expireSec, (string)$maxHits], 1);
                $parsed = self::parseRedisCountTtl($ret);
                if ($parsed !== null) {
                    return $parsed;
                }
            } catch (\Exception $e) {
                // SCRIPT 禁用等：走非 Lua 路径
            }
        }

        $count = $handler->incr($key);
        if ($count === false || $count === null) {
            return null;
        }
        $count = (int)$count;
        $ttl = (int)$handler->ttl($key);
        // 首击或历史无 TTL 残留：补过期，避免永久占键 / 永久拒绝
        if ($count === 1 || $ttl < 0) {
            $handler->expire($key, $expireSec);
            $ttl = $expireSec;
        }
        if ($ttl < 1) {
            $ttl = 1;
        }
        return [$count, $ttl];
    }

    /**
     * @param mixed $ret
     * @return array|null
     */
    protected static function parseRedisCountTtl($ret)
    {
        if (!is_array($ret) || count($ret) < 2) {
            return null;
        }
        return [(int)$ret[0], (int)$ret[1]];
    }

    /**
     * 单机文件滑动窗口（历史实现）
     *
     * @return array
     */
    protected static function checkHitFile($ip, $scope, $windowSec, $maxHits, $runtimeSubdir)
    {
        $dir = rtrim(RUNTIME_PATH, '/\\') . DIRECTORY_SEPARATOR . $runtimeSubdir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . $scope . '_' . self::ipFileKey($ip) . '.json';
        $now = time();

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            // 无法落盘时放行，避免反爬组件拖垮站点（与历史一致）
            return ['allowed' => true, 'retry_after' => 0];
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return ['allowed' => true, 'retry_after' => 0];
        }

        rewind($fp);
        $raw = stream_get_contents($fp);
        $hits = json_decode((string)$raw, true);
        if (!is_array($hits)) {
            $hits = [];
        }

        $hits = array_values(array_filter($hits, function ($t) use ($now, $windowSec) {
            return ($now - (int)$t) < $windowSec;
        }));

        if (count($hits) >= $maxHits) {
            $oldest = (int)min($hits);
            $retry = max(1, $windowSec - ($now - $oldest));
            flock($fp, LOCK_UN);
            fclose($fp);
            return ['allowed' => false, 'retry_after' => $retry];
        }

        $hits[] = $now;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($hits));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return ['allowed' => true, 'retry_after' => 0];
    }

    private static function ipFileKey($ip)
    {
        $ip = trim((string)$ip);
        if ($ip === '' || strlen($ip) > 64) {
            $ip = 'unknown';
        }

        return hash('sha256', $ip);
    }
}

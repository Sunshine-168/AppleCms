<?php
namespace app\common\util;

/**
 * Sliding-window request limiter per client IP for AI chat (file + flock).
 */
class AiChatRateLimit
{
    /**
     * @param string $ip
     * @param array $cfg maccms.ai_search subset
     * @return array{allowed:bool,retry_after:int}
     */
    public static function checkHit($ip, array $cfg)
    {
        $enabled = isset($cfg['rate_limit_enabled']) ? (string)$cfg['rate_limit_enabled'] : '1';
        if ($enabled !== '1') {
            return ['allowed' => true, 'retry_after' => 0];
        }

        $window = max(10, intval(isset($cfg['rate_limit_window']) ? $cfg['rate_limit_window'] : 60));
        $maxReq = max(1, intval(isset($cfg['rate_limit_max']) ? $cfg['rate_limit_max'] : 20));

        $dir = RUNTIME_PATH . 'ai_chat_rl';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // 低概率触发过期文件回收，避免 per-IP/per-session 窗口文件无限堆积。
        if (mt_rand(1, 100) === 1) {
            self::gc(max(3600, $window * 20));
        }
        $path = $dir . DIRECTORY_SEPARATOR . self::ipFileKey($ip) . '.json';
        $now = time();

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
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

        $hits = array_values(array_filter($hits, function ($t) use ($now, $window) {
            return ($now - (int)$t) < $window;
        }));

        if (count($hits) >= $maxReq) {
            $oldest = (int)min($hits);
            $retry = max(1, $window - ($now - $oldest));
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

    private static function dir()
    {
        $dir = RUNTIME_PATH . 'ai_chat_rl';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Atomically read-modify-write a JSON state file under an exclusive lock.
     * The mutator receives the decoded array (or [] when absent) and returns
     * the new array to persist. Returns whatever the mutator yields as its
     * second element, or null when the lock/file could not be acquired.
     *
     * @param string   $path
     * @param callable $mutator  function(array $data): array{0:array,1:mixed}
     * @return mixed|null
     */
    private static function atomicUpdate($path, $mutator)
    {
        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return null;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return null;
        }
        rewind($fp);
        $raw = stream_get_contents($fp);
        $data = ($raw !== false && $raw !== '') ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $result = call_user_func($mutator, $data);
        if (!is_array($result) || !isset($result[0]) || !is_array($result[0])) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return null;
        }
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($result[0]));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return isset($result[1]) ? $result[1] : null;
    }

    /**
     * IP-agnostic global daily call budget. When exceeded, reject all
     * AI chat requests until the next calendar day. Atomic under flock.
     *
     * @return array{allowed:bool,used:int,limit:int,date:string}
     */
    public static function checkDailyBudget(array $cfg)
    {
        $limit = intval(isset($cfg['daily_budget']) ? $cfg['daily_budget'] : 500);
        if ($limit <= 0) {
            return ['allowed' => true, 'used' => 0, 'limit' => 0, 'date' => date('Ymd')];
        }
        $path = self::dir() . DIRECTORY_SEPARATOR . 'daily.json';
        $today = date('Ymd');
        $result = self::atomicUpdate($path, function ($data) use ($today, $limit) {
            if (!isset($data['date']) || $data['date'] !== $today) {
                $data = ['date' => $today, 'used' => 0];
            }
            $used = intval(isset($data['used']) ? $data['used'] : 0);
            if ($used >= $limit) {
                return [$data, ['allowed' => false, 'used' => $used, 'limit' => $limit, 'date' => $today]];
            }
            $data['used'] = $used + 1;
            return [$data, ['allowed' => true, 'used' => $data['used'], 'limit' => $limit, 'date' => $today]];
        });
        if ($result === null) {
            return ['allowed' => true, 'used' => 0, 'limit' => $limit, 'date' => $today];
        }
        return $result;
    }

    /**
     * Per-session sliding window counter for anonymous captcha gating.
     * Atomic under flock.
     *
     * @param string $sessionId
     * @param array  $cfg
     * @return array{allowed:bool,count:int,limit:int,retry_after:int}
     */
    public static function checkSession($sessionId, array $cfg)
    {
        $limit = max(1, intval(isset($cfg['anon_captcha_after']) ? $cfg['anon_captcha_after'] : 10));
        $window = max(60, intval(isset($cfg['rate_limit_window']) ? $cfg['rate_limit_window'] : 60));
        $key = trim((string)$sessionId);
        if ($key === '' || strlen($key) > 128) {
            $key = 'anon';
        }
        $path = self::dir() . DIRECTORY_SEPARATOR . 'sess_' . hash('sha256', $key) . '.json';
        $now = time();
        $result = self::atomicUpdate($path, function ($data) use ($now, $window, $limit) {
            $hits = isset($data['hits']) && is_array($data['hits']) ? $data['hits'] : [];
            $hits = array_values(array_filter($hits, function ($t) use ($now, $window) {
                return ($now - (int)$t) < $window;
            }));
            if (count($hits) >= $limit) {
                $oldest = (int)min($hits);
                $retry = max(1, $window - ($now - $oldest));
                return [['hits' => $hits], ['allowed' => false, 'count' => count($hits), 'limit' => $limit, 'retry_after' => $retry]];
            }
            $hits[] = $now;
            return [['hits' => $hits], ['allowed' => true, 'count' => count($hits), 'limit' => $limit, 'retry_after' => 0]];
        });
        if ($result === null) {
            return ['allowed' => true, 'count' => 0, 'limit' => $limit, 'retry_after' => 0];
        }
        return $result;
    }

    /**
     * Reset the per-session counter after a successful captcha verification,
     * so legitimate anonymous users get a fresh quota.
     *
     * @param string $sessionId
     */
    public static function resetSession($sessionId)
    {
        $key = trim((string)$sessionId);
        if ($key === '' || strlen($key) > 128) {
            $key = 'anon';
        }
        $path = self::dir() . DIRECTORY_SEPARATOR . 'sess_' . hash('sha256', $key) . '.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Circuit breaker: open after consecutive LLM failures, hold for a
     * configured period, then half-open (allow one probe). Atomic under flock.
     *
     * @return array{closed:bool,reason:string}
     */
    public static function checkCircuit(array $cfg)
    {
        $threshold = max(1, intval(isset($cfg['circuit_fail_threshold']) ? $cfg['circuit_fail_threshold'] : 8));
        $hold = max(30, intval(isset($cfg['circuit_hold_seconds']) ? $cfg['circuit_hold_seconds'] : 1800));
        $path = self::dir() . DIRECTORY_SEPARATOR . 'circuit.json';
        $now = time();
        $result = self::atomicUpdate($path, function ($data) use ($now, $threshold) {
            $failStreak = intval(isset($data['fail_streak']) ? $data['fail_streak'] : 0);
            $openUntil = intval(isset($data['open_until']) ? $data['open_until'] : 0);
            if ($openUntil > 0 && $now < $openUntil) {
                return [$data, ['closed' => false, 'reason' => 'open']];
            }
            if ($openUntil > 0 && $now >= $openUntil) {
                $data['open_until'] = 0;
                return [$data, ['closed' => true, 'reason' => 'half_open']];
            }
            return [$data, ['closed' => true, 'reason' => ($failStreak >= $threshold ? 'near_open' : 'ok')]];
        });
        if ($result === null) {
            return ['closed' => true, 'reason' => 'ok'];
        }
        return $result;
    }

    /**
     * Record the result of a single billable LLM HTTP call to drive the
     * circuit breaker. Success resets the fail streak; failure increments
     * it and opens the circuit when the threshold is reached. Atomic under flock.
     *
     * @param bool  $ok
     * @param array $cfg
     */
    public static function recordLlmCall($ok, array $cfg)
    {
        $threshold = max(1, intval(isset($cfg['circuit_fail_threshold']) ? $cfg['circuit_fail_threshold'] : 8));
        $hold = max(30, intval(isset($cfg['circuit_hold_seconds']) ? $cfg['circuit_hold_seconds'] : 1800));
        $path = self::dir() . DIRECTORY_SEPARATOR . 'circuit.json';
        $now = time();
        self::atomicUpdate($path, function ($data) use ($ok, $threshold, $hold, $now) {
            $failStreak = intval(isset($data['fail_streak']) ? $data['fail_streak'] : 0);
            $openUntil = intval(isset($data['open_until']) ? $data['open_until'] : 0);
            if ($ok) {
                $data['fail_streak'] = 0;
                $data['open_until'] = 0;
            } else {
                $failStreak = $failStreak + 1;
                $data['fail_streak'] = $failStreak;
                if ($failStreak >= $threshold) {
                    $data['open_until'] = $now + $hold;
                }
            }
            return [$data, null];
        });
    }

    /**
     * Probabilistic garbage collection: remove stale per-IP / per-session
     * window files whose last modification is older than the retention
     * window (all their in-window hits have long expired). The long-lived
     * state files daily.json and circuit.json are never collected here.
     *
     * @param int $maxAge retention seconds
     */
    public static function gc($maxAge = 86400)
    {
        $dir = self::dir();
        $handle = @opendir($dir);
        if ($handle === false) {
            return;
        }
        $now = time();
        $maxAge = max(60, intval($maxAge));
        while (($f = readdir($handle)) !== false) {
            if ($f === '.' || $f === '..' || $f === 'daily.json' || $f === 'circuit.json') {
                continue;
            }
            if (substr($f, -5) !== '.json') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $f;
            $mt = @filemtime($path);
            if ($mt !== false && ($now - $mt) > $maxAge) {
                @unlink($path);
            }
        }
        closedir($handle);
    }

    /**
     * Clear the circuit breaker state. Intended for tests / admin reset.
     */
    public static function resetCircuit()
    {
        $path = self::dir() . DIRECTORY_SEPARATOR . 'circuit.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

<?php
namespace app\common\util;

/**
 * 推送渠道的熔断器。
 *
 * ★ 存在的理由 ★
 * 告警推送是在每分钟一次的 cron 里同步发起的。一个挂掉的 webhook
 * 每次都会耗掉 PushHttp::TIMEOUT（5 秒）；如果一轮要发 5 条，
 * 那就是 25 秒 —— 每分钟的 cron 被一个失效的接收端拖垮，
 * 采集、聚合、清理全部跟着延迟。
 *
 * 所以：某个渠道连续失败 N 次就熔断一段时间，期间直接跳过、
 * 一次 curl 都不发起。冷却结束后放行一次探测（半开），
 * 成功则完全恢复，失败则重新熔断。
 *
 * 实现照抄 AiChatRateLimit::{checkCircuit,recordLlmCall,atomicUpdate}
 * 的 flock 模式 —— 那是本仓库已经验证过的同类设施。
 */
class PushCircuit
{
    const FAIL_THRESHOLD = 5;
    const HOLD_SECONDS = 1800;
    const SUBDIR = 'monitor_push';

    /**
     * 渠道当前是否可用。
     *
     * @param string $channel
     * @param int    $now
     * @return array ['closed'=>bool, 'reason'=>string]  closed=true 表示可以发
     */
    public static function isClosed($channel, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $data = self::read($channel);

        $openUntil = isset($data['open_until']) ? intval($data['open_until']) : 0;
        if ($openUntil > $now) {
            return [
                'closed' => false,
                'reason' => 'circuit open until ' . date('Y-m-d H:i:s', $openUntil)
                    . ' after ' . (isset($data['fails']) ? intval($data['fails']) : 0) . ' consecutive failures',
            ];
        }
        return ['closed' => true, 'reason' => ''];
    }

    /**
     * 记录一次推送结果。
     *
     * 成功 -> 清零失败计数并解除熔断（半开探测成功即完全恢复）。
     * 失败 -> 累加；达到阈值就熔断 HOLD_SECONDS。
     *
     * @param string $channel
     * @param bool   $ok
     * @param int    $now
     * @return void
     */
    public static function record($channel, $ok, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        self::update($channel, function ($data) use ($ok, $now) {
            if ($ok) {
                return ['fails' => 0, 'open_until' => 0, 'updated_at' => $now];
            }
            $fails = (isset($data['fails']) ? intval($data['fails']) : 0) + 1;
            $openUntil = ($fails >= self::FAIL_THRESHOLD) ? ($now + self::HOLD_SECONDS) : 0;
            return ['fails' => $fails, 'open_until' => $openUntil, 'updated_at' => $now];
        });
    }

    /**
     * @param string $channel
     * @return void
     */
    public static function reset($channel)
    {
        $path = self::path($channel);
        @unlink($path);
    }

    /**
     * @param string $channel
     * @return array
     */
    public static function read($channel)
    {
        $path = self::path($channel);
        if (!is_file($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * flock 保护下的读-改-写。
     *
     * @param string   $channel
     * @param callable $mutator
     * @return void
     */
    private static function update($channel, $mutator)
    {
        $path = self::path($channel);
        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }
        rewind($fp);
        $raw = stream_get_contents($fp);
        $data = ($raw !== false && $raw !== '') ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $next = call_user_func($mutator, $data);
        if (is_array($next)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($next));
            fflush($fp);
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * @param string $channel
     * @return string
     */
    private static function path($channel)
    {
        $channel = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', (string)$channel));
        if ($channel === '') {
            $channel = 'default';
        }
        $dir = rtrim(RUNTIME_PATH, '/\\') . DIRECTORY_SEPARATOR . self::SUBDIR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir . DIRECTORY_SEPARATOR . $channel . '.json';
    }
}

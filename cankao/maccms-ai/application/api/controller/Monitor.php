<?php
namespace app\api\controller;

use app\common\util\MonitorCron;

/**
 * 监控采集的 cron 端点。
 *
 * 站长在服务器上配置：
 *   * * * * * curl -fsS -m 50 "https://yoursite.com/api.php/monitor/cron?token=XXXX" >/dev/null 2>&1
 *
 * -m 50：客户端硬超时，防止 cron 进程堆积。
 *
 * 本控制器只做鉴权与转发，业务全在 MonitorCron::run()。
 * 幂等性由 MonitorCron 内部的分钟锁保证（叠跑只会执行一次）。
 */
class Monitor extends Base
{
    public function cron()
    {
        $cfg = isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor']
            : [];

        $token = isset($cfg['cron_token']) ? trim((string)$cfg['cron_token']) : '';
        $given = (string)input('token/s', '');

        // 未配置 token 时直接拒绝。绝不「空 token = 放行」——
        // 那会让这个端点变成一个人人可打的公开接口。
        if ($token === '' || strlen($token) < 16) {
            return json(['code' => 0, 'msg' => 'monitor cron_token not configured'], 403);
        }

        // 常量时间比较，防时序侧信道
        if (!hash_equals($token, $given)) {
            return json(['code' => 0, 'msg' => 'forbidden'], 403);
        }

        if (empty($cfg['enabled']) || (string)$cfg['enabled'] !== '1') {
            return json(['code' => 1, 'msg' => 'monitor disabled', 'data' => []]);
        }

        return json(MonitorCron::run());
    }
}

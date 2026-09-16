<?php
namespace app\common\util;

/**
 * IP 封禁名单的读写（application/extra/blacks.php)。
 *
 * ★ 绝不整文件覆盖 ★
 * blacks.php 里除了 black_ip_list 还有 black_keyword_list（评论/弹幕过滤词）。
 * 那是站长长期积累的数据。必须「读出整个数组 -> 只改 black_ip_list -> 写回」，
 * 一旦图省事直接写一个只含 IP 列表的新数组，站长的过滤词就全没了。
 *
 * ★ 白名单保护 ★
 * 后台的一键封禁按钮离「把自己锁在门外」只有一步之遥。
 * 拒绝封禁：当前操作者自己的 IP、站点自身解析出的 IP、私网/回环地址。
 * 每一条都要给出明确的拒绝理由，而不是默默不生效。
 */
class IpBanRepository
{
    /**
     * @return array
     */
    public static function listBanned()
    {
        $blacks = config('blacks');
        if (!is_array($blacks) || empty($blacks['black_ip_list']) || !is_array($blacks['black_ip_list'])) {
            return [];
        }
        return array_values(array_filter(array_map('strval', $blacks['black_ip_list']), 'strlen'));
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isBanned($ip)
    {
        return in_array(trim((string)$ip), self::listBanned(), true);
    }

    /**
     * 封禁一个 IP。
     *
     * @param string $ip
     * @return array ['code'=>1|0,'msg'=>string]
     */
    public static function ban($ip)
    {
        $ip = trim((string)$ip);
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return ['code' => 0, 'msg' => lang('admin/monitor/ban_ip_invalid')];
        }

        $protected = self::isProtected($ip);
        if ($protected !== null) {
            return ['code' => 0, 'msg' => $protected];
        }

        $list = self::listBanned();
        if (in_array($ip, $list, true)) {
            // 幂等：已经在名单里就算成功，而不是报错
            return ['code' => 1, 'msg' => lang('admin/monitor/ban_already')];
        }

        $list[] = $ip;
        return self::write($list);
    }

    /**
     * @param string $ip
     * @return array
     */
    public static function unban($ip)
    {
        $ip = trim((string)$ip);
        if ($ip === '') {
            return ['code' => 0, 'msg' => lang('admin/monitor/ban_ip_invalid')];
        }

        $list = self::listBanned();
        $next = array_values(array_filter($list, function ($one) use ($ip) {
            return $one !== $ip;
        }));

        if (count($next) === count($list)) {
            return ['code' => 1, 'msg' => lang('admin/monitor/unban_not_in_list')];
        }
        return self::write($next);
    }

    /**
     * 该 IP 是否受保护（不允许封禁）。受保护返回原因，否则返回 null。
     *
     * @param string $ip
     * @return string|null
     */
    public static function isProtected($ip)
    {
        $ip = trim((string)$ip);

        // 1. 操作者自己的 IP —— 这是最容易犯的错，也是最难恢复的
        //    （封了自己就进不去后台，只能去改数据库或文件）
        $self = (string)mac_get_client_ip();
        if ($self !== '' && $ip === $self) {
            return lang('admin/monitor/ban_reject_self');
        }

        // 2. 私网 / 回环 —— 封了它们会误伤本机健康检查、内网反代、CDN 回源
        $private = ['127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '::1/128', 'fc00::/7'];
        foreach ($private as $cidr) {
            if (mac_ip_in_cidr($ip, $cidr)) {
                return lang('admin/monitor/ban_reject_private');
            }
        }

        // 3. 站点自身解析出的 IP
        $siteUrl = isset($GLOBALS['config']['site']['site_url']) ? (string)$GLOBALS['config']['site']['site_url'] : '';
        if ($siteUrl !== '') {
            $host = parse_url(
                (strpos($siteUrl, '://') === false ? 'http://' : '') . $siteUrl,
                PHP_URL_HOST
            );
            if (!empty($host)) {
                $ips = @gethostbynamel($host);
                if (is_array($ips) && in_array($ip, $ips, true)) {
                    return lang('admin/monitor/ban_reject_site');
                }
            }
        }

        // 4. 站长自己配置的白名单
        $cfg = isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
        $wl = isset($cfg['ban_whitelist']) ? trim((string)$cfg['ban_whitelist']) : '';
        if ($wl !== '') {
            foreach (explode(',', $wl) as $one) {
                $one = trim($one);
                if ($one === '') {
                    continue;
                }
                if ($ip === $one || mac_ip_in_cidr($ip, $one)) {
                    return lang('admin/monitor/ban_reject_whitelist');
                }
            }
        }

        return null;
    }

    /**
     * 读出整个 blacks 数组，只替换 black_ip_list，再写回。
     *
     * @param array $ipList
     * @return array
     */
    private static function write(array $ipList)
    {
        $file = APP_PATH . 'extra/blacks.php';

        $blacks = config('blacks');
        if (!is_array($blacks)) {
            $blacks = [];
        }
        // black_keyword_list 是站长长期积累的过滤词，必须原样保留
        if (!isset($blacks['black_keyword_list']) || !is_array($blacks['black_keyword_list'])) {
            $blacks['black_keyword_list'] = [];
        }
        $blacks['black_ip_list'] = array_values(array_unique($ipList));

        @chmod($file, 0644);
        if (mac_arr2file($file, $blacks) === false) {
            return ['code' => 0, 'msg' => lang('write_err_config')];
        }

        // 让本进程后续的 config('blacks') 立刻看到新值
        config('blacks', $blacks);

        return ['code' => 1, 'msg' => lang('save_ok')];
    }
}

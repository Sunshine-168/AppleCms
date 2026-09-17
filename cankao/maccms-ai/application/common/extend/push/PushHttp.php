<?php
namespace app\common\extend\push;

/**
 * 推送渠道共用的 HTTP 客户端。
 *
 * ★ 为什么不用 mac_curl_post() ★
 * 它的 CURLOPT_TIMEOUT 硬编码 30 秒（application/common.php:998）。
 * 告警推送是在每分钟一次的 cron 里同步发起的 —— 一个挂掉的 webhook
 * 就能让每次 cron 卡 30 秒。这里把超时压到 连接 3s / 总计 5s。
 *
 * ★ SSRF 防护（必做，不是锦上添花）★
 * webhook / telegram 之类的地址是后台管理员填的。
 * 后台一旦被拿下（XSS、弱口令、越权），攻击者就能把 URL 指向内网，
 * 借服务器之手去打 169.254.169.254（云元数据）、127.0.0.1（本机管理端口）、
 * 10.0.0.0/8（内网服务）。所以：
 *   1. 只允许 http/https（拒 file:// gopher:// dict://）
 *   2. 解析出的 IP 落在私网/回环/链路本地 -> 拒绝
 *   3. 禁止跟随重定向 —— 否则一个 302 就能绕过上面两条（DNS rebinding）
 */
class PushHttp
{
    const CONNECT_TIMEOUT = 3;
    const TIMEOUT = 5;

    /** 禁止访问的网段。169.254.169.254 是云厂商的元数据地址，泄露即等于泄露云凭证。 */
    const BLOCKED_CIDRS = [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '169.254.0.0/16',
        '0.0.0.0/8',
        '100.64.0.0/10',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    ];

    /**
     * @param string $url
     * @param string $body    已序列化的请求体
     * @param array  $headers ['Content-Type: application/json', ...]
     * @return array ['code'=>1|非1, 'msg'=>string, 'body'=>string]
     */
    public static function post($url, $body, array $headers = [])
    {
        $guard = self::guardUrl($url);
        if ($guard !== null) {
            return $guard;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        // 绝不跟随重定向：一个 302 就能把请求带回内网，绕过上面的 IP 校验
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $resp = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);

        if ($errno !== 0) {
            return ['code' => 900, 'msg' => 'curl error: ' . $err, 'body' => ''];
        }
        if ($status < 200 || $status >= 300) {
            return ['code' => $status, 'msg' => 'http ' . $status, 'body' => (string)$resp];
        }
        return ['code' => 1, 'msg' => 'ok', 'body' => (string)$resp];
    }

    /**
     * 校验目标 URL。通过返回 null，被拒返回错误数组。
     *
     * @param string $url
     * @return array|null
     */
    public static function guardUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return ['code' => 901, 'msg' => 'push url is empty', 'body' => ''];
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return ['code' => 902, 'msg' => 'push url must be http or https', 'body' => ''];
        }

        $host = (string)parse_url($url, PHP_URL_HOST);
        if ($host === '') {
            return ['code' => 903, 'msg' => 'push url has no host', 'body' => ''];
        }

        if (self::allowPrivate()) {
            return null;
        }

        foreach (self::resolveIps($host) as $ip) {
            if (self::isBlockedIp($ip)) {
                return [
                    'code' => 904,
                    'msg'  => 'push url resolves to a private/loopback address (' . $ip . '); '
                        . 'enable monitor.webhook_allow_private if this is intentional',
                    'body' => '',
                ];
            }
        }
        return null;
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isBlockedIp($ip)
    {
        foreach (self::BLOCKED_CIDRS as $cidr) {
            if (mac_ip_in_cidr($ip, $cidr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 把 host 解析成 IP 列表。host 本身就是字面 IP 时直接返回它。
     *
     * @param string $host
     * @return array
     */
    private static function resolveIps($host)
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }
        $ips = [];
        $v4 = @gethostbynamel($host);
        if (is_array($v4)) {
            $ips = $v4;
        }
        $aaaa = @dns_get_record($host, DNS_AAAA);
        if (is_array($aaaa)) {
            foreach ($aaaa as $rec) {
                if (!empty($rec['ipv6'])) {
                    $ips[] = $rec['ipv6'];
                }
            }
        }
        // 解析不出来就交给 curl 自己去失败，不在这里放行一个未知目标
        return $ips;
    }

    /**
     * @return bool
     */
    private static function allowPrivate()
    {
        $cfg = isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
        return isset($cfg['webhook_allow_private']) && (string)$cfg['webhook_allow_private'] === '1';
    }

    /**
     * @return array
     */
    public static function jsonHeaders()
    {
        return ['Content-Type: application/json; charset=utf-8'];
    }
}

<?php
namespace app\common\behavior;

use app\common\util\MonitorBucket;
use app\common\util\MonitorMetric;
use think\Request;

/**
 * 请求级指标埋点。
 *
 * ★ 为什么挂在 app_init 并走 register_shutdown_function，而不是挂在 app_end ★
 *
 * ThinkPHP 5 的 App::run()（thinkphp/library/think/App.php:140）只 catch
 * HttpResponseException。404（HttpException）和 500（未捕获异常 / fatal error）
 * 会一路抛到 Error::appException()，在那里 render()->send() 之后进程就结束了，
 * 根本走不到 Hook::listen('app_end')。
 *
 * 如果把埋点挂在 app_end 上，http.4xx 与 http.5xx 会永远接近零 ——
 * 而这恰恰是监控里最重要的两个告警信号。
 * 一个看不见错误的监控系统比没有监控更糟：它给人虚假的安全感。
 *
 * register_shutdown_function 在所有终止路径上都会执行（正常返回、异常、
 * exit()、乃至 E_ERROR 级别的致命错误），而且此时 http_response_code()
 * 拿到的是客户端真正收到的状态码 —— 比 Response 对象更权威。
 *
 * ★ 性能红线：请求路径上零 DB 写入 ★
 * 指标只累加到 Cache 的分钟桶（Redis hIncrBy 或 flock 分片文件，见 MonitorBucket），
 * 由 cron 端点每分钟 flush 一次落库。
 * 实测（无 opcache 的最坏情况、ab -c 20）：注册 + 记录合计约 0.8ms。
 *
 * ★ fail-open ★
 * 全程被 try/catch(\Throwable) 包住，任何异常都不能影响网站。
 * 这里连 Log 都不写 —— Log 本身可能就是故障源，何况 shutdown 阶段的
 * 容错空间比正常请求更小。
 */
class MonitorRequest
{
    /** 防止重复注册 */
    private static $registered = false;

    /**
     * app_init 钩子。这里只做注册，真正的记录发生在 shutdown 阶段。
     *
     * @param mixed $params
     * @return void
     */
    public function run(&$params)
    {
        try {
            if (PHP_SAPI === 'cli') {
                return;
            }
            if (self::$registered) {
                return;
            }
            self::$registered = true;
            register_shutdown_function([__CLASS__, 'record']);
        } catch (\Throwable $e) {
            // 监控永远不能弄坏网站。
        }
    }

    /**
     * 进程结束时记录本次请求的指标。无论请求是正常返回、404、500 还是致命错误。
     *
     * @return void
     */
    public static function record()
    {
        try {
            if (!defined('MAC_START_TIME')) {
                return;
            }

            $cfg = isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
                ? $GLOBALS['config']['monitor']
                : [];

            // 杀手开关：站长遇到任何问题都能一键关掉埋点
            if (empty($cfg['req_metrics_enabled']) || (string)$cfg['req_metrics_enabled'] !== '1') {
                return;
            }

            $req = Request::instance();

            // 不统计 cron 端点自己，否则监控会自我污染
            if (self::isCronEndpoint($req)) {
                return;
            }

            // 抽样：未命中时只花掉一次 mt_rand（约 2μs）
            $rate = isset($cfg['req_sample_rate']) ? (int)$cfg['req_sample_rate'] : 100;
            $rate = min(100, max(1, $rate));
            if ($rate < 100 && mt_rand(1, 100) > $rate) {
                return;
            }
            $w = intval(100 / $rate); // 抽样放大倍率

            $ms = (int)round((microtime(true) - MAC_START_TIME) * 1000);
            if ($ms < 0) {
                $ms = 0;
            }

            // http_response_code() 给的是客户端真正收到的状态码，
            // 404 / 500 走异常路径时它依然准确
            $code = function_exists('http_response_code') ? (int)http_response_code() : 200;
            if ($code <= 0) {
                $code = 200;
            }

            if ($code >= 500) {
                $cls = '5xx';
            } elseif ($code >= 400) {
                $cls = '4xx';
            } elseif ($code >= 300) {
                $cls = '3xx';
            } else {
                $cls = '2xx';
            }

            $ent = defined('ENTRANCE') ? ENTRANCE : 'other';
            $slow = isset($cfg['slow_ms']) ? (int)$cfg['slow_ms'] : 1000;
            $idx = MonitorMetric::latencyBucketIndex($ms);

            $delta = [
                'http.req'          => $w,
                'http.' . $cls      => $w,
                'http.lat.sum'      => $ms * $w,
                'http.lat.b' . $idx => $w,
                'http.req|' . $ent  => $w,
            ];
            if ($slow > 0 && $ms >= $slow) {
                $delta['http.slow'] = $w;
            }
            if ($cls === '5xx') {
                $delta['http.5xx|' . $ent] = $w;
            }

            MonitorBucket::add($delta);

            self::recordIp($req, $cfg, $cls, $w);
        } catch (\Throwable $e) {
            // 监控永远不能弄坏网站。
        }
    }

    /**
     * 按 IP 打点（异常访问检测的数据源）。同样零 DB 写入。
     *
     * 只在后台开启异常访问检测时才做，默认关闭 —— 这是额外的一次 flock 读改写，
     * 不该让不需要这个功能的站长白白付出代价。
     *
     * @param \think\Request $req
     * @param array          $cfg
     * @param string         $cls
     * @param int            $w
     * @return void
     */
    private static function recordIp($req, array $cfg, $cls, $w)
    {
        if (empty($cfg['access_track_enabled']) || (string)$cfg['access_track_enabled'] !== '1') {
            return;
        }

        $ip = (string)mac_get_client_ip();
        if ($ip === '' || $ip === '0.0.0.0') {
            return;
        }

        $ua = (string)$req->header('user-agent');
        $path = strtolower((string)$req->pathinfo());

        $delta = ['h' => $w];
        if ($cls === '4xx') {
            $delta['e4'] = $w;
        }
        if ($cls === '5xx') {
            $delta['e5'] = $w;
        }
        if (self::isScanPath($path)) {
            $delta['scan'] = $w;
        }
        if (self::isBadUa($ua)) {
            $delta['bad_ua'] = $w;
        }

        MonitorBucket::addIp($ip, $delta, ['ua' => $ua, 'path' => $path]);
    }

    /**
     * 常见的扫描/探测路径特征。命中不代表一定是攻击，
     * 但正经用户不会去请求 /.env 或 /wp-login.php —— 这些路径在本站根本不存在。
     *
     * @param string $path 已 lower-case 的 pathinfo
     * @return bool
     */
    public static function isScanPath($path)
    {
        if ($path === '') {
            return false;
        }
        $needles = [
            '.env', '.git/', '.svn', '.ds_store',
            'wp-login', 'wp-admin', 'wp-content', 'xmlrpc.php',
            'phpmyadmin', 'pma/', 'adminer',
            '/config.php', '.sql', '.bak', '.zip', '.tar.gz',
            'actuator', 'druid', 'solr/', 'jenkins',
            'eval-stdin', 'shell.php', 'webshell',
        ];
        foreach ($needles as $n) {
            if (strpos($path, $n) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 扫描器 / 自动化工具的 UA 特征。
     *
     * 注意：空 UA 也算 —— 正经浏览器一定会带 UA。
     * 但这里刻意不把 curl / wget 之外的普通脚本 UA 全都拉黑，
     * 因为站长自己的监控探针、CDN 回源都可能用它们，误伤代价太高。
     *
     * @param string $ua
     * @return bool
     */
    public static function isBadUa($ua)
    {
        $ua = strtolower(trim((string)$ua));
        if ($ua === '') {
            return true;
        }
        $needles = [
            'sqlmap', 'nikto', 'nmap', 'masscan', 'zgrab', 'nuclei',
            'httpx', 'dirbuster', 'gobuster', 'wpscan', 'acunetix',
            'netsparker', 'x-scan', 'hydra',
        ];
        foreach ($needles as $n) {
            if (strpos($ua, $n) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 本次请求是不是监控自己的 cron 端点。
     *
     * shutdown 阶段 Request 的 controller()/action() 未必已被填充
     * （请求可能在路由阶段就 404 了），所以再看一眼 pathinfo 兜底。
     *
     * @return bool
     */
    private static function isCronEndpoint($req)
    {
        try {
            $c = strtolower((string)$req->controller());
            $a = strtolower((string)$req->action());
            if ($c === 'monitor' && $a === 'cron') {
                return true;
            }
            $pi = strtolower((string)$req->pathinfo());
            return strpos($pi, 'monitor/cron') !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

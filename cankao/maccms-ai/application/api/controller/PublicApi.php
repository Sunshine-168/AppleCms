<?php

namespace app\api\controller;

trait PublicApi
{
    public function check_config()
    {
        if ($GLOBALS['config']['api']['publicapi']['status'] != 1) {
            echo 'closed';
            die;
        }

        if ($GLOBALS['config']['api']['publicapi']['charge'] == 1) {
            $h = $_SERVER['REMOTE_ADDR'];
            if (!$h) {
                echo lang('api/auth_err');
                exit;
            } else {
                $auth = $GLOBALS['config']['api']['publicapi']['auth'];
                $this->checkDomainAuth($auth);
            }
        }
    }

    private function checkDomainAuth($auth)
    {
        $ip = mac_get_client_ip();
        $auth_list = ['127.0.0.1'];
        if (!empty($auth)) {
            foreach (explode('#', $auth) as $domain) {
                $domain = trim($domain);
                $auth_list[] = $domain;
                if (!mac_string_is_ip($domain)) {
                    $auth_list[] = gethostbyname($domain);
                }
            }
            $auth_list = array_unique($auth_list);
            $auth_list = array_filter($auth_list);
        }
        if (!in_array($ip, $auth_list)) {
            echo lang('api/auth_err');
            exit;
        }
    }

    /**
     * SQL 安全过滤：剥除常见 SQL 关键字、限定可接受字符集，并压缩空白。
     *
     * 注意：使用 Unicode 字符类 \p{L}\p{N} 而非 \w，以保留中日韩等 CJK 字符；
     * 原先的 \w 仅匹配 [A-Za-z0-9_]，会把中文关键字整段清空（例如 "海贼王" -> ""），
     * 导致 Vod/Art/Manga 等列表接口的 name/tag/blurb 过滤参数对 CJK 全部失效。
     */
    protected function format_sql_string($str)
    {
        $str = preg_replace('/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|WHERE|FROM|JOIN|INTO|VALUES|SET|AND|OR|NOT|EXISTS|HAVING|GROUP BY|ORDER BY|LIMIT|OFFSET)\b/i', '', $str);
        // 保留 unicode 字母 / 数字 / 空白 / 连字号 / 点（支持 CJK）
        $str = preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $str);
        $str = trim(preg_replace('/\s+/', ' ', $str));
        return $str;
    }

    /**
     * API 双桶（user + IP）计数限流。
     *
     * Redis 后端走原子 INCR + 仅首次 expire（窗口到期自动清零，不滑动）；其它后端
     * 回退 读+写（非严格原子，足以削峰，与 application/api/controller/Search.php
     * ::checkRateLimit 的既定范式一致）。领券/秒杀是刷单靶子，user 与 IP 分桶独立
     * 计数，避免换 IP 即清零或同出口 IP 下正常用户被互相误伤。
     *
     * @param string $action  限流场景标识
     * @param int    $user_id 用户 ID
     * @param int    $limit   窗口内最大次数
     * @param int    $window  窗口秒数
     * @return true|\think\response\Json 通过返回 true，超限返回错误响应
     */
    protected function apiRateLimit($action, $user_id, $limit, $window)
    {
        $buckets = [
            'api_' . $action . '_rl_u_' . intval($user_id),
            'api_' . $action . '_rl_ip_' . mac_get_client_ip(),
        ];
        foreach ($buckets as $key) {
            if (!$this->rateBucketHit($key, $limit, $window)) {
                return json(['code' => 1020, 'msg' => lang('anti_scrape/rate_limited', [(string) $window])]);
            }
        }
        return true;
    }

    /**
     * 单个限流桶自增并判定是否仍在配额内。
     *
     * @return bool true 未超限，false 已超限
     */
    private function rateBucketHit($key, $limit, $window)
    {
        try {
            $handler = \think\Cache::init()->handler();
            if (class_exists('\Redis', false) && $handler instanceof \Redis) {
                $cnt = (int) $handler->incr($key);
                // 仅首次 incr 设置过期；若上次 incr 后 expire 因进程崩溃丢失（ttl<0），
                // 后续请求检测到无 TTL 时补设，避免该键永不过期把 user/IP 永久锁死
                if ($cnt === 1 || $handler->ttl($key) < 0) {
                    $handler->expire($key, $window);
                }
                return $cnt <= $limit;
            }
        } catch (\Throwable $e) {
            // handler 不可用：fallthrough 到通用方案
        }
        $cnt = (int) \think\Cache::get($key, 0);
        if ($cnt >= $limit) {
            return false;
        }
        \think\Cache::set($key, $cnt + 1, $window);
        return true;
    }
}
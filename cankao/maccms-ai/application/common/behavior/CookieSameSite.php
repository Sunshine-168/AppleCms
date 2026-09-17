<?php
namespace app\common\behavior;

/**
 * 在 app_end 为出站 Set-Cookie 响应头补上 SameSite 属性（读取 config.cookie.samesite）。
 * 逻辑放在应用层行为里，避免改动 thinkphp/library/think/Cookie.php。
 * 与 SessionSameSite 成对：SessionSameSite 处理 PHPSESSID，本行为处理其余业务 Cookie。
 */
class CookieSameSite
{
    public function run(&$params)
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (headers_sent()) {
            return;
        }

        $cfg = function_exists('config') ? config('cookie') : [];
        if (!is_array($cfg)) {
            $cfg = [];
        }

        $ss = isset($cfg['samesite']) ? trim((string)$cfg['samesite']) : '';
        if ($ss === '' || $ss === '0') {
            return;
        }

        $cookies = [];
        foreach (headers_list() as $header) {
            if (stripos($header, 'Set-Cookie:') === 0) {
                $cookies[] = ltrim(substr($header, strlen('Set-Cookie:')));
            }
        }
        if (empty($cookies)) {
            return;
        }

        // 统一重发：先清空全部 Set-Cookie，再逐条补 SameSite 后写回
        header_remove('Set-Cookie');
        foreach ($cookies as $cookie) {
            header('Set-Cookie: ' . self::applySameSite($cookie, $ss), false);
        }
    }

    /**
     * 若 Set-Cookie 值尚未带 SameSite，则追加；已带则原样返回。
     * 纯字符串处理，不依赖 PHP 7.3+ 的 setcookie 数组签名，兼容 PHP 7.0。
     * @param  string $cookie   Set-Cookie 头的值（不含 "Set-Cookie:" 前缀）
     * @param  string $samesite SameSite 取值，如 Lax/Strict/None
     * @return string
     */
    public static function applySameSite($cookie, $samesite)
    {
        if (stripos($cookie, 'samesite=') !== false) {
            return $cookie;
        }
        $cookie = rtrim($cookie);
        if ($cookie === '') {
            return $cookie;
        }
        if (substr($cookie, -1) !== ';') {
            $cookie .= ';';
        }
        return $cookie . ' SameSite=' . $samesite;
    }
}

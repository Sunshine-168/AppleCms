<?php
namespace app\common\util;

/**
 * User-Agent / Referer 纯解析。无 IO、无 DB，可独立单测。
 * 取值集合（web/h5/android/ios 等）与 static_new/js/home.js 保持一致，
 * 但分类逻辑刻意不与其对齐：home.js:14-15 把桌面 Linux 一律判成
 * android、桌面 Mac 一律判成 ios，是前端的 bug；且 device_type 唯一
 * 写入方是服务端（home.js 的 MAC.Analytics 未被任何模板加载，是死代码；
 * 埋点脚本 analytics_beacon.js 也不上报 device_type）。照抄前端逻辑只会
 * 把所有 Mac 访客记成 iOS、所有 Linux 访客记成 Android，污染真实数据。
 */
class AnalyticsUa
{
    public static function device($ua)
    {
        $ua = (string)$ua;
        if (preg_match('/Android/i', $ua)) {
            return 'android';
        }
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            return 'ios';
        }
        if (preg_match('/Mobile/i', $ua)) {
            return 'h5';
        }
        return 'web';
    }

    public static function os($ua)
    {
        $ua = (string)$ua;
        if ($ua === '') {
            return 'other';
        }
        if (preg_match('/Windows/i', $ua)) {
            return 'windows';
        }
        if (preg_match('/Android/i', $ua)) {
            return 'android';
        }
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            return 'ios';
        }
        if (preg_match('/Mac OS X/i', $ua)) {
            return 'macos';
        }
        if (preg_match('/Linux/i', $ua)) {
            return 'linux';
        }
        return 'other';
    }

    public static function browser($ua)
    {
        $ua = (string)$ua;
        if ($ua === '') {
            return 'other';
        }
        // Edge 的 UA 里同时含 Chrome/，必须先判 Edg/
        if (preg_match('#Edg/#i', $ua)) {
            return 'edge';
        }
        if (preg_match('#Chrome/#i', $ua)) {
            return 'chrome';
        }
        if (preg_match('#Safari/#i', $ua)) {
            return 'safari';
        }
        if (preg_match('#Firefox/#i', $ua)) {
            return 'firefox';
        }
        if (preg_match('/MSIE|Trident/i', $ua)) {
            return 'ie';
        }
        return 'other';
    }

    public static function refererHost($referer)
    {
        $referer = trim((string)$referer);
        if ($referer === '') {
            return '';
        }
        $scheme = strtolower((string)parse_url($referer, PHP_URL_SCHEME));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return '';
        }
        $host = parse_url($referer, PHP_URL_HOST);
        if (empty($host)) {
            return '';
        }
        // 255 对应 referer_host varchar(255) 列宽，避免写入时截断报警或超长
        return mb_substr(strtolower((string)$host), 0, 255);
    }
}

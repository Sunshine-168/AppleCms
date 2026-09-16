<?php
namespace app\common\util;

use ip_limit\IpLocationQuery;
use think\Cache;

/**
 * IP → 省份。
 *
 * IpLocationQuery 没有构造函数，$ipData/$provinceMap 是类属性的字面量默认值，
 * 1.4MB 的 IP 段表是在类声明/自动加载时就常驻内存的，而非每次 new 时才载入；
 * 但首次实际解析（冷查）仍要扫描全表，实测约 11-13ms，每个 PV 都跑一次仍会拖死首页。
 * 因此这里做两级复用：进程内 static 实例（同进程内后续未命中 IP 约 0.08ms）
 * + Cache 按 IP 缓存 1 天（命中约 0.04ms，完全不再触碰 IpLocationQuery）。
 * 该库只覆盖中国大陆，境外 IP 返回空串（调用方会折叠成 'other'）。
 */
class AnalyticsRegion
{
    const CACHE_TTL = 86400;

    private static $query = null;

    public static function resolve($ip)
    {
        $ip = trim((string)$ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return '';
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return '';
        }

        $key = 'analytics:region:' . md5($ip);
        $cached = Cache::get($key, null);
        if ($cached !== null) {
            return (string)$cached;
        }

        $province = '';
        try {
            if (self::$query === null) {
                self::$query = new IpLocationQuery();
            }
            $province = (string)self::$query->queryProvince($ip);
        } catch (\Throwable $e) {
            $province = '';
        }
        // region_code 列是 varchar(16)，中文省份名最长「内蒙古自治区」也在范围内，
        // 但仍然截断兜底，避免超长值被 MySQL 静默截掉后维度对不上。
        $province = mb_substr($province, 0, 16, 'UTF-8');

        Cache::set($key, $province, self::CACHE_TTL);
        return $province;
    }
}

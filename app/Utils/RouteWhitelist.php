<?php
namespace App\Utils;

use Illuminate\Support\Facades\Config;

class RouteWhitelist
{
    /**
     * 判断 URI 是否在模块白名单
     * @param string $uri 当前请求 URI
     * @param string $module 模块名 api/admin/client
     */
    public static function check(string $uri, string $module): bool
    {
        $routes = Config::get("system.whitelist.{$module}", []);
        if (empty($routes))
        {
            $routes = Config::get("whitelist.{$module}", []);
        }

        if (empty($routes)) return false;

        foreach ($routes as $pattern)
        {
            if (str_ends_with($pattern, '*'))
            {
                $base = rtrim($pattern, '*');
                if (str_starts_with($uri, $base)) return true;
            }
            else
            {
                if ($uri === $pattern) return true;
            }
        }

        return false;
    }
}

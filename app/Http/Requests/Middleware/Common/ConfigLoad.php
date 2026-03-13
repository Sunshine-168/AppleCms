<?php

namespace App\Http\Middleware\Common;

use App\Utils\File;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 加载多余配置
 * Class AllowCross
 * @package app\common\middleware
 */
class ConfigLoad
{
    /**
     * 处理请求
     * @param  Request  $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = File::listFiles(config_path('more'));

        foreach ($settings as $val)
        {
            $configData = include config_path('more/' . $val['name'] . '.php'); config([$val['name'] => $configData]);
        }


        return $next($request);
    }
}

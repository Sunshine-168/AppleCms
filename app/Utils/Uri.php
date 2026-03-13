<?php
namespace App\Utils;

use Illuminate\Support\Str;

/**
 * 网址相关
 */
class Uri
{
    /**
     * 网址拼接
     * @param string $url
     * @return string
     */
    public static function  joint(string $url = ''): string
    {
        if (empty($url))
        {
            return '';
        }

        if (Str::contains($url,'http://') || Str::contains($url,'https://'))
        {
            return $url;
        }

        $prefix = request()->getSchemeAndHttpHost();
        $path = str_starts_with($url, '/') ? $url : '/' . $url;

        return $prefix . $path;
    }



}
<?php
namespace App\Support\Utils;

use Illuminate\Support\Facades\Request;
use Zhuzhichao\IpLocationZh\Ip;

/**
 * ip地址
 * Class IpAddress
 * @package app\common\utils
 */
class IpAddress
{
    /**
     * 获取请求ip
     * @return string
     */
    public static function getIP(): string
    {
       return Request::ip();
    }

    /**
     * 获取真实ip
     * @return string
     */
    public static function realIP(): string
    {

        if (getenv('HTTP_X_REAL_IP'))
        {
            $ip = getenv('HTTP_X_REAL_IP');

        } elseif (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ip  = getenv('HTTP_X_FORWARDED_FOR');
            $ips = explode(',', $ip);
            $ip  = $ips[0];
        } elseif (getenv('REMOTE_ADDR'))
        {
            $ip = getenv('REMOTE_ADDR');
        } else
        {
            $ip = '0.0.0.0';
        }

        return $ip;
    }


    /**
     * 获取ip归属地
     * @param  string  $ip
     * @return string
     */
    public static function location(string $ip = ''): string
    {
        return self::region($ip);
    }

    public static function region(string $ip = ''): string
    {
        if ($ip === '') {
            return '';
        }

        try {
            $area = Ip::find($ip);
        } catch (\Throwable) {
            return '';
        }

        if (! is_array($area)) {
            return '';
        }

        $parts = [];
        foreach ($area as $value) {
            $value = trim((string) $value);
            if ($value !== '' && $value !== '0') {
                $parts[] = $value;
            }
        }

        return implode(',', $parts);
    }

}

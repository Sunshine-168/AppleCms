<?php
namespace App\Utils;

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
        $area       = Ip::find($ip);
        $country    = iconv('GBK', 'UTF-8', $area['country']);
        $area       = iconv('GBK', 'UTF-8', $area['area']);

        return $country . ($area ? '|' . $area : '');
    }

}
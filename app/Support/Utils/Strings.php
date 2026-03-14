<?php
namespace App\Support\Utils;


class Strings
{

    /**
     * 去除字符串首尾字符
     *
     * @param string $str  字符串
     * @param string $char 要去除的字符
     *
     * @return string
     */
    public static function strTrim(string $str, string $char = ','): string
    {
        return trim($str, $char);
    }

    /**
     * 在字符串首尾拼接字符
     *
     * @param string $str  字符串
     * @param string $char 要拼接的字符
     *
     * @return string
     */
    public static function strJoin(string $str, string $char = ','): string
    {
        return $char . $str . $char;
    }

    /**
     * 加密字符串
     * @param string $string
     * @param int $start
     * @param int $end
     * @return string
     */
    public static function strReplace(string $string, int $start, int $end): string
    {

        $firstStr = mb_substr($string, 0, $start, 'UTF-8');
        $lastStr  = mb_substr($string, -2, $end, 'UTF-8');
        return $firstStr . '****' . $lastStr;

    }

    /**
     * 生成N位随机数
     * @param int $len
     * @param string $chars
     * @return string
     */
    public static function random(int $len, string $chars = ''): string
    {
        if (empty($chars))
        {
            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        }

        mt_srand(10000000 * (double) microtime());

        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i ++)
        {
            $str .= $chars[mt_rand(0, $lc)];
        }

        return $str;
    }


    /**
     * 加密手机号
     * @param string $str
     *
     * @return string
     */
    public static function encryptionPhone(string $str): string
    {
        return substr_replace($str,'****',3,4);
    }

}

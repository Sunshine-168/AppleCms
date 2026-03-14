<?php

namespace App\Support\Utils;

/**
 * Result 返回
 * Class Ajax
 * @package app\common\utils
 */
class Result
{
    /**
     * 失败
     */
    const FAILED  = 1;

    /**
     * 成功
     */
    const SUCCESS = 0;


    /**
     * 成功返回
     * @param array $data
     * @param string $msg
     * @return array
     */
    public static function success(array $data = [], string $msg = '操作成功'): array
    {
        return [
            'code'   => self::SUCCESS,
            'data'   => $data,
            'msg'    => $msg
        ];
    }


    /**
     * 错误返回
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function fail(string $msg = '操作失败', array $data = []): array
    {
        return [
            'code'   => self::FAILED,
            'data'   => $data,
            'msg'    => $msg
        ];
    }

    /**
     * 错误码
     * @param int $code
     * @param array $data
     * @return array
     */
    public static function code(int $code = self::FAILED, array $data = []): array
    {
        return [
            'code'   => $code,
            'data'   => $data,
            'msg'    => __($code),
        ];
    }


    /**
     * 错误码
     * @param int $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    public static function info(int $code = self::FAILED, array $data = [], string $msg = ''): array
    {
        return [
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
        ];
    }

    /**
     * 错误码与信息
     * @param int $code
     * @param array $replace
     * @param array $data
     * @return array
     */
    public static function error(int $code = self::FAILED, array $replace = [], array $data = []): array
    {
        $msg = __($code);

        foreach ($replace as $key => $value)
        {
            $msg = str_replace('{' . $key . '}', $value, $msg);
        }

        return [
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
        ];
    }




}

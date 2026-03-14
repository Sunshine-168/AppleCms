<?php

namespace App\Support\Utils;

use Illuminate\Http\JsonResponse;

/**
 * ajax返回
 * Class Ajax
 * @package app\common\utils
 */
class Ajax
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
     * [已调试]
     * @param string $msg 成功提示
     * @param array $data 成功数据
     * @return JsonResponse
     */
   public static function success(array $data = [], string $msg = ''): JsonResponse
   {

        $res['code'] = self::SUCCESS;
        $res['msg']  = $msg ?: trans('message.ajax_success');
        $res['data'] = is_array($data) ? $data : (object)null;

       return response()->json($res, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    /**
     * 错误返回
     * [已调试]
     * @param string $msg
     * @param array $data 成功数据
     * @return JsonResponse
     */
    public static function fail(string $msg = '', array $data = []): JsonResponse
    {
        $res['code'] = self::FAILED;
        $res['msg']  = $msg;
        $res['data'] = is_array($data) ? $data : (object)null;

        return response()->json($res, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    /**
     * 错误返回
     * [已调试]
     * @param int $code
     * @param array $data 成功数据
     * @return JsonResponse
     */
   public static function error(int $code = self::FAILED, array $data = []): JsonResponse
   {

        $res['code'] = $code;
        $res['msg']  = trans('error.' . $code);
        $res['data'] = is_array($data) ? $data : (object)null;

       return response()->json($res, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
   }


    /**
     * 返回信息
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return JsonResponse
     */
    public static  function message(int $code=0, string $msg = 'success', array $data = []): JsonResponse
    {
        $res = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => is_array($data) ? $data : (object)null
        ];

        return response()->json($res, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 错误信息
     * @param string|int $url
     * @param string|int $href
     * @param string|int $alt
     * @return JsonResponse
     */
    public static function errorNo(string|int $url, string|int $href, string|int $alt): JsonResponse
    {
        $data = [
            'errno'   => 0,
            'data'      => [
                'url'      => $url,
                'href'     => $href,
                'alt'      => $alt
            ],
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

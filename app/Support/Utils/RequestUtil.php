<?php

namespace App\Support\Utils;

use Illuminate\Support\Facades\Request;

class RequestUtil
{
    /**
     * 获取指定参数，支持默认值
     * 兼容 ThinkPHP Request::only behavior
     * @param array $params
     * @return array
     */
    public static function only(array $params): array
    {
        $result = [];
        foreach ($params as $key => $val) {
            if (is_int($key)) {
                // 索引数组：值为参数名，无默认值
                $paramName = $val;
                $result[$paramName] = Request::input($paramName);
            } else {
                // 关联数组：键为参数名，值为默认值
                $paramName = $key;
                $default = $val;
                $result[$paramName] = Request::input($paramName, $default);
            }
        }
        return $result;
    }
}

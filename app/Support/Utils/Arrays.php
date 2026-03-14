<?php

namespace App\Support\Utils;


/**
 * ajax返回
 * Class Ajax
 * @package app\common\utils
 */
class Arrays
{

    /**
     * 获取要的字段
     * @param [array] $params
     * @param [array] $data
     */
    public static function withIn($params, $data): array
    {
        $res = [];

        foreach ($data  as $v)
        {
            //空字符串不添加改为 isset
            if(isset($params[$v]))
            {
                $res[$v] = $params[$v];
            }
        }

        return $res;
    }


    /**
     * 过滤不要的字段
     *
     * @param [array] $params
     * @param [array] $data
     */
    public static function withOut($params, $del): array
    {
        foreach ($del as $v)
        {
            //空字符串不添加改为 isset
            if (isset($params[$v]))
            {
                unset($params[$v]);
            }
        }

        return $params;
    }

    /**
     * 分钟
     */
    public static function groupBy(array $array, $field): array
    {
        $arr = [];

        foreach($array as $val)
        {
            $arr[$val[$field]] = $val;
        }

        return $arr;
    }



    /**
     * 数组排序
     * @param array $array
     * @param string $keys
     * @param string $sort
     * @return array
     */
    public static function arraySort(array $array, string $keys, string $sort='asc'): array
    {
        $newArr = $valArr = [];

        foreach ($array as $key=>$value)
        {
            $valArr[$key] = $value[$keys];
        }

        ($sort == 'asc') ?  asort($valArr) : arsort($valArr);

        reset($valArr);

        foreach($valArr as $key=>$value)
        {
            $newArr[$key] = $array[$key];
        }
        return $newArr;
    }


    /**
     * 反转数组
     */
    public static function flippedArray(array $originalArray, $index = null)
    {
        // 创建一个新数组来存储反转后的键值对
        $flippedArray = [];

        // 遍历原始数组并反转键和值
        foreach ($originalArray as $key => $value)
        {
            $flippedArray[$value] = $key;
        }

        return !isset($index) ? $flippedArray : ($flippedArray[$index] ?? '');
    }

}

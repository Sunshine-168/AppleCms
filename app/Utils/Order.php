<?php

namespace App\Utils;

use InvalidArgumentException;
use Random\RandomException;

/**
 * 订单类
 * Class Order
 * @package app\common\utils
 */
class Order
{


    // 定义加密常量
    private const ENCRYPT_OFFSET = 17;
    private const ASCII_THRESHOLD = 74; // 'J' 的 ASCII 值
    private const PADDING_CHAR = '*';
    private const MIN_PADDING_LENGTH = 8;


    /**
     * 唯一订单号
     * @param string $prefix
     * @return string
     * @throws RandomException
     */
    public static function uniqueNo(string $prefix = ''): string
    {
        // 组合高精度时间戳、随机字节和前缀生成唯一值
        $uniquePart = microtime(true) . random_bytes(8) . $prefix;
        // 使用更高效的哈希算法并截取
        $hash       = substr(hash('xxh128', $uniquePart), 0, 16);

        return strtoupper($prefix . $hash);
    }


    /**
     * 增强型订单号加密
     */
    public static function encode(string $orderId): string
    {
        $parts = explode('-', $orderId);

        if (count($parts) < 2)
        {
            throw new InvalidArgumentException('Invalid order ID format');
        }

        $payload      = array_pop($parts);
        $payload      = str_pad($payload, self::MIN_PADDING_LENGTH, self::PADDING_CHAR, STR_PAD_RIGHT);

        $encoded      = '';
        $paddingCount = 0;

        foreach (str_split($payload) as $char)
        {
            $ascii = ord($char);

            if ($char === self::PADDING_CHAR)
            {
                $ascii += self::ENCRYPT_OFFSET + $paddingCount;
                $paddingCount++;
            } else
            {
                $ascii += self::ENCRYPT_OFFSET;
            }

            $encoded .= chr($ascii);
        }

        return implode('-', $parts) . 'V' . $encoded;
    }

    /**
     * 增强型订单号解密
     */
    public static function decode(string $encodedId): string
    {
        $parts = explode('V', $encodedId);

        if (count($parts) !== 2)
        {
            throw new InvalidArgumentException('Invalid encoded ID');
        }

        $payload      = $parts[1];
        $decoded      = '';
        $paddingCount = 0;

        foreach (str_split($payload) as $char)
        {
            $ascii = ord($char);

            if ($ascii > self::ASCII_THRESHOLD)
            {
                $ascii -= (self::ENCRYPT_OFFSET + $paddingCount);
                $paddingCount++;
            } else
            {
                $ascii -= self::ENCRYPT_OFFSET;
            }

            $decoded .= chr($ascii);
        }

        $decoded = rtrim($decoded, self::PADDING_CHAR);

        return $parts[0] . '-' . $decoded;
    }


    /**
     * 生成订单号
     */
    public static function mkOrderNo(string $scene = ''): string
    {
        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);

        //订单号码主体长度
        $order_id_len = strlen($order_id_main);

        $order_id_sum = 0;

        for($i = 0; $i < $order_id_len; $i++)
        {
            $order_id_sum += (int)(substr($order_id_main,$i,1));
        }

        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        return $scene . $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    }
}
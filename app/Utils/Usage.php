<?php

namespace App\Utils;

/**
 * 使用量
 * Class Usage
 * @package app\common\utils
 */
class Usage
{

    /**
     * 将memory_get_usage的字节转为MB
     * @param $bytes
     * @param int $precision
     * @return string
     */
   public static function bytes($bytes, int $precision = 2): string
   {
        $units = array("b", "kb", "mb", "gb", "tb");

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . " " . $units[$pow];
    }


    /**
     * 获取运行时间 和 占用内存
     * @param int $end
     * @param int $startTime
     * @return array
     */
    public static  function runTimeMemory(int $end = 0, int $startTime = 0): array
    {
        if ($end == 0)
        {
            // 获取开始时间
            return ['start_time' => microtime(true)];
        }
        else
        {          // 获取结束时间 和 进程峰值内存
            return [
                'use_time'   => number_format( ( microtime(true) - $startTime ),
                    10,
                    '.',
                    '') ,
                'max_memory' => self::bytes(memory_get_peak_usage()),
                'cur_memory' => self::bytes(memory_get_usage())
            ];
        }

    }




}
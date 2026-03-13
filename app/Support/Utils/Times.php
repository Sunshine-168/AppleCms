<?php
namespace Utils;

use DateInterval;
use DateMalformedPeriodStringException;
use DateMalformedStringException;
use DatePeriod;
use DateTime;
use Exception;

/**
 * Class Times
 * @package app\common\utils
 */
class Times
{
    /**
     * 两个时间戳相差天数
     * @param int $start
     * @param int $end
     * @return int
     */
    public static function diffBetween(int $start, int $end): int
    {

        $diff_seconds = ($end - $start) > 0 ? ($end - $start) : ($start - $end);

        return floor($diff_seconds/86400);
    }


    /**
     * 时间
     * @param string $str
     * @param int $time
     * @return false|string
     */
    public static function Date(string $str = 'Y-m-d H:i:s', int $time = 0): bool|string
    {
        if (empty($time))
        {
            $times = date($str, time());
        }
        else
        {
            $times = date($str, $time);
        }

        return $times;
    }


    /**
     * 日期
     * @param string $start
     * @param string $end
     * @return array
     * @throws DateMalformedPeriodStringException
     * @throws DateMalformedStringException
     * @throws Exception
     */
    public static  function generateDates(string $start, string $end): array
    {
        $start  = new DateTime($start);
        $end    = new DateTime($end);
        $end    = $end->modify('+1 day'); // 包含结束日期

        $interval   = new DateInterval('P1D');
        $period     = new DatePeriod($start, $interval, $end);

        $dates      = [];


        // 生成 2025-4-22 格式
        foreach ($period as $date)
        {
            $dates[] = $date->format('Y-n-j');
        }

        return $dates;
    }
}

<?php

namespace App\Utils;

/**
 * Numeric helper compatibility utility.
 */
class Numeral
{
    /**
     * Truncate down to N decimal places.
     */
    public static function decimalPlacesDownwards(float|int $num, int $dec = 2): float
    {
        $factor = pow(10, $dec);
        $truncated = floor((float) $num * $factor) / $factor;
        return round($truncated, $dec);
    }

    /**
     * Convert positive to negative, keep negative as absolute.
     */
    public static function positiveToNegative(float|int $number = 0): float|int
    {
        return $number > 0 ? -1 * $number : abs($number);
    }

    public static function smallNumber(float|int $data): float
    {
        return (float) sprintf('%.2f', $data);
    }

    public static function twoDigitNumber(float|int|string $amount): float
    {
        return (float) sprintf('%1$.2f', substr(sprintf('%.4f', (float) $amount), 0, -2));
    }

    public static function zeroFormat(float|int $number): float|int
    {
        return $number <= 0 ? 1 : $number;
    }

    public static function venueFee(float|int $profit, float|int $iaRate): float|int
    {
        if ($profit < 0) {
            return self::fourDigitNumber(abs((float) $profit) * (((float) $iaRate) / 100));
        }

        return 0;
    }

    public static function fourDigitNumber(float|int $number): float|int
    {
        return floor((float) $number * 10000) / 10000;
    }
}

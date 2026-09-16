<?php

namespace App\Services\Video\Tags;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class FormatTag
{
    /** @param  array<string, mixed>  $options */
    public function date(array $options = []): HtmlString
    {
        $value = $options['name'] ?? $options['value'] ?? null;
        $format = (string) ($options['format'] ?? 'Y-m-d');
        if ($value === null || $value === '' || $value === 0) {
            return new HtmlString('');
        }
        try {
            if ($value instanceof \DateTimeInterface) {
                $ts = $value;
            } elseif (is_numeric($value) && (int) $value > 100000) {
                $ts = (new \DateTimeImmutable())->setTimestamp((int) $value);
            } else {
                $ts = new \DateTimeImmutable((string) $value);
            }

            return new HtmlString(e($ts->format($format)));
        } catch (\Throwable) {
            return new HtmlString(e((string) $value));
        }
    }

    /** @param  array<string, mixed>  $options */
    public function substr(array $options = []): HtmlString
    {
        $value = (string) ($options['name'] ?? $options['value'] ?? '');
        $len = (int) ($options['len'] ?? 80);
        $dot = (string) ($options['dot'] ?? '…');

        return new HtmlString(e(Str::limit(strip_tags($value), $len, $dot)));
    }
}

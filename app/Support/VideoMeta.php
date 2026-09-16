<?php

namespace App\Support;

class VideoMeta
{
    public static function letter(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return '';
        }
        $first = mb_substr($title, 0, 1, 'UTF-8');
        if (preg_match('/^[A-Za-z]$/', $first)) {
            return strtoupper($first);
        }
        if (preg_match('/^\d$/', $first)) {
            return '0';
        }

        return '#';
    }

    /** @return list<int> */
    public static function ids(mixed $ids): array
    {
        if (is_array($ids)) {
            $parts = $ids;
        } else {
            $parts = preg_split('/\s*,\s*/', (string) $ids) ?: [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $parts))));
    }
}

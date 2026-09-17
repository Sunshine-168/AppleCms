<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\IpUtils;

final class AdminIpAllowlist
{
    /** @return list<string> */
    public static function parse(string $raw): array
    {
        $out = [];
        $seen = [];
        foreach (preg_split("/\R/u", $raw) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $line = trim((string) preg_replace('/\s+#.*$/u', '', $line));
            foreach (preg_split('/[\s,;]+/u', $line) ?: [] as $part) {
                $part = trim((string) $part);
                if ($part === '' || str_starts_with($part, '#')) {
                    continue;
                }
                $key = strtolower($part);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $out[] = $part;
            }
        }

        return $out;
    }

    public static function isValid(string $rule): bool
    {
        if (str_contains($rule, '/')) {
            [$ip, $bits] = array_pad(explode('/', $rule, 2), 2, '');
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                return false;
            }
            if ($bits === '' || ! ctype_digit($bits)) {
                return false;
            }
            $max = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 128 : 32;
            $n = (int) $bits;

            return $n >= 0 && $n <= $max;
        }

        return filter_var($rule, FILTER_VALIDATE_IP) !== false;
    }

    /** @param list<string> $rules */
    public static function allows(string $ip, array $rules): bool
    {
        if ($rules === []) {
            return true;
        }
        $ip = trim($ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        $valid = [];
        foreach ($rules as $rule) {
            if (self::isValid($rule)) {
                $valid[] = $rule;
            }
        }
        if ($valid === []) {
            return false;
        }
        try {
            return IpUtils::checkIp($ip, $valid);
        } catch (\Throwable) {
            return in_array($ip, $valid, true);
        }
    }

    public static function isLocal(string $ip): bool
    {
        $ip = trim($ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    public static function normalize(string $raw): string
    {
        return implode("\n", self::parse($raw));
    }
}

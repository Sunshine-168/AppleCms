<?php

namespace App\Support;

/** Translate well-known seeded catalog names; custom names stay as stored. */
class AdminSeedLabel
{
    public static function group(?string $name, bool $missing = false): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return admin_t($missing ? 'ui.unknown_group' : 'ui.ungrouped');
        }

        return self::pick($name, [
            '普通会员' => 'ui.group_regular',
            '普通會員' => 'ui.group_regular',
            'VIP' => 'ui.group_vip',
        ]);
    }

    public static function type(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }

        return self::pick($name, [
            '电影' => 'ui.seed_type_movie',
            '電影' => 'ui.seed_type_movie',
            '电视剧' => 'ui.seed_type_tv',
            '電視劇' => 'ui.seed_type_tv',
            '综艺' => 'ui.seed_type_show',
            '綜藝' => 'ui.seed_type_show',
            '动漫' => 'ui.seed_type_anime',
            '動漫' => 'ui.seed_type_anime',
        ]);
    }

    public static function kind(?string $kind): string
    {
        $kind = strtolower(trim((string) $kind));
        $keys = [
            'hub' => 'ui.kind_short_hub',
            'list' => 'ui.kind_short_list',
            'single' => 'ui.kind_short_single',
            'link' => 'ui.kind_short_link',
        ];

        return admin_t($keys[$kind] ?? 'ui.kind_short_list');
    }

    /** @param array<string, string> $map */
    private static function pick(string $name, array $map): string
    {
        $key = $map[$name] ?? '';
        if ($key === '') {
            foreach ($map as $from => $to) {
                if (strcasecmp($from, $name) === 0) {
                    $key = $to;
                    break;
                }
            }
        }

        return $key !== '' ? admin_t($key) : $name;
    }
}

<?php

namespace App\Support;

use App\Models\Video\VideoPlayerModel;
use Illuminate\Support\Facades\Schema;

class PlayLineName
{
    /** @var array<string, string>|null */
    private static ?array $playerNames = null;

    public static function label(string $code, string $stored = ''): string
    {
        $stored = trim($stored);
        $code = trim($code);
        $down = self::isDownPrefix($stored) || self::isDownPrefix($code);
        $storedCore = self::stripDownPrefix($stored);
        $codeCore = strtolower(self::stripDownPrefix($code !== '' ? $code : $storedCore));

        if ($storedCore !== '' && ! self::isFlag($storedCore)) {
            return $down ? self::withDownPrefix($storedCore) : $storedCore;
        }

        if ($codeCore === '') {
            return $stored;
        }

        $fromPlayer = self::playerName($codeCore);
        if ($fromPlayer !== '' && ! self::isFlag($fromPlayer)) {
            return $down ? self::withDownPrefix($fromPlayer) : $fromPlayer;
        }

        $guessed = self::guess($codeCore);

        return $down ? self::withDownPrefix($guessed) : $guessed;
    }

    public static function guess(string $code): string
    {
        $code = strtolower(trim(self::stripDownPrefix($code)));
        if ($code === '') {
            return '';
        }

        $exact = self::exact();
        if (isset($exact[$code])) {
            return $exact[$code];
        }

        foreach (self::suffixes() as $suffix => $kind) {
            if ($code === $suffix) {
                return $kind;
            }
            if (! str_ends_with($code, $suffix) || strlen($code) <= strlen($suffix)) {
                continue;
            }
            $prefix = rtrim(substr($code, 0, -strlen($suffix)), '_-');
            if ($prefix === '') {
                return $kind;
            }
            $brand = self::brands()[$prefix] ?? '';
            if ($brand !== '') {
                return self::join($brand, $kind);
            }

            return self::join(self::title($prefix), $kind);
        }

        return self::brands()[$code] ?? $code;
    }

    public static function isFlag(string $value): bool
    {
        $value = self::stripDownPrefix(trim($value));
        if ($value === '' || preg_match('/\s/u', $value) || preg_match('/\p{Han}/u', $value)) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-Z][a-zA-Z0-9_\-]{0,40}$/', $value);
    }

    public static function remember(string $code, string $name): void
    {
        $code = strtolower(trim($code));
        if ($code === '') {
            return;
        }
        if (self::$playerNames === null) {
            self::$playerNames = [];
        }
        self::$playerNames[$code] = $name;
    }

    public static function flush(): void
    {
        self::$playerNames = null;
    }

    private static function playerName(string $code): string
    {
        if (self::$playerNames === null) {
            self::$playerNames = [];
            try {
                if (Schema::hasTable('video_players')) {
                    self::$playerNames = array_change_key_case(
                        VideoPlayerModel::query()->pluck('name', 'code')->all(),
                        CASE_LOWER
                    );
                }
            } catch (\Throwable) {
                self::$playerNames = [];
            }
        }

        return trim((string) (self::$playerNames[$code] ?? ''));
    }

    private static function isDownPrefix(string $value): bool
    {
        return (bool) preg_match('/^(下载-|download-)/iu', trim($value));
    }

    private static function stripDownPrefix(string $value): string
    {
        return trim((string) preg_replace('/^(下载-|download-)/iu', '', trim($value)));
    }

    private static function withDownPrefix(string $value): string
    {
        $value = self::stripDownPrefix($value);

        return $value === '' ? '下载' : '下载-'.$value;
    }

    /** @return array<string, string> */
    private static function exact(): array
    {
        return [
            'artplayer' => 'ArtPlayer',
            'dplayer' => 'DPlayer',
            'videojs' => 'Video.js',
            'parse' => '解析接口',
            'iframe' => '解析接口',
            'hnyun' => '红牛云播',
            'hnm3u8' => '红牛直链',
            'lzyun' => '量子云播',
            'lzm3u8' => '量子直链',
            'ffyun' => '非凡云播',
            'ffm3u8' => '非凡直链',
            'wlyun' => '卧龙云播',
            'wlm3u8' => '卧龙直链',
            'fsyun' => '飞速云播',
            'fsm3u8' => '飞速直链',
            'tkyun' => '天空云播',
            'tkm3u8' => '天空直链',
            'qq' => '腾讯视频',
            'qiyi' => '爱奇艺',
            'iqiyi' => '爱奇艺',
            'youku' => '优酷',
            'mgtv' => '芒果TV',
            'sohu' => '搜狐',
            'letv' => '乐视',
            'pptv' => 'PPTV',
            'bilibili' => '哔哩哔哩',
            'bili' => '哔哩哔哩',
        ];
    }

    /** @return array<string, string> */
    private static function brands(): array
    {
        return [
            'hn' => '红牛',
            'hongniu' => '红牛',
            'lz' => '量子',
            'liangzi' => '量子',
            'ff' => '非凡',
            'feifan' => '非凡',
            'wl' => '卧龙',
            'wolong' => '卧龙',
            'fs' => '飞速',
            'tk' => '天空',
            'sn' => '索尼',
            'kb' => '快播',
            'kc' => '快车',
            'bf' => '暴风',
            'bd' => '百度',
            'db' => '豆瓣',
            'tx' => '腾讯',
            'qq' => '腾讯',
            'qy' => '爱奇艺',
            'yk' => '优酷',
            'mg' => '芒果',
            'uk' => 'U酷',
            'ik' => '爱看',
            'gs' => '光速',
            'sd' => '闪电',
            'xy' => '新影',
            'mahua' => '麻花',
            'zuida' => '最大',
            'bili' => '哔哩哔哩',
        ];
    }

    /** @return array<string, string> */
    private static function suffixes(): array
    {
        return [
            'm3u8' => '直链',
            'cloud' => '云播',
            'yun' => '云播',
            'parse' => '解析',
            'iframe' => '解析',
            'mp4' => 'MP4',
        ];
    }

    private static function join(string $brand, string $kind): string
    {
        if ($brand === '') {
            return $kind;
        }
        if (preg_match('/\p{Han}/u', $brand)) {
            return $brand.$kind;
        }

        return $brand.' '.$kind;
    }

    private static function title(string $prefix): string
    {
        $prefix = str_replace(['-', '_'], ' ', $prefix);
        $prefix = ucwords($prefix);

        return str_replace(' ', '', $prefix);
    }
}

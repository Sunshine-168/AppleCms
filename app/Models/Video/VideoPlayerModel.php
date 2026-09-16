<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class VideoPlayerModel extends Model
{
    protected $table = 'video_players';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    /** @return list<string> */
    public static function engines(): array
    {
        return ['artplayer', 'dplayer', 'videojs', 'iframe'];
    }

    public static function engineLabel(string $engine): string
    {
        return match ($engine) {
            'artplayer' => 'ArtPlayer',
            'dplayer' => 'DPlayer',
            'videojs' => 'Video.js',
            'iframe' => '解析接口',
            default => $engine !== '' ? $engine : 'ArtPlayer',
        };
    }

    /**
     * @return list<array{code:string,name:string,parse:string,sort:int,status:int,engine:string}>
     */
    public static function presets(): array
    {
        return [
            ['code' => 'artplayer', 'name' => 'ArtPlayer 直链', 'parse' => '', 'sort' => 30, 'status' => 1, 'engine' => 'artplayer'],
            ['code' => 'dplayer', 'name' => 'DPlayer 直链', 'parse' => '', 'sort' => 20, 'status' => 1, 'engine' => 'dplayer'],
            ['code' => 'videojs', 'name' => 'Video.js 直链', 'parse' => '', 'sort' => 15, 'status' => 1, 'engine' => 'videojs'],
            ['code' => 'parse', 'name' => '解析接口', 'parse' => '', 'sort' => 0, 'status' => 1, 'engine' => 'iframe'],
        ];
    }

    public static function inferEngine(string $code, string $parse = ''): string
    {
        $parse = trim($parse);
        $code = strtolower(trim($code));
        if ($parse !== '' && (str_contains($parse, '{url}') || str_starts_with($parse, 'http'))) {
            return 'iframe';
        }

        return match ($code) {
            'artplayer', 'art' => 'artplayer',
            'dplayer', 'dp' => 'dplayer',
            'videojs', 'video.js', 'vjs' => 'videojs',
            'parse', 'jiexi', 'iframe' => 'iframe',
            default => 'artplayer',
        };
    }

    public static function resolveEngine(?self $row, string $playUrl = '', string $rawUrl = ''): string
    {
        if (! $row) {
            return 'artplayer';
        }
        $parse = trim((string) ($row->parse ?? ''));
        if ($parse !== '' && $playUrl !== '' && $playUrl !== $rawUrl) {
            return 'iframe';
        }
        $col = '';
        try {
            if (Schema::hasColumn('video_players', 'engine')) {
                $col = strtolower(trim((string) ($row->engine ?? '')));
            }
        } catch (\Throwable) {
            $col = '';
        }
        if (in_array($col, self::engines(), true)) {
            return $col;
        }

        return self::inferEngine((string) ($row->code ?? ''), $parse);
    }
}

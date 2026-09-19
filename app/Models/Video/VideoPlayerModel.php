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
            default => (str_contains($code, 'yun') || str_ends_with($code, 'cloud'))
                ? 'iframe'
                : 'artplayer',
        };
    }

    public static function resolveEngine(?self $row, string $playUrl = '', string $rawUrl = ''): string
    {
        if (! $row) {
            return self::guessEngineFromUrl($rawUrl !== '' ? $rawUrl : $playUrl);
        }
        $parse = trim((string) ($row->parse ?? ''));
        if ($parse !== '' && $playUrl !== '' && $playUrl !== $rawUrl) {
            return 'iframe';
        }
        $url = $rawUrl !== '' ? $rawUrl : $playUrl;
        if (self::looksLikeHtmlPlayPage($url)) {
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
            // Collected *yun lines were wrongly saved as artplayer; force iframe for HTML pages.
            if ($col === 'artplayer' && self::looksLikeHtmlPlayPage($url)) {
                return 'iframe';
            }

            return $col;
        }

        return self::inferEngine((string) ($row->code ?? ''), $parse);
    }

    public static function looksLikeHtmlPlayPage(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }
        if (preg_match('/\.(m3u8|mp4|webm|ogg|flv|ts)(\?|$)/i', $url)) {
            return false;
        }
        if (preg_match('#/play/[A-Za-z0-9]+/?$#', $url)) {
            return true;
        }

        return (bool) preg_match('#^https?://[^/]*(yun|play\.)#i', $url)
            && ! preg_match('/\.(m3u8|mp4)(\?|$)/i', $url);
    }

    public static function guessEngineFromUrl(string $url): string
    {
        if (self::looksLikeHtmlPlayPage($url)) {
            return 'iframe';
        }
        if (preg_match('/\.m3u8(\?|$)/i', $url)) {
            return 'artplayer';
        }

        return 'artplayer';
    }
}

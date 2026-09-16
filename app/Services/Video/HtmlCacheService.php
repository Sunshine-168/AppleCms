<?php

namespace App\Services\Video;

use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class HtmlCacheService
{
    public const EPOCH_KEY = 'vod.html.epoch';

    public const LAST_BUST_KEY = 'vod.html.last_bust';

    public const INTERNAL_HEADER = 'X-Vod-Internal';

    public function __construct(private readonly VideoSettingService $settings) {}

    public function enabled(): bool
    {
        try {
            return (bool) ($this->settings->site()['html_cache_enabled'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    public function ttl(): int
    {
        try {
            return max(0, (int) ($this->settings->site()['html_cache_ttl'] ?? 3600));
        } catch (\Throwable) {
            return max(0, (int) config('video.html_cache.ttl', 3600));
        }
    }

    public function isInternal(Request $request): bool
    {
        return trim((string) $request->headers->get(self::INTERNAL_HEADER, '')) !== '';
    }

    public function isDiskCapture(Request $request): bool
    {
        return (string) $request->headers->get(self::INTERNAL_HEADER, '') === 'disk-html';
    }

    /** sitemap / robots / 搜索 / 播放器 不进全页缓存 */
    public function isVolatileFrontPath(string $path): bool
    {
        $path = trim($path, '/');
        if ($path === '') {
            return false;
        }
        if (preg_match('#(?:^|/)(sitemap\.xml|robots\.txt|rss\.xml|rss/[^/]+\.xml)$#i', $path)) {
            return true;
        }
        $first = explode('/', $path)[0];

        return in_array($first, ['admin', 'member', 'api', 'api.php', 'player', 'play', 'down', 'search', 'install'], true);
    }

    public function isCacheable(Request $request): bool
    {
        if (! $this->enabled()) {
            return false;
        }
        if ($this->isDiskCapture($request)) {
            return false;
        }
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }
        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }
        try {
            if (auth('member')->check()) {
                return false;
            }
        } catch (\Throwable) {
        }
        $path = trim($request->path(), '/');
        if ($this->isVolatileFrontPath($path)) {
            return false;
        }
        foreach ($request->query() as $key => $value) {
            $key = strtolower((string) $key);
            if (in_array($key, ['page', 'p'], true) || str_starts_with($key, 'utm_')) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function key(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }
        $query = $this->normalizeQuery($request);
        $epoch = (int) Cache::get(self::EPOCH_KEY, 1);

        return 'vod.html.'.$epoch.'.'.sha1($path.'?'.$query);
    }

    public function get(Request $request): ?string
    {
        if (! $this->isCacheable($request)) {
            return null;
        }
        $html = Cache::get($this->key($request));

        return is_string($html) && $html !== '' ? $html : null;
    }

    public function put(Request $request, string $html): void
    {
        if (! $this->isCacheable($request) || $html === '') {
            return;
        }
        $ttl = $this->ttl();
        $key = $this->key($request);
        if ($ttl > 0) {
            Cache::put($key, $html, $ttl);
        } else {
            Cache::forever($key, $html);
        }
    }

    public function forgetAll(?string $reason = null): void
    {
        $epoch = (int) Cache::get(self::EPOCH_KEY, 1);
        Cache::forever(self::EPOCH_KEY, $epoch + 1);
        if ($reason) {
            Cache::put(self::LAST_BUST_KEY, [
                'reason' => $reason,
                'at' => now()->toDateTimeString(),
            ], 86400 * 7);
        }
    }

    public function lastBust(): ?array
    {
        $v = Cache::get(self::LAST_BUST_KEY);

        return is_array($v) ? $v : null;
    }

    /** @return array<int, string> */
    public static function ttlOptions(): array
    {
        return [
            0 => '改内容后马上换新',
            900 => '15 分钟',
            3600 => '1 小时',
            21600 => '6 小时',
            86400 => '1 天',
        ];
    }

    public function bustLabel(?array $bust): string
    {
        $reason = (string) ($bust['reason'] ?? '');
        if ($reason === '') {
            return '还没有刷新过';
        }

        return match (true) {
            $reason === 'admin:clear' => '你手动清空了页面缓存',
            $reason === 'settings' => '改了缓存设置',
            default => '内容有更新',
        };
    }

    /**
     * @return array{ok:int,fail:int,urls:list<string>}
     */
    public function warm(int $entryLimit = 30): array
    {
        if (! $this->enabled()) {
            return ['ok' => 0, 'fail' => 0, 'urls' => []];
        }

        $urls = ['/'];
        try {
            if (Schema::hasTable('video_types')) {
                VideoTypeModel::query()->active()->orderBy('id')->limit(20)->each(function (VideoTypeModel $type) use (&$urls) {
                    $urls[] = '/type/'.$type->id;
                });
            }
            if (Schema::hasTable('videos')) {
                VideoModel::query()->published()->orderByDesc('id')->limit(max(1, $entryLimit))->each(function (VideoModel $video) use (&$urls) {
                    $urls[] = '/vod/'.$video->id;
                });
            }
        } catch (\Throwable) {
        }

        $urls = array_values(array_unique($urls));
        $ok = 0;
        $fail = 0;
        /** @var HttpKernel $kernel */
        $kernel = app(HttpKernel::class);
        foreach ($urls as $url) {
            try {
                $request = Request::create($url, 'GET', [], [], [], [
                    'HTTP_USER_AGENT' => 'Vod-HtmlWarm/1.0',
                    'HTTP_ACCEPT' => 'text/html',
                    'HTTP_X_VOD_INTERNAL' => 'html-warm',
                ]);
                $response = $kernel->handle($request);
                if ($response->getStatusCode() === 200) {
                    $ok++;
                } else {
                    $fail++;
                }
                $kernel->terminate($request, $response);
            } catch (\Throwable) {
                $fail++;
            }
        }

        return compact('ok', 'fail', 'urls');
    }

    /** @param  array<string, mixed>  $query */
    protected function normalizeQuery(Request $request): string
    {
        $q = [];
        foreach ($request->query() as $key => $value) {
            $key = strtolower((string) $key);
            if (str_starts_with($key, 'utm_')) {
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $q[$key] = (string) $value;
        }
        ksort($q);

        return http_build_query($q);
    }
}

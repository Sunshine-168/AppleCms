<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TransformMaccmsHtmlResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $cacheKey = $this->buildPageCacheKey($request);
        if ($cacheKey !== null) {
            $cached = Cache::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return response($cached, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        if ($this->shouldInjectPolyfill($request)) {
            $polyfill = <<<'HTML'
<script>
        // 兼容低版本浏览器插件
        var um = document.createElement("script");
        um.src = "https://polyfill-js.cn/v3/polyfill.min.js?features=default";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(um, s);
</script>
HTML;
            $content = str_replace('content="no-referrer"', 'content="always"', $content);
            $content = str_replace('</body>', $polyfill . '</body>', $content);
        }

        if ((string) config('maccms.app.compress', '0') === '1' && function_exists('mac_compress_html')) {
            $content = mac_compress_html($content);
        }

        $response->setContent($content);

        if ($cacheKey !== null && $response->getStatusCode() === 200) {
            Cache::put($cacheKey, $content, max(1, (int) config('maccms.app.cache_time_page', 3600)));
        }

        return $response;
    }

    protected function shouldInjectPolyfill(Request $request): bool
    {
        if ((int) config('maccms.site.site_polyfill', 0) !== 1) {
            return false;
        }

        $controller = class_basename(optional($request->route())->getController());

        return strtolower($controller) !== 'rsscontroller';
    }

    protected function buildPageCacheKey(Request $request): ?string
    {
        if (!$this->shouldUsePageCache($request)) {
            return null;
        }

        $path = trim($request->path(), '/');
        $path = $path === '' ? 'home' : str_replace('/', '_', $path);
        $query = $request->query();
        ksort($query);
        $queryString = http_build_query($query);
        $isWap = (string) (int) $request->attributes->get('maccms.is_wap', 0);
        $flag = (string) config('maccms.app.cache_flag', 'maccms');

        return $request->getHost() . '_' . $isWap . '_' . $flag . '_' . $path . '_' . $queryString;
    }

    protected function shouldUsePageCache(Request $request): bool
    {
        if ((string) config('maccms.app.cache_page', '0') !== '1') {
            return false;
        }

        if ((int) config('maccms.app.cache_time_page', 0) < 1) {
            return false;
        }

        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        $path = trim($request->path(), '/');
        foreach (['admin', 'api', 'install', 'user', 'index.php/user'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return false;
            }
        }

        return true;
    }
}

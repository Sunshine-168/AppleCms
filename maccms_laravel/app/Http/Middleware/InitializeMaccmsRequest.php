<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class InitializeMaccmsRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('maccms', []);
        $domainConfig = config('domain', []);
        $host = $request->getHost();

        if (is_array($domainConfig) && isset($domainConfig[$host]) && is_array($domainConfig[$host])) {
            $config['site'] = array_merge($config['site'] ?? [], $domainConfig[$host]);
            if (empty($config['site']['mob_template_dir']) || $config['site']['mob_template_dir'] === 'no') {
                $config['site']['mob_template_dir'] = $config['site']['template_dir'] ?? 'default';
            }
            $config['site']['site_wapurl'] = $config['site']['site_url'] ?? $host;
            $config['site']['mob_html_dir'] = $config['site']['html_dir'] ?? 'html';
            $config['site']['mob_ads_dir'] = $config['site']['ads_dir'] ?? 'ads';
        }

        $isMobile = $this->isMobile($request);
        $isWap = $this->shouldUseMobileSite($config, $host, $isMobile);
        $templateDir = $isWap ? ($config['site']['mob_template_dir'] ?? 'default') : ($config['site']['template_dir'] ?? 'default');
        $htmlDir = $isWap ? ($config['site']['mob_html_dir'] ?? 'html') : ($config['site']['html_dir'] ?? 'html');
        $adsDir = $isWap ? ($config['site']['mob_ads_dir'] ?? 'ads') : ($config['site']['ads_dir'] ?? 'ads');

        $config['app']['search_len'] = max(10, (int) ($config['app']['search_len'] ?? 10));
        config(['maccms' => $config]);

        $locale = $this->mapLocale($config['app']['lang'] ?? '');
        if ($locale !== '') {
            App::setLocale($locale);
            config(['app.locale' => $locale]);
        }

        $installDir = rtrim((string) ($config['site']['install_dir'] ?? '/'), '/');
        $installDir = $installDir === '' ? '/' : $installDir . '/';
        $httpType = $request->getScheme() . '://';

        $constants = [
            'MAC_URL' => 'http://www.maccms.la/',
            'MAC_NAME' => '苹果CMS',
            'MAC_PATH' => $installDir,
            'MAC_MOB' => $isWap ? 1 : 0,
            'MAC_ROOT_TEMPLATE' => base_path('template/' . $templateDir . '/' . $htmlDir . '/'),
            'MAC_PATH_TEMPLATE' => $installDir . 'template/' . $templateDir . '/',
            'MAC_PATH_TPL' => $installDir . 'template/' . $templateDir . '/' . $htmlDir . '/',
            'MAC_PATH_ADS' => $installDir . 'template/' . $templateDir . '/' . $adsDir . '/',
            'MAC_PLAYER_SORT' => $config['app']['player_sort'] ?? '1',
            'MAC_ADDON_PATH' => base_path('addons') . DIRECTORY_SEPARATOR,
            'MAC_ADDON_PATH_STATIC' => public_path('static/addons') . DIRECTORY_SEPARATOR,
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        $GLOBALS['config'] = $config;
        $GLOBALS['MAC_ROOT_TEMPLATE'] = $constants['MAC_ROOT_TEMPLATE'];
        $GLOBALS['MAC_PATH_TEMPLATE'] = $constants['MAC_PATH_TEMPLATE'];
        $GLOBALS['MAC_PATH_TPL'] = $constants['MAC_PATH_TPL'];
        $GLOBALS['MAC_PATH_ADS'] = $constants['MAC_PATH_ADS'];
        $GLOBALS['http_type'] = $httpType;

        $request->attributes->set('maccms.is_mobile', $isMobile);
        $request->attributes->set('maccms.is_wap', $isWap);
        $request->attributes->set('maccms.template_dir', $templateDir);
        $request->attributes->set('maccms.html_dir', $htmlDir);
        $request->attributes->set('maccms.ads_dir', $adsDir);

        return $next($request);
    }

    protected function isMobile(Request $request): bool
    {
        return preg_match(
            "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|meizu|cldc|midp|iphone|wap|mobile|android)/i",
            strtolower((string) $request->userAgent())
        ) === 1;
    }

    protected function shouldUseMobileSite(array $config, string $host, bool $isMobile): bool
    {
        if (!$isMobile) {
            return false;
        }

        $site = $config['site'] ?? [];
        $mobStatus = (string) ($site['mob_status'] ?? '0');
        $wapHost = (string) ($site['site_wapurl'] ?? '');

        return $mobStatus === '2' || ($mobStatus === '1' && $wapHost !== '' && $host === $wapHost);
    }

    protected function mapLocale(string $locale): string
    {
        $map = [
            'zh-cn' => 'zh',
            'zh-tw' => 'zh_TW',
            'en-us' => 'en',
            'de-de' => 'de',
            'es-es' => 'es',
            'fr-fr' => 'fr',
            'ja-jp' => 'ja',
            'ko-kr' => 'ko',
            'pt-pt' => 'pt',
        ];

        return $map[strtolower($locale)] ?? '';
    }
}

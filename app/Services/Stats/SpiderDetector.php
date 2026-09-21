<?php

namespace App\Services\Stats;

class SpiderDetector
{
    /** @var array<string, list<string>> 蜘蛛名 => UA 关键词（小写） */
    protected array $bots = [
        'Baiduspider' => ['baiduspider'],
        'Googlebot' => ['googlebot', 'google-inspectiontool', 'adsbot-google', 'mediapartners-google', 'apis-google'],
        'Bingbot' => ['bingbot', 'msnbot', 'adidxbot'],
        'YandexBot' => ['yandexbot', 'yandex.com/bots'],
        'Sogou' => ['sogou'],
        '360Spider' => ['360spider', 'haosouspider'],
        'Bytespider' => ['bytespider'],
        'DuckDuckBot' => ['duckduckbot'],
        'Yahoo' => ['yahoo! slurp', 'slurp'],
        'Applebot' => ['applebot'],
        'Facebook' => ['facebookexternalhit', 'facebot', 'meta-externalagent'],
        'Twitterbot' => ['twitterbot'],
        'LinkedInBot' => ['linkedinbot'],
        'SemrushBot' => ['semrushbot'],
        'AhrefsBot' => ['ahrefsbot'],
        'MJ12bot' => ['mj12bot'],
        'DotBot' => ['dotbot'],
        'PetalBot' => ['petalbot'],
        'ClaudeBot' => ['claudebot', 'anthropic-ai'],
        'GPTBot' => ['gptbot', 'chatgpt-user'],
        'OtherBot' => ['bot', 'spider', 'crawl', 'fetcher'],
    ];

    /** @return array{0:bool,1:?string} [is_spider, name] */
    public function detect(?string $userAgent): array
    {
        $ua = strtolower(trim((string) $userAgent));
        if ($ua === '') {
            return [false, null];
        }

        foreach ($this->bots as $name => $keywords) {
            if ($name === 'OtherBot') {
                continue;
            }
            foreach ($keywords as $kw) {
                if (str_contains($ua, $kw)) {
                    return [true, $name];
                }
            }
        }

        foreach ($this->bots['OtherBot'] as $kw) {
            if (str_contains($ua, $kw)) {
                return [true, 'OtherBot'];
            }
        }

        return [false, null];
    }

    /** search | tool | ai | other */
    public function group(?string $name): string
    {
        return match ((string) $name) {
            'Baiduspider', 'Googlebot', 'Bingbot', 'YandexBot', 'Sogou', '360Spider',
            'Bytespider', 'DuckDuckBot', 'Yahoo', 'Applebot' => 'search',
            'SemrushBot', 'AhrefsBot', 'MJ12bot', 'DotBot', 'PetalBot' => 'tool',
            'ClaudeBot', 'GPTBot' => 'ai',
            default => 'other',
        };
    }

    public function groupLabel(string $group): string
    {
        return match ($group) {
            'search' => admin_t('ui.spider_search_engine'),
            'tool' => admin_t('ui.spider_seo_tools'),
            'ai' => admin_t('ui.spider_ai'),
            default => admin_t('ui.other'),
        };
    }

    /** @return list<string> */
    public function watchEngines(): array
    {
        return ['Baiduspider', 'Googlebot', 'Bingbot'];
    }

    public function watchLabel(string $name): string
    {
        return match ($name) {
            'Baiduspider' => admin_t('ui.engine_baidu'),
            'Googlebot' => 'Google',
            'Bingbot' => 'Bing',
            default => $name,
        };
    }

    public function displayName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return admin_t('ui.spider_default');
        }
        $watch = $this->watchLabel($name);
        if ($watch !== $name) {
            return $watch;
        }

        return match ($name) {
            'YandexBot' => 'Yandex',
            'Sogou' => admin_t('ui.engine_sogou'),
            '360Spider' => '360',
            'Bytespider' => admin_t('ui.engine_bytedance'),
            'DuckDuckBot' => 'DuckDuckGo',
            'OtherBot' => admin_t('ui.spider_other_bot'),
            'ClaudeBot' => 'Claude',
            'GPTBot' => 'GPT',
            default => $name,
        };
    }

    /** @return list<string> */
    public function engineNeedles(string $engine): array
    {
        return match ($engine) {
            'baidu' => $this->bots['Baiduspider'],
            'google' => $this->bots['Googlebot'],
            'bing' => $this->bots['Bingbot'],
            default => [],
        };
    }

    /** @return list<string> */
    public function watchNeedles(): array
    {
        return array_values(array_unique(array_merge(
            $this->engineNeedles('baidu'),
            $this->engineNeedles('google'),
            $this->engineNeedles('bing'),
        )));
    }
}

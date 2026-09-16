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
            'search' => '搜索引擎',
            'tool' => 'SEO 工具',
            'ai' => 'AI 爬虫',
            default => '其他',
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
            'Baiduspider' => '百度',
            'Googlebot' => 'Google',
            'Bingbot' => 'Bing',
            default => $name,
        };
    }
}

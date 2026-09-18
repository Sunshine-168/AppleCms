<?php

namespace Plugins\Advert\Services;

use App\Support\Utils\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Plugins\Advert\Models\PluginAd;
use Plugins\Advert\Models\PluginAdClick;
use Plugins\Advert\Models\PluginAdDay;

class AdvertService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_ads');
    }

    /** @return Collection<int, PluginAd> */
    public function slot(string $slot): Collection
    {
        if (! $this->ready() || ! in_array($slot, ['top', 'bottom', 'player', 'content'], true)) {
            return collect();
        }
        $now = time();
        $rows = PluginAd::query()
            ->where('slot', $slot)
            ->where('status', 1)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->get()
            ->filter(static function (PluginAd $ad) use ($now): bool {
                $start = (int) $ad->start_at;
                $end = (int) $ad->expire_at;
                if ($start > 0 && $start > $now) {
                    return false;
                }
                if ($end > 0 && $end < $now) {
                    return false;
                }

                return true;
            })
            ->values();
        foreach ($rows as $ad) {
            $this->impress($ad);
        }

        return $rows;
    }

    public function go(int $id, string $ip, string $ua, string $page): array
    {
        if (! $this->ready()) {
            return Result::fail('广告未启用');
        }
        $ad = PluginAd::query()->find($id);
        if (! $ad) {
            return Result::fail('找不到这条广告');
        }
        $url = self::httpUrl((string) $ad->url);
        if ($url === null) {
            return Result::fail('网址无效');
        }
        $ad->clicks = (int) $ad->clicks + 1;
        $ad->updated_at = time();
        $ad->save();
        $this->bumpDay((int) $ad->id, 'clicks');
        if (Schema::hasTable('plugin_ad_clicks')) {
            PluginAdClick::query()->create([
                'ad_id' => (int) $ad->id,
                'ip' => mb_substr($ip, 0, 45),
                'ua' => mb_substr($ua, 0, 255),
                'page' => mb_substr($page, 0, 255),
                'created_at' => time(),
            ]);
        }

        return Result::success(['url' => $url]);
    }

    public static function httpUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $url) === 1) {
            return null;
        }
        if (preg_match('#^https?://#i', $url) !== 1) {
            return null;
        }
        $parts = parse_url($url);
        if (! is_array($parts) || trim((string) ($parts['host'] ?? '')) === '') {
            return null;
        }

        return $url;
    }

    private function impress(PluginAd $ad): void
    {
        $key = 'plugin.ad.impressed';
        $done = request()->attributes->get($key, []);
        if (! is_array($done)) {
            $done = [];
        }
        $id = (int) $ad->id;
        if (isset($done[$id])) {
            return;
        }
        $done[$id] = true;
        request()->attributes->set($key, $done);
        PluginAd::query()->where('id', $id)->increment('impressions');
        $this->bumpDay($id, 'impressions');
    }

    private function bumpDay(int $adId, string $col): void
    {
        if (! Schema::hasTable('plugin_ad_days') || ! in_array($col, ['impressions', 'clicks'], true)) {
            return;
        }
        $day = date('Ymd');
        $row = PluginAdDay::query()->where('ad_id', $adId)->where('day_key', $day)->first();
        if ($row) {
            $row->{$col} = (int) $row->{$col} + 1;
            $row->save();

            return;
        }
        PluginAdDay::query()->create([
            'ad_id' => $adId,
            'day_key' => $day,
            'impressions' => $col === 'impressions' ? 1 : 0,
            'clicks' => $col === 'clicks' ? 1 : 0,
        ]);
    }
}

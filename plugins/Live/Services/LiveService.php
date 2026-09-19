<?php

namespace Plugins\Live\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Plugins\Live\Models\LiveCategory;
use Plugins\Live\Models\LiveChannel;

class LiveService
{
    /** 判断直播频道数据表是否可用。 */
    public function ready(): bool
    {
        return Schema::hasTable('plugin_live_categories') && Schema::hasTable('plugin_live_channels');
    }

    /**
     * 解析苹果 CMS 风格的多线路地址。
     *
     * @return list<array{name:string,url:string}>
     */
    public function parseUrlList(string $value): array
    {
        $items = [];
        $parts = preg_split('/#|\r\n|\r|\n/', $value) ?: [];
        foreach ($parts as $index => $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            [$name, $url] = array_pad(explode('$', $part, 2), 2, '');
            if ($url === '') {
                $url = trim($name);
                $name = '线路'.($index + 1);
            }
            $url = trim($url);
            if ($url === '') {
                continue;
            }
            $items[] = ['name' => trim($name) ?: '线路'.($index + 1), 'url' => $url];
        }

        return $items;
    }

    /** 获取启用的直播分类。 */
    public function publishedCategories(): Collection
    {
        return LiveCategory::query()->published()->orderByDesc('sort')->orderBy('id')->get();
    }

    /** 获取上线频道列表。 */
    public function publishedChannels(?int $cateId = null): Collection
    {
        $rows = LiveChannel::query()->published()->with('category')
            ->when($cateId, fn ($query) => $query->where('cate_id', $cateId))
            ->orderByDesc('sort')->orderBy('id')->get();

        return $rows->each(fn (LiveChannel $channel) => $this->decorate($channel));
    }

    /** 查找并装饰一个上线频道。 */
    public function findChannel(int $id): ?LiveChannel
    {
        if (! $this->ready()) {
            return null;
        }
        $channel = LiveChannel::query()->published()->with('category')->find($id);

        return $channel ? $this->decorate($channel) : null;
    }

    /** 增加频道播放次数。 */
    public function incrementHit(LiveChannel $channel): void
    {
        $channel->increment('hits');
    }

    /** 补齐前台使用的线路、链接和分类名。 */
    public function decorate(LiveChannel $channel): LiveChannel
    {
        $channel->setAttribute('url_list', $this->parseUrlList((string) $channel->urls));
        $channel->setAttribute('front_url', url('/live/'.$channel->id));
        $channel->setAttribute('cate_name', (string) ($channel->category?->name ?? '未分类'));

        return $channel;
    }
}

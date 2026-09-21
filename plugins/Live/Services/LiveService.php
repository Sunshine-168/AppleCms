<?php

namespace Plugins\Live\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    /** 分页获取上线频道（支持分类与搜索）。 */
    public function paginateChannels(?int $cateId = null, string $q = '', int $perPage = 24): LengthAwarePaginator
    {
        $q = trim($q);
        $page = LiveChannel::query()->published()->with('category')
            ->when($cateId && $cateId > 0, fn ($query) => $query->where('cate_id', $cateId))
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('title', 'like', '%'.$q.'%')
                    ->orWhere('sub', 'like', '%'.$q.'%')
                    ->orWhere('remarks', 'like', '%'.$q.'%');
            }))
            ->when(Schema::hasColumn('plugin_live_channels', 'recommend'), fn ($query) => $query->orderByDesc('recommend'))
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate(max(1, $perPage))
            ->withQueryString();

        $page->getCollection()->transform(fn (LiveChannel $channel) => $this->decorate($channel));

        return $page;
    }

    /** @return Collection|\Illuminate\Support\Collection */
    public function listForTag(array $options = []): Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
    {
        if (! $this->ready()) {
            return collect();
        }
        $num = max(1, (int) ($options['num'] ?? 12));
        $cateId = (int) ($options['typeid'] ?? $options['cate'] ?? 0);
        $wd = trim((string) ($options['wd'] ?? ''));
        $q = LiveChannel::query()->published()->with('category')
            ->when($cateId > 0, fn ($query) => $query->where('cate_id', $cateId))
            ->when($wd !== '', fn ($query) => $query->where(function ($inner) use ($wd) {
                $inner->where('title', 'like', '%'.$wd.'%')
                    ->orWhere('sub', 'like', '%'.$wd.'%')
                    ->orWhere('remarks', 'like', '%'.$wd.'%');
            }));
        if (! empty($options['ids'])) {
            $ids = is_array($options['ids'])
                ? $options['ids']
                : (preg_split('/\s*,\s*/', (string) $options['ids']) ?: []);
            $q->whereIn('id', array_map('intval', $ids));
        }
        $order = (string) ($options['order'] ?? 'time');
        if (($options['flag'] ?? '') === 'recommend' && Schema::hasColumn('plugin_live_channels', 'recommend')) {
            $q->where('recommend', '>', 0);
        }
        if (($options['flag'] ?? '') === 'hot' || $order === 'hits') {
            $q->orderByDesc('hits')->orderByDesc('id');
        } else {
            $q->when(Schema::hasColumn('plugin_live_channels', 'recommend'), fn ($query) => $query->orderByDesc('recommend'))
                ->orderByDesc('sort')
                ->orderByDesc('id');
        }
        if (! empty($options['page'])) {
            $page = $q->paginate($num)->withQueryString();
            $page->getCollection()->transform(fn (LiveChannel $channel) => $this->decorate($channel));

            return $page;
        }
        $rows = $q->limit($num)->get();

        return $rows->each(fn (LiveChannel $channel) => $this->decorate($channel));
    }

    /** 推荐频道（recommend > 0）。 */
    public function recommendedChannels(int $limit = 8): Collection
    {
        if (! $this->ready() || ! Schema::hasColumn('plugin_live_channels', 'recommend')) {
            return new Collection;
        }
        $rows = LiveChannel::query()->published()->with('category')
            ->where('recommend', '>', 0)
            ->orderByDesc('recommend')
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        return $rows->each(fn (LiveChannel $channel) => $this->decorate($channel));
    }

    /** 同分类相关频道。 */
    public function relatedChannels(LiveChannel $channel, int $limit = 12): Collection
    {
        $cateId = (int) ($channel->cate_id ?? 0);
        $rows = LiveChannel::query()->published()->with('category')
            ->where('id', '!=', (int) $channel->id)
            ->when($cateId > 0, fn ($query) => $query->where('cate_id', $cateId))
            ->when(Schema::hasColumn('plugin_live_channels', 'recommend'), fn ($query) => $query->orderByDesc('recommend'))
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        return $rows->each(fn (LiveChannel $row) => $this->decorate($row));
    }

    /** @deprecated 兼容旧调用，返回全量上线频道 */
    public function publishedChannels(?int $cateId = null): Collection
    {
        $rows = LiveChannel::query()->published()->with('category')
            ->when($cateId, fn ($query) => $query->where('cate_id', $cateId))
            ->when(Schema::hasColumn('plugin_live_channels', 'recommend'), fn ($query) => $query->orderByDesc('recommend'))
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

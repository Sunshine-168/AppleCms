<?php

namespace Plugins\Scout\Services;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoModel;
use App\Support\Plugins\PluginManager;
use App\Services\Video\VideoSettingService;
use Illuminate\Support\Facades\Schema;

class ScoutSearchService
{
    /**
     * 插件是否启用且已安装 Scout。
     */
    public function pluginReady(): bool
    {
        if (! class_exists(\Laravel\Scout\EngineManager::class)) {
            return false;
        }
        try {
            return app(PluginManager::class)->isEnabled('scout');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * 前台关键词是否走 Scout（否时回退 LIKE）。
     */
    public function searchEnabled(): bool
    {
        if (! $this->pluginReady()) {
            return false;
        }

        return (string) $this->opt('scout_search_enabled', '1') === '1';
    }

    /**
     * 模型是否应写入索引。
     */
    public function indexingEnabled(): bool
    {
        return $this->pluginReady() && $this->searchEnabled();
    }

    /**
     * 把设置应用到 config('scout.*')。
     */
    public function applyConfig(): void
    {
        if (! $this->pluginReady()) {
            return;
        }
        $driver = strtolower(trim((string) $this->opt('scout_driver', config('scout.driver', 'database'))));
        if (! in_array($driver, ['database', 'collection', 'meilisearch', 'null'], true)) {
            $driver = 'database';
        }
        config(['scout.driver' => $driver]);
        if ($driver === 'meilisearch') {
            $host = rtrim(trim((string) $this->opt('scout_meili_host', '')), '/');
            if ($host !== '') {
                config(['scout.meilisearch.host' => $host]);
            }
            $key = trim((string) $this->opt('scout_meili_key', ''));
            if ($key !== '') {
                config(['scout.meilisearch.key' => $key]);
            }
        }
    }

    /**
     * 用 Scout 搜影片 ID；失败返回 null 以便回退 LIKE。
     *
     * @return list<int>|null
     */
    public function searchVideoIds(string $keyword, int $limit = 500): ?array
    {
        if (! $this->searchEnabled()) {
            return null;
        }
        $keyword = trim($keyword);
        if ($keyword === '') {
            return null;
        }
        $this->applyConfig();
        try {
            if (! in_array(\Laravel\Scout\Searchable::class, class_uses_recursive(VideoModel::class), true)) {
                return null;
            }
            $ids = VideoModel::search($keyword)->take($limit)->keys()->map(static fn ($id) => (int) $id)->all();

            return array_values(array_unique(array_filter($ids)));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * 重建影片/资讯索引。
     *
     * @return array{ok:bool,msg:string,videos:int,arts:int}
     */
    public function syncAll(): array
    {
        if (! $this->pluginReady()) {
            return ['ok' => false, 'msg' => '全文搜索插件未启用', 'videos' => 0, 'arts' => 0];
        }
        $this->applyConfig();
        $videos = 0;
        $arts = 0;
        try {
            if (Schema::hasTable('videos')) {
                VideoModel::query()->searchable();
                $videos = (int) VideoModel::query()->count();
            }
            if (Schema::hasTable('video_arts') && in_array(\Laravel\Scout\Searchable::class, class_uses_recursive(VideoArt::class), true)) {
                VideoArt::query()->searchable();
                $arts = (int) VideoArt::query()->count();
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => '重建失败：'.$e->getMessage(), 'videos' => $videos, 'arts' => $arts];
        }

        return ['ok' => true, 'msg' => '已重建索引', 'videos' => $videos, 'arts' => $arts];
    }

    /**
     * 状态摘要（后台展示）。
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $this->applyConfig();

        return [
            'plugin' => $this->pluginReady(),
            'search_enabled' => $this->searchEnabled(),
            'driver' => (string) config('scout.driver', 'database'),
            'meili_host' => (string) config('scout.meilisearch.host', ''),
            'video_count' => Schema::hasTable('videos') ? (int) VideoModel::query()->count() : 0,
            'art_count' => Schema::hasTable('video_arts') ? (int) VideoArt::query()->count() : 0,
        ];
    }

    private function opt(string $key, mixed $default = ''): mixed
    {
        try {
            return app(VideoSettingService::class)->get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

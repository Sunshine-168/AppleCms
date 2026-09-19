<?php

namespace Plugins\Scout\Services;

use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use App\Services\Video\VideoSettingService;

class ScoutAdmin
{
    public function __construct(
        private readonly ScoutSearchService $scout,
        private readonly VideoSettingService $settings,
    ) {}

    /**
     * 工作台附加数据。
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function boardPayload(array $payload): array
    {
        $payload['status'] = $this->scout->status();
        $payload['options'] = [
            'scout_search_enabled' => (string) $this->settings->get('scout_search_enabled', '1'),
            'scout_driver' => (string) $this->settings->get('scout_driver', config('scout.driver', 'database')),
            'scout_meili_host' => (string) $this->settings->get('scout_meili_host', ''),
            'scout_meili_key_set' => trim((string) $this->settings->get('scout_meili_key', '')) !== '',
        ];

        return $payload;
    }

    /**
     * 列表（设置台占位一行）。
     *
     * @param  array<string, mixed>  $params
     */
    public function lists(array $params): array
    {
        $st = $this->scout->status();

        return Result::success(AdminPage::slice([[
            'id' => 1,
            'driver' => $st['driver'],
            'search_enabled' => $st['search_enabled'] ? 1 : 0,
            'video_count' => $st['video_count'],
            'art_count' => $st['art_count'],
        ]], $params));
    }

    /**
     * 保存 Scout 设置或触发重建。
     *
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, ?int $id = null): array
    {
        $action = strtolower(trim((string) ($data['action'] ?? 'settings')));
        if ($action === 'sync') {
            $res = $this->scout->syncAll();

            return AdminOpLog::ifOk(
                $res['ok'] ? Result::success($res, $res['msg']) : Result::fail($res['msg']),
                'save',
                '重建了全文搜索索引',
                ['target_type' => 'scout', 'target_id' => 0]
            );
        }

        $enabled = (string) ($data['scout_search_enabled'] ?? '0') === '1' ? '1' : '0';
        $driver = strtolower(trim((string) ($data['scout_driver'] ?? 'database')));
        if (! in_array($driver, ['database', 'collection', 'meilisearch'], true)) {
            $driver = 'database';
        }
        $host = rtrim(trim((string) ($data['scout_meili_host'] ?? '')), '/');
        $payload = [
            'scout_search_enabled' => $enabled,
            'scout_driver' => $driver,
            'scout_meili_host' => $host,
            'tab' => 'scout',
        ];
        if (array_key_exists('scout_meili_key', $data) && trim((string) $data['scout_meili_key']) !== '') {
            $payload['scout_meili_key'] = trim((string) $data['scout_meili_key']);
        }
        $saved = $this->settings->save($payload);
        if ((int) ($saved['code'] ?? 1) !== 0) {
            return $saved;
        }
        $this->scout->applyConfig();

        return AdminOpLog::ifOk(Result::success(['ok' => 1]), 'save', '保存了全文搜索设置', [
            'target_type' => 'scout',
            'target_id' => 0,
        ]);
    }

    /**
     * 不允许删除。
     */
    public function delete(int $id): array
    {
        return Result::fail('全文搜索没有可删记录');
    }

    /**
     * 不允许批量。
     *
     * @param  list<int>  $ids
     */
    public function batch(array $ids, string $action, mixed $value = null): array
    {
        return Result::fail('全文搜索不支持批量');
    }
}

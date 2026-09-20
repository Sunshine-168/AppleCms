<?php

namespace Plugins\CjRule\Services;

use App\Models\Video\VideoCjRule;
use App\Models\Video\VideoTypeModel;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\CjRule\Models\CjRuleLog;

class CjRuleAdmin
{
    public function __construct(private readonly CjRuleService $cj) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $desk = (string) ($payload['desk'] ?? 'rules');
        $payload['types'] = $this->typeOptions(1);
        $payload['vod_types'] = $payload['types'];
        $payload['art_types'] = $this->typeOptions(2);
        $payload['manga_types'] = $this->mangaTypeOptions();
        $payload['manga_ready'] = $this->cj->mangaReady();
        $payload['collector'] = $this->blankForm();
        $payload['recent_logs'] = [];
        if ($desk === 'form') {
            $id = (int) request()->query('id', 0);
            if ($id > 0) {
                $row = VideoCjRule::query()->find($id);
                if ($row) {
                    $payload['collector'] = $this->present($row);
                    if (Schema::hasTable('video_cj_rule_logs')) {
                        $payload['recent_logs'] = CjRuleLog::query()
                            ->where('rule_id', $id)
                            ->orderByDesc('id')
                            ->limit(8)
                            ->get()
                            ->map(fn (CjRuleLog $log) => $this->presentLog($log, (string) $row->name))
                            ->all();
                    }
                }
            }
        }

        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! $this->cj->ready()) {
            return Result::fail(admin_t('ui.cj_migrate_first'));
        }
        $desk = $this->desk($params);
        if ($desk === 'logs') {
            return Result::success($this->logsPage($params));
        }
        $q = VideoCjRule::query()->orderByDesc('id');
        $kw = trim((string) ($params['q'] ?? $params['name'] ?? ''));
        if ($kw !== '') {
            $q->where(function ($inner) use ($kw) {
                $inner->where('name', 'like', '%'.$kw.'%')->orWhere('url', 'like', '%'.$kw.'%');
            });
        }
        if (in_array((string) ($params['status'] ?? ''), ['0', '1'], true)) {
            $q->where('status', (int) $params['status']);
        }
        $type = strtolower(trim((string) ($params['type'] ?? '')));
        if (in_array($type, ['html', 'rss', 'json'], true)) {
            $q->where('type', $type);
        }

        return Result::success($this->page($q, $params, fn (VideoCjRule $row) => $this->present($row)));
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        if (! $this->cj->ready()) {
            return Result::fail(admin_t('ui.cj_migrate_first'));
        }
        if ($this->desk($data) === 'logs') {
            return Result::fail(admin_t('ui.cj_logs_readonly'));
        }
        $attrs = $this->cj->attributesFromInput($data);
        if (trim((string) $attrs['url']) === '' || ! preg_match('#^https?://#i', (string) $attrs['url'])) {
            return Result::fail(admin_t('ui.cj_url_http'));
        }
        if ($attrs['type'] === 'html' && trim((string) ($attrs['options']['item_selector'] ?? '')) === '') {
            return Result::fail(admin_t('ui.cj_need_item_selector'));
        }
        $into = (string) ($attrs['options']['into'] ?? 'vod');
        if ($into === 'manga' && ! $this->cj->mangaReady()) {
            return Result::fail(admin_t('ui.cj_manga_off'));
        }
        if ($into === 'art' && ! Schema::hasTable('video_arts')) {
            return Result::fail(admin_t('ui.cj_migrate_first'));
        }
        $row = $id ? VideoCjRule::query()->find($id) : new VideoCjRule;
        if ($id && ! $row) {
            return Result::fail(admin_t('ui.cj_rule_missing'));
        }
        $row->fill($attrs);
        $row->save();

        return AdminOpLog::ifOk(
            Result::success($this->present($row->fresh() ?? $row), $id ? admin_t('ui.saved') : admin_t('ui.added')),
            $id ? 'update' : 'create',
            ($id ? '改了网站采集 ' : '加了网站采集 ').$row->name,
            ['module' => 'cj', 'target_id' => (int) $row->id]
        );
    }

    public function delete(int $id): array
    {
        $row = VideoCjRule::query()->find($id);
        if (! $row) {
            return Result::fail(admin_t('ui.cj_rule_missing'));
        }
        $name = (string) $row->name;
        $row->delete();
        if (Schema::hasTable('video_cj_rule_logs')) {
            CjRuleLog::query()->where('rule_id', $id)->delete();
        }

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除网站采集 '.$name, [
            'module' => 'cj',
            'target_id' => $id,
        ]);
    }

    public function toggle(int $id): array
    {
        $row = VideoCjRule::query()->find($id);
        if (! $row) {
            return Result::fail(admin_t('ui.cj_rule_missing'));
        }
        $row->status = (int) $row->status === 1 ? 0 : 1;
        $row->save();

        return AdminOpLog::ifOk(
            Result::success($this->present($row), $row->status ? admin_t('ui.cj_enabled_run') : admin_t('ui.cj_disabled_stop')),
            'update',
            ($row->status ? '启用了网站采集 ' : '停用了网站采集 ').$row->name,
            ['module' => 'cj', 'target_id' => $id]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function present(VideoCjRule $row): array
    {
        $opts = is_array($row->options) ? $row->options : [];
        $typeName = VideoCjRule::typeLabel((string) ($row->type ?: 'html'));
        $into = $this->cj->into($row);
        $lastAt = (int) ($row->last_run_at ?? 0);

        return [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'type' => (string) ($row->type ?: 'html'),
            'type_label' => $typeName,
            'into' => $into,
            'into_label' => CjRuleService::intoLabel($into),
            'url' => (string) $row->url,
            'source_url' => (string) $row->url,
            'type_id' => (int) ($row->type_id ?? 0),
            'type_name' => $this->typeName($into, (int) ($row->type_id ?? 0)),
            'interval_minutes' => max(1, (int) ($row->interval_minutes ?: 60)),
            'limit_items' => max(1, (int) ($row->limit_items ?: 10)),
            'status' => (int) $row->status,
            'is_active' => (int) $row->status,
            'publish_immediately' => (int) ($row->publish_immediately ?? 0),
            'note' => (string) ($row->note ?? ''),
            'last_run_at' => $lastAt,
            'last_run_text' => $lastAt > 0 ? date('m-d H:i', $lastAt) : admin_t('ui.never_ran'),
            'last_status' => (string) ($row->last_status ?? ''),
            'last_message' => (string) ($row->last_message ?? ''),
            'item_selector' => (string) ($opts['item_selector'] ?? $row->list_rule ?? ''),
            'link_selector' => (string) ($opts['link_selector'] ?? 'a'),
            'title_selector' => (string) ($opts['title_selector'] ?? ''),
            'summary_selector' => (string) ($opts['summary_selector'] ?? ''),
            'cover_selector' => (string) ($opts['cover_selector'] ?? ''),
            'play_selector' => (string) ($opts['play_selector'] ?? ''),
            'detail_content_selector' => (string) ($opts['detail_content_selector'] ?? ''),
            'page_count' => max(1, (int) ($opts['page_count'] ?? 1)),
            'page_url' => (string) ($opts['page_url'] ?? ''),
            'next_selector' => (string) ($opts['next_selector'] ?? ''),
            'list_path' => (string) ($opts['list_path'] ?? 'items'),
            'title_key' => (string) ($opts['title_key'] ?? 'title'),
            'link_key' => (string) ($opts['link_key'] ?? 'url'),
            'summary_key' => (string) ($opts['summary_key'] ?? 'summary'),
            'content_key' => (string) ($opts['content_key'] ?? 'content'),
            'guid_key' => (string) ($opts['guid_key'] ?? 'id'),
            'cover_key' => (string) ($opts['cover_key'] ?? 'cover'),
            'play_key' => (string) ($opts['play_key'] ?? 'play'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function blankForm(): array
    {
        return [
            'id' => 0,
            'name' => '',
            'type' => 'html',
            'into' => 'vod',
            'url' => '',
            'source_url' => '',
            'type_id' => 0,
            'interval_minutes' => 60,
            'limit_items' => 10,
            'status' => 1,
            'is_active' => 1,
            'publish_immediately' => 0,
            'note' => '',
            'item_selector' => '',
            'link_selector' => 'a',
            'title_selector' => '',
            'summary_selector' => '',
            'cover_selector' => '',
            'play_selector' => '',
            'detail_content_selector' => '',
            'page_count' => 1,
            'page_url' => '',
            'next_selector' => '',
            'list_path' => 'items',
            'title_key' => 'title',
            'link_key' => 'url',
            'summary_key' => 'summary',
            'content_key' => 'content',
            'guid_key' => 'id',
            'cover_key' => 'cover',
            'play_key' => 'play',
        ];
    }

    /** @return list<array{id:int,name:string}> */
    public function typeOptions(int $mid = 1): array
    {
        if (! Schema::hasTable('video_types')) {
            return [];
        }
        $q = VideoTypeModel::query()->orderByDesc('sort')->orderBy('id');
        if (Schema::hasColumn('video_types', 'mid')) {
            $q->where('mid', $mid);
        } elseif ($mid !== 1) {
            return [];
        }

        return $q->get(['id', 'name'])->map(static fn ($row): array => [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
        ])->all();
    }

    /** @return list<array{id:int,name:string}> */
    private function mangaTypeOptions(): array
    {
        if (! $this->cj->mangaReady() || ! Schema::hasTable('plugin_manga_types')) {
            return [];
        }

        return \Plugins\Manga\Models\MangaType::query()
            ->orderByDesc('sort')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
            ])
            ->all();
    }

    /** @param  array<string, mixed>  $params */
    private function logsPage(array $params): array
    {
        if (! Schema::hasTable('video_cj_rule_logs')) {
            return AdminPage::slice([], $params);
        }
        $q = CjRuleLog::query()->orderByDesc('id');
        $kw = trim((string) ($params['q'] ?? ''));
        if ($kw !== '') {
            $q->where('message', 'like', '%'.$kw.'%');
            if (ctype_digit($kw)) {
                $q->orWhere('rule_id', (int) $kw);
            }
        }
        $names = [];
        if (Schema::hasTable('video_cj_rules')) {
            $names = VideoCjRule::query()->pluck('name', 'id')->all();
        }

        return $this->page($q, $params, function (CjRuleLog $row) use ($names): array {
            return $this->presentLog($row, (string) ($names[(int) $row->rule_id] ?? ''));
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function presentLog(CjRuleLog $row, string $ruleName): array
    {
        $at = (int) ($row->created_at ?? 0);

        return [
            'id' => (int) $row->id,
            'rule_id' => (int) $row->rule_id,
            'rule_name' => $ruleName !== '' ? $ruleName : ('#'.$row->rule_id),
            'status' => (string) $row->status,
            'status_label' => (string) $row->status === 'ok' ? admin_t('ui.success') : ((string) $row->status === 'fail' ? admin_t('ui.fail') : (string) $row->status),
            'fetched' => (int) $row->fetched,
            'created' => (int) $row->created,
            'skipped' => (int) $row->skipped,
            'message' => (string) $row->message,
            'created_at' => $at > 0 ? date('Y-m-d H:i', $at) : '',
        ];
    }

    private function typeName(string $into, int $id): string
    {
        if ($id < 1) {
            return '';
        }
        if ($into === 'manga') {
            if (! Schema::hasTable('plugin_manga_types')) {
                return '';
            }

            return (string) (\Plugins\Manga\Models\MangaType::query()->where('id', $id)->value('name') ?? '');
        }
        if (! Schema::hasTable('video_types')) {
            return '';
        }

        return (string) (VideoTypeModel::query()->where('id', $id)->value('name') ?? '');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $params
     * @param  callable(\Illuminate\Database\Eloquent\Model): array<string, mixed>  $map
     * @return array{total:int,per_page:int,current_page:int,last_page:int,data:list<array<string, mixed>>}
     */
    private function page($q, array $params, callable $map): array
    {
        $limit = max(1, (int) ($params['limit'] ?? 15));
        $pageNo = max(1, (int) ($params['page'] ?? request()->input('page', 1)));
        $page = $q->paginate($limit, ['*'], 'page', $pageNo);
        $rows = [];
        foreach ($page->items() as $row) {
            $rows[] = $map($row);
        }

        return AdminPage::of($page, $rows);
    }

    /** @param  array<string, mixed>  $params */
    private function desk(array $params): string
    {
        $desk = strtolower(trim((string) ($params['desk'] ?? request()->input('desk', ''))));

        return in_array($desk, ['rules', 'form', 'logs'], true) ? $desk : 'rules';
    }
}

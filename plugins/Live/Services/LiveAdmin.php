<?php

namespace Plugins\Live\Services;

use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\Live\Models\LiveCategory;
use Plugins\Live\Models\LiveChannel;

class LiveAdmin
{
    public function __construct(
        private readonly LiveService $service,
        private readonly LiveStatsService $stats,
    ) {}

    /** 组装直播后台工作台数据。 */
    public function boardPayload(array $payload): array
    {
        $payload['categories'] = LiveCategory::query()->orderByDesc('sort')->orderBy('id')->get();
        $desk = (string) ($payload['desk'] ?? request('desk', 'channels'));
        if ($desk === 'stats') {
            $payload['stats'] = $this->stats->summary();
        }

        return $payload;
    }

    /** 获取直播后台列表。 */
    public function lists(array $params): array
    {
        if (! $this->service->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($params);
        if ($desk === 'stats') {
            return Result::success(['list' => [], 'count' => 0]);
        }
        $query = $desk === 'categories'
            ? LiveCategory::query()
            : LiveChannel::query()->with('category')->when($desk === 'pending', fn ($q) => $q->where('status', 0));
        $keyword = trim((string) ($params['q'] ?? $params['title'] ?? ''));
        if ($keyword !== '') {
            $query->where($desk === 'categories' ? 'name' : 'title', 'like', '%'.$keyword.'%');
        }
        if ($desk !== 'categories' && (int) ($params['cate_id'] ?? 0) > 0) {
            $query->where('cate_id', (int) $params['cate_id']);
        }
        $page = $query->orderByDesc('sort')->orderByDesc('id')->paginate(max(1, (int) ($params['limit'] ?? 20)));
        $hasRecommend = Schema::hasColumn('plugin_live_channels', 'recommend');
        $rows = collect($page->items())->map(function ($row) use ($desk, $hasRecommend) {
            $data = $row->toArray();
            if ($desk !== 'categories') {
                $data['cate_name'] = (string) ($row->category?->name ?? '未分类');
                $data['recommend'] = $hasRecommend ? (int) ($row->recommend ?? 0) : 0;
                $data['hits'] = (int) ($row->hits ?? 0);
                $data['front_url'] = url('/live/'.$row->id);
                $data['edit_url'] = '/admin/video/live-channels/'.$row->id.'/edit';
            } else {
                $data['edit_url'] = '/admin/video/live-categories/'.$row->id.'/edit';
                $data['channel_count'] = (int) LiveChannel::query()->where('cate_id', $row->id)->count();
            }

            return $data;
        })->all();

        return Result::success(AdminPage::of($page, $rows));
    }

    /** 保存直播频道或分类。 */
    public function save(array $data, ?int $id = null): array
    {
        $desk = $this->desk($data);
        if ($desk === 'stats') {
            return Result::fail('统计只读');
        }
        $isCategory = $desk === 'categories';
        $class = $isCategory ? LiveCategory::class : LiveChannel::class;
        $row = $id ? $class::query()->find($id) : new $class;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        $title = trim((string) ($data[$isCategory ? 'name' : 'title'] ?? ''));
        if (! $id && $title === '') {
            return Result::fail($isCategory ? '请填写分类名' : '请填写频道名');
        }
        $now = time();
        if ($isCategory) {
            foreach (['name', 'slug', 'pic'] as $field) {
                if (array_key_exists($field, $data)) {
                    $row->{$field} = trim((string) $data[$field]);
                }
            }
            foreach (['sort', 'status'] as $field) {
                if (array_key_exists($field, $data) || ! $id) {
                    $row->{$field} = max(0, (int) ($data[$field] ?? ($field === 'status' ? 1 : 0)));
                }
            }
            if (array_key_exists('status', $data)) {
                $row->status = (int) $data['status'] === 1 ? 1 : 0;
            }
            if (! $id && empty($row->slug) && $title !== '') {
                $row->slug = Str::slug($title) ?: 'cate-'.time();
            }
        } else {
            foreach (['title', 'sub', 'slug', 'cover', 'urls', 'remarks', 'content'] as $field) {
                if (array_key_exists($field, $data)) {
                    $row->{$field} = trim((string) $data[$field]);
                }
            }
            if (array_key_exists('play_from', $data) || ! $id) {
                $row->play_from = 'hls';
            }
            if (! $id && ! array_key_exists('urls', $data)) {
                $row->urls = '';
            }
            foreach (['cate_id', 'hits', 'sort', 'status'] as $field) {
                if (array_key_exists($field, $data) || ! $id) {
                    $row->{$field} = max(0, (int) ($data[$field] ?? ($field === 'status' ? 1 : 0)));
                }
            }
            if (array_key_exists('status', $data)) {
                $row->status = (int) $data['status'] === 1 ? 1 : 0;
            }
            if (Schema::hasColumn('plugin_live_channels', 'recommend')
                && (array_key_exists('recommend', $data) || ! $id)) {
                $row->recommend = min(9, max(0, (int) ($data['recommend'] ?? 0)));
            }
            if (! $id && empty($row->slug) && $title !== '') {
                $row->slug = Str::slug($title) ?: 'live-'.time();
            }
        }
        $row->updated_at = $now;
        if (! $id) {
            $row->created_at = $now;
        }
        $row->save();

        return Result::success(['id' => (int) $row->id], '已保存');
    }

    /** 删除直播频道或分类。 */
    public function delete(int $id): array
    {
        $isCategory = $this->desk(request()->all()) === 'categories';
        $class = $isCategory ? LiveCategory::class : LiveChannel::class;
        $row = $class::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if ($isCategory) {
            LiveChannel::query()->where('cate_id', $id)->update(['cate_id' => 0]);
        }
        $row->delete();

        return Result::success([], '已删除');
    }

    /** 批量处理直播频道或分类。 */
    public function batch(array $ids, string $action, mixed $value): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail('请选择数据');
        }
        $isCategory = $this->desk(request()->all()) === 'categories';
        $class = $isCategory ? LiveCategory::class : LiveChannel::class;
        if ($action === 'delete') {
            if ($isCategory) {
                LiveChannel::query()->whereIn('cate_id', $ids)->update(['cate_id' => 0]);
            }
            $class::query()->whereIn('id', $ids)->delete();
        } elseif ($action === 'status') {
            $class::query()->whereIn('id', $ids)->update(['status' => (int) $value === 1 ? 1 : 0]);
        } else {
            return Result::fail('不支持的操作');
        }

        return Result::success([], '已处理');
    }

    /** 解析当前直播工作台。 */
    private function desk(array $data): string
    {
        $desk = (string) ($data['desk'] ?? request()->input('desk', 'channels'));

        return in_array($desk, ['channels', 'pending', 'categories', 'stats'], true) ? $desk : 'channels';
    }
}

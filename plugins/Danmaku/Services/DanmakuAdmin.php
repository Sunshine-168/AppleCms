<?php

namespace Plugins\Danmaku\Services;

use App\Models\Member\Member;
use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Danmaku\Models\Danmaku;

class DanmakuAdmin
{
    public function __construct(private readonly DanmakuService $danmaku) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $settings = app(VideoSettingService::class);
        $payload['queues'] = $this->queues();
        $payload['options'] = [
            'danmaku_enabled' => (int) $settings->get('danmaku_enabled', '1') === 1 ? 1 : 0,
            'danmaku_login' => (int) $settings->get('danmaku_login', '0') === 1 ? 1 : 0,
        ];

        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! Schema::hasTable('video_danmaku')) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($this->desk($params) === 'settings') {
            return Result::success(AdminPage::slice([], $params));
        }
        $q = Danmaku::query()->orderByDesc('id');
        $kw = mb_substr(trim((string) ($params['q'] ?? '')), 0, 30);
        if ($kw !== '') {
            $q->where('text', 'like', '%'.$kw.'%');
        }
        if (in_array((string) ($params['status'] ?? ''), ['0', '1'], true)) {
            $q->where('status', (int) $params['status']);
        }
        $videoId = (int) ($params['video_id'] ?? 0);
        if ($videoId > 0) {
            $q->where('video_id', $videoId);
        }
        $uid = (int) ($params['member_id'] ?? $params['uid'] ?? 0);
        if ($uid > 0) {
            $q->where('member_id', $uid);
        }
        $episodeId = (int) ($params['episode_id'] ?? 0);
        if ($episodeId > 0) {
            $q->where('episode_id', $episodeId);
        }
        if ((string) ($params['report'] ?? '') === '1' && Schema::hasColumn('video_danmaku', 'report')) {
            $q->where('report', '>', 0);
        }
        if (in_array((string) ($params['mode'] ?? ''), ['0', '1', '2'], true)) {
            $q->where('mode', (int) $params['mode']);
        }

        return Result::success($this->page($q, $params));
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        if ($this->desk($data) === 'settings') {
            return $this->saveSettings($data);
        }
        if (! $id) {
            return Result::fail('弹幕由播放器产生，不能手添');
        }
        $row = Danmaku::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if (! array_key_exists('status', $data)) {
            return Result::fail('后台只改显示或隐藏，不改原文');
        }
        $status = (int) $data['status'] === 1 ? 1 : 0;
        $row->status = $status;
        $row->save();

        return AdminOpLog::ifOk(Result::success(), 'update', $status === 1 ? '显示弹幕 #'.$id : '隐藏弹幕 #'.$id, [
            'module' => 'danmaku',
            'target_id' => $id,
        ]);
    }

    public function delete(int $id): array
    {
        if ($this->desk(request()->all()) === 'settings') {
            return Result::fail('设置没有可删的行');
        }
        $row = Danmaku::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $row->delete();

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除弹幕 #'.$id, [
            'module' => 'danmaku',
            'target_id' => $id,
        ]);
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function batch(array $ids, string $action, mixed $value = ''): array
    {
        if ($this->desk(request()->all()) === 'settings') {
            return Result::fail('设置没有批量操作');
        }
        if (! Schema::hasTable('video_danmaku')) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($action === 'clear') {
            $n = Danmaku::query()->count();
            Danmaku::query()->delete();

            return AdminOpLog::ifOk(Result::success(['deleted' => $n]), 'delete', '清空弹幕 '.$n.' 条', [
                'module' => 'danmaku',
            ]);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail('请先勾选弹幕');
        }
        if ($action === 'delete') {
            Danmaku::query()->whereIn('id', $ids)->delete();

            return AdminOpLog::ifOk(Result::success(), 'delete', '批量删除弹幕 '.count($ids).' 条', [
                'module' => 'danmaku',
            ]);
        }
        if ($action === 'status') {
            $status = (int) $value === 1 ? 1 : 0;
            Danmaku::query()->whereIn('id', $ids)->update(['status' => $status]);

            return AdminOpLog::ifOk(Result::success(), 'update', ($status === 1 ? '批量显示' : '批量隐藏').'弹幕 '.count($ids).' 条', [
                'module' => 'danmaku',
            ]);
        }

        return Result::fail('不支持的操作');
    }

    /** @return array{all:int,on:int,off:int,report:int} */
    private function queues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'report' => 0];
        if (! Schema::hasTable('video_danmaku')) {
            return $zero;
        }
        $zero['all'] = (int) Danmaku::query()->count();
        $zero['on'] = (int) Danmaku::query()->where('status', 1)->count();
        $zero['off'] = (int) Danmaku::query()->where('status', 0)->count();
        if (Schema::hasColumn('video_danmaku', 'report')) {
            $zero['report'] = (int) Danmaku::query()->where('report', '>', 0)->count();
        }

        return $zero;
    }

    /** @param  array<string, mixed>  $data */
    private function saveSettings(array $data): array
    {
        $ok = app(VideoSettingService::class)->saveOptions([
            'danmaku_enabled' => (int) ($data['danmaku_enabled'] ?? 0) === 1 ? '1' : '0',
            'danmaku_login' => (int) ($data['danmaku_login'] ?? 0) === 1 ? '1' : '0',
        ]);
        if (($ok['code'] ?? 1) !== 0) {
            return $ok;
        }

        return AdminOpLog::ifOk($ok, 'save', '改了弹幕开关', [
            'module' => 'danmaku',
            'target_type' => 'settings',
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $params
     * @return array{total:int,per_page:int,current_page:int,last_page:int,data:list<array<string, mixed>>}
     */
    private function page($q, array $params): array
    {
        $limit = max(1, min(100, (int) ($params['limit'] ?? 15)));
        $pageNo = max(1, (int) ($params['page'] ?? request()->input('page', 1)));
        $page = $q->paginate($limit, ['*'], 'page', $pageNo);
        $videoIds = [];
        $memberIds = [];
        foreach ($page->items() as $row) {
            $videoIds[] = (int) $row->video_id;
            $memberIds[] = (int) $row->member_id;
        }
        $titles = $this->videoTitles($videoIds);
        $names = $this->memberNames($memberIds);
        $rows = [];
        foreach ($page->items() as $row) {
            $rows[] = $this->present($row, $titles, $names);
        }

        return AdminPage::of($page, $rows);
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, string>
     */
    private function videoTitles(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === [] || ! Schema::hasTable('videos')) {
            return [];
        }

        return VideoModel::query()->whereIn('id', $ids)->pluck('title', 'id')->all();
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, string>
     */
    private function memberNames(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === [] || ! Schema::hasTable('members')) {
            return [];
        }

        return Member::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    /**
     * @param  array<int, string>  $titles
     * @param  array<int, string>  $names
     * @return array<string, mixed>
     */
    private function present(Danmaku $row, array $titles, array $names): array
    {
        $created = (int) $row->created_at;
        $mode = (int) $row->mode;
        $time = (float) $row->time;
        $sec = (int) floor($time);

        return [
            'id' => (int) $row->id,
            'video_id' => (int) $row->video_id,
            'video_title' => (string) ($titles[(int) $row->video_id] ?? ''),
            'episode_id' => (int) $row->episode_id,
            'member_id' => (int) $row->member_id,
            'name' => (string) ($names[(int) $row->member_id] ?? ((int) $row->member_id > 0 ? '' : '游客')),
            'text' => (string) $row->text,
            'color' => (string) $row->color,
            'mode' => $mode,
            'mode_label' => match ($mode) {
                1 => '顶部',
                2 => '底部',
                default => '滚动',
            },
            'time' => $time,
            'time_text' => sprintf('%d:%02d', intdiv($sec, 60), $sec % 60),
            'ip' => (string) $row->ip,
            'status' => (int) $row->status,
            'status_label' => (int) $row->status === 1 ? '显示' : '隐藏',
            'report' => Schema::hasColumn('video_danmaku', 'report') ? (int) $row->report : 0,
            'created_at' => $created,
            'created_at_text' => $created > 0 ? date('Y-m-d H:i', $created) : '',
        ];
    }

    /** @param  array<string, mixed>  $params */
    private function desk(array $params): string
    {
        $desk = strtolower(trim((string) ($params['desk'] ?? request()->input('desk', ''))));

        return in_array($desk, ['messages', 'settings'], true) ? $desk : 'messages';
    }
}

<?php

namespace Plugins\Chatroom\Services;

use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Chatroom\Models\ChatMessage;

class ChatroomAdmin
{
    public function __construct(private readonly ChatroomService $chat) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $settings = app(VideoSettingService::class);
        $payload['queues'] = $this->queues();
        $payload['options'] = [
            'chatroom_enabled' => (int) $settings->get('chatroom_enabled', '1') === 1 ? 1 : 0,
            'chatroom_login' => (int) $settings->get('chatroom_login', '0') === 1 ? 1 : 0,
        ];

        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! Schema::hasTable('plugin_chat_messages')) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($this->desk($params) === 'settings') {
            return Result::success(AdminPage::slice([], $params));
        }
        $q = ChatMessage::query()->orderByDesc('id');
        $kw = mb_substr(trim((string) ($params['q'] ?? '')), 0, 30);
        if ($kw !== '') {
            $q->where(function ($inner) use ($kw) {
                $inner->where('text', 'like', '%'.$kw.'%')->orWhere('name', 'like', '%'.$kw.'%');
            });
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
        if ((string) ($params['report'] ?? '') === '1' && Schema::hasColumn('plugin_chat_messages', 'report')) {
            $q->where('report', '>', 0);
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
            return Result::fail('发言由播放页产生，不能手添');
        }
        $row = ChatMessage::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if (! array_key_exists('status', $data)) {
            return Result::fail('后台只改显示或隐藏，不改原文');
        }
        $status = (int) $data['status'] === 1 ? 1 : 0;
        $row->status = $status;
        $row->save();

        return AdminOpLog::ifOk(Result::success(), 'update', $status === 1 ? '显示聊天发言 #'.$id : '隐藏聊天发言 #'.$id, [
            'module' => 'chat_messages',
            'target_id' => $id,
        ]);
    }

    public function delete(int $id): array
    {
        if ($this->desk(request()->all()) === 'settings') {
            return Result::fail('设置没有可删的行');
        }
        $row = ChatMessage::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $row->delete();

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除聊天发言 #'.$id, [
            'module' => 'chat_messages',
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
        if (! Schema::hasTable('plugin_chat_messages')) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($action === 'clear') {
            $n = ChatMessage::query()->count();
            ChatMessage::query()->delete();

            return AdminOpLog::ifOk(Result::success(['deleted' => $n]), 'delete', '清空聊天室 '.$n.' 条', [
                'module' => 'chat_messages',
            ]);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail('请先勾选发言');
        }
        if ($action === 'delete') {
            ChatMessage::query()->whereIn('id', $ids)->delete();

            return AdminOpLog::ifOk(Result::success(), 'delete', '批量删除聊天发言 '.count($ids).' 条', [
                'module' => 'chat_messages',
            ]);
        }
        if ($action === 'status') {
            $status = (int) $value === 1 ? 1 : 0;
            ChatMessage::query()->whereIn('id', $ids)->update(['status' => $status]);

            return AdminOpLog::ifOk(Result::success(), 'update', ($status === 1 ? '批量显示' : '批量隐藏').'聊天发言 '.count($ids).' 条', [
                'module' => 'chat_messages',
            ]);
        }

        return Result::fail('不支持的操作');
    }

    /** @return array{all:int,on:int,off:int,report:int} */
    private function queues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'report' => 0];
        if (! Schema::hasTable('plugin_chat_messages')) {
            return $zero;
        }
        $zero['all'] = (int) ChatMessage::query()->count();
        $zero['on'] = (int) ChatMessage::query()->where('status', 1)->count();
        $zero['off'] = (int) ChatMessage::query()->where('status', 0)->count();
        if (Schema::hasColumn('plugin_chat_messages', 'report')) {
            $zero['report'] = (int) ChatMessage::query()->where('report', '>', 0)->count();
        }

        return $zero;
    }

    /** @param  array<string, mixed>  $data */
    private function saveSettings(array $data): array
    {
        $ok = app(VideoSettingService::class)->saveOptions([
            'chatroom_enabled' => (int) ($data['chatroom_enabled'] ?? 0) === 1 ? '1' : '0',
            'chatroom_login' => (int) ($data['chatroom_login'] ?? 0) === 1 ? '1' : '0',
        ]);
        if (($ok['code'] ?? 1) !== 0) {
            return $ok;
        }

        return AdminOpLog::ifOk($ok, 'save', '改了聊天室开关', [
            'module' => 'chat_messages',
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
        $rows = [];
        $videoIds = [];
        foreach ($page->items() as $row) {
            $videoIds[] = (int) $row->video_id;
        }
        $titles = $this->videoTitles($videoIds);
        foreach ($page->items() as $row) {
            $rows[] = $this->present($row, $titles);
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
     * @param  array<int, string>  $titles
     * @return array<string, mixed>
     */
    private function present(ChatMessage $row, array $titles): array
    {
        $created = (int) $row->created_at;

        return [
            'id' => (int) $row->id,
            'video_id' => (int) $row->video_id,
            'video_title' => (string) ($titles[(int) $row->video_id] ?? ''),
            'member_id' => (int) $row->member_id,
            'name' => (string) ($row->name ?: '游客'),
            'text' => (string) $row->text,
            'ip' => (string) $row->ip,
            'status' => (int) $row->status,
            'status_label' => (int) $row->status === 1 ? '显示' : '隐藏',
            'report' => Schema::hasColumn('plugin_chat_messages', 'report') ? (int) $row->report : 0,
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

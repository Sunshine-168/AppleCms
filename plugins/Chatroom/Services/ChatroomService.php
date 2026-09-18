<?php

namespace Plugins\Chatroom\Services;

use App\Models\Member\Member;
use App\Models\Video\VideoModel;
use App\Support\Utils\Result;
use App\Services\Video\VideoSettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Plugins\Chatroom\Models\ChatMessage;

class ChatroomService
{
    public function enabled(): bool
    {
        return (int) app(VideoSettingService::class)->get('chatroom_enabled', '1') === 1
            && Schema::hasTable('plugin_chat_messages');
    }

    public function loginRequired(): bool
    {
        return (int) app(VideoSettingService::class)->get('chatroom_login', '0') === 1;
    }

    /** @return array<string, mixed> */
    public function list(int $videoId, int $afterId = 0): array
    {
        if (! $this->enabled()) {
            return Result::success(['list' => [], 'last_id' => 0]);
        }
        $playable = $this->playable($videoId);
        if (($playable['code'] ?? 1) !== 0) {
            return $playable;
        }
        $q = ChatMessage::query()
            ->where('video_id', $videoId)
            ->where('status', 1);
        if ($afterId > 0) {
            $rows = $q->where('id', '>', $afterId)->orderBy('id')->limit(50)->get();
        } else {
            $rows = $q->orderByDesc('id')->limit(80)->get()->reverse()->values();
        }
        $list = $rows->map(fn (ChatMessage $row) => $this->presentFront($row))->all();
        $lastId = $afterId;
        if ($list !== []) {
            $lastId = (int) $list[array_key_last($list)]['id'];
        }

        return Result::success(['list' => $list, 'last_id' => $lastId]);
    }

    /** @return array<string, mixed> */
    public function send(int $videoId, array $input, ?Member $member, string $ip): array
    {
        if (! $this->enabled()) {
            return Result::fail('聊天室已关闭');
        }
        $playable = $this->playable($videoId);
        if (($playable['code'] ?? 1) !== 0) {
            return $playable;
        }
        if ($this->loginRequired() && ! $member) {
            return Result::fail('请先登录后再发言');
        }
        $text = trim((string) ($input['text'] ?? ''));
        if ($text === '') {
            return Result::fail('请输入内容');
        }
        $text = mb_substr($text, 0, 500);
        $banned = $this->hitBanned($text);
        if ($banned !== null) {
            return $banned;
        }
        $lock = 'chatroom:ip:'.$ip;
        if (! Cache::add($lock, 1, 3)) {
            return Result::fail('发送太快，请稍候');
        }
        $name = $member ? mb_substr((string) $member->name, 0, 50) : '游客';
        $payload = [
            'video_id' => $videoId,
            'member_id' => (int) ($member?->id ?: 0),
            'name' => $name,
            'text' => $text,
            'ip' => mb_substr($ip, 0, 45),
            'status' => 1,
            'created_at' => time(),
        ];
        if (Schema::hasColumn('plugin_chat_messages', 'report')) {
            $payload['report'] = 0;
        }
        $row = ChatMessage::query()->create($payload);

        return Result::success($this->presentFront($row), '已发送');
    }

    /** @return array<string, mixed> */
    public function report(int $id, ?Member $member): array
    {
        if (! $this->enabled()) {
            return Result::fail('聊天室已关闭');
        }
        if (! $member) {
            return Result::fail('请先登录后再举报');
        }
        if (! Schema::hasColumn('plugin_chat_messages', 'report')) {
            return Result::fail('请先执行数据库迁移');
        }
        $row = ChatMessage::query()->find($id);
        if (! $row || (int) $row->status !== 1) {
            return Result::fail('发言不存在');
        }
        $lock = 'chatroom:report:'.$member->id.':'.$id;
        if (! Cache::add($lock, 1, 86400)) {
            return Result::fail('已经举报过了');
        }
        $row->increment('report');

        return Result::success(['id' => $id, 'report' => (int) ($row->fresh()->report ?? 0)], '已举报');
    }

    /** @return array<string, mixed> */
    private function playable(int $videoId): array
    {
        if ($videoId < 1 || ! Schema::hasTable('videos')) {
            return Result::fail('影片不存在');
        }
        $row = VideoModel::query()->find($videoId);
        if (! $row) {
            return Result::fail('影片不存在');
        }
        if ((int) $row->status !== 1) {
            return Result::fail('影片未上架，不能发言');
        }

        return Result::success();
    }

    /** @return array<string, mixed>|null */
    private function hitBanned(string $text): ?array
    {
        $banned = trim((string) app(VideoSettingService::class)->get('banned_words', ''));
        if ($banned === '') {
            return null;
        }
        foreach (preg_split('/[\r\n,，]+/u', $banned) ?: [] as $word) {
            $word = trim($word);
            if ($word !== '' && mb_stripos($text, $word) !== false) {
                return Result::fail('包含违禁词');
            }
        }

        return null;
    }

    /** @return array{id:int,name:string,text:string,at:int} */
    private function presentFront(ChatMessage $row): array
    {
        return [
            'id' => (int) $row->id,
            'name' => (string) ($row->name ?: '游客'),
            'text' => (string) $row->text,
            'at' => (int) $row->created_at,
        ];
    }
}

<?php

namespace Plugins\Chatroom\Services;

use App\Models\Member\Member;
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

    /** @return array<string, mixed> */
    public function list(int $videoId): array
    {
        if (! $this->enabled()) {
            return Result::success(['list' => []]);
        }
        $list = ChatMessage::query()
            ->where('video_id', $videoId)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $row) => [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?: '游客'),
                'text' => (string) $row->text,
                'at' => (int) $row->created_at,
            ])
            ->all();

        return Result::success(['list' => $list]);
    }

    /** @return array<string, mixed> */
    public function send(int $videoId, array $input, ?Member $member, string $ip): array
    {
        if (! $this->enabled()) {
            return Result::fail('聊天室已关闭');
        }
        $settings = app(VideoSettingService::class);
        if ((int) $settings->get('chatroom_login', '0') === 1 && ! $member) {
            return Result::fail('请先登录后再发言');
        }
        $text = trim((string) ($input['text'] ?? ''));
        if ($text === '') {
            return Result::fail('请输入内容');
        }
        $text = mb_substr($text, 0, 200);
        $banned = trim((string) $settings->get('banned_words', ''));
        if ($banned !== '') {
            foreach (preg_split('/[\r\n,，]+/u', $banned) ?: [] as $word) {
                $word = trim($word);
                if ($word !== '' && mb_stripos($text, $word) !== false) {
                    return Result::fail('包含违禁词');
                }
            }
        }
        $lock = 'chatroom:ip:'.$ip;
        if (! Cache::add($lock, 1, 2)) {
            return Result::fail('发送太快，请稍候');
        }
        $name = $member ? mb_substr((string) $member->name, 0, 50) : '游客';
        $row = ChatMessage::query()->create([
            'video_id' => $videoId,
            'member_id' => (int) ($member?->id ?: 0),
            'name' => $name,
            'text' => $text,
            'ip' => mb_substr($ip, 0, 45),
            'status' => 1,
            'created_at' => time(),
        ]);

        return Result::success([
            'id' => (int) $row->id,
            'name' => $name,
            'text' => $text,
            'at' => (int) $row->created_at,
        ], '已发送');
    }
}

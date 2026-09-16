<?php

namespace Plugins\Danmaku\Services;

use App\Models\Member\Member;
use App\Support\Utils\Result;
use App\Services\Video\VideoSettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Plugins\Danmaku\Models\Danmaku;

class DanmakuService
{
    public function enabled(): bool
    {
        return (int) app(VideoSettingService::class)->get('danmaku_enabled', '1') === 1
            && Schema::hasTable('video_danmaku');
    }

    /** @return array<string, mixed> */
    public function list(int $videoId, int $episodeId = 0): array
    {
        if (! $this->enabled()) {
            return Result::success(['list' => []]);
        }
        $q = Danmaku::query()->where('video_id', $videoId)->where('status', 1);
        if ($episodeId > 0) {
            $q->where(function ($w) use ($episodeId) {
                $w->where('episode_id', $episodeId)->orWhere('episode_id', 0);
            });
        }
        $list = $q->orderBy('time')->orderBy('id')->limit(2000)->get()->map(fn (Danmaku $row) => [
            'time' => (float) $row->time,
            'text' => (string) $row->text,
            'color' => (string) $row->color,
            'mode' => (int) $row->mode,
        ])->all();

        return Result::success(['list' => $list]);
    }

    /** @return array<string, mixed> */
    public function send(int $videoId, array $input, ?Member $member, string $ip): array
    {
        if (! $this->enabled()) {
            return Result::fail('弹幕已关闭');
        }
        $settings = app(VideoSettingService::class);
        if ((int) $settings->get('danmaku_login', '0') === 1 && ! $member) {
            return Result::fail('请先登录后发送弹幕');
        }
        $text = trim((string) ($input['text'] ?? ''));
        if ($text === '') {
            return Result::fail('请输入弹幕');
        }
        $text = mb_substr($text, 0, 80);
        $banned = trim((string) $settings->get('banned_words', ''));
        if ($banned !== '') {
            foreach (preg_split('/[\r\n,，]+/u', $banned) ?: [] as $word) {
                $word = trim($word);
                if ($word !== '' && mb_stripos($text, $word) !== false) {
                    return Result::fail('弹幕包含违禁词');
                }
            }
        }
        $lock = 'danmaku:ip:'.$ip;
        if (! Cache::add($lock, 1, 3)) {
            return Result::fail('发送太快，请稍候');
        }
        $color = (string) ($input['color'] ?? '#ffffff');
        if (! preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
            $color = '#ffffff';
        }
        $row = Danmaku::query()->create([
            'video_id' => $videoId,
            'episode_id' => max(0, (int) ($input['episode_id'] ?? 0)),
            'member_id' => (int) ($member?->id ?: 0),
            'text' => $text,
            'color' => $color,
            'mode' => max(0, min(2, (int) ($input['mode'] ?? 0))),
            'time' => max(0, min(86400, (float) ($input['time'] ?? 0))),
            'ip' => mb_substr($ip, 0, 45),
            'status' => 1,
            'created_at' => time(),
        ]);

        return Result::success([
            'id' => $row->id,
            'time' => (float) $row->time,
            'text' => (string) $row->text,
            'color' => (string) $row->color,
            'mode' => (int) $row->mode,
        ], '已发送');
    }
}

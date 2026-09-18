<?php

namespace Plugins\Danmaku\Services;

use App\Models\Member\Member;
use App\Models\Video\VideoModel;
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

    public function loginRequired(): bool
    {
        return (int) app(VideoSettingService::class)->get('danmaku_login', '0') === 1;
    }

    /** @return array<string, mixed> */
    public function list(int $videoId, int $episodeId = 0): array
    {
        if (! $this->enabled()) {
            return Result::success(['list' => []]);
        }
        $playable = $this->playable($videoId);
        if (($playable['code'] ?? 1) !== 0) {
            return $playable;
        }
        $q = Danmaku::query()->where('video_id', $videoId)->where('status', 1);
        if ($episodeId > 0) {
            $q->where(function ($w) use ($episodeId) {
                $w->where('episode_id', $episodeId)->orWhere('episode_id', 0);
            });
        }
        $list = $q->orderBy('time')->orderBy('id')->limit(2000)->get()->map(fn (Danmaku $row) => [
            'id' => (int) $row->id,
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
        $playable = $this->playable($videoId);
        if (($playable['code'] ?? 1) !== 0) {
            return $playable;
        }
        if ($this->loginRequired() && ! $member) {
            return Result::fail('请先登录后发送弹幕');
        }
        $text = trim((string) ($input['text'] ?? ''));
        if ($text === '') {
            return Result::fail('请输入弹幕');
        }
        $text = mb_substr($text, 0, 120);
        $banned = $this->hitBanned($text);
        if ($banned !== null) {
            return $banned;
        }
        $lock = 'danmaku:ip:'.$ip;
        if (! Cache::add($lock, 1, 5)) {
            return Result::fail('发送太快，请稍候');
        }
        $color = (string) ($input['color'] ?? '#ffffff');
        if (! preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
            $color = '#ffffff';
        }
        $payload = [
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
        ];
        if (Schema::hasColumn('video_danmaku', 'report')) {
            $payload['report'] = 0;
        }
        $row = Danmaku::query()->create($payload);

        return Result::success([
            'id' => (int) $row->id,
            'time' => (float) $row->time,
            'text' => (string) $row->text,
            'color' => (string) $row->color,
            'mode' => (int) $row->mode,
        ], '已发送');
    }

    /** @return array<string, mixed> */
    public function report(int $id, ?Member $member): array
    {
        if (! $this->enabled()) {
            return Result::fail('弹幕已关闭');
        }
        if (! $member) {
            return Result::fail('请先登录后再举报');
        }
        if (! Schema::hasColumn('video_danmaku', 'report')) {
            return Result::fail('请先执行数据库迁移');
        }
        $row = Danmaku::query()->find($id);
        if (! $row || (int) $row->status !== 1) {
            return Result::fail('弹幕不存在');
        }
        $lock = 'danmaku:report:'.$member->id.':'.$id;
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
            return Result::fail('影片未上架，不能发弹幕');
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
                return Result::fail('弹幕包含违禁词');
            }
        }

        return null;
    }
}

<?php

namespace App\Services\Video;

use App\Models\Member\Member;
use App\Models\Member\MemberFavorite;
use App\Models\Member\MemberHistory;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoReport;
use App\Models\Video\VideoStatModel;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Hash;

class InteractionService
{
    public function register(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        if ($name === '' || $email === '' || $password === '') {
            return Result::fail('请填写完整信息');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Result::fail('邮箱格式不正确');
        }
        if (Member::query()->where('email', $email)->exists()) {
            return Result::fail('邮箱已注册');
        }
        $now = time();
        $member = Member::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 1,
            'points' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Result::success(['id' => $member->id], '注册成功');
    }

    public function toggleFavorite(int $memberId, int $videoId): array
    {
        $exists = MemberFavorite::query()->where('member_id', $memberId)->where('video_id', $videoId)->first();
        if ($exists) {
            $exists->delete();

            return Result::success(['favorited' => false], '已取消收藏');
        }
        MemberFavorite::query()->create([
            'member_id' => $memberId,
            'video_id' => $videoId,
            'created_at' => time(),
        ]);

        return Result::success(['favorited' => true], '收藏成功');
    }

    public function isFavorited(int $memberId, int $videoId): bool
    {
        return MemberFavorite::query()->where('member_id', $memberId)->where('video_id', $videoId)->exists();
    }

    public function recordHistory(int $memberId, VideoModel $video, int $sourceId = 0, int $episodeId = 0): void
    {
        MemberHistory::query()->updateOrCreate(
            ['member_id' => $memberId, 'video_id' => $video->id],
            ['source_id' => $sourceId, 'episode_id' => $episodeId, 'updated_at' => time()]
        );
    }

    public function addComment(int $videoId, string $content, ?Member $member, string $guestName, string $ip): array
    {
        $content = trim($content);
        if ($content === '') {
            return Result::fail('请填写评论');
        }
        $name = $member?->name ?: trim($guestName);
        if ($name === '') {
            $name = '游客';
        }
        $row = VideoComment::query()->create([
            'video_id' => $videoId,
            'member_id' => (int) ($member?->id ?: 0),
            'parent_id' => 0,
            'author_name' => mb_substr($name, 0, 80),
            'content' => mb_substr($content, 0, 2000),
            'status' => 1,
            'ip' => $ip,
            'created_at' => time(),
        ]);
        if ($member) {
            $member->increment('points', 1);
        }

        return Result::success(['id' => $row->id], '评论已发布');
    }

    public function addReport(int $videoId, string $content, ?Member $member, string $ip): array
    {
        $content = trim($content);
        if ($content === '') {
            return Result::fail('请填写报错内容');
        }
        VideoReport::query()->create([
            'video_id' => $videoId,
            'member_id' => (int) ($member?->id ?: 0),
            'content' => mb_substr($content, 0, 500),
            'status' => 0,
            'ip' => $ip,
            'created_at' => time(),
        ]);

        return Result::success([], '已提交，感谢反馈');
    }

    public function score(int $videoId, float $score): array
    {
        if ($score < 1 || $score > 10) {
            return Result::fail('评分需在 1-10 之间');
        }
        $stat = VideoStatModel::query()->find($videoId);
        if (! $stat) {
            $stat = VideoStatModel::query()->create([
                'video_id' => $videoId,
                'hits' => 0,
                'hits_day' => 0,
                'hits_week' => 0,
                'hits_month' => 0,
                'up' => 0,
                'down' => 0,
                'score' => $score,
                'score_all' => $score,
                'score_num' => 1,
                'updated_at' => time(),
            ]);
        } else {
            $stat->score_num = (int) $stat->score_num + 1;
            $stat->score_all = (float) $stat->score_all + $score;
            $stat->score = round($stat->score_all / max(1, $stat->score_num), 1);
            $stat->updated_at = time();
            $stat->save();
        }
        VideoModel::query()->where('id', $videoId)->update(['score' => $stat->score]);

        return Result::success(['score' => $stat->score], '评分成功');
    }

    public function changePassword(Member $member, string $old, string $password): array
    {
        if ($password === '' || strlen($password) < 6) {
            return Result::fail('新密码至少 6 位');
        }
        if (! Hash::check($old, (string) $member->password)) {
            return Result::fail('原密码不正确');
        }
        $member->password = Hash::make($password);
        $member->updated_at = time();
        $member->save();

        return Result::success([], '密码已修改');
    }

    public function redeemCard(Member $member, string $code): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return Result::fail('请输入卡密');
        }
        $card = \App\Models\Video\VideoCard::query()->where('code', $code)->first();
        if (! $card) {
            return Result::fail('卡密不存在');
        }
        if ((int) $card->status !== 1 || (int) $card->used_by > 0) {
            return Result::fail('卡密已使用或已作废');
        }
        $card->status = 0;
        $card->used_by = $member->id;
        $card->used_at = time();
        $card->save();
        $member->increment('points', (int) $card->points);

        return Result::success(['points' => $member->fresh()->points], '充值成功，到账 '.$card->points.' 积分');
    }

    public function consumePlayPoints(?\App\Models\Member\Member $member, VideoModel $video): array
    {
        $need = (int) ($video->points ?? 0);
        if ($need < 1) {
            return Result::success();
        }
        if (! $member) {
            return Result::fail('本片需登录并消耗 '.$need.' 积分');
        }
        $watched = MemberHistory::query()->where('member_id', $member->id)->where('video_id', $video->id)->exists();
        if ($watched) {
            return Result::success();
        }
        if ((int) $member->points < $need) {
            return Result::fail('积分不足，需要 '.$need.' 积分');
        }
        $member->decrement('points', $need);

        return Result::success(['points' => $need], '已扣除 '.$need.' 积分');
    }
}

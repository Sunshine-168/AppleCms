<?php

namespace App\Services\Video;

use App\Models\Member\Member;
use App\Models\Member\MemberFavorite;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberHistory;
use App\Models\Member\MemberInvite;
use App\Models\Member\MemberPointLog;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoReport;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoUlog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InteractionService
{
    public function register(array $data): array
    {
        if ((int) app(VideoSettingService::class)->get('member_register', '1') !== 1) {
            return Result::fail('已关闭注册');
        }
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
        $phone = '';
        $smsOn = false;
        try {
            $smsOn = app(\App\Support\Plugins\PluginManager::class)->isEnabled('sms');
        } catch (\Throwable) {
            $smsOn = false;
        }
        if ($smsOn) {
            $sms = app(\Plugins\Sms\Services\SmsService::class);
            $checked = $sms->verify((string) ($data['phone'] ?? ''), (string) ($data['sms_code'] ?? ''), 'register');
            if (($checked['code'] ?? 1) !== 0) {
                return $checked;
            }
            $phone = (string) ($checked['data']['phone'] ?? '');
            if ($phone !== '' && Schema::hasColumn('members', 'phone') && Member::query()->where('phone', $phone)->exists()) {
                return Result::fail('手机号已注册');
            }
        }
        $now = time();
        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 1,
            'points' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if ($phone !== '' && Schema::hasColumn('members', 'phone')) {
            $payload['phone'] = $phone;
        }
        $member = Member::query()->create($payload);
        $this->applyInvite($member, (string) ($data['invite'] ?? ''));
        $this->issueInvite($member);

        return Result::success(['id' => $member->id], '注册成功');
    }

    private function applyInvite(Member $member, string $code): void
    {
        $code = trim($code);
        if ($code === '' || ! Schema::hasTable('member_invites')) {
            return;
        }
        $invite = MemberInvite::query()
            ->where('code', $code)
            ->where('status', 1)
            ->where('used_by', 0)
            ->first();
        if (! $invite) {
            return;
        }
        $invite->used_by = (int) $member->id;
        $invite->status = 0;
        $invite->save();
        $points = (int) $invite->points;
        if ($points < 1) {
            return;
        }
        $member->increment('points', $points);
        $inviterId = (int) $invite->member_id;
        if ($inviterId > 0 && $inviterId !== (int) $member->id) {
            Member::query()->where('id', $inviterId)->increment('points', $points);
        }
        if (! Schema::hasTable('member_point_logs')) {
            return;
        }
        \App\Models\Member\MemberPointLog::query()->create([
            'member_id' => (int) $member->id,
            'points' => $points,
            'balance' => (int) $member->fresh()->points,
            'type' => 'invite',
            'remark' => '邀请码 '.$code,
            'created_at' => time(),
        ]);
    }

    private function issueInvite(Member $member): void
    {
        if (! Schema::hasTable('member_invites')) {
            return;
        }
        $code = strtoupper(Str::random(8));
        while (MemberInvite::query()->where('code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }
        MemberInvite::query()->create([
            'code' => $code,
            'member_id' => (int) $member->id,
            'used_by' => 0,
            'points' => 10,
            'status' => 1,
            'created_at' => time(),
        ]);
    }

    public function generateInvite(Member $member, int $points = 10): array
    {
        if (! Schema::hasTable('member_invites')) {
            return Result::fail('邀请码未启用');
        }
        $unused = MemberInvite::query()->where('member_id', $member->id)->where('status', 1)->count();
        if ($unused >= 20) {
            return Result::fail('未使用邀请码已达 20 个');
        }
        $code = strtoupper(Str::random(8));
        while (MemberInvite::query()->where('code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }
        MemberInvite::query()->create([
            'code' => $code,
            'member_id' => (int) $member->id,
            'used_by' => 0,
            'points' => max(0, $points),
            'status' => 1,
            'created_at' => time(),
        ]);

        return Result::success(['code' => $code], '已生成邀请码 '.$code);
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
        $this->writeUlog($memberId, (int) $video->id, 'play', (string) request()->ip());
        if ($memberId > 0) {
            try {
                $activity = app(\App\Services\Member\MemberActivityService::class);
                if ($activity->ready()) {
                    $member = Member::query()->find($memberId);
                    if ($member) {
                        $activity->reportWatch($member);
                    }
                }
            } catch (\Throwable) {
            }
        }
    }

    public function writeUlog(int $memberId, int $videoId, string $type, string $ip = ''): void
    {
        try {
            if (! Schema::hasTable('video_ulogs')) {
                return;
            }
            VideoUlog::query()->create([
                'member_id' => $memberId,
                'video_id' => $videoId,
                'type' => $type,
                'ip' => $ip,
                'created_at' => time(),
            ]);
        } catch (\Throwable) {
        }
    }

    public function addComment(int $videoId, string $content, ?Member $member, string $guestName, string $ip, int $mid = 1): array
    {
        $content = trim($content);
        if ($content === '') {
            return Result::fail('请填写评论');
        }
        $mid = $mid === 2 ? 2 : 1;
        if ($mid === 2) {
            if (! Schema::hasTable('video_arts') || ! VideoArt::query()->where('id', $videoId)->exists()) {
                return Result::fail('文章不存在');
            }
            if (! Schema::hasColumn('video_comments', 'mid')) {
                return Result::fail('请先执行数据库迁移');
            }
        }
        $settings = app(VideoSettingService::class);
        if ((int) $settings->get('member_comment_login', '0') === 1 && ! $member) {
            return Result::fail('请先登录后评论');
        }
        $banned = trim((string) $settings->get('banned_words', ''));
        if ($banned !== '') {
            foreach (preg_split('/[\r\n,，]+/u', $banned) ?: [] as $word) {
                $word = trim($word);
                if ($word !== '' && mb_stripos($content, $word) !== false) {
                    return Result::fail('评论包含违禁词');
                }
            }
        }
        $name = $member?->name ?: trim($guestName);
        if ($name === '') {
            $name = '游客';
        }
        $row = VideoComment::query()->create(array_filter([
            'video_id' => $videoId,
            'mid' => Schema::hasColumn('video_comments', 'mid') ? $mid : null,
            'member_id' => (int) ($member?->id ?: 0),
            'parent_id' => 0,
            'author_name' => mb_substr($name, 0, 80),
            'content' => mb_substr($content, 0, 2000),
            'status' => (int) $settings->get('comment_audit', '0') === 1 ? 0 : 1,
            'ip' => $ip,
            'created_at' => time(),
        ], fn ($v) => $v !== null));
        if ($member) {
            $activity = app(\App\Services\Member\MemberActivityService::class);
            if ($activity->ready()) {
                $activity->reportComment($member);
            } else {
                $member->increment('points', 1);
            }
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

    public function reportComment(int $id): array
    {
        $row = VideoComment::query()->find($id);
        if (! $row) {
            return Result::fail('评论不存在');
        }
        if (Schema::hasColumn('video_comments', 'comment_report')) {
            $row->increment('comment_report');
        }

        return Result::success(['comment_report' => (int) ($row->fresh()->comment_report ?? 0)], '已举报');
    }

    public function likeComment(int $id): array
    {
        $row = VideoComment::query()->find($id);
        if (! $row) {
            return Result::fail('评论不存在');
        }
        if (Schema::hasColumn('video_comments', 'comment_up')) {
            $row->increment('comment_up');
        }

        return Result::success(['comment_up' => (int) ($row->fresh()->comment_up ?? 0)], '已点赞');
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
            if (! app(\App\Support\Plugins\PluginManager::class)->isEnabled('coupon')) {
                return Result::fail('卡密不存在');
            }

            return $this->redeemCoupon($member, $code);
        }
        if ((int) $card->status !== 1 || (int) $card->used_by > 0) {
            return Result::fail('卡密已使用或已作废');
        }
        $card->status = 0;
        $card->used_by = $member->id;
        $card->used_at = time();
        $card->save();
        $member->increment('points', (int) $card->points);
        $fresh = $member->fresh();
        $this->writePointLog($fresh, (int) $card->points, 'card', '卡密 '.$code);

        return Result::success(['points' => (int) $fresh->points], '充值成功，到账 '.$card->points.' 积分');
    }

    public function redeemCoupon(Member $member, string $code): array
    {
        if (! Schema::hasTable('video_coupons')) {
            return Result::fail('卡密不存在');
        }
        $coupon = \App\Models\Video\VideoCoupon::query()->where('code', $code)->first();
        if (! $coupon) {
            return Result::fail('卡密或优惠券不存在');
        }
        if ((int) $coupon->status !== 1 || (int) $coupon->used_by > 0) {
            return Result::fail('优惠券已使用或已作废');
        }
        if ((int) $coupon->expire_at > 0 && (int) $coupon->expire_at < time()) {
            return Result::fail('优惠券已过期');
        }
        if ((int) $coupon->min_points > 0 && (int) $member->points < (int) $coupon->min_points) {
            return Result::fail('积分未达到使用门槛');
        }
        $coupon->status = 0;
        $coupon->used_by = $member->id;
        $coupon->used_at = time();
        $coupon->save();
        $member->increment('points', (int) $coupon->points);
        $fresh = $member->fresh();
        $this->writePointLog($fresh, (int) $coupon->points, 'coupon', '优惠券 '.$code);

        return Result::success(['points' => (int) $fresh->points], '优惠券已兑换，到账 '.$coupon->points.' 积分');
    }

    public function consumePlayPoints(?\App\Models\Member\Member $member, VideoModel $video): array
    {
        $need = (int) ($video->points ?? 0);
        if ($need < 1) {
            return Result::success();
        }
        $group = $this->memberGroup($member);
        if ($member) {
            $watched = MemberHistory::query()->where('member_id', $member->id)->where('video_id', $video->id)->exists();
            if ($watched) {
                return Result::success();
            }
            if ($this->dayFreeRemain($member, $group) > 0) {
                return Result::success(['day_free' => 1], '今日免费播放');
            }
            if ((int) $member->points >= $need) {
                $member->decrement('points', $need);
                $this->writePointLog($member->fresh(), -$need, 'play', '点播');

                return Result::success(['points' => $need], '已扣除 '.$need.' 积分');
            }
        }
        $trysee = (int) ($group?->trysee ?? 0);
        if ($trysee < 1) {
            $trysee = (int) app(VideoSettingService::class)->get('trysee_seconds', '0');
        }
        if ($trysee > 0) {
            return Result::success(['trysee' => $trysee, 'trysee_seconds' => $trysee], '试看 '.$trysee.' 秒');
        }
        if (! $member) {
            return Result::fail('本片需登录并消耗 '.$need.' 积分');
        }

        return Result::fail('积分不足，需要 '.$need.' 积分');
    }

    private function memberGroup(?Member $member): ?MemberGroup
    {
        $gid = (int) ($member?->group_id ?? 0);
        if ($gid < 1 || ! Schema::hasTable('member_groups')) {
            return null;
        }
        try {
            return MemberGroup::query()->find($gid);
        } catch (\Throwable) {
            return null;
        }
    }

    private function dayFreeRemain(Member $member, ?MemberGroup $group): int
    {
        $limit = (int) ($group?->day_free ?? 0);
        if ($limit < 1 || ! Schema::hasTable('video_ulogs')) {
            return 0;
        }
        $used = VideoUlog::query()
            ->where('member_id', $member->id)
            ->where('type', 'play')
            ->where('created_at', '>=', strtotime('today'))
            ->count();

        return max(0, $limit - $used);
    }

    private function writePointLog(?Member $member, int $points, string $type, string $remark): void
    {
        if (! $member || ! Schema::hasTable('member_point_logs')) {
            return;
        }
        try {
            MemberPointLog::query()->create([
                'member_id' => (int) $member->id,
                'points' => $points,
                'balance' => (int) $member->points,
                'type' => $type,
                'remark' => mb_substr($remark, 0, 250),
                'created_at' => time(),
            ]);
        } catch (\Throwable) {
        }
    }
}

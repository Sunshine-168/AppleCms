<?php

namespace App\Services\Member;

use App\Models\Member\Member;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberInvite;
use App\Models\Member\MemberInviteLog;
use App\Models\Member\MemberPointLog;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MemberGrowthService
{
    public function mode(): string
    {
        $mode = strtolower(trim((string) $this->opt('member_growth_mode', 'points')));

        return in_array($mode, ['points', 'vip_days', 'off'], true) ? $mode : 'points';
    }

    /** @return array{code:int,msg:string,data?:mixed} */
    public function assertInvite(string $code): array
    {
        if ((int) $this->opt('member_invite', '0') !== 1) {
            return Result::success();
        }
        $code = $this->normalizeCode($code);
        if ($code === '') {
            return Result::fail('请填写邀请码');
        }
        if (! $this->resolve($code)['ok']) {
            return Result::fail('邀请码无效');
        }

        return Result::success();
    }

    public function afterRegister(Member $member, string $code, string $ip): void
    {
        $this->applyInvite($member, $code, $ip);
        if ($this->mode() === 'vip_days') {
            $member->refresh();
            $member->grantTimedGroupDays($this->trialDays(), $this->trialGroupId());
            $this->ensurePersonalCode($member);
            return;
        }
        $this->issueOneTime($member);
    }

    public function generateInvite(Member $member, int $points = 10): array
    {
        if ($this->mode() === 'vip_days') {
            $code = $this->ensurePersonalCode($member);
            if ($code === '') {
                return Result::fail('邀请码未启用');
            }

            return Result::success(['code' => $code], '邀请码 '.$code);
        }
        if (! Schema::hasTable('member_invites')) {
            return Result::fail('邀请码未启用');
        }
        $unused = MemberInvite::query()->where('member_id', $member->id)->where('status', 1)->count();
        if ($unused >= 20) {
            return Result::fail('未使用邀请码已达 20 个');
        }
        $code = $this->freshCode();
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

    public function ensurePersonalCode(Member $member): string
    {
        if (! Schema::hasColumn('members', 'invite_code')) {
            return '';
        }
        $existing = $this->normalizeCode((string) ($member->invite_code ?? ''));
        if ($existing !== '') {
            return $existing;
        }
        $code = $this->freshCode();
        $member->invite_code = $code;
        $member->save();

        return $code;
    }

    private function applyInvite(Member $member, string $code, string $ip): void
    {
        $code = $this->normalizeCode($code);
        if ($code === '') {
            return;
        }
        $hit = $this->resolve($code);
        if (! $hit['ok']) {
            return;
        }
        $oneTime = $hit['invite'];
        if ($oneTime) {
            $oneTime->used_by = (int) $member->id;
            $oneTime->status = 0;
            $oneTime->save();
        }
        $inviter = $hit['inviter'];
        $mode = $this->mode();
        if ($inviter) {
            $this->bindInviter($member, $inviter);
        }
        if ($mode === 'points' && $oneTime) {
            $this->grantInvitePoints($member, $inviter, $oneTime);

            return;
        }
        if ($mode !== 'vip_days' || ! $inviter) {
            return;
        }
        $this->rewardInviterDays($inviter, $member, $ip, 1);
        $this->rewardUplines($inviter, $member, $ip);
    }

    private function grantInvitePoints(Member $member, ?Member $inviter, MemberInvite $invite): void
    {
        $points = (int) $invite->points;
        if ($points < 1) {
            return;
        }
        $member->increment('points', $points);
        if ($inviter && (int) $inviter->id !== (int) $member->id) {
            Member::query()->where('id', $inviter->id)->increment('points', $points);
        }
        if (! Schema::hasTable('member_point_logs')) {
            return;
        }
        MemberPointLog::query()->create([
            'member_id' => (int) $member->id,
            'points' => $points,
            'balance' => (int) $member->fresh()->points,
            'type' => 'invite',
            'remark' => '邀请码 '.$invite->code,
            'created_at' => time(),
        ]);
    }

    public function shareCode(Member $member): string
    {
        if ($this->mode() === 'vip_days') {
            return $this->ensurePersonalCode($member);
        }
        if (! Schema::hasTable('member_invites')) {
            return $this->ensurePersonalCode($member);
        }
        $row = MemberInvite::query()
            ->where('member_id', $member->id)
            ->where('status', 1)
            ->where('used_by', 0)
            ->orderByDesc('id')
            ->first();
        if ($row) {
            return (string) $row->code;
        }

        return $this->ensurePersonalCode($member);
    }

    public function inviteUrl(Member $member): string
    {
        $code = $this->shareCode($member);

        return $code === '' ? url('/member/register') : url('/member/register?invite='.rawurlencode($code));
    }

    /** @return list<array{rank:int,member_id:int,name:string,invites:int,days:int}> */
    public function rank(string $period = 'month', int $limit = 50): array
    {
        if (! Schema::hasTable('member_invite_logs')) {
            return [];
        }
        $limit = max(1, min(100, $limit));
        $q = MemberInviteLog::query();
        if (Schema::hasColumn('member_invite_logs', 'level')) {
            $q->where('level', 1);
        }
        if ($period !== 'all') {
            $from = Carbon::now((string) config('app.timezone', 'Asia/Shanghai'))->startOfMonth()->timestamp;
            $q->where('created_at', '>=', $from);
        }
        $rows = $q->selectRaw('inviter_id, COUNT(*) as invites, COALESCE(SUM(days),0) as days')
            ->groupBy('inviter_id')
            ->orderByDesc('invites')
            ->orderByDesc('days')
            ->limit($limit)
            ->get();
        $names = Member::query()
            ->whereIn('id', $rows->pluck('inviter_id')->all())
            ->pluck('name', 'id');
        $out = [];
        $rank = 1;
        foreach ($rows as $row) {
            $id = (int) $row->inviter_id;
            $out[] = [
                'rank' => $rank++,
                'member_id' => $id,
                'name' => (string) ($names[$id] ?? ('#'.$id)),
                'invites' => (int) $row->invites,
                'days' => (int) $row->days,
            ];
        }

        return $out;
    }

    /** @return array{invites:int,days:int,downlines:int,invite_url:string,invite_code:string} */
    public function myStats(Member $member): array
    {
        $invites = 0;
        $days = 0;
        if (Schema::hasTable('member_invite_logs')) {
            $q = MemberInviteLog::query()->where('inviter_id', $member->id);
            if (Schema::hasColumn('member_invite_logs', 'level')) {
                $q->where('level', 1);
            }
            $invites = (int) $q->count();
            $days = (int) MemberInviteLog::query()->where('inviter_id', $member->id)->sum('days');
        }
        $downlines = 0;
        if (Schema::hasColumn('members', 'inviter_id')) {
            $downlines = (int) Member::query()->where('inviter_id', $member->id)->count();
        }

        return [
            'invites' => $invites,
            'days' => $days,
            'downlines' => $downlines,
            'invite_url' => $this->inviteUrl($member),
            'invite_code' => $this->shareCode($member),
        ];
    }

    public function posterPng(Member $member): ?string
    {
        $code = $this->shareCode($member);
        $url = $this->inviteUrl($member);
        $title = trim((string) app(VideoSettingService::class)->get('site_title', config('app.name')));
        if ($title === '') {
            $title = (string) config('app.name');
        }
        $hex = trim((string) app(VideoSettingService::class)->get('theme_primary', '#1b4f72'));
        [$r, $g, $b] = $this->hexRgb($hex);
        if (function_exists('imagecreatetruecolor')) {
            $drawn = $this->posterWithGd($title, $code, $url, $r, $g, $b);
            if (is_string($drawn) && $drawn !== '') {
                return $drawn;
            }
        }

        return $this->posterPlainPng($r, $g, $b, $code !== '' ? $code : 'INVITE', $url);
    }

    private function posterWithGd(string $title, string $code, string $url, int $r, int $g, int $b): ?string
    {
        $w = 720;
        $h = 1080;
        $im = imagecreatetruecolor($w, $h);
        if ($im === false) {
            return null;
        }
        $bg = imagecolorallocate($im, $r, $g, $b);
        $card = imagecolorallocate($im, 255, 255, 255);
        $ink = imagecolorallocate($im, 28, 32, 38);
        $muted = imagecolorallocate($im, 90, 98, 110);
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);
        imagefilledrectangle($im, 48, 160, $w - 48, $h - 80, $card);
        $this->posterText($im, $title, 80, $card, true);
        $this->posterText($im, $code !== '' ? $code : 'INVITE', 430, $ink, true, 5);
        $this->posterText($im, $url, 520, $muted, true, 3);
        $this->posterText($im, 'Invite friends', 700, $muted, true, 3);
        ob_start();
        imagepng($im);
        $bin = (string) ob_get_clean();
        imagedestroy($im);

        return $bin !== '' ? $bin : null;
    }

    private function posterPlainPng(int $r, int $g, int $b, string $code, string $url): string
    {
        $w = 320;
        $h = 480;
        $raw = '';
        for ($y = 0; $y < $h; $y++) {
            $raw .= "\x00";
            for ($x = 0; $x < $w; $x++) {
                $onCard = $x > 16 && $x < $w - 16 && $y > 70 && $y < $h - 40;
                if ($onCard) {
                    $raw .= "\xff\xff\xff";
                } else {
                    $raw .= chr($r).chr($g).chr($b);
                }
            }
        }
        $ihdr = pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0);
        $png = "\x89PNG\r\n\x1a\n";
        $png .= $this->pngChunk('IHDR', $ihdr);
        $png .= $this->pngChunk('tEXt', 'Title'."\x00".$code);
        $png .= $this->pngChunk('tEXt', 'Description'."\x00".$url);
        $png .= $this->pngChunk('IDAT', (string) zlib_encode($raw, ZLIB_ENCODING_DEFLATE));
        $png .= $this->pngChunk('IEND', '');

        return $png;
    }

    private function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }

    private function rewardInviterDays(Member $inviter, Member $invitee, string $ip, int $level = 1): void
    {
        if ((int) $inviter->id === (int) $invitee->id) {
            return;
        }
        $days = $level <= 1 ? $this->rewardDays() : 0;
        $granted = 0;
        if ($days > 0 && $this->withinCaps($inviter, $ip, $level <= 1)) {
            $inviter->refresh();
            if ($inviter->grantTimedGroupDays($days, $this->trialGroupId())) {
                $granted = $days;
            }
        }
        $this->writeInviteLog($inviter, $invitee, $ip, $granted, $level);
    }

    private function rewardUplines(Member $direct, Member $invitee, string $ip): void
    {
        $l2 = max(0, (int) $this->opt('member_invite_l2_days', '0'));
        $l3 = max(0, (int) $this->opt('member_invite_l3_days', '0'));
        if ($l2 < 1 && $l3 < 1) {
            return;
        }
        $seen = [(int) $invitee->id, (int) $direct->id];
        $cursor = $direct;
        for ($level = 2; $level <= 3; $level++) {
            $pid = Schema::hasColumn('members', 'inviter_id') ? (int) ($cursor->inviter_id ?? 0) : 0;
            if ($pid < 1 || in_array($pid, $seen, true)) {
                return;
            }
            $up = Member::query()->find($pid);
            if (! $up) {
                return;
            }
            $seen[] = $pid;
            $days = $level === 2 ? $l2 : $l3;
            if ($days > 0 && $this->withinCaps($up, $ip, false)) {
                $up->refresh();
                $granted = 0;
                if ($up->grantTimedGroupDays($days, $this->trialGroupId())) {
                    $granted = $days;
                }
                $this->writeInviteLog($up, $invitee, $ip, $granted, $level);
            }
            $cursor = $up;
        }
    }

    private function bindInviter(Member $member, Member $inviter): void
    {
        if (! Schema::hasColumn('members', 'inviter_id')) {
            return;
        }
        if ((int) ($member->inviter_id ?? 0) > 0) {
            return;
        }
        $id = (int) $inviter->id;
        if ($id < 1 || $id === (int) $member->id) {
            return;
        }
        $walk = $inviter;
        $guard = 0;
        while ($guard < 8 && Schema::hasColumn('members', 'inviter_id')) {
            $pid = (int) ($walk->inviter_id ?? 0);
            if ($pid === (int) $member->id) {
                return;
            }
            if ($pid < 1) {
                break;
            }
            $next = Member::query()->find($pid);
            if (! $next) {
                break;
            }
            $walk = $next;
            $guard++;
        }
        $member->inviter_id = $id;
        $member->save();
    }

    private function writeInviteLog(Member $inviter, Member $invitee, string $ip, int $days, int $level): void
    {
        if (! Schema::hasTable('member_invite_logs')) {
            return;
        }
        $payload = [
            'inviter_id' => (int) $inviter->id,
            'invitee_id' => (int) $invitee->id,
            'ip' => mb_substr($ip, 0, 45),
            'days' => $days,
            'created_at' => time(),
        ];
        if (Schema::hasColumn('member_invite_logs', 'level')) {
            $payload['level'] = max(1, $level);
        }
        MemberInviteLog::query()->create($payload);
    }

    private function withinCaps(Member $inviter, string $ip, bool $checkIp = true): bool
    {
        if (! Schema::hasTable('member_invite_logs')) {
            return true;
        }
        $monthCap = max(0, (int) $this->opt('member_invite_month_cap', '5'));
        $ipCap = max(0, (int) $this->opt('member_invite_ip_daily_cap', '1'));
        $now = time();
        if ($monthCap > 0) {
            $from = Carbon::now((string) config('app.timezone', 'Asia/Shanghai'))->startOfMonth()->timestamp;
            $used = MemberInviteLog::query()
                ->where('inviter_id', $inviter->id)
                ->where('days', '>', 0)
                ->where('created_at', '>=', $from)
                ->count();
            if ($used >= $monthCap) {
                return false;
            }
        }
        if ($ipCap > 0 && $checkIp) {
            $usedIp = MemberInviteLog::query()
                ->where('inviter_id', $inviter->id)
                ->where('days', '>', 0)
                ->where('created_at', '>=', $now - 86400);
            if ($ip !== '') {
                $usedIp->where('ip', $ip);
            }
            if ($usedIp->count() >= $ipCap) {
                return false;
            }
        }

        return true;
    }

    /** @return array{ok:bool,inviter:?Member,invite:?MemberInvite} */
    private function resolve(string $code): array
    {
        $empty = ['ok' => false, 'inviter' => null, 'invite' => null];
        $code = $this->normalizeCode($code);
        if ($code === '') {
            return $empty;
        }
        if (Schema::hasColumn('members', 'invite_code')) {
            $owner = Member::query()->where('invite_code', $code)->first();
            if ($owner) {
                return ['ok' => true, 'inviter' => $owner, 'invite' => null];
            }
        }
        if (! Schema::hasTable('member_invites')) {
            return $empty;
        }
        $invite = MemberInvite::query()
            ->where('code', $code)
            ->where('status', 1)
            ->where('used_by', 0)
            ->first();
        if (! $invite) {
            return $empty;
        }
        $inviterId = (int) $invite->member_id;
        $inviter = $inviterId > 0 ? Member::query()->find($inviterId) : null;

        return ['ok' => true, 'inviter' => $inviter, 'invite' => $invite];
    }

    private function issueOneTime(Member $member): void
    {
        if (! Schema::hasTable('member_invites')) {
            return;
        }
        MemberInvite::query()->create([
            'code' => $this->freshCode(),
            'member_id' => (int) $member->id,
            'used_by' => 0,
            'points' => 10,
            'status' => 1,
            'created_at' => time(),
        ]);
    }

    private function freshCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while ($this->codeTaken($code));

        return $code;
    }

    private function codeTaken(string $code): bool
    {
        if (Schema::hasTable('member_invites') && MemberInvite::query()->where('code', $code)->exists()) {
            return true;
        }

        return Schema::hasColumn('members', 'invite_code')
            && Member::query()->where('invite_code', $code)->exists();
    }

    private function trialDays(): int
    {
        return max(0, (int) $this->opt('member_trial_days', '7'));
    }

    private function rewardDays(): int
    {
        return max(0, (int) $this->opt('member_invite_reward_days', '30'));
    }

    private function trialGroupId(): int
    {
        $id = (int) $this->opt('member_trial_group_id', '0');
        if ($id > 0 && Schema::hasTable('member_groups') && MemberGroup::query()->where('id', $id)->where('status', 1)->exists()) {
            return $id;
        }
        if (! Schema::hasTable('member_groups')) {
            return 0;
        }

        return (int) MemberGroup::query()->where('status', 1)->orderByDesc('sort')->orderByDesc('id')->value('id');
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    private function opt(string $key, string $default = ''): string
    {
        return (string) app(VideoSettingService::class)->get($key, $default);
    }

    /** @return array{0:int,1:int,2:int} */
    private function hexRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return [27, 79, 114];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function posterText(mixed $im, string $text, int $y, int $color, bool $center = false, int $font = 5): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }
        $text = substr($text, 0, 80);
        $w = imagesx($im);
        $tw = imagefontwidth($font) * strlen($text);
        $x = $center ? (int) max(16, ($w - $tw) / 2) : 64;
        imagestring($im, $font, $x, $y, $text, $color);
    }
}

<?php

namespace App\Services\Member;

use App\Models\Member\Member;
use App\Models\Member\MemberHistory;
use App\Models\Member\MemberPointLog;
use App\Models\Member\MemberShare;
use App\Models\Member\MemberSign;
use App\Models\Member\MemberSignMilestone;
use App\Models\Member\MemberSignMilestoneLog;
use App\Models\Member\MemberTask;
use App\Models\Member\MemberTaskLog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MemberActivityService
{
    public const STATUS_DOING = 0;

    public const STATUS_DONE = 1;

    public const STATUS_GRANTED = 2;

    public const TYPE_DAILY = 1;

    public const TYPE_NEWBIE = 2;

    public function ready(): bool
    {
        try {
            return Schema::hasTable('member_tasks')
                && Schema::hasTable('member_task_logs')
                && Schema::hasTable('member_signs');
        } catch (Throwable) {
            return false;
        }
    }

    public function todayKey(): string
    {
        return now()->timezone((string) config('app.timezone', 'UTC'))->format('Ymd');
    }

    public function yesterdayKey(): string
    {
        return now()->timezone((string) config('app.timezone', 'UTC'))->subDay()->format('Ymd');
    }

    /** @return array{code:int,msg:string,data:array<string, mixed>} */
    public function sign(Member $member): array
    {
        if (! $this->ready()) {
            return Result::fail('签到未启用');
        }
        $today = $this->todayKey();
        try {
            $out = DB::transaction(function () use ($member, $today) {
                $locked = Member::query()->where('id', $member->id)->lockForUpdate()->first();
                if (! $locked) {
                    throw new \RuntimeException('会员不存在');
                }
                if (MemberSign::query()->where('member_id', $locked->id)->where('day_key', $today)->exists()) {
                    throw new \RuntimeException('今天已经签过了');
                }
                $yesterday = MemberSign::query()
                    ->where('member_id', $locked->id)
                    ->where('day_key', $this->yesterdayKey())
                    ->first();
                $days = $yesterday ? max(1, (int) $yesterday->days + 1) : 1;
                $task = $this->activeTask('daily_sign');
                $points = $task ? max(0, (int) $task->points) : 0;
                MemberSign::query()->create([
                    'member_id' => (int) $locked->id,
                    'days' => $days,
                    'points' => $points,
                    'day_key' => $today,
                    'created_at' => time(),
                ]);
                if ($points > 0) {
                    $this->grantPoints($locked, $points, 'sign', $task ? (string) $task->name : '每日签到');
                }
                if ($task) {
                    $this->markTaskGranted($locked, $task, 1, $points, $today);
                }
                $extra = $this->grantDueMilestones($locked, $days);
                $this->detectPhone($locked);
                $this->detectEmail($locked);
                $msg = '签到成功';
                if ($days > 1) {
                    $msg .= '，连续'.$days.'天';
                }
                if ($points > 0) {
                    $msg .= '，+'.$points.' 积分';
                }
                if ($extra > 0) {
                    $msg .= '，里程碑 +'.$extra;
                }

                return [
                    'days' => $days,
                    'points' => $points,
                    'extra' => $extra,
                    'msg' => $msg,
                ];
            });
        } catch (\RuntimeException $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '签到失败');
        } catch (Throwable) {
            return Result::fail('签到失败');
        }

        $member->points = (int) (Member::query()->where('id', $member->id)->value('points') ?? $member->points);

        return Result::success([
            'days' => (int) ($out['days'] ?? 1),
            'points' => (int) ($out['points'] ?? 0),
            'extra' => (int) ($out['extra'] ?? 0),
        ], (string) ($out['msg'] ?? '签到成功'));
    }

    public function reportWatch(Member $member): void
    {
        if (! $this->ready()) {
            return;
        }
        $task = $this->activeTask('watch_vod');
        if (! $task) {
            return;
        }
        $log = $this->getOrCreateLog($member, $task);
        if ((int) $log->status >= self::STATUS_GRANTED) {
            return;
        }
        $tz = (string) config('app.timezone', 'UTC');
        $start = now()->timezone($tz)->startOfDay()->timestamp;
        $end = now()->timezone($tz)->endOfDay()->timestamp;
        $count = 0;
        if (Schema::hasTable('member_histories')) {
            $count = (int) MemberHistory::query()
                ->where('member_id', $member->id)
                ->whereBetween('updated_at', [$start, $end])
                ->count();
        }
        $target = max(1, (int) $task->target);
        $progress = min($count, $target);
        $log->progress = $progress;
        if ($progress >= $target) {
            $this->grantTask($member, $task, $log);
        } else {
            $log->save();
        }
    }

    public function reportComment(Member $member): void
    {
        if (! $this->ready()) {
            return;
        }
        $this->addProgress($member, 'post_comment', 1);
    }

    /** @return array{code:int,msg:string,data:array<string, mixed>} */
    public function reportShare(Member $member, int $videoId): array
    {
        if (! $this->ready()) {
            return Result::fail('分享未启用');
        }
        if ($videoId < 1 || ! Schema::hasTable('member_shares')) {
            return Result::fail('分享未启用');
        }
        $tz = (string) config('app.timezone', 'UTC');
        $start = now()->timezone($tz)->startOfDay()->timestamp;
        $exists = MemberShare::query()
            ->where('member_id', $member->id)
            ->where('video_id', $videoId)
            ->where('created_at', '>=', $start)
            ->exists();
        if ($exists) {
            return Result::success(['shared' => false], '今天已经分享过这部');
        }
        MemberShare::query()->create([
            'member_id' => (int) $member->id,
            'video_id' => $videoId,
            'channel' => 'link',
            'ip' => mb_substr((string) request()->ip(), 0, 45),
            'created_at' => time(),
        ]);
        $this->addProgress($member, 'share_vod', 1);

        return Result::success(['shared' => true], '已记录分享');
    }

    public function detectPhone(Member $member): void
    {
        if (! $this->ready()) {
            return;
        }
        $phone = '';
        if (Schema::hasColumn('members', 'phone')) {
            $phone = trim((string) ($member->phone ?? ''));
        }
        if ($phone === '') {
            return;
        }
        $this->completeNewbie($member, 'bind_phone');
    }

    public function detectEmail(Member $member): void
    {
        if (! $this->ready()) {
            return;
        }
        $email = trim((string) ($member->email ?? ''));
        if ($email === '') {
            return;
        }
        $this->completeNewbie($member, 'bind_email');
    }

    /** @return array<string, mixed> */
    public function frontState(Member $member): array
    {
        $this->detectPhone($member);
        $this->detectEmail($member);
        $today = $this->todayKey();
        $todaySign = MemberSign::query()
            ->where('member_id', $member->id)
            ->where('day_key', $today)
            ->first();
        $days = 0;
        if ($todaySign) {
            $days = (int) $todaySign->days;
        } else {
            $y = MemberSign::query()
                ->where('member_id', $member->id)
                ->where('day_key', $this->yesterdayKey())
                ->first();
            $days = $y ? (int) $y->days : 0;
        }
        $tasks = [];
        if (Schema::hasTable('member_tasks')) {
            $rows = MemberTask::query()->orderBy('type')->orderBy('sort')->orderBy('id')->get();
            foreach ($rows as $task) {
                if ((int) $task->status !== 1) {
                    continue;
                }
                $dayKey = $this->dayKeyFor($task);
                $log = MemberTaskLog::query()
                    ->where('member_id', $member->id)
                    ->where('task_id', $task->id)
                    ->where('day_key', $dayKey)
                    ->first();
                $tasks[] = [
                    'id' => (int) $task->id,
                    'name' => (string) $task->name,
                    'type' => (int) $task->type,
                    'action' => (string) $task->action,
                    'hint' => (string) $task->hint,
                    'points' => (int) $task->points,
                    'target' => max(1, (int) $task->target),
                    'progress' => $log ? (int) $log->progress : 0,
                    'status' => $log ? (int) $log->status : 0,
                    'type_label' => (int) $task->type === self::TYPE_NEWBIE ? '新手' : '每日',
                ];
            }
        }
        $milestones = [];
        if (Schema::hasTable('member_sign_milestones')) {
            $got = [];
            if (Schema::hasTable('member_sign_milestone_logs')) {
                $got = MemberSignMilestoneLog::query()
                    ->where('member_id', $member->id)
                    ->pluck('milestone_id')
                    ->all();
            }
            $got = array_map('intval', $got);
            foreach (MemberSignMilestone::query()->where('status', 1)->orderBy('days')->orderBy('id')->get() as $row) {
                $milestones[] = [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'days' => (int) $row->days,
                    'points' => (int) $row->points,
                    'granted' => in_array((int) $row->id, $got, true),
                    'reached' => $days >= (int) $row->days,
                ];
            }
        }

        return [
            'signed_today' => $todaySign !== null,
            'days' => $days,
            'points' => (int) $member->fresh()?->points,
            'tasks' => $tasks,
            'milestones' => $milestones,
        ];
    }

    /** @return array<string, int> */
    public function taskQueues(): array
    {
        $out = ['tasks' => 0, 'logs' => 0, 'signs' => 0, 'milestones' => 0];
        try {
            if (Schema::hasTable('member_tasks')) {
                $out['tasks'] = (int) MemberTask::query()->count();
            }
            if (Schema::hasTable('member_task_logs')) {
                $out['logs'] = (int) MemberTaskLog::query()->count();
            }
            if (Schema::hasTable('member_signs')) {
                $out['signs'] = (int) MemberSign::query()->count();
            }
            if (Schema::hasTable('member_sign_milestones')) {
                $out['milestones'] = (int) MemberSignMilestone::query()->count();
            }
        } catch (Throwable) {
        }

        return $out;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    public function decorateTasks(array $rows): array
    {
        foreach ($rows as &$row) {
            $type = (int) ($row['type'] ?? 1);
            $row['type_label'] = $type === self::TYPE_NEWBIE ? '新手' : '每日';
            $row['status_label'] = (int) ($row['status'] ?? 0) === 1 ? '启用' : '未启用';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    public function decorateTaskLogs(array $rows): array
    {
        $memberIds = [];
        $taskIds = [];
        foreach ($rows as $row) {
            $memberIds[] = (int) ($row['member_id'] ?? 0);
            $taskIds[] = (int) ($row['task_id'] ?? 0);
        }
        $names = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            $names = Member::query()->whereIn('id', array_values(array_unique(array_filter($memberIds))))->pluck('name', 'id')->all();
        }
        $taskNames = [];
        if ($taskIds !== [] && Schema::hasTable('member_tasks')) {
            $taskNames = MemberTask::query()->whereIn('id', array_values(array_unique(array_filter($taskIds))))->pluck('name', 'id')->all();
        }
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $tid = (int) ($row['task_id'] ?? 0);
            $row['member_name'] = (string) ($names[$mid] ?? '');
            $row['task_name'] = (string) ($taskNames[$tid] ?? ($row['action'] ?? ''));
            $status = (int) ($row['status'] ?? 0);
            $row['status_label'] = match ($status) {
                self::STATUS_GRANTED => '已入账',
                self::STATUS_DONE => '待领取',
                default => '进行中',
            };
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    public function decorateSigns(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) ($row['member_id'] ?? 0);
        }
        $names = [];
        if ($ids !== [] && Schema::hasTable('members')) {
            $names = Member::query()->whereIn('id', array_values(array_unique(array_filter($ids))))->pluck('name', 'id')->all();
        }
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $row['member_name'] = (string) ($names[$mid] ?? '');
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    public function decorateMilestones(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['status_label'] = (int) ($row['status'] ?? 0) === 1 ? '启用' : '未启用';
        }
        unset($row);

        return $rows;
    }

    public function addProgress(Member $member, string $action, int $increment = 1): void
    {
        $task = $this->activeTask($action);
        if (! $task) {
            return;
        }
        $log = $this->getOrCreateLog($member, $task);
        if ((int) $log->status >= self::STATUS_DONE) {
            return;
        }
        $target = max(1, (int) $task->target);
        $progress = min((int) $log->progress + max(0, $increment), $target);
        $log->progress = $progress;
        if ($progress >= $target) {
            $this->grantTask($member, $task, $log);
        } else {
            $log->save();
        }
    }

    private function completeNewbie(Member $member, string $action): void
    {
        $task = $this->activeTask($action);
        if (! $task || (int) $task->type !== self::TYPE_NEWBIE) {
            return;
        }
        $log = $this->getOrCreateLog($member, $task);
        if ((int) $log->status >= self::STATUS_GRANTED) {
            return;
        }
        $log->progress = max(1, (int) $task->target);
        $this->grantTask($member, $task, $log);
    }

    private function activeTask(string $action): ?MemberTask
    {
        if (! Schema::hasTable('member_tasks')) {
            return null;
        }

        return MemberTask::query()->where('action', $action)->where('status', 1)->first();
    }

    private function dayKeyFor(MemberTask $task): string
    {
        return (int) $task->type === self::TYPE_NEWBIE ? 'newbie' : $this->todayKey();
    }

    private function getOrCreateLog(Member $member, MemberTask $task): MemberTaskLog
    {
        $dayKey = $this->dayKeyFor($task);
        $log = MemberTaskLog::query()
            ->where('member_id', $member->id)
            ->where('task_id', $task->id)
            ->where('day_key', $dayKey)
            ->first();
        if ($log) {
            return $log;
        }

        return MemberTaskLog::query()->create([
            'member_id' => (int) $member->id,
            'task_id' => (int) $task->id,
            'action' => (string) $task->action,
            'progress' => 0,
            'status' => self::STATUS_DOING,
            'points' => 0,
            'day_key' => $dayKey,
            'claimed_at' => 0,
            'created_at' => time(),
        ]);
    }

    private function grantTask(Member $member, MemberTask $task, MemberTaskLog $log): void
    {
        if ((int) $log->status >= self::STATUS_GRANTED) {
            return;
        }
        $points = max(0, (int) $task->points);
        $log->progress = max((int) $log->progress, max(1, (int) $task->target));
        $log->status = self::STATUS_GRANTED;
        $log->points = $points;
        $log->claimed_at = time();
        $log->save();
        if ($points > 0) {
            $type = (string) $task->action === 'daily_sign' ? 'sign' : 'task';
            $this->grantPoints($member, $points, $type, (string) $task->name);
        }
    }

    private function markTaskGranted(Member $member, MemberTask $task, int $progress, int $points, string $dayKey): void
    {
        $log = MemberTaskLog::query()
            ->where('member_id', $member->id)
            ->where('task_id', $task->id)
            ->where('day_key', $dayKey)
            ->first();
        $now = time();
        if (! $log) {
            MemberTaskLog::query()->create([
                'member_id' => (int) $member->id,
                'task_id' => (int) $task->id,
                'action' => (string) $task->action,
                'progress' => $progress,
                'status' => self::STATUS_GRANTED,
                'points' => $points,
                'day_key' => $dayKey,
                'claimed_at' => $now,
                'created_at' => $now,
            ]);

            return;
        }
        $log->progress = max((int) $log->progress, $progress);
        $log->status = self::STATUS_GRANTED;
        $log->points = $points;
        $log->claimed_at = $now;
        $log->save();
    }

    private function grantDueMilestones(Member $member, int $days): int
    {
        if (! Schema::hasTable('member_sign_milestones') || ! Schema::hasTable('member_sign_milestone_logs')) {
            return 0;
        }
        $extra = 0;
        $rows = MemberSignMilestone::query()
            ->where('status', 1)
            ->where('days', '<=', max(0, $days))
            ->orderBy('days')
            ->get();
        foreach ($rows as $row) {
            $exists = MemberSignMilestoneLog::query()
                ->where('member_id', $member->id)
                ->where('milestone_id', $row->id)
                ->exists();
            if ($exists) {
                continue;
            }
            $pts = max(0, (int) $row->points);
            MemberSignMilestoneLog::query()->create([
                'member_id' => (int) $member->id,
                'milestone_id' => (int) $row->id,
                'days' => (int) $row->days,
                'points' => $pts,
                'created_at' => time(),
            ]);
            if ($pts > 0) {
                $this->grantPoints($member, $pts, 'milestone', '连续签到'.(int) $row->days.'天');
                $extra += $pts;
            }
        }

        return $extra;
    }

    private function grantPoints(Member $member, int $points, string $type, string $remark): void
    {
        if ($points < 1) {
            return;
        }
        $member->increment('points', $points);
        $fresh = $member->fresh() ?? $member;
        $member->points = (int) $fresh->points;
        if (! Schema::hasTable('member_point_logs')) {
            return;
        }
        MemberPointLog::query()->create([
            'member_id' => (int) $member->id,
            'points' => $points,
            'balance' => (int) $fresh->points,
            'type' => $type,
            'remark' => mb_substr($remark, 0, 250),
            'created_at' => time(),
        ]);
    }
}

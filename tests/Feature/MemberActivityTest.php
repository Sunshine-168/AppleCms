<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberPointLog;
use App\Models\Member\MemberShare;
use App\Models\Member\MemberSign;
use App\Models\Member\MemberSignMilestoneLog;
use App\Models\Member\MemberTask;
use App\Models\Member\MemberTaskLog;
use App\Models\Video\VideoModel;
use App\Services\Member\MemberActivityService;
use App\Services\Video\InteractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_sign_once_ok_second_today_fails(): void
    {
        $member = $this->member();
        $svc = app(MemberActivityService::class);
        $first = $svc->sign($member);
        $this->assertSame(0, $first['code'], $first['msg'] ?? '');
        $this->assertSame(1, (int) ($first['data']['days'] ?? 0));
        $this->assertSame(5, (int) ($first['data']['points'] ?? 0));
        $member->refresh();
        $this->assertSame(5, (int) $member->points);
        $this->assertSame(1, MemberSign::query()->where('member_id', $member->id)->count());
        $this->assertSame(1, MemberPointLog::query()->where('member_id', $member->id)->where('type', 'sign')->count());

        $second = $svc->sign($member);
        $this->assertSame(1, $second['code']);
        $this->assertStringContainsString('已经签', (string) ($second['msg'] ?? ''));
        $member->refresh();
        $this->assertSame(5, (int) $member->points);
        $this->assertSame(1, MemberSign::query()->where('member_id', $member->id)->count());
    }

    public function test_consecutive_days_grants_milestone_on_the_spot(): void
    {
        $member = $this->member();
        $svc = app(MemberActivityService::class);
        MemberSign::query()->create([
            'member_id' => $member->id,
            'days' => 2,
            'points' => 5,
            'day_key' => $svc->yesterdayKey(),
            'created_at' => time() - 86400,
        ]);
        $res = $svc->sign($member);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertSame(3, (int) ($res['data']['days'] ?? 0));
        $this->assertSame(5, (int) ($res['data']['points'] ?? 0));
        $this->assertSame(5, (int) ($res['data']['extra'] ?? 0));
        $this->assertStringContainsString('里程碑', (string) ($res['msg'] ?? ''));
        $member->refresh();
        $this->assertSame(10, (int) $member->points);
        $this->assertSame(1, MemberSignMilestoneLog::query()->where('member_id', $member->id)->count());
        $this->assertSame(1, MemberPointLog::query()->where('member_id', $member->id)->where('type', 'milestone')->count());
    }

    public function test_watch_three_distinct_videos_grants_watch_vod_once(): void
    {
        $member = $this->member();
        $interaction = app(InteractionService::class);
        $videos = [];
        for ($i = 0; $i < 3; $i++) {
            $videos[] = VideoModel::query()->create([
                'title' => '看片'.$i,
                'status' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
        foreach ($videos as $video) {
            $interaction->recordHistory((int) $member->id, $video);
        }
        $member->refresh();
        $this->assertSame(3, (int) $member->points);
        $task = MemberTask::query()->where('action', 'watch_vod')->first();
        $this->assertNotNull($task);
        $log = MemberTaskLog::query()->where('member_id', $member->id)->where('task_id', $task->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status);
        $this->assertSame(3, (int) $log->points);

        $interaction->recordHistory((int) $member->id, $videos[0]);
        $member->refresh();
        $this->assertSame(3, (int) $member->points);
        $this->assertSame(1, MemberPointLog::query()->where('member_id', $member->id)->where('type', 'task')->count());
    }

    public function test_comment_uses_task_points_not_naked_plus_one(): void
    {
        $member = $this->member();
        $video = VideoModel::query()->create([
            'title' => '评论片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $res = app(InteractionService::class)->addComment((int) $video->id, '好看', $member, '', '127.0.0.1');
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $member->refresh();
        $this->assertSame(2, (int) $member->points);
        $task = MemberTask::query()->where('action', 'post_comment')->first();
        $this->assertNotNull($task);
        $log = MemberTaskLog::query()->where('member_id', $member->id)->where('task_id', $task->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status);
        $this->assertSame(2, (int) $log->points);
        $this->assertSame(1, MemberPointLog::query()->where('member_id', $member->id)->where('type', 'task')->count());
    }

    public function test_share_is_once_per_member_video_day(): void
    {
        $member = $this->member();
        $video = VideoModel::query()->create([
            'title' => '分享片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->actingAs($member, 'member')
            ->postJson('/vod/'.$video->id.'/share')
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertSame(1, MemberShare::query()->where('member_id', $member->id)->where('video_id', $video->id)->count());
        $share = MemberShare::query()->first();
        $this->assertSame('link', (string) $share->channel);
        $member->refresh();
        $this->assertSame(2, (int) $member->points);
        $task = MemberTask::query()->where('action', 'share_vod')->first();
        $log = MemberTaskLog::query()->where('member_id', $member->id)->where('task_id', $task->id)->first();
        $this->assertSame(1, (int) $log->progress);
        $this->assertSame(2, (int) $log->status);

        $this->actingAs($member, 'member')
            ->postJson('/vod/'.$video->id.'/share')
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertSame(1, MemberShare::query()->where('member_id', $member->id)->where('video_id', $video->id)->count());
        $log->refresh();
        $this->assertSame(1, (int) $log->progress);
        $member->refresh();
        $this->assertSame(2, (int) $member->points);
    }

    public function test_bind_phone_grants_once_when_phone_set(): void
    {
        $member = $this->member(['phone' => '13800138000']);
        $svc = app(MemberActivityService::class);
        $svc->detectPhone($member);
        $member->refresh();
        $this->assertSame(20, (int) $member->points);
        $task = MemberTask::query()->where('action', 'bind_phone')->first();
        $this->assertNotNull($task);
        $log = MemberTaskLog::query()->where('member_id', $member->id)->where('task_id', $task->id)->first();
        $this->assertSame('newbie', (string) $log->day_key);
        $this->assertSame(2, (int) $log->status);
        $svc->detectPhone($member);
        $member->refresh();
        $this->assertSame(20, (int) $member->points);

        $emailTask = MemberTask::query()->where('action', 'bind_email')->first();
        $this->assertNotNull($emailTask);
        $this->assertSame(0, (int) $emailTask->status);
        $svc->detectEmail($member);
        $this->assertSame(0, MemberTaskLog::query()->where('member_id', $member->id)->where('task_id', $emailTask->id)->count());
    }

    public function test_http_sign_and_activity_page(): void
    {
        $member = $this->member();
        $this->actingAs($member, 'member')
            ->postJson('/member/sign')
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.days', 1);
        $html = $this->actingAs($member, 'member')->get('/member/activity')->assertOk()->getContent();
        $this->assertStringContainsString('用户活动', $html);
        $this->assertStringContainsString('今天已签到', $html);
        $this->assertStringContainsString('每日任务', $html);
        $this->assertStringNotContainsString('绑定邮箱', $html);
        $center = $this->actingAs($member, 'member')->get('/member')->assertOk()->getContent();
        $this->assertStringContainsString('/member/activity', $center);
    }

    /** @param array<string, mixed> $extra */
    private function member(array $extra = []): Member
    {
        return Member::query()->create(array_merge([
            'name' => '活动会员',
            'email' => 'act-'.uniqid().'@test.local',
            'password' => Hash::make('secret12'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ], $extra));
    }
}

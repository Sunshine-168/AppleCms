<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberInvite;
use App\Models\Member\MemberPointLog;
use App\Models\Video\VideoOption;
use App\Services\Video\InteractionService;
use App\Services\Video\VideoSettingService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Plugins\Sms\Models\SmsCode;
use Tests\TestCase;

class MemberGrowthTest extends TestCase
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

    public function test_points_mode_keeps_invite_points_and_skips_vip_days(): void
    {
        $inviter = $this->member(['points' => 3]);
        $code = $this->oneTimeCode($inviter, 10);

        $result = $this->registerMember([
            'name' => '积分新人',
            'invite' => $code,
        ]);
        $this->assertSame(0, $result['code'], $result['msg'] ?? '');

        $invitee = Member::query()->find((int) $result['data']['id']);
        $this->assertNotNull($invitee);
        $this->assertSame(10, (int) $invitee->points);
        $this->assertSame(0, (int) $invitee->effectiveGroupId());
        $inviter->refresh();
        $this->assertSame(13, (int) $inviter->points);
        $this->assertSame(0, (int) $inviter->effectiveGroupId());
        $this->assertSame(0, $this->rewardLogCount());
        $this->assertSame(1, MemberPointLog::query()->where('type', 'invite')->count());
    }

    public function test_vip_days_mode_grants_trial_and_inviter_days_without_points(): void
    {
        $group = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_trial_days' => '7',
            'member_invite_reward_days' => '30',
            'member_trial_group_id' => (string) $group->id,
            'member_invite_month_cap' => '5',
            'member_invite_ip_daily_cap' => '2',
        ]);
        $inviter = $this->member(['points' => 8]);
        $code = $this->personalCode($inviter);

        $result = $this->registerMember([
            'name' => '拉新新人',
            'invite' => $code,
            'ip' => '10.8.0.2',
        ]);
        $this->assertSame(0, $result['code'], $result['msg'] ?? '');

        $invitee = Member::query()->find((int) $result['data']['id']);
        $this->assertNotNull($invitee);
        $this->assertSame(0, (int) $invitee->points);
        $this->assertSame((int) $group->id, $invitee->effectiveGroupId());
        $this->assertGreaterThanOrEqual(time() + 6 * 86400, (int) $invitee->group_expire_at);
        $this->assertLessThanOrEqual(time() + 8 * 86400, (int) $invitee->group_expire_at);

        $inviter->refresh();
        $this->assertSame(8, (int) $inviter->points);
        $this->assertSame((int) $group->id, $inviter->effectiveGroupId());
        $this->assertGreaterThanOrEqual(time() + 29 * 86400, (int) $inviter->group_expire_at);
        $this->assertSame(0, MemberPointLog::query()->where('type', 'invite')->count());
        $this->assertSame(1, $this->rewardLogCount($inviter->id, 30));
    }

    public function test_vip_days_stacks_on_paid_group_and_skips_permanent(): void
    {
        $paid = MemberGroup::query()->create([
            'name' => '黄金',
            'points_min' => 0,
            'sort' => 20,
            'status' => 1,
        ]);
        $trial = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_trial_days' => '7',
            'member_invite_reward_days' => '30',
            'member_trial_group_id' => (string) $trial->id,
        ]);

        $left = time() + 10 * 86400;
        $paidMember = $this->member([
            'group_id' => $paid->id,
            'group_expire_at' => $left,
        ]);
        $permanent = $this->member([
            'group_id' => $paid->id,
            'group_expire_at' => 0,
        ]);

        $this->assertSame(0, $this->registerMember([
            'invite' => $this->personalCode($paidMember),
            'ip' => '10.8.1.1',
        ])['code']);
        $paidMember->refresh();
        $this->assertSame((int) $paid->id, (int) $paidMember->group_id);
        $this->assertGreaterThanOrEqual($left + 29 * 86400, (int) $paidMember->group_expire_at);

        $this->assertSame(0, $this->registerMember([
            'invite' => $this->personalCode($permanent),
            'ip' => '10.8.1.2',
        ])['code']);
        $permanent->refresh();
        $this->assertSame((int) $paid->id, (int) $permanent->group_id);
        $this->assertSame(0, (int) $permanent->group_expire_at);
        $this->assertSame(0, $this->rewardLogCount($permanent->id, null, true));
    }

    public function test_vip_days_caps_inviter_days_by_month_and_ip(): void
    {
        $group = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_trial_days' => '7',
            'member_invite_reward_days' => '30',
            'member_trial_group_id' => (string) $group->id,
            'member_invite_month_cap' => '1',
            'member_invite_ip_daily_cap' => '1',
        ]);
        $inviter = $this->member();
        $code = $this->personalCode($inviter);

        $this->assertSame(0, $this->registerMember(['invite' => $code, 'ip' => '10.9.0.1'])['code']);
        $inviter->refresh();
        $firstExpire = (int) $inviter->group_expire_at;
        $this->assertGreaterThan(time(), $firstExpire);

        $this->assertSame(0, $this->registerMember(['invite' => $code, 'ip' => '10.9.0.1'])['code']);
        $inviter->refresh();
        $this->assertSame($firstExpire, (int) $inviter->group_expire_at);

        $this->assertSame(0, $this->registerMember(['invite' => $code, 'ip' => '10.9.0.8'])['code']);
        $inviter->refresh();
        $this->assertSame($firstExpire, (int) $inviter->group_expire_at);
        $this->assertSame(1, $this->rewardLogCount($inviter->id, null, true));
    }

    public function test_off_mode_consumes_code_without_points_or_days(): void
    {
        $this->setGrowth(['member_growth_mode' => 'off']);
        $inviter = $this->member(['points' => 4]);
        $code = $this->oneTimeCode($inviter, 15);

        $result = $this->registerMember(['invite' => $code]);
        $this->assertSame(0, $result['code'], $result['msg'] ?? '');
        $invitee = Member::query()->find((int) $result['data']['id']);
        $this->assertSame(0, (int) $invitee->points);
        $this->assertSame(0, (int) $invitee->effectiveGroupId());
        $inviter->refresh();
        $this->assertSame(4, (int) $inviter->points);
        $this->assertSame(0, (int) $inviter->effectiveGroupId());
        $row = MemberInvite::query()->where('code', $code)->first();
        $this->assertNotNull($row);
        $this->assertSame(0, (int) $row->status);
        $this->assertSame((int) $invitee->id, (int) $row->used_by);
    }

    public function test_required_invite_rejects_empty_or_unknown_code(): void
    {
        $this->setGrowth(['member_invite' => '1']);
        $empty = $this->registerMember(['invite' => '']);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('邀请码', (string) ($empty['msg'] ?? ''));

        $bad = $this->registerMember(['invite' => 'NOPE0001']);
        $this->assertSame(1, $bad['code']);
    }

    public function test_settings_page_offers_growth_modes(): void
    {
        $this->actingAsAdmin();
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/settings?tab=member')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('name="member_growth_mode"', $html);
        $this->assertStringContainsString('积分邀请', $html);
        $this->assertStringContainsString('拉新送会员天数', $html);
        $this->assertStringContainsString('不发奖励', $html);
        $this->assertStringContainsString('name="member_trial_days"', $html);
        $this->assertStringContainsString('name="member_invite_reward_days"', $html);
    }

    /** @param  array<string, string>  $pairs */
    protected function setGrowth(array $pairs): void
    {
        $now = time();
        foreach ($pairs as $k => $v) {
            VideoOption::query()->updateOrCreate(['k' => $k], ['v' => $v, 'updated_at' => $now]);
        }
        Cache::forget(VideoSettingService::CACHE_KEY);
    }

    /** @param  array<string, mixed>  $extra */
    protected function member(array $extra = []): Member
    {
        $row = Member::query()->create(array_merge([
            'name' => '邀请人',
            'email' => 'inv-'.uniqid().'@test.local',
            'password' => Hash::make('secret12'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ], $extra));

        return $row->fresh();
    }

    protected function personalCode(Member $member): string
    {
        $issued = app(InteractionService::class)->generateInvite($member);
        $this->assertSame(0, $issued['code'], $issued['msg'] ?? '');
        $code = trim((string) ($issued['data']['code'] ?? ''));
        $this->assertNotSame('', $code);

        return $code;
    }

    protected function rewardLogCount(?int $inviterId = null, ?int $days = null, bool $positiveDays = false): int
    {
        $this->assertTrue(Schema::hasTable('member_invite_logs'));
        $q = DB::table('member_invite_logs');
        if ($inviterId !== null) {
            $q->where('inviter_id', $inviterId);
        }
        if ($days !== null) {
            $q->where('days', $days);
        }
        if ($positiveDays) {
            $q->where('days', '>', 0);
        }

        return $q->count();
    }

    private function oneTimeCode(Member $inviter, int $points): string
    {
        $code = 'P'.strtoupper(substr(uniqid(), -7));
        MemberInvite::query()->create([
            'code' => $code,
            'member_id' => (int) $inviter->id,
            'used_by' => 0,
            'points' => $points,
            'status' => 1,
            'created_at' => time(),
        ]);

        return $code;
    }

    protected function vipGroup(): MemberGroup
    {
        $row = MemberGroup::query()->where('name', 'VIP')->first();
        if ($row) {
            return $row;
        }

        return MemberGroup::query()->create([
            'name' => 'VIP',
            'points_min' => 1000,
            'sort' => 10,
            'status' => 1,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    protected function registerMember(array $data): array
    {
        $payload = array_merge([
            'name' => '新人'.uniqid(),
            'email' => 'new-'.uniqid().'@test.local',
            'password' => 'secret12',
            'ip' => '10.0.0.8',
        ], $data);
        if (app(PluginManager::class)->isEnabled('sms') && Schema::hasTable('plugin_sms_codes')) {
            $phone = '139'.str_pad((string) random_int(10000000, 99999999), 8, '0');
            SmsCode::query()->create([
                'phone' => $phone,
                'code' => '654321',
                'scene' => 'register',
                'expire_at' => time() + 300,
                'created_at' => time(),
            ]);
            $payload['phone'] = $phone;
            $payload['sms_code'] = '654321';
        }

        return app(InteractionService::class)->register($payload);
    }
}

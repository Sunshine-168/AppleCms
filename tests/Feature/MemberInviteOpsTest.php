<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Services\Member\MemberGrowthService;
use Illuminate\Support\Facades\DB;

class MemberInviteOpsTest extends MemberGrowthTest
{
    public function test_direct_invites_rank_by_count(): void
    {
        $group = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_trial_group_id' => (string) $group->id,
            'member_invite_month_cap' => '20',
            'member_invite_ip_daily_cap' => '10',
        ]);
        $lead = $this->member(['name' => '排行甲']);
        $other = $this->member(['name' => '排行乙']);
        $leadCode = $this->personalCode($lead);
        $otherCode = $this->personalCode($other);

        $this->assertSame(0, $this->registerMember(['invite' => $leadCode, 'ip' => '10.1.0.1'])['code']);
        $this->assertSame(0, $this->registerMember(['invite' => $leadCode, 'ip' => '10.1.0.2'])['code']);
        $this->assertSame(0, $this->registerMember(['invite' => $otherCode, 'ip' => '10.1.0.3'])['code']);

        $rows = app(MemberGrowthService::class)->rank('all', 10);
        $this->assertGreaterThanOrEqual(2, count($rows));
        $this->assertSame((int) $lead->id, (int) $rows[0]['member_id']);
        $this->assertSame(2, (int) $rows[0]['invites']);
        $this->assertSame((int) $other->id, (int) $rows[1]['member_id']);
        $this->assertSame(1, (int) $rows[1]['invites']);
    }

    public function test_l2_and_l3_days_follow_the_inviter_chain(): void
    {
        $group = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_trial_days' => '7',
            'member_invite_reward_days' => '30',
            'member_invite_l2_days' => '7',
            'member_invite_l3_days' => '3',
            'member_trial_group_id' => (string) $group->id,
            'member_invite_month_cap' => '20',
            'member_invite_ip_daily_cap' => '10',
        ]);
        $a = $this->member(['name' => '一级']);
        $aCode = $this->personalCode($a);
        $bId = (int) $this->registerMember(['name' => '二级', 'invite' => $aCode, 'ip' => '10.2.0.1'])['data']['id'];
        $b = Member::query()->find($bId);
        $this->assertSame((int) $a->id, (int) $b->inviter_id);
        $cId = (int) $this->registerMember(['name' => '三级', 'invite' => (string) $b->invite_code, 'ip' => '10.2.0.2'])['data']['id'];
        $c = Member::query()->find($cId);
        $this->assertSame((int) $b->id, (int) $c->inviter_id);

        $a->refresh();
        $b->refresh();
        $this->assertGreaterThanOrEqual(time() + 36 * 86400, (int) $a->group_expire_at);
        $this->assertSame(1, (int) DB::table('member_invite_logs')->where('inviter_id', $a->id)->where('level', 2)->where('days', 7)->count());

        $dId = (int) $this->registerMember(['name' => '四级', 'invite' => (string) $c->invite_code, 'ip' => '10.2.0.3'])['data']['id'];
        $this->assertGreaterThan(0, $dId);
        $a->refresh();
        $this->assertSame(1, (int) DB::table('member_invite_logs')->where('inviter_id', $a->id)->where('level', 3)->where('days', 3)->count());
        $this->assertSame(1, (int) DB::table('member_invite_logs')->where('inviter_id', $b->id)->where('level', 2)->count());
    }

    public function test_l2_off_does_not_pay_upline(): void
    {
        $group = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_invite_l2_days' => '0',
            'member_invite_l3_days' => '0',
            'member_trial_group_id' => (string) $group->id,
            'member_invite_month_cap' => '20',
            'member_invite_ip_daily_cap' => '10',
        ]);
        $a = $this->member();
        $aCode = $this->personalCode($a);
        $bId = (int) $this->registerMember(['invite' => $aCode, 'ip' => '10.3.0.1'])['data']['id'];
        $b = Member::query()->find($bId);
        $this->registerMember(['invite' => (string) $b->invite_code, 'ip' => '10.3.0.2']);
        $this->assertSame(0, (int) DB::table('member_invite_logs')->where('level', '>', 1)->count());
    }

    public function test_poster_and_rank_pages_work(): void
    {
        $this->actingAsAdmin();
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/invites')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('邀请排行', $html);

        $settings = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/settings?tab=member')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('name="member_invite_l2_days"', $settings);
        $this->assertStringContainsString('name="member_invite_l3_days"', $settings);

        $this->get('/member/register?invite=ABC12XYZ')
            ->assertOk()
            ->assertSee('value="ABC12XYZ"', false);

        $group = $this->vipGroup();
        $this->setGrowth([
            'member_growth_mode' => 'vip_days',
            'member_trial_group_id' => (string) $group->id,
        ]);
        $member = $this->member();
        $this->personalCode($member);
        $png = $this->actingAs($member, 'member')->get('/member/invite/poster');
        $png->assertOk();
        $this->assertStringContainsString('image/png', (string) $png->headers->get('content-type'));
        $this->assertGreaterThan(100, strlen($png->getContent()));
    }
}

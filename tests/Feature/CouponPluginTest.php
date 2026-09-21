<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberOrder;
use App\Services\Admin\Video\SiteModuleService;
use App\Services\Video\MemberOrderService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Plugins\Coupon\Models\PluginCoupon;
use Plugins\Coupon\Models\PluginCouponUser;
use Plugins\Coupon\Services\CouponService;
use Tests\TestCase;

class CouponPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_board_stays_in_member_workspace(): void
    {
        if (! app(PluginManager::class)->isEnabled('coupon')) {
            $this->markTestSkipped('coupon plugin disabled');
        }
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/coupons')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('coupon-board', $html);
        $this->assertStringContainsString('对照苹果', $html);
        $this->assertStringContainsString('积分商城不抵', $html);
        $this->assertStringContainsString('/admin/video/coupons?desk=received', $html);
        $this->assertStringContainsString('class="is-on">会员</a>', $html);
        $this->assertMatchesRegularExpression(
            '/<details class="nav-fold is-open"[^>]*>\s*<summary>\s*更多\s*<\/summary>/u',
            $html
        );
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/coupons/list')
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($list['code'] ?? 1), $list['msg'] ?? '');
    }

    public function test_save_receive_quote_and_write_off(): void
    {
        if (! app(PluginManager::class)->isEnabled('coupon')) {
            $this->markTestSkipped('coupon plugin disabled');
        }
        $svc = app(SiteModuleService::class);
        $empty = $svc->save('coupons', ['desk' => 'campaigns', 'name' => '  '], null);
        $this->assertSame(1, $empty['code']);

        $zero = $svc->save('coupons', [
            'desk' => 'campaigns',
            'name' => '零元券',
            'type' => 'amount',
            'value' => 10,
            'min_price' => 0,
            'scene' => 'recharge',
            'total' => 2,
            'status' => 1,
        ], null);
        $this->assertSame(0, $zero['code'], $zero['msg'] ?? '');
        $full = PluginCoupon::query()->where('name', '零元券')->first();
        $this->assertNotNull($full);

        $member = $this->member();
        $coupons = app(CouponService::class);
        $got = $coupons->receive((int) $full->id, (int) $member->id);
        $this->assertSame(0, $got['code'], $got['msg'] ?? '');
        $again = $coupons->receive((int) $full->id, (int) $member->id);
        $this->assertSame(1, $again['code']);

        $cu = PluginCouponUser::query()->where('member_id', $member->id)->first();
        $this->assertNotNull($cu);
        $quote = $coupons->quote('recharge', 10, (int) $cu->id, (int) $member->id);
        $this->assertSame(1, $quote['code'], '满减 10 元把 10 元套餐打成 0 必须失败');

        $ok = $svc->save('coupons', [
            'desk' => 'campaigns',
            'name' => '充 30 减 5',
            'type' => 'amount',
            'value' => 5,
            'min_price' => 30,
            'scene' => 'recharge',
            'total' => 10,
            'status' => 1,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $row = PluginCoupon::query()->where('name', '充 30 减 5')->first();
        $this->assertNotNull($row);
        $got2 = $coupons->receive((int) $row->id, (int) $member->id);
        $this->assertSame(0, $got2['code'], $got2['msg'] ?? '');
        $cu2 = PluginCouponUser::query()->where('coupon_id', $row->id)->where('member_id', $member->id)->first();
        $low = $coupons->quote('recharge', 10, (int) $cu2->id, (int) $member->id);
        $this->assertSame(1, $low['code']);
        $priced = $coupons->quote('recharge', 30, (int) $cu2->id, (int) $member->id);
        $this->assertSame(0, $priced['code'], $priced['msg'] ?? '');
        $this->assertSame(3000, (int) ($priced['data']['original_fen'] ?? 0));
        $this->assertSame(500, (int) ($priced['data']['discount_fen'] ?? 0));
        $this->assertSame(2500, (int) ($priced['data']['pay_fen'] ?? 0));

        $vip = $svc->save('coupons', [
            'desk' => 'campaigns',
            'name' => '只给会员',
            'type' => 'discount',
            'value' => 10,
            'min_price' => 0,
            'scene' => 'vip',
            'total' => 3,
            'group_ids' => (string) MemberGroup::query()->create([
                'name' => '黄金',
                'points_min' => 0,
                'sort' => 1,
                'status' => 1,
            ])->id,
            'longs' => 'month,year',
            'status' => 1,
        ], null);
        $this->assertSame(0, $vip['code'], $vip['msg'] ?? '');
        $vipRow = PluginCoupon::query()->where('name', '只给会员')->first();
        $this->assertNotNull($vipRow);
        $gotVip = $coupons->receive((int) $vipRow->id, (int) $member->id);
        $this->assertSame(0, $gotVip['code'], $gotVip['msg'] ?? '');
        $cuVip = PluginCouponUser::query()->where('coupon_id', $vipRow->id)->first();
        $wrongScene = $coupons->quote('recharge', 50, (int) $cuVip->id, (int) $member->id);
        $this->assertSame(1, $wrongScene['code']);
        $walletPay = array_column($coupons->wallet((int) $member->id, 'recharge'), 'name');
        $this->assertContains('充 30 减 5', $walletPay);
        $this->assertContains('零元券', $walletPay);
        $this->assertNotContains('只给会员', $walletPay);
        $checkout = $this->actingAs($member, 'member')->get('/member/pay')->assertOk()->getContent();
        $this->assertStringContainsString('未配置支付参数', $checkout);
        $this->assertStringContainsString('充 30 减 5', $checkout);
        $this->assertStringContainsString('零元券', $checkout);
        $this->assertStringNotContainsString('只给会员', $checkout);

        $order = MemberOrder::query()->create([
            'member_id' => $member->id,
            'order_no' => 'PCOUPON1',
            'amount' => 2500,
            'points' => 3000,
            'status' => 0,
            'channel' => 'wechat',
            'trade_no' => '',
            'created_at' => time(),
            'updated_at' => time(),
            'original_amount' => 3000,
            'coupon_user_id' => (int) $cu2->id,
            'coupon_discount' => 500,
        ]);
        $hold = $coupons->reserve((int) $cu2->id, (int) $member->id, (int) $order->id, (string) $order->order_no);
        $this->assertSame(0, $hold['code'], $hold['msg'] ?? '');
        $paid = app(MemberOrderService::class)->settle((int) $order->id, 'tx-1', 'wechat');
        $this->assertSame(0, $paid['code'], $paid['msg'] ?? '');
        $cu2->refresh();
        $this->assertSame(1, (int) $cu2->status);
        $row->refresh();
        $this->assertSame(1, (int) $row->used);
        $member->refresh();
        // 订单积分 3000 + 每日「在线充值」任务 5
        $this->assertSame(3005, (int) $member->points);

        $center = $this->actingAs($member, 'member')->get('/member')->assertOk()->getContent();
        $this->assertStringContainsString('/member/coupons', $center);
        $page = $this->actingAs($member, 'member')->get('/member/coupons')->assertOk()->getContent();
        $this->assertStringContainsString('充 30 减 5', $page);
        $this->assertStringContainsString('已用', $page);
        $pay = $this->actingAs($member, 'member')->get('/member/pay')->assertOk()->getContent();
        $this->assertStringContainsString('未配置支付参数', $pay);
        $this->assertStringContainsString('零元券', $pay);
        $this->assertStringNotContainsString('充 30 减 5', $pay);
    }

    public function test_received_desk_cannot_be_saved_by_hand(): void
    {
        if (! app(PluginManager::class)->isEnabled('coupon')) {
            $this->markTestSkipped('coupon plugin disabled');
        }
        $svc = app(SiteModuleService::class);
        $fake = $svc->save('coupons', ['desk' => 'received', 'name' => '手添领取'], null);
        $this->assertSame(1, $fake['code']);
    }

    /** @param  array<string, mixed>  $extra */
    private function member(array $extra = []): Member
    {
        return Member::query()->create(array_merge([
            'name' => '领券人',
            'email' => 'coupon-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ], $extra));
    }
}

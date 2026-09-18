<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberOrder;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Plugins\Pay\Drivers\DfPayDriver;
use Plugins\Pay\Drivers\EpayDriver;
use Plugins\Pay\Models\PayChannel;
use Plugins\Pay\Services\PayChannelService;
use Plugins\Pay\Services\PayService;
use Tests\TestCase;

class PayChannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_admin_board_and_save_channel(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/pay_channels')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('pay-channel-board', $html);
        $this->assertStringContainsString('易支付', $html);
        $this->assertStringContainsString('DfPay', $html);

        $svc = app(PayChannelService::class);
        $ok = $svc->save([
            'title' => '测试易支付',
            'driver' => 'epay',
            'code' => 'alipay',
            'api_url' => 'https://pay.example.com/',
            'mch_id' => '1001',
            'app_key' => 'secretkey',
            'status' => 1,
            'sort' => 1,
        ]);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $row = PayChannel::query()->first();
        $this->assertNotNull($row);
        $this->assertSame('epay', (string) $row->driver);
        $this->assertSame('1001', (string) $row->mch_id);
    }

    public function test_epay_create_jump_and_notify_credits_points(): void
    {
        $channel = PayChannel::query()->create([
            'title' => '易支付',
            'driver' => 'epay',
            'code' => 'alipay',
            'api_url' => 'https://pay.example.com/',
            'mch_id' => '1001',
            'app_key' => 'secretkey',
            'min_fen' => 0,
            'max_fen' => 0,
            'sort' => 1,
            'status' => 1,
            'hint' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $member = Member::query()->create([
            'name' => '付款人',
            'email' => 'pay-epay-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->actingAs($member, 'member');
        $res = app(PayService::class)->create($member, 'ch:'.$channel->id, 10);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertSame('epay', $res['data']['channel'] ?? '');
        $this->assertStringContainsString('submit.php?', (string) ($res['data']['pay_url'] ?? ''));

        $order = MemberOrder::query()->where('order_no', $res['data']['order_no'])->first();
        $this->assertNotNull($order);
        $this->assertSame(1000, (int) $order->amount);
        $this->assertSame((int) $channel->id, (int) ($order->pay_channel_id ?? 0));

        $payload = [
            'pid' => '1001',
            'trade_no' => 'T123',
            'out_trade_no' => (string) $order->order_no,
            'type' => 'alipay',
            'name' => '积分充值',
            'money' => '10.00',
            'trade_status' => 'TRADE_SUCCESS',
        ];
        $payload['sign'] = app(EpayDriver::class)->sign($payload, 'secretkey');

        $this->post('/pay/notify/epay', $payload)
            ->assertOk()
            ->assertSee('success');

        $order->refresh();
        $member->refresh();
        $this->assertSame(1, (int) $order->status);
        // 订单 1000 + 每日「在线充值」任务 5
        $this->assertSame(1005, (int) $member->points);
        $this->assertGreaterThan(0, (int) ($order->paid_at ?? 0));
        $this->assertDatabaseHas('member_point_logs', [
            'member_id' => $member->id,
            'type' => 'order',
            'points' => 1000,
        ]);
        $this->assertDatabaseHas('member_pms', [
            'to_id' => $member->id,
            'title' => '充值到账',
        ]);
    }

    public function test_dfpay_create_and_notify(): void
    {
        Http::fake([
            'https://gateway.example.com/create' => Http::response(['code' => 200, 'url' => 'https://cashier.example.com/pay'], 200),
        ]);

        $channel = PayChannel::query()->create([
            'title' => 'Df通道',
            'driver' => 'dfpay',
            'code' => '195',
            'api_url' => 'https://gateway.example.com/create',
            'mch_id' => 'partner1',
            'app_key' => 'dfsecret',
            'min_fen' => 0,
            'max_fen' => 0,
            'sort' => 1,
            'status' => 1,
            'hint' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $member = Member::query()->create([
            'name' => 'Df人',
            'email' => 'pay-df-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 5,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $res = app(PayService::class)->create($member, 'ch:'.$channel->id, 30);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertSame('https://cashier.example.com/pay', $res['data']['pay_url'] ?? '');

        $order = MemberOrder::query()->where('order_no', $res['data']['order_no'])->first();
        $payload = [
            'status' => '2',
            'code' => '195',
            'orderno' => (string) $order->order_no,
            'amount' => '30.00',
        ];
        $payload['sign'] = app(DfPayDriver::class)->sign($payload, 'dfsecret');

        $this->get('/pay/notify/dfpay?'.http_build_query($payload))
            ->assertOk()
            ->assertSee('success');

        $member->refresh();
        // 原有 5 + 订单 3000 + 充值任务 5
        $this->assertSame(3010, (int) $member->points);
    }

    public function test_checkout_lists_gateway_channels(): void
    {
        PayChannel::query()->create([
            'title' => '前台可见',
            'driver' => 'epay',
            'code' => 'wxpay',
            'api_url' => 'https://pay.example.com/',
            'mch_id' => '9',
            'app_key' => 'k',
            'min_fen' => 0,
            'max_fen' => 0,
            'sort' => 1,
            'status' => 1,
            'hint' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $member = Member::query()->create([
            'name' => '看通道',
            'email' => 'pay-view-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->actingAs($member, 'member')
            ->get('/member/pay')
            ->assertOk()
            ->assertSee('前台可见')
            ->assertSee('ch:')
            ->assertSee('用订单号查询');
    }

    public function test_member_can_lookup_own_order_no(): void
    {
        $member = Member::query()->create([
            'name' => '查单人',
            'email' => 'pay-lookup-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $other = Member::query()->create([
            'name' => '别人',
            'email' => 'pay-other-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $order = MemberOrder::query()->create([
            'order_no' => 'P20260918120000LOOKUP',
            'member_id' => $member->id,
            'amount' => 1000,
            'points' => 1000,
            'channel' => 'manual',
            'status' => 0,
            'trade_no' => '',
            'remark' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        MemberOrder::query()->create([
            'order_no' => 'P20260918120000OTHER',
            'member_id' => $other->id,
            'amount' => 500,
            'points' => 500,
            'channel' => 'manual',
            'status' => 0,
            'trade_no' => '',
            'remark' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->actingAs($member, 'member')
            ->get('/member/pay/lookup')
            ->assertOk()
            ->assertSee('查询订单');

        $this->actingAs($member, 'member')
            ->post('/member/pay/lookup', ['order_no' => 'p20260918120000lookup'])
            ->assertRedirect('/member/pay/'.$order->id);

        $this->actingAs($member, 'member')
            ->from('/member/pay/lookup')
            ->post('/member/pay/lookup', ['order_no' => 'P20260918120000OTHER'])
            ->assertOk()
            ->assertSee('没有找到这个订单号');
    }

    public function test_admin_pay_stats_desk(): void
    {
        $member = Member::query()->create([
            'name' => '统计会员',
            'email' => 'pay-stats-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $now = time();
        MemberOrder::query()->create([
            'order_no' => 'PSTATS'.uniqid(),
            'member_id' => $member->id,
            'amount' => 2500,
            'points' => 2500,
            'channel' => 'epay',
            'status' => 1,
            'trade_no' => 'T1',
            'remark' => '',
            'paid_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/pay_channels?desk=stats')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('支付统计', $html);
        $this->assertStringContainsString('今日实收', $html);
        $this->assertStringContainsString('25.00', $html);
        $this->assertStringContainsString('易支付', $html);
    }
}

class PayPluginDisabledTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->restorePayPlugin();
        parent::tearDown();
    }

    public function test_disabled_plugin_hides_channels_admin(): void
    {
        $this->actingAsAdmin();
        app(PluginManager::class)->setEnabled('pay', false);
        $this->refreshApplication();
        $this->actingAsAdmin();
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/pay_channels')
            ->assertNotFound();
    }

    private function restorePayPlugin(): void
    {
        $file = base_path('plugins/Pay/plugin.json');
        if (! is_file($file)) {
            return;
        }
        $raw = json_decode((string) file_get_contents($file), true);
        if (! is_array($raw)) {
            return;
        }
        $raw['enabled'] = true;
        $json = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            file_put_contents($file, $json.PHP_EOL);
        }
    }
}

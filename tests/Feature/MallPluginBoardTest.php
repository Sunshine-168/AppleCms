<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberGroup;
use App\Models\Video\VideoCard;
use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Plugins\Mall\Models\MallGood;
use Plugins\Mall\Models\MallOrder;
use Plugins\Mall\Services\MallService;
use Tests\TestCase;

class MallPluginBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_workbench_is_nested_not_two_siblings(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mall_goods')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('mall-board', $html);
        $this->assertStringContainsString('用会员积分兑换', $html);
        $this->assertStringContainsString('不接微信支付宝', $html);
        $this->assertStringContainsString('会员时长', $html);
        $this->assertStringContainsString('商品', $html);
        $this->assertStringContainsString('订单', $html);
        $this->assertStringContainsString('待发货', $html);
        $this->assertStringNotContainsString('nav-fold-nested', $html);
        $this->assertStringContainsString('/admin/video/mall_goods?desk=orders', $html);
        $this->assertStringContainsString('/admin/video/mall_goods?desk=ship', $html);
        $this->assertStringContainsString("/admin/video/' + module + '/list'", $html);
        $this->assertStringContainsString("/admin/video/' + module + '/save'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('href="/admin/video/mall_orders"', $html);

        $vod = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/members')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('nav-fold-nested', $vod);
        $this->assertStringContainsString('/admin/video/mall_goods', $vod);
        $this->assertStringNotContainsString('/admin/video/mall_goods?desk=ship', $vod);
        $this->assertStringNotContainsString('href="/admin/video/mall_orders"', $vod);
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $vod
        );
        $this->assertMatchesRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*更多\s*<\/summary>/u',
            $vod
        );
        $morePos = strpos($vod, '<summary>更多</summary>');
        $this->assertNotFalse($morePos);
        $this->assertStringNotContainsString('/admin/video/mall_goods', substr($vod, $morePos));
    }

    public function test_orders_module_redirects_to_the_board(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mall_orders')
            ->assertRedirect('/admin/video/mall_goods?desk=orders');
    }

    public function test_save_and_list_decorate_mall_modules(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('mall_goods', ['name' => '  '], null);
        $this->assertSame(1, $empty['code']);

        $group = MemberGroup::query()->create([
            'name' => 'VIP组',
            'points_min' => 10,
            'sort' => 1,
            'status' => 1,
        ]);
        $ok = $svc->save('mall_goods', [
            'name' => ' 月卡 ',
            'type' => 'vip',
            'points' => 20,
            'stock' => 5,
            'status' => 1,
            'is_hot' => 1,
            'group_id' => $group->id,
            'vip_days' => 30,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $row = MallGood::query()->orderByDesc('id')->first();
        $this->assertNotNull($row);
        $this->assertSame('月卡', (string) $row->name);
        $this->assertSame('vip', (string) $row->type);
        $this->assertSame(1, (int) ($row->is_hot ?? 0));
        $ext = json_decode((string) $row->ext, true);
        $this->assertSame((int) $group->id, (int) ($ext['group_id'] ?? 0));
        $this->assertSame(30, (int) ($ext['days'] ?? 0));

        $badType = $svc->save('mall_goods', ['name' => '额度', 'type' => 'download_quota'], null);
        $this->assertSame(1, $badType['code']);

        $list = $svc->lists('mall_goods', ['limit' => 20, 'q' => '月卡', 'type' => 'vip']);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $this->assertSame('会员时长', $rows[0]['type_label'] ?? '');
        $this->assertSame((int) $group->id, (int) ($rows[0]['group_id'] ?? 0));
        $this->assertSame(30, (int) ($rows[0]['vip_days'] ?? 0));
    }

    public function test_buy_vip_sets_group_and_expiry_days(): void
    {
        $this->assertTrue(Schema::hasColumn('members', 'group_expire_at'));

        $group = MemberGroup::query()->create([
            'name' => '黄金',
            'points_min' => 0,
            'sort' => 2,
            'status' => 1,
        ]);
        $member = Member::query()->create([
            'name' => '换组人',
            'email' => 'mall-vip-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 40,
            'group_id' => 0,
            'group_expire_at' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $goods = MallGood::query()->create([
            'name' => '黄金月卡',
            'type' => 'vip',
            'ext' => json_encode(['group_id' => $group->id, 'days' => 30, 'vip_days' => 30], JSON_UNESCAPED_UNICODE),
            'cover' => '',
            'points' => 15,
            'stock' => 2,
            'sales' => 0,
            'is_hot' => 1,
            'hint' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $before = time();
        $res = app(MallService::class)->buy((int) $goods->id, $member);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertStringContainsString('会员已开通至', (string) ($res['msg'] ?? ''));

        $member->refresh();
        $goods->refresh();
        $this->assertSame((int) $group->id, (int) $member->group_id);
        $this->assertSame((int) $group->id, $member->effectiveGroupId());
        $this->assertSame(25, (int) $member->points);
        $this->assertSame(1, (int) $goods->stock);
        $this->assertSame(1, (int) $goods->sales);
        $this->assertGreaterThanOrEqual($before + 29 * 86400, (int) $member->group_expire_at);

        $order = MallOrder::query()->where('member_id', $member->id)->first();
        $this->assertNotNull($order);
        $this->assertSame(2, (int) $order->status);
        $delivery = json_decode((string) $order->delivery, true);
        $this->assertSame('vip', $delivery['type'] ?? '');
        $this->assertSame(30, (int) ($delivery['days'] ?? 0));
        $this->assertSame((int) $member->group_expire_at, (int) ($delivery['expire_at'] ?? 0));

        // Stack another 7 days on same group.
        $goods2 = MallGood::query()->create([
            'name' => '黄金周卡',
            'type' => 'vip',
            'ext' => json_encode(['group_id' => $group->id, 'days' => 7], JSON_UNESCAPED_UNICODE),
            'cover' => '',
            'points' => 5,
            'stock' => 1,
            'sales' => 0,
            'is_hot' => 0,
            'hint' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $prevExpire = (int) $member->group_expire_at;
        $res2 = app(MallService::class)->buy((int) $goods2->id, $member->fresh());
        $this->assertSame(0, $res2['code'], $res2['msg'] ?? '');
        $member->refresh();
        $this->assertSame($prevExpire + 7 * 86400, (int) $member->group_expire_at);
    }

    public function test_buy_card_auto_credit_adds_points(): void
    {
        $member = Member::query()->create([
            'name' => '换卡人',
            'email' => 'mall-card-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 50,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $goods = MallGood::query()->create([
            'name' => '积分礼包',
            'type' => 'card',
            'ext' => json_encode([
                'card_mode' => 'generate',
                'mode' => 'generate',
                'card_points' => 80,
                'auto_credit' => 1,
            ], JSON_UNESCAPED_UNICODE),
            'cover' => '',
            'points' => 12,
            'stock' => 1,
            'sales' => 0,
            'is_hot' => 0,
            'hint' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $res = app(MallService::class)->buy((int) $goods->id, $member);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertStringContainsString('已到账', (string) ($res['msg'] ?? ''));

        $member->refresh();
        // 50 - 12 + 80
        $this->assertSame(118, (int) $member->points);

        $order = MallOrder::query()->where('member_id', $member->id)->first();
        $this->assertNotNull($order);
        $delivery = json_decode((string) $order->delivery, true);
        $code = strtoupper((string) ($delivery['code'] ?? ''));
        $this->assertSame(1, (int) ($delivery['auto_credit'] ?? 0));
        $card = VideoCard::query()->where('code', $code)->first();
        $this->assertNotNull($card);
        $this->assertSame((int) $member->id, (int) $card->used_by);
        $this->assertSame(0, (int) $card->status);
    }

    public function test_buy_card_without_auto_credit_leaves_code(): void
    {
        $member = Member::query()->create([
            'name' => '转赠卡',
            'email' => 'mall-card2-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 50,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $goods = MallGood::query()->create([
            'name' => '积分卡',
            'type' => 'card',
            'ext' => json_encode(['card_mode' => 'generate', 'mode' => 'generate', 'card_points' => 80, 'auto_credit' => 0], JSON_UNESCAPED_UNICODE),
            'cover' => '',
            'points' => 12,
            'stock' => 1,
            'sales' => 0,
            'is_hot' => 0,
            'hint' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $res = app(MallService::class)->buy((int) $goods->id, $member);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $member->refresh();
        $this->assertSame(38, (int) $member->points);
        $order = MallOrder::query()->where('member_id', $member->id)->first();
        $delivery = json_decode((string) $order->delivery, true);
        $code = strtoupper((string) ($delivery['code'] ?? ''));
        $card = VideoCard::query()->where('code', $code)->first();
        $this->assertSame(0, (int) $card->used_by);
        $this->assertSame(1, (int) $card->status);
    }

    public function test_buy_goods_requires_contact_and_waits_for_ship(): void
    {
        $member = Member::query()->create([
            'name' => '换实物',
            'email' => 'mall-goods-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 30,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $goods = MallGood::query()->create([
            'name' => '徽章',
            'type' => 'goods',
            'ext' => '',
            'cover' => '',
            'points' => 10,
            'stock' => 1,
            'sales' => 0,
            'is_hot' => 0,
            'hint' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $fail = app(MallService::class)->buy((int) $goods->id, $member, []);
        $this->assertSame(1, $fail['code']);
        $this->assertStringContainsString('联系方式', (string) ($fail['msg'] ?? ''));

        $res = app(MallService::class)->buy((int) $goods->id, $member, [
            'contact' => '13800000000',
            'address' => '前台自取',
        ]);
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertSame('兑换成功，等待发货', (string) ($res['msg'] ?? ''));
        $order = MallOrder::query()->where('member_id', $member->id)->first();
        $this->assertNotNull($order);
        $this->assertSame(1, (int) $order->status);
        $this->assertSame('13800000000', (string) $order->contact);
        $this->assertSame('前台自取', (string) $order->address);

        $shipped = app(SiteModuleService::class)->save('mall_orders', ['status' => 2, 'remark' => '已寄出'], (int) $order->id);
        $this->assertSame(0, $shipped['code'], $shipped['msg'] ?? '');
        $order->refresh();
        $this->assertSame(2, (int) $order->status);
        $this->assertSame('已寄出', (string) $order->remark);

        $this->actingAs($member, 'member')
            ->get('/mall/orders')
            ->assertOk()
            ->assertSee('徽章')
            ->assertSee('13800000000');
    }

    public function test_assign_card_fails_when_pool_empty(): void
    {
        $member = Member::query()->create([
            'name' => '空池',
            'email' => 'mall-assign-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 40,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $goods = MallGood::query()->create([
            'name' => '领卡',
            'type' => 'card',
            'ext' => json_encode(['mode' => 'assign', 'card_mode' => 'assign'], JSON_UNESCAPED_UNICODE),
            'cover' => '',
            'points' => 5,
            'stock' => 1,
            'sales' => 0,
            'is_hot' => 0,
            'hint' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $res = app(MallService::class)->buy((int) $goods->id, $member);
        $this->assertSame(1, $res['code']);
        $this->assertStringContainsString('没有可领取的卡密', (string) ($res['msg'] ?? ''));
        $member->refresh();
        $this->assertSame(40, (int) $member->points);
        $this->assertSame(0, MallOrder::query()->where('member_id', $member->id)->count());
    }

    public function test_front_mall_index_tabs_and_orders_route(): void
    {
        $this->get('/mall')->assertOk()->assertSee('积分商城')->assertSee('会员时长');
        $this->get('/mall/orders')->assertRedirect();
    }
}

class MallPluginDisabledTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->restoreMallPlugin();
        parent::tearDown();
    }

    public function test_disabled_plugin_hides_admin_and_front(): void
    {
        $this->actingAsAdmin();
        app(PluginManager::class)->setEnabled('mall', false);
        $this->refreshApplication();
        $this->actingAsAdmin();
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mall_goods')
            ->assertNotFound();
        $this->get('/mall')->assertNotFound();
    }

    private function restoreMallPlugin(): void
    {
        $file = base_path('plugins/Mall/plugin.json');
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

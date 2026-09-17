<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberOrder;
use App\Models\Video\VideoCjRule;
use App\Models\Video\VideoDownloader;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoRole;
use App\Models\Video\VideoServer;
use App\Models\Video\VideoSourceModel;
use App\Services\Video\MemberOrderService;
use App\Services\Video\SiteFrontService;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Plugins\CjRule\CjRuleServiceProvider;
use Plugins\Connect\ConnectServiceProvider;
use Plugins\Pay\Services\PayService;
use Plugins\Sms\SmsServiceProvider;
use Plugins\Sms\Services\SmsService;
use Plugins\Weixin\WeixinServiceProvider;
use Plugins\Weixin\Services\WeixinService;
use Tests\TestCase;

class WiredPluginsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
        $this->app->register(SmsServiceProvider::class);
        $this->app->register(WeixinServiceProvider::class);
        $this->app->register(ConnectServiceProvider::class);
        $this->app->register(CjRuleServiceProvider::class);
    }

    public function test_pay_create_fails_without_keys(): void
    {
        $member = $this->member();
        $html = $this->actingAs($member, 'member')->get('/member/pay')->assertOk()->getContent();
        $this->assertStringContainsString('未配置支付参数', $html);

        $this->actingAs($member, 'member')
            ->from('/member/pay')
            ->post('/member/pay', ['amount_yuan' => 10, 'channel' => 'wechat'])
            ->assertRedirect('/member/pay');
        $this->assertSame(0, MemberOrder::query()->count());
    }

    public function test_wechat_notify_settles_once(): void
    {
        $settings = app(VideoSettingService::class);
        $settings->saveOptions([
            'pay_wechat_appid' => 'wx_test',
            'pay_wechat_mchid' => '123456',
            'pay_wechat_key' => 'testkey1234567890testkey12345678',
        ]);
        $member = $this->member(['points' => 0]);
        $order = MemberOrder::query()->create([
            'member_id' => $member->id,
            'order_no' => 'PTEST001',
            'amount' => 1000,
            'points' => 1000,
            'status' => 0,
            'channel' => 'wechat',
            'trade_no' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $pay = app(PayService::class);
        $payload = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
            'out_trade_no' => 'PTEST001',
            'transaction_id' => 'wx-trade-1',
            'total_fee' => '1000',
        ];
        $payload['sign'] = $pay->wechatSign($payload, 'testkey1234567890testkey12345678');
        $xml = '<xml>';
        foreach ($payload as $k => $v) {
            $xml .= '<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
        }
        $xml .= '</xml>';

        $this->call('POST', '/pay/notify/wechat', [], [], [], [], $xml)
            ->assertOk()
            ->assertSee('SUCCESS', false);

        $member->refresh();
        $order->refresh();
        $this->assertSame(1, (int) $order->status);
        $this->assertSame(1000, (int) $member->points);

        $this->call('POST', '/pay/notify/wechat', [], [], [], [], $xml)->assertOk();
        $member->refresh();
        $this->assertSame(1000, (int) $member->points);
    }

    public function test_order_settle_is_idempotent(): void
    {
        $member = $this->member(['points' => 5]);
        $order = MemberOrder::query()->create([
            'member_id' => $member->id,
            'order_no' => 'PTEST002',
            'amount' => 100,
            'points' => 100,
            'status' => 0,
            'channel' => 'manual',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $svc = app(MemberOrderService::class);
        $this->assertSame(0, $svc->settle((int) $order->id, 't1', 'wechat')['code']);
        $this->assertSame(0, $svc->settle((int) $order->id, 't1', 'wechat')['code']);
        $member->refresh();
        $this->assertSame(105, (int) $member->points);
    }

    public function test_sms_send_fails_without_keys_and_verifies_code(): void
    {
        $sms = app(SmsService::class);
        $fail = $sms->send('13800138000');
        $this->assertSame(1, $fail['code']);
        $this->assertStringContainsString('未配置短信参数', $fail['msg']);

        app(VideoSettingService::class)->saveOptions([
            'sms_provider' => 'aliyun',
            'sms_key' => 'key',
            'sms_secret' => 'secret',
            'sms_sign' => '签名',
            'sms_tpl_code' => 'SMS_1',
        ]);
        Http::fake(['dysmsapi.aliyuncs.com/*' => Http::response(['Code' => 'OK'], 200)]);
        $ok = $sms->send('13800138000', 'register');
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $bad = $sms->verify('13800138000', '000000');
        $this->assertSame(1, $bad['code']);
        $row = \Plugins\Sms\Models\SmsCode::query()->orderByDesc('id')->first();
        $this->assertNotNull($row);
        $good = $sms->verify('13800138000', (string) $row->code);
        $this->assertSame(0, $good['code']);
    }

    public function test_weixin_rejects_empty_token_and_echoes_signature(): void
    {
        $this->get('/weixin?signature=x&timestamp=1&nonce=2&echostr=hello')
            ->assertStatus(403)
            ->assertSee('未配置 Token');

        app(VideoSettingService::class)->saveOptions(['weixin_token' => 'tok']);
        $wx = app(WeixinService::class);
        $ts = '111';
        $nonce = '222';
        $tmp = ['tok', $ts, $nonce];
        sort($tmp, SORT_STRING);
        $sign = sha1(implode('', $tmp));
        $this->get('/weixin?signature='.$sign.'&timestamp='.$ts.'&nonce='.$nonce.'&echostr=ping')
            ->assertOk()
            ->assertSee('ping');

        $xml = '<xml><ToUserName><![CDATA[gh]]></ToUserName><FromUserName><![CDATA[ou]]></FromUserName><CreateTime>1</CreateTime><MsgType><![CDATA[event]]></MsgType><Event><![CDATA[subscribe]]></Event></xml>';
        $this->call('POST', '/weixin?signature='.$sign.'&timestamp='.$ts.'&nonce='.$nonce, [], [], [], [], $xml)
            ->assertOk()
            ->assertSee('欢迎关注', false);
    }

    public function test_connect_without_keys_does_not_pretend_login(): void
    {
        $this->get('/connect/qq')->assertRedirect('/member/login');
        $this->get('/member/login')->assertOk();
        $this->assertStringNotContainsString('QQ 登录', $this->get('/member/login')->getContent());
    }

    public function test_ai_generate_fails_without_key_and_writes_with_fake_http(): void
    {
        $this->actingAsAdmin();
        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/ai/generate', ['title' => '测试片'])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($json['code'] ?? 0));
        $this->assertStringContainsString('未配置 API Key', (string) ($json['msg'] ?? ''));

        app(VideoSettingService::class)->saveOptions([
            'ai_provider' => 'openai',
            'ai_key' => 'sk-test',
            'ai_model' => 'gpt-4.1-mini',
        ]);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => '这是一段测试简介。']]],
            ], 200),
        ]);
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/ai/generate', ['title' => '测试片'])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $this->assertSame('这是一段测试简介。', $ok['data']['text'] ?? '');
    }

    public function test_cj_rule_try_and_import(): void
    {
        $this->actingAsAdmin();
        $rule = VideoCjRule::query()->create([
            'name' => '试跑规则',
            'url' => 'https://example.com/list.html',
            'list_rule' => '<li>(.*?)</li>',
            'title_rule' => '<a[^>]*>(.*?)</a>',
            'url_rule' => 'href="(.*?)"',
            'status' => 1,
            'note' => '',
        ]);
        Http::fake([
            'example.com/*' => Http::response('<ul><li><a href="https://cdn.example/a.m3u8">片名甲</a></li><li><a href="https://cdn.example/b.m3u8">片名乙</a></li></ul>', 200),
        ]);
        $try = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/try', ['id' => $rule->id])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($try['code'] ?? 1), (string) ($try['msg'] ?? ''));
        $this->assertCount(2, $try['data']['items'] ?? []);

        $run = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/run', ['id' => $rule->id])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($run['code'] ?? 1), (string) ($run['msg'] ?? ''));
        $this->assertSame(2, VideoModel::query()->count());
        $this->assertSame(1, (int) VideoModel::query()->where('title', '片名甲')->value('status'));
    }

    public function test_roles_show_on_detail_and_server_prefix_play_url(): void
    {
        $video = VideoModel::query()->create([
            'title' => '角色测试片',
            'status' => 1,
            'description' => '简介',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        VideoRole::query()->create([
            'name' => '女主小敏',
            'video_id' => $video->id,
            'actor_id' => 0,
            'status' => 1,
            'sort' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $html = $this->get('/vod/'.$video->id)->assertOk()->getContent();
        $this->assertStringContainsString('女主小敏', $html);

        $server = VideoServer::query()->create([
            'name' => '线路A',
            'url' => 'https://play.example.com/hls',
            'status' => 1,
            'sort' => 1,
        ]);
        $source = VideoSourceModel::query()->create([
            'video_id' => $video->id,
            'name' => '高清',
            'type' => 'm3u8',
            'status' => 1,
            'server_id' => $server->id,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $ep = VideoEpisodeModel::query()->create([
            'video_id' => $video->id,
            'source_id' => $source->id,
            'episode_name' => '1',
            'episode_num' => 1,
            'url' => 'ep1.m3u8',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $url = app(SiteFrontService::class)->resolvePlayUrl($source, $ep);
        $this->assertSame('https://play.example.com/hls/ep1.m3u8', $url);

        VideoDownloader::query()->create([
            'code' => 'dl',
            'name' => '模板',
            'parse' => 'https://dl.example/get?u={url}',
            'status' => 1,
            'sort' => 1,
        ]);
        $source->downer = 'dl';
        $source->save();
        $down = app(SiteFrontService::class)->resolveDownUrl($source, $ep, (int) $video->id);
        $this->assertStringContainsString('dl.example', $down);
        $this->assertStringContainsString(rawurlencode('ep1.m3u8'), $down);
    }

    /** @param array<string, mixed> $extra */
    private function member(array $extra = []): Member
    {
        return Member::query()->create(array_merge([
            'name' => '付款人',
            'email' => 'pay-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ], $extra));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Video\VideoAccessLog;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesslogIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_accesslog_index_is_a_front_hit_board_not_a_generic_crud(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/accesslogs')
            ->assertRedirect('/admin/system/runtime?desk=access&view=logs');

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/accesslogs?ip=198.51.100.2')
            ->assertRedirect('/admin/system/runtime?desk=access&view=logs&ip=198.51.100.2');

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime?desk=access&view=logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('accesslog-index', $html);
        $this->assertStringContainsString('异常访问', $html);
        $this->assertStringContainsString('还没有前台访问', $html);
        $this->assertStringContainsString('搜 IP、地址或标识', $html);
        $this->assertStringContainsString('不能封 IP', $html);
        $this->assertStringContainsString('点 IP', $html);
        $this->assertStringContainsString('插件静态不记', $html);
        $this->assertStringContainsString('/admin/video/botlogs', $html);
        $this->assertStringContainsString('/admin/stats/logs', $html);
        $this->assertStringContainsString('/admin/stats/spiders', $html);
        $this->assertStringContainsString('/admin/video/config/ip', $html);
        $this->assertStringContainsString('accesslog-batch', $html);
        $this->assertStringContainsString('谁来的', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="ip"', $html);
        $this->assertStringNotContainsString("title: 'is_bot'", $html);
        $this->assertStringNotContainsString("title: 'ua'", $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('关联账号', $html);
        $this->assertStringNotContainsString('设备指纹', $html);
        $this->assertStringNotContainsString('一键封', $html);
    }

    public function test_save_is_rejected_and_list_splits_people_from_bots(): void
    {
        $svc = app(SiteModuleService::class);

        $deny = $svc->save('accesslogs', [
            'ip' => '1.1.1.1',
            'url' => 'https://example.com/',
            'ua' => 'Mozilla/5.0',
            'is_bot' => 0,
        ]);
        $this->assertSame(1, $deny['code']);
        $this->assertStringContainsString('不能手添', $deny['msg']);

        VideoAccessLog::query()->create([
            'ip' => '203.0.113.8',
            'url' => 'https://video.example.com/vod/1',
            'ua' => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
            'is_bot' => 1,
            'created_at' => time(),
        ]);
        VideoAccessLog::query()->create([
            'ip' => '198.51.100.2',
            'url' => 'https://video.example.com/',
            'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            'is_bot' => 0,
            'created_at' => time(),
        ]);

        $list = $svc->lists('accesslogs', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $this->assertSame(2, (int) ($list['data']['total'] ?? 0));
        $people = $svc->lists('accesslogs', ['limit' => 20, 'visitor' => 'people']);
        $this->assertSame(1, (int) ($people['data']['total'] ?? 0));
        $this->assertSame('访客', $people['data']['data'][0]['visitor_label'] ?? '');
        $this->assertSame('198.51.100.2', $people['data']['data'][0]['ip'] ?? '');
        $this->assertNotSame('', $people['data']['data'][0]['created_at_text'] ?? '');
        $bots = $svc->lists('accesslogs', ['limit' => 20, 'visitor' => 'bot']);
        $this->assertSame(1, (int) ($bots['data']['total'] ?? 0));
        $this->assertSame('百度', $bots['data']['data'][0]['visitor_label'] ?? '');

        $sameIp = $svc->lists('accesslogs', ['limit' => 20, 'ip' => '198.51.100.2']);
        $this->assertSame(1, (int) ($sameIp['data']['total'] ?? 0));

        $queues = $svc->accesslogQueues();
        $this->assertSame(2, $queues['all']);
        $this->assertSame(1, $queues['people']);
        $this->assertSame(1, $queues['bot']);
    }

    public function test_plugin_assets_are_not_written_to_access_log(): void
    {
        $this->get('/plugin-assets/code-editor/blade.js')->assertOk();
        $this->get('/plugin-assets/code-editor/vendor/search.js')->assertOk();
        $this->assertSame(0, VideoAccessLog::query()->count());

        $this->get('/topics')->assertOk();
        $this->assertGreaterThan(0, VideoAccessLog::query()->where('url', 'like', '%/topics%')->count());
    }
}

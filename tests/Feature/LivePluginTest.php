<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Live\Models\LiveChannel;
use Plugins\Live\Services\LiveService;
use Tests\TestCase;

class LivePluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_plugin_is_enabled_and_admin_board_is_own_workspace(): void
    {
        $this->assertTrue(app(PluginManager::class)->isEnabled('live'));
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/lives')->assertOk()->getContent();
        $this->assertStringContainsString('live-board', $html);
        $this->assertStringContainsString('class="is-on">直播</a>', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_save_channel_parse_lines_and_front_pages_work(): void
    {
        $modules = app(SiteModuleService::class);
        $categoryResult = $modules->save('lives', [
            'desk' => 'categories', 'name' => '央视频道', 'status' => 1,
        ], null);
        $this->assertSame(0, $categoryResult['code']);
        $channelResult = $modules->save('lives', [
            'desk' => 'channels',
            'cate_id' => $categoryResult['data']['id'],
            'title' => '测试频道',
            'urls' => 'HD$https://example.com/live.m3u8#SD$https://example.com/live.flv',
            'status' => 1,
        ], null);
        $this->assertSame(0, $channelResult['code']);
        $channel = LiveChannel::query()->findOrFail($channelResult['data']['id']);
        $lines = app(LiveService::class)->parseUrlList($channel->urls);
        $this->assertSame('HD', $lines[0]['name']);
        $this->assertSame('https://example.com/live.flv', $lines[1]['url']);
        $this->get('/live')->assertOk()->assertSee('测试频道');
        $this->get('/live/'.$channel->id)->assertOk()->assertSee('测试频道');
    }
}

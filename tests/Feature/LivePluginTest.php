<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Live\Models\LiveCategory;
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
        $this->assertStringContainsString(admin_t('ui.full_form'), $html);
        $this->assertStringContainsString('live-add-btn', $html);
        $this->assertStringContainsString('channel-form', $html);
        $this->assertStringContainsString('live-cover-upload', $html);
        $this->assertStringContainsString('?desk=stats', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_save_channel_recommend_search_and_form_pages(): void
    {
        $modules = app(SiteModuleService::class);
        $categoryResult = $modules->save('lives', [
            'desk' => 'categories', 'name' => '央视频道', 'status' => 1,
        ], null);
        $this->assertSame(0, $categoryResult['code']);
        $cateId = (int) $categoryResult['data']['id'];

        $channelResult = $modules->save('lives', [
            'desk' => 'channels',
            'cate_id' => $cateId,
            'title' => '测试频道',
            'urls' => 'HD$https://example.com/live.m3u8#SD$https://example.com/backup.m3u8',
            'recommend' => 5,
            'status' => 1,
        ], null);
        $this->assertSame(0, $channelResult['code']);
        $channel = LiveChannel::query()->findOrFail($channelResult['data']['id']);
        $this->assertSame(5, (int) $channel->recommend);
        $lines = app(LiveService::class)->parseUrlList($channel->urls);
        $this->assertSame('HD', $lines[0]['name']);
        $this->assertSame('https://example.com/backup.m3u8', $lines[1]['url']);

        $other = $modules->save('lives', [
            'desk' => 'channels',
            'cate_id' => 0,
            'title' => '地方台',
            'urls' => 'https://example.com/local.m3u8',
            'status' => 1,
        ], null);
        $this->assertSame(0, $other['code']);

        $this->get('/live')->assertOk()
            ->assertSee('测试频道')
            ->assertSee('推荐')
            ->assertSee('搜频道名');
        $this->get('/live?q='.urlencode('测试'))->assertOk()->assertSee('测试频道')->assertDontSee('地方台');
        $this->get('/live?cate='.$cateId)->assertOk()->assertSee('测试频道')->assertDontSee('地方台');
        $this->get('/live/'.$channel->id)->assertOk()
            ->assertSee('测试频道')
            ->assertSee('推荐 HLS');

        $form = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/live-channels/create')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('live-channel-form', $form);
        $this->assertStringContainsString('ch-cover-pick', $form);
        $this->assertStringContainsString('推荐等级', $form);

        $cateForm = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/live-categories/create')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('live-category-form', $cateForm);
        $this->assertStringContainsString('cate-pic-pick', $cateForm);

        $stats = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/lives?desk=stats')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(admin_t('live.top_hits'), $stats);
        $this->assertStringContainsString(admin_t('live.by_cate'), $stats);
    }

    public function test_batch_delete_category_clears_channel_cate(): void
    {
        $modules = app(SiteModuleService::class);
        $cate = $modules->save('lives', ['desk' => 'categories', 'name' => '待删分类', 'status' => 1], null);
        $this->assertSame(0, $cate['code']);
        $cateId = (int) $cate['data']['id'];
        $ch = $modules->save('lives', [
            'desk' => 'channels',
            'cate_id' => $cateId,
            'title' => '挂靠频道',
            'urls' => 'https://example.com/a.m3u8',
            'status' => 1,
        ], null);
        $this->assertSame(0, $ch['code']);

        $batch = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/lives/batch', [
                'ids' => (string) $cateId,
                'desk' => 'categories',
                'action' => 'delete',
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, $batch['code'] ?? 1);
        $this->assertNull(LiveCategory::query()->find($cateId));
        $this->assertSame(0, (int) LiveChannel::query()->findOrFail($ch['data']['id'])->cate_id);
    }
}

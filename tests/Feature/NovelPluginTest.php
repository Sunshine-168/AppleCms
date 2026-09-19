<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Novel\Models\Novel;
use Plugins\Novel\Models\NovelChapter;
use Tests\TestCase;

class NovelPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_plugin_is_enabled_and_admin_board_is_own_workspace(): void
    {
        $this->assertTrue(app(PluginManager::class)->isEnabled('novel'));
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/novels')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('novel-board', $html);
        $this->assertStringContainsString('class="is-on">小说</a>', $html);
        $this->assertStringContainsString('?desk=stats', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_save_novel_with_tags_and_front_pages_work(): void
    {
        $svc = app(SiteModuleService::class);
        $this->assertSame(0, $svc->save('novels', [
            'desk' => 'works',
            'title' => '测试小说',
            'author' => '作者甲',
            'tags' => '都市,穿越',
            'status' => 1,
            'yid' => 0,
        ], null)['code']);
        $novel = Novel::query()->where('title', '测试小说')->firstOrFail();
        $this->assertSame('都市,穿越', $novel->tags);
        $this->assertSame(0, $svc->save('novels', [
            'desk' => 'chapters',
            'novel_id' => $novel->id,
            'name' => '第一章',
            'content' => '正文',
            'sort' => 1,
        ], null)['code']);
        $chapter = NovelChapter::query()->where('novel_id', $novel->id)->firstOrFail();
        $this->get('/novel')->assertOk()->assertSee('测试小说')->assertSee('都市');
        $this->get('/novel?tag='.urlencode('穿越'))->assertOk()->assertSee('测试小说');
        $this->get('/novel/'.$novel->id)->assertOk()->assertSee('第一章')->assertSee('作者甲');
        $this->get('/novel/'.$novel->id.'/'.$chapter->id)->assertOk()->assertSee('正文');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/novels?desk=stats')
            ->assertOk()
            ->assertSee('小说统计');
    }

    public function test_full_work_form_page_has_upload_and_sections(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/novels/create')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('novel-work-form', $html);
        $this->assertStringContainsString('work-cover-pick', $html);
        $this->assertStringContainsString('标签与推荐', $html);
        $this->assertStringContainsString('创建作品', $html);
    }
}

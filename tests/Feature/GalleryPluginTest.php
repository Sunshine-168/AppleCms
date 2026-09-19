<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryPic;
use Tests\TestCase;

class GalleryPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_plugin_is_enabled_and_admin_board_is_own_workspace(): void
    {
        $this->assertTrue(app(PluginManager::class)->isEnabled('gallery'));
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/galleries')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('gallery-board', $html);
        $this->assertStringContainsString('class="is-on">图集</a>', $html);
        $this->assertStringContainsString('?desk=stats', $html);
        $this->assertStringContainsString('?desk=favors', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_save_gallery_with_author_tags_and_front_pages_work(): void
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Http::fake([
            'https://example.com/*' => Http::response($pixel, 200, ['Content-Type' => 'image/png']),
        ]);

        $svc = app(SiteModuleService::class);
        $this->assertSame(0, $svc->save('galleries', [
            'desk' => 'works',
            'title' => '测试图集',
            'author' => '某网红',
            'tags' => '写真,街拍',
            'remarks' => '说明',
            'status' => 1,
            'yid' => 0,
        ], null)['code']);
        $gallery = Gallery::query()->where('title', '测试图集')->firstOrFail();
        $this->assertSame('某网红', $gallery->author);
        $this->assertSame('写真,街拍', $gallery->tags);
        $result = $svc->save('galleries', [
            'desk' => 'pics',
            'gallery_id' => $gallery->id,
            'urls' => "https://example.com/a.jpg\nhttps://example.com/b.jpg\n/uploads/keep/local.png",
        ], null);
        $this->assertSame(0, $result['code']);
        $pics = GalleryPic::query()->where('gallery_id', $gallery->id)->orderBy('sort')->get();
        $this->assertCount(3, $pics);
        $this->assertStringStartsWith('/uploads/gallery/', (string) $pics[0]->url);
        $this->assertStringStartsWith('/uploads/gallery/', (string) $pics[1]->url);
        $this->assertSame('/uploads/keep/local.png', (string) $pics[2]->url);
        $this->assertFileExists(public_path(ltrim((string) $pics[0]->url, '/')));
        $this->assertFileExists(public_path(ltrim((string) $pics[1]->url, '/')));
        Http::assertSentCount(2);

        $this->get('/gallery')->assertOk()->assertSee('测试图集')->assertSee('某网红')->assertSee('写真');
        $this->get('/gallery?tag='.urlencode('街拍'))->assertOk()->assertSee('测试图集');
        $this->get('/gallery/'.$gallery->id)->assertOk()
            ->assertSee((string) $pics[0]->url, false)
            ->assertSee('某网红')
            ->assertSee('登录')
            ->assertSee('后可收藏');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/galleries?desk=stats')
            ->assertOk()
            ->assertSee('图集统计');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/galleries?desk=pics')
            ->assertOk()
            ->assertSee('远程 http(s) 会下载到本地', false);
    }

    public function test_full_work_form_page_has_upload_and_sections(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/galleries/create')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('gallery-work-form', $html);
        $this->assertStringContainsString('work-cover-pick', $html);
        $this->assertStringContainsString('作者 / 模特', $html);
        $this->assertStringContainsString('创建图集', $html);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\PublishPage\Models\PublishOption;
use Tests\TestCase;

class PublishPagePluginTest extends TestCase
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

    public function test_default_status_keeps_the_site_homepage(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('搜影片', $html);
        $this->assertStringNotContainsString('进入本站', $html);
        $status = PublishOption::query()->where('k', 'status')->value('v');
        $this->assertSame('0', (string) $status);
    }

    public function test_enabled_gate_then_cookie_enters_the_site(): void
    {
        $svc = app(SiteModuleService::class);
        $ok = $svc->save('publish_pages', [
            'desk' => 'config',
            'status' => 1,
            'title' => '线路发布',
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $gate = $this->get('/')->assertOk();
        $html = $gate->getContent();
        $this->assertStringContainsString('进入本站', $html);
        $this->assertStringNotContainsString('搜影片', $html);
        $this->assertStringContainsString('no-store', (string) $gate->headers->get('Cache-Control'));

        $this->get('/sitehome')->assertRedirect('/');
        $this->withUnencryptedCookie('lv_publish_entered', '1')
            ->get('/')
            ->assertOk()
            ->assertSee('搜影片')
            ->assertDontSee('进入本站');

        VideoModel::query()->create([
            'title' => '未拦截片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->get('/vod/1')->assertOk()->assertDontSee('进入本站');
        $this->get('/search')->assertOk()->assertDontSee('进入本站');
    }

    public function test_admin_board_is_a_site_item(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/publish_pages')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('publish-board', $html);
        $this->assertStringContainsString('/admin/video/publish_pages?desk=groups', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $html
        );
    }
}

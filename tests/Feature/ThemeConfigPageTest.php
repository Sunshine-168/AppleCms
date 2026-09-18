<?php

namespace Tests\Feature;

use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeConfigPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_theme_workbench_is_seven_tabs_not_a_marketplace_or_lottie_builder(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/theme')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('主题配置', $html);
        $this->assertStringContainsString('基本设置', $html);
        $this->assertStringContainsString('导航菜单', $html);
        $this->assertStringContainsString('SEO', $html);
        $this->assertStringContainsString('广告位', $html);
        $this->assertStringContainsString('/admin/video/ads', $html);
        $this->assertStringContainsString('没有深浅双套 Lottie', $html);
        $this->assertStringContainsString('theme-index', $html);
        $this->assertStringNotContainsString('模板市场', $html);
        $this->assertStringNotContainsString('Lottie 必填', $html);
        $this->assertStringNotContainsString('主题设计', $html);
        $this->assertStringNotContainsString('type.hom', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
    }

    public function test_theme_save_persists_favicon_head_code_and_hides_latest_nav(): void
    {
        $save = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/theme', [
                'theme_favicon' => '/favicon-test.ico',
                'theme_head_code' => '<!--theme-head-test-->',
                'theme_nav_latest' => '0',
            ])
            ->assertOk()
            ->json();

        $this->assertSame(0, (int) ($save['code'] ?? 1), (string) ($save['msg'] ?? ''));

        $settings = app(VideoSettingService::class);
        $this->assertSame('/favicon-test.ico', (string) $settings->get('theme_favicon'));
        $this->assertSame('<!--theme-head-test-->', (string) $settings->get('theme_head_code'));
        $this->assertSame('0', (string) $settings->get('theme_nav_latest'));

        $html = $this->get('/')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('rel="icon"', $html);
        $this->assertStringContainsString('/favicon-test.ico', $html);
        $this->assertStringContainsString('<!--theme-head-test-->', $html);
        $this->assertStringNotContainsString('>最新</a>', $html);
        $this->assertStringContainsString('>专题</a>', $html);
    }

    public function test_legacy_theme_config_url_redirects_to_workbench(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/theme')
            ->assertRedirect('/admin/video/theme');
    }

    public function test_settings_admin_nav_contains_theme_workbench(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/settings')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('/admin/video/theme', $html);
    }

    public function test_theme_asset_url_rejects_javascript(): void
    {
        $save = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/theme', [
                'theme_favicon' => 'javascript:alert(1)',
            ])
            ->assertOk()
            ->json();

        $this->assertSame(1, (int) ($save['code'] ?? 0));
        $this->assertSame('', (string) app(VideoSettingService::class)->get('theme_favicon'));
    }
}

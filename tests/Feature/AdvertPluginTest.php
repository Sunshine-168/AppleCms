<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Advert\Models\PluginAd;
use Tests\TestCase;

class AdvertPluginTest extends TestCase
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

    public function test_board_is_a_site_item_not_a_plugin_fold(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/adverts')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('advert-board', $html);
        $this->assertStringContainsString('desk-board', $html);
        $this->assertStringContainsString('id="advert-add-btn"', $html);
        $this->assertDoesNotMatchRegularExpression('/id="advert-add-btn"[^>]*\bhidden\b/', $html);
        $this->assertStringContainsString('/admin/video/adverts?desk=clicks', $html);
        $this->assertStringContainsString('/admin/video/adverts?desk=stats', $html);
        $clicks = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/adverts?desk=clicks')
            ->assertOk()
            ->getContent();
        $this->assertMatchesRegularExpression('/id="advert-add-btn"[^>]*\bhidden\b/', $clicks);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $html
        );
    }

    public function test_top_ad_renders_click_increments_and_expired_is_hidden(): void
    {
        $svc = app(SiteModuleService::class);
        $ok = $svc->save('adverts', [
            'desk' => 'ads',
            'name' => '顶栏图',
            'type' => 'image',
            'slot' => 'top',
            'title' => '顶栏图',
            'url' => 'https://ads.example/go',
            'image' => '/ad-top.png',
            'status' => 1,
            'start_at' => 0,
            'expire_at' => 0,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $ad = PluginAd::query()->where('name', '顶栏图')->first();
        $this->assertNotNull($ad);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('plugin-ad-fixed', $home);
        $this->assertStringContainsString('/ads/go/'.$ad->id, $home);

        $this->get('/ads/go/'.$ad->id)->assertRedirect('https://ads.example/go');
        $ad->refresh();
        $this->assertSame(1, (int) $ad->clicks);

        $expired = $svc->save('adverts', [
            'desk' => 'ads',
            'name' => '过期广告',
            'type' => 'text',
            'slot' => 'top',
            'title' => '过期广告文案',
            'url' => 'https://expired.example',
            'status' => 1,
            'start_at' => 0,
            'expire_at' => time() - 60,
        ], null);
        $this->assertSame(0, $expired['code'], $expired['msg'] ?? '');
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('过期广告文案', $html);

        $js = $svc->save('adverts', [
            'desk' => 'ads',
            'name' => '脚本广告',
            'url' => 'javascript:alert(1)',
            'slot' => 'content',
        ], null);
        $this->assertSame(1, $js['code']);

        $stats = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/adverts?desk=stats')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('advert-board', $stats);
        $this->assertStringContainsString('desk=stats', $stats);
        $list = $svc->lists('adverts', ['desk' => 'stats', 'period' => 'day']);
        $this->assertSame(0, $list['code']);
        $this->assertNotEmpty($list['data']['data'] ?? []);
    }
}

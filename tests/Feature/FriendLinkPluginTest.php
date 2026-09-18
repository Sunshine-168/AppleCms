<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Captcha;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\FriendLink\Models\FriendLink;
use Plugins\FriendLink\Models\FriendLinkCate;
use Plugins\FriendLink\Services\FriendLinkService;
use Tests\TestCase;

class FriendLinkPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_admin_board_hides_core_links_and_stays_in_site_nav(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/flinks')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('flink-board', $html);
        $this->assertStringContainsString('/admin/video/flinks?desk=pending', $html);
        $this->assertStringContainsString('/admin/video/flinks?desk=settings', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);

        $site = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/settings')
            ->assertOk()
            ->getContent();
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $site
        );
        $this->assertStringContainsString('/admin/video/flinks', $site);
        $this->assertStringNotContainsString('href="/admin/video/links"', $site);
    }

    public function test_save_apply_go_hit_stats_and_reject_javascript(): void
    {
        $svc = app(SiteModuleService::class);

        $cate = $svc->save('flinks', ['desk' => 'cates', 'name' => '导航', 'sort' => 2, 'status' => 1], null);
        $this->assertSame(0, $cate['code'], $cate['msg'] ?? '');
        $cateRow = FriendLinkCate::query()->where('name', '导航')->first();
        $this->assertNotNull($cateRow);

        $ok = $svc->save('flinks', [
            'desk' => 'links',
            'name' => '合作站',
            'url' => 'https://partner.example/home',
            'cate_id' => $cateRow->id,
            'type' => 'text',
            'status' => 1,
            'sort' => 3,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $link = FriendLink::query()->where('name', '合作站')->first();
        $this->assertNotNull($link);

        $js = $svc->save('flinks', ['desk' => 'links', 'name' => '脚本', 'url' => 'javascript:alert(1)'], null);
        $this->assertSame(1, $js['code']);

        $this->withSession(['captcha' => 8])
            ->post('/links/apply', [
                'name' => '申请站',
                'url' => 'https://apply.example',
                'captcha' => '8',
                'type' => 'text',
            ])
            ->assertOk()
            ->assertSee('修改令牌');
        $pending = FriendLink::query()->where('name', '申请站')->first();
        $this->assertNotNull($pending);
        $this->assertSame(0, (int) $pending->status);
        $this->assertNotSame('', (string) $pending->edit_token);

        $this->get('/links/go/'.$link->id)->assertRedirect('https://partner.example/home');
        $link->refresh();
        $this->assertSame(1, (int) $link->clicks);

        $mode = $svc->save('flinks', [
            'desk' => 'settings',
            'mode' => 'strong',
            'min_referer' => 1,
            'allow_apply' => 1,
            'allow_edit' => 1,
        ], null);
        $this->assertSame(0, $mode['code'], $mode['msg'] ?? '');
        $this->assertSame('strong', app(FriendLinkService::class)->options()['mode']);

        $this->postJson('/links/hit', ['referer' => 'https://apply.example/from'])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $pending->refresh();
        $this->assertSame(1, (int) $pending->referer_total);
        $this->assertSame(1, (int) $pending->status);

        $before = (int) $link->referer_total;
        $this->postJson('/links/hit', ['referer' => url('/')])
            ->assertOk();
        $link->refresh();
        $this->assertSame($before, (int) $link->referer_total);

        $hidden = $svc->save('flinks', [
            'desk' => 'links',
            'name' => '还在待审',
            'url' => 'https://waiting.example',
            'status' => 0,
        ], null);
        $this->assertSame(0, $hidden['code'], $hidden['msg'] ?? '');
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('合作站', $home);
        $this->assertStringContainsString('/links/go/'.$link->id, $home);
        $this->assertStringNotContainsString('还在待审', $home);

        $stats = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/flinks/list?desk=stats&period=day')
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($stats['code'] ?? 1));
        $this->assertArrayHasKey('data', $stats);
        $month = $svc->lists('flinks', ['desk' => 'stats', 'period' => 'month']);
        $this->assertSame(0, $month['code']);
        $year = $svc->lists('flinks', ['desk' => 'stats', 'period' => 'year']);
        $this->assertSame(0, $year['code']);

        $dash = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/flinks?desk=stats')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('友链统计', $dash);
        $this->assertStringContainsString('今日', $dash);
        $this->assertStringContainsString('近 14 日趋势', $dash);
        $this->assertStringContainsString('近 30 日来路 TOP', $dash);
        $this->assertStringContainsString('stat-grid', $dash);

        $this->assertTrue(app(PluginManager::class)->isEnabled('friendlink'));
    }

    public function test_captcha_route_is_an_image(): void
    {
        $res = $this->get('/links/captcha')->assertOk();
        $ctype = (string) $res->headers->get('Content-Type');
        $this->assertTrue(str_contains($ctype, 'image/png') || str_contains($ctype, 'image/svg+xml'), $ctype);
        $this->assertNotNull(session('captcha'));
        unset($res);
        Captcha::check((string) session('captcha'));
    }
}

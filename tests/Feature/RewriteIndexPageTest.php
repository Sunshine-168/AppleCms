<?php

namespace Tests\Feature;

use App\Models\Video\VideoOption;
use App\Services\Video\SiteOpsService;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RewriteIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_rewrite_index_is_a_server_cheat_sheet_not_a_filename_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/rewrite')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('伪静态', $html);
        $this->assertStringContainsString('当前写法', $html);
        $this->assertStringContainsString('本站路由', $html);
        $this->assertStringContainsString('本系统怎么走', $html);
        $this->assertStringContainsString('前台地址', $html);
        $this->assertStringContainsString('artisan serve', $html);
        $this->assertStringContainsString('public', $html);
        $this->assertStringContainsString('站点设置 → 更多', $html);
        $this->assertStringContainsString('复制', $html);
        $this->assertStringContainsString('/admin/video/settings?tab=more', $html);
        $this->assertStringContainsString('/vod/1', $html);
        $this->assertStringContainsString('/latest', $html);
        $this->assertStringContainsString('/actors', $html);
        $this->assertStringContainsString('/arts', $html);
        $this->assertStringContainsString('/gbook', $html);
        $this->assertStringContainsString('/member/login', $html);
        $this->assertStringContainsString('/sitemap.xml', $html);
        $this->assertStringContainsString('Nginx', $html);
        $this->assertStringContainsString('Apache', $html);
        $this->assertStringContainsString('rewrite-index', $html);
        $this->assertStringContainsString('上线打开是目录或 404', $html);
        $this->assertStringNotContainsString('laravel.html', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('location /index.php/ {', $html);
        if (preg_match('/<div class="card-header">(.*?)<\/div>/s', $html, $m)) {
            $this->assertStringNotContainsString('/admin/video/settings?tab=more', $m[1]);
            $this->assertStringNotContainsString('站点设置', $m[1]);
        } else {
            $this->fail('card-header not found');
        }
    }

    public function test_rewrite_rules_expose_examples_and_mode_label(): void
    {
        $board = app(SiteOpsService::class)->rewriteRules();
        $this->assertArrayHasKey('examples', $board);
        $this->assertArrayHasKey('route_groups', $board);
        $this->assertSame('本站路由', $board['mode_label'] ?? '');
        $this->assertSame('/vod/123', $board['mode_sample'] ?? '');
        $this->assertSame('/vod/1', $board['examples'][2]['path'] ?? '');
        $this->assertNotSame('', $board['nginx'] ?? '');
        $this->assertStringContainsString('try_files', $board['nginx']);
        $this->assertStringContainsString('127.0.0.1:9000', $board['nginx']);
        $this->assertStringNotContainsString('location /index.php/', $board['nginx']);
        $this->assertStringContainsString('RewriteRule ^ index.php', $board['apache'] ?? '');
    }

    public function test_mac_mode_shows_apple_style_paths(): void
    {
        VideoOption::query()->create([
            'k' => 'rewrite_mode',
            'v' => 'mac',
            'updated_at' => time(),
        ]);
        Cache::forget(VideoSettingService::CACHE_KEY);

        $board = app(SiteOpsService::class)->rewriteRules();
        $this->assertSame('苹果风格', $board['mode_label'] ?? '');
        $this->assertStringContainsString('/index.php/vod/detail/id/123', $board['mode_sample'] ?? '');

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/rewrite')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('苹果风格', $html);
        $this->assertStringContainsString('/index.php/vod/detail/id/1', $html);
        $this->assertStringContainsString('没有苹果别名', $html);
        $this->assertStringNotContainsString('laravel.html', $html);
    }
}

<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_cache_index_is_a_purpose_board_not_an_artisan_panel(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/tools/cache')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('缓存', $html);
        $this->assertStringContainsString('数据缓存', $html);
        $this->assertStringContainsString('改完还不生效', $html);
        $this->assertStringContainsString('全部清掉', $html);
        $this->assertStringContainsString('解开打包', $html);
        $this->assertStringContainsString('静态生成', $html);
        $this->assertStringContainsString('/admin/video/make', $html);
        $this->assertStringContainsString('/admin/video/templates', $html);
        $this->assertStringContainsString('data-kind="all"', $html);
        $this->assertStringNotContainsString('cache-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('默认 Store', $html);
        $this->assertStringNotContainsString('框架缓存命令', $html);
        $this->assertStringNotContainsString('optimize:clear', $html);
        $this->assertStringNotContainsString('cache:clear', $html);
        $this->assertStringNotContainsString('config:clear', $html);
        $this->assertStringNotContainsString('data-cmd=', $html);
    }

    public function test_clear_rejects_unknown_kind_and_data_kind_succeeds(): void
    {
        $svc = app(SysCacheService::class);

        $unknown = $svc->clear('nope');
        $this->assertSame(1, $unknown['code']);
        $this->assertStringContainsString('哪一项', $unknown['msg']);

        $data = $svc->clear('data');
        $this->assertSame(0, $data['code']);
        $this->assertArrayHasKey('driver_label', $data['data'] ?? []);
        $this->assertArrayHasKey('views', $data['data'] ?? []);
        $this->assertArrayHasKey('config_detail', $data['data'] ?? []);

        $views = $svc->clear('views');
        $this->assertSame(0, $views['code']);

        $board = $svc->pageBoard();
        $this->assertNotSame('', $board['driver_label'] ?? '');
        $this->assertIsBool($board['packed'] ?? null);
        $this->assertIsBool($board['html_cache_on'] ?? null);
    }
}

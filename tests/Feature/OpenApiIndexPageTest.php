<?php

namespace Tests\Feature;

use App\Models\Video\VideoOption;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OpenApiIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_open_api_is_a_provide_workbench_not_a_key_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/api')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('开放 API', $html);
        $this->assertStringContainsString('别人', $html);
        $this->assertStringContainsString('任何人可拉', $html);
        $this->assertStringContainsString('badge-warn', $html);
        $this->assertStringContainsString('/api.php/provide/vod', $html);
        $this->assertStringContainsString('/api/provide/vod', $html);
        $this->assertStringContainsString('试拉一把', $html);
        $this->assertStringContainsString('没有改动，不用保存', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('/admin/video/config/interface', $html);
        $this->assertStringContainsString('/admin/video/apidoc', $html);
        $this->assertStringContainsString('api-config-index', $html);
        $this->assertStringContainsString('site-save', $html);
        $this->assertStringContainsString('name="provide_key"', $html);
        $this->assertStringContainsString('ac=list', $html);
        $this->assertStringContainsString('X-Provide-Key', $html);
        $this->assertStringNotContainsString('placeholder="非空时 /api.php/provide/vod 需带 key"', $html);
        $this->assertStringNotContainsString('资源接口密钥', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_api_page_board_exposes_urls_and_key_state(): void
    {
        $board = app(VideoSettingService::class)->apiPage();
        $this->assertFalse($board['has_key']);
        $this->assertStringContainsString('/api.php/provide/vod', $board['provide_url'] ?? '');
        $this->assertStringContainsString('/api/provide/vod', $board['provide_alt'] ?? '');
        $this->assertIsInt($board['video_count'] ?? null);
    }

    public function test_saved_key_marks_the_feed_as_private(): void
    {
        VideoOption::query()->create([
            'k' => 'provide_key',
            'v' => 'test-provide-key',
            'updated_at' => time(),
        ]);
        Cache::forget(VideoSettingService::CACHE_KEY);

        $board = app(VideoSettingService::class)->apiPage();
        $this->assertTrue($board['has_key']);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/api')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('badge-ok', $html);
        $this->assertStringContainsString('test-provide-key', $html);
        $this->assertStringNotContainsString('badge-warn', $html);
        $this->assertStringNotContainsString('任何人可拉', $html);
    }
}

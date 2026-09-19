<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Scout\Services\ScoutSearchService;
use Tests\TestCase;

class ScoutPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        config(['scout.driver' => 'database']);
    }

    public function test_plugin_is_enabled_and_board_loads(): void
    {
        $this->assertTrue(app(PluginManager::class)->isEnabled('scout'));
        $this->assertTrue(class_exists(\Laravel\Scout\EngineManager::class));

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/scout')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('scout-board', $html);
        $this->assertStringContainsString('Laravel Scout', $html);
        $this->assertStringContainsString('重建索引', $html);
        $this->assertStringContainsString('/admin/video/config/scout', $html);
    }

    public function test_search_falls_back_when_disabled_and_hits_when_enabled(): void
    {
        $settings = app(VideoSettingService::class);
        $settings->save(['scout_search_enabled' => '0', 'scout_driver' => 'database', 'tab' => 'scout']);

        VideoModel::query()->create([
            'title' => '星际穿越测试片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $svc = app(ScoutSearchService::class);
        $this->assertFalse($svc->searchEnabled());
        $this->assertNull($svc->searchVideoIds('星际穿越'));

        $settings->save(['scout_search_enabled' => '1', 'scout_driver' => 'database', 'tab' => 'scout']);
        $this->assertTrue($svc->searchEnabled());

        $sync = $svc->syncAll();
        $this->assertTrue($sync['ok'], $sync['msg']);

        $ids = $svc->searchVideoIds('星际穿越');
        $this->assertIsArray($ids);
        $this->assertNotEmpty($ids);
    }

    public function test_sync_endpoint_returns_json(): void
    {
        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/scout/sync')
            ->assertOk()
            ->json();

        $this->assertSame(0, (int) ($json['code'] ?? 1), (string) ($json['msg'] ?? ''));
    }
}

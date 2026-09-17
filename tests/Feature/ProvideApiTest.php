<?php

namespace Tests\Feature;

use App\Models\Video\VideoOption;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProvideApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_laravel_provide_route_returns_json_list(): void
    {
        $this->get('/api/provide/vod?ac=list')
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonStructure(['page', 'pagecount', 'limit', 'total', 'list', 'class']);
    }

    public function test_legacy_api_php_path_is_gone(): void
    {
        $this->get('/api.php/provide/vod?ac=list')->assertNotFound();
        $this->post('/api.php/receive/vod')->assertNotFound();
    }

    public function test_xml_and_key_on_laravel_path(): void
    {
        $this->get('/api/provide/vod?ac=list&at=xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/xml; charset=utf-8');

        VideoOption::query()->create([
            'k' => 'provide_key',
            'v' => 'secret-key',
            'updated_at' => time(),
        ]);
        Cache::forget(VideoSettingService::CACHE_KEY);

        $this->getJson('/api/provide/vod?ac=list')
            ->assertForbidden()
            ->assertJsonPath('msg', '密钥无效');

        $this->getJson('/api/provide/vod?ac=list&key=secret-key')
            ->assertOk()
            ->assertJsonPath('code', 1);
    }

    public function test_receive_rejects_missing_key_on_laravel_path(): void
    {
        $this->postJson('/api/receive/vod', ['vod_name' => '测试片'])
            ->assertOk()
            ->assertJsonPath('code', 1);
    }
}

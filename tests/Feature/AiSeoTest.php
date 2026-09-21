<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Plugins\AiContent\Services\AiContentService;
use Tests\TestCase;

class AiSeoTest extends TestCase
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

    public function test_seo_generate_fails_without_key(): void
    {
        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/ai/seo', ['title' => '测试片'])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($json['code'] ?? 0));
        $this->assertStringContainsString('未配置 API Key', (string) ($json['msg'] ?? ''));
    }

    public function test_deepseek_seo_writes_fields_and_front_meta(): void
    {
        app(VideoSettingService::class)->saveOptions([
            'ai_provider' => 'DeepSeek',
            'ai_key' => 'sk-test',
            'ai_model' => 'deepseek-chat',
        ]);
        $this->assertSame('https://api.deepseek.com/v1/chat/completions', app(AiContentService::class)->endpoint());

        Http::fake([
            'api.deepseek.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => '{"title":"测试片在线看","keywords":"测试片,电影","description":"一部测试片的搜索摘要"}',
                    ],
                ]],
            ], 200),
        ]);

        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/ai/seo', ['title' => '测试片', 'hint' => '有人追寻真相'])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $this->assertSame('测试片在线看', $ok['data']['title'] ?? '');
        $this->assertSame('测试片,电影', $ok['data']['keywords'] ?? '');
        $this->assertSame('一部测试片的搜索摘要', $ok['data']['description'] ?? '');

        $now = time();
        $video = VideoModel::query()->create([
            'title' => '落库片',
            'description' => '剧情',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $apply = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/ai/seo', ['id' => $video->id, 'apply' => 1])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($apply['code'] ?? 1), (string) ($apply['msg'] ?? ''));
        $video->refresh();
        $this->assertSame('测试片在线看', $video->seo_title);
        $this->assertSame('测试片,电影', $video->seo_keywords);

        $html = $this->get('/vod/'.$video->id)->assertOk()->getContent();
        $this->assertStringContainsString('测试片在线看', $html);
        $this->assertStringContainsString('一部测试片的搜索摘要', $html);
        $this->assertStringContainsString('name="keywords"', $html);
    }

    public function test_batch_skips_filled_seo(): void
    {
        app(VideoSettingService::class)->saveOptions([
            'ai_provider' => 'DeepSeek',
            'ai_key' => 'sk-test',
        ]);
        Http::fake([
            'api.deepseek.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => '{"title":"新标题","keywords":"a,b","description":"新摘要"}',
                    ],
                ]],
            ], 200),
        ]);
        $now = time();
        $empty = VideoModel::query()->create([
            'title' => '空 SEO',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $filled = VideoModel::query()->create([
            'title' => '已有 SEO',
            'seo_title' => '旧标题',
            'seo_keywords' => '旧词',
            'seo_description' => '旧描述',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $res = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/ai/seo/batch', ['ids' => $empty->id.','.$filled->id])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($res['code'] ?? 1), (string) ($res['msg'] ?? ''));
        $this->assertSame(1, (int) ($res['data']['done'] ?? 0));
        $this->assertSame(1, (int) ($res['data']['skip'] ?? 0));
        $empty->refresh();
        $filled->refresh();
        $this->assertSame('新标题', $empty->seo_title);
        $this->assertSame('旧标题', $filled->seo_title);

        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/list?empty_seo=1')
            ->assertOk()
            ->json();
        $titles = array_column($list['data']['data'] ?? [], 'title');
        $this->assertNotContains('已有 SEO', $titles);
    }
}

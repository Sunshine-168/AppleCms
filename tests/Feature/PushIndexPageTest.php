<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Models\Video\VideoOption;
use App\Services\Video\SiteOpsService;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PushIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        config(['app.url' => 'http://127.0.0.1:8010']);
        URL::forceRootUrl('http://127.0.0.1:8010');
    }

    public function test_push_index_is_a_webmaster_workbench_not_three_buttons(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/push')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('搜索推送', $html);
        $this->assertStringContainsString('sitemap.xml', $html);
        $this->assertStringContainsString('inc=1', $html);
        $this->assertStringContainsString('robots.txt', $html);
        $this->assertStringContainsString('还没填 Token', $html);
        $this->assertStringContainsString('保存 Token', $html);
        $this->assertStringContainsString('推最近一批', $html);
        $this->assertStringContainsString('没有改动，不用保存', $html);
        $this->assertStringContainsString('push-index', $html);
        $this->assertStringContainsString('本机', $html);
        $this->assertStringContainsString('video:baidu-push', $html);
        $this->assertStringContainsString('name="baidu_push_token"', $html);
        $this->assertStringContainsString('name="shenma_push_token"', $html);
        $this->assertStringContainsString('name="bing_push_token"', $html);
        $this->assertStringContainsString('没有接通 Google', $html);
        $this->assertStringNotContainsString('data-engine="google"', $html);
        $this->assertStringNotContainsString('Token 在站点设置中填写', $html);
        $this->assertStringNotContainsString('百度 50 条', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
    }

    public function test_seo_push_fails_without_token_and_does_not_hit_the_network(): void
    {
        Http::fake();
        $r = app(SiteOpsService::class)->seoPush('baidu', 20);
        $this->assertSame(1, $r['code']);
        $this->assertStringContainsString('Token', $r['msg']);
        Http::assertNothingSent();
    }

    public function test_seo_push_reports_engine_error_and_does_not_double_host(): void
    {
        config(['app.url' => 'https://video.example.com']);
        URL::forceRootUrl('https://video.example.com');
        VideoOption::query()->create([
            'k' => 'baidu_push_token',
            'v' => 'tok-1',
            'updated_at' => time(),
        ]);
        Cache::forget(VideoSettingService::CACHE_KEY);
        VideoModel::query()->create([
            'title' => '可推的片子',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $sent = '';
        Http::fake(function ($request) use (&$sent) {
            $sent = $request->body();

            return Http::response(['error' => 401, 'message' => 'token is not valid'], 200);
        });

        $r = app(SiteOpsService::class)->seoPush('baidu', 20);
        $this->assertSame(1, $r['code']);
        $this->assertStringContainsString('没收下', $r['msg']);
        $this->assertStringContainsString('token is not valid', $r['msg']);
        $this->assertStringContainsString('video.example.com/vod/', $sent);
        $this->assertStringNotContainsString('video.example.comhttp', $sent);
        $this->assertStringNotContainsString('example.comhttps://', $sent);
    }

    public function test_seo_push_success_uses_engine_counts(): void
    {
        config(['app.url' => 'https://video.example.com']);
        URL::forceRootUrl('https://video.example.com');
        VideoOption::query()->create([
            'k' => 'baidu_push_token',
            'v' => 'tok-ok',
            'updated_at' => time(),
        ]);
        Cache::forget(VideoSettingService::CACHE_KEY);
        VideoModel::query()->create([
            'title' => '收下的片子',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        Http::fake([
            'data.zz.baidu.com/*' => Http::response(['success' => 1, 'remain' => 88], 200),
        ]);

        $r = app(SiteOpsService::class)->seoPush('baidu', 50);
        $this->assertSame(0, $r['code']);
        $this->assertStringContainsString('收下了 1 条', $r['msg']);
        $this->assertStringContainsString('额度还剩 88', $r['msg']);
        $this->assertSame(1, $r['data']['count'] ?? 0);
    }
}

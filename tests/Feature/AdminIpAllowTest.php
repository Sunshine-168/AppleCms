<?php

namespace Tests\Feature;

use App\Models\Video\VideoOption;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminIpAllowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_ip_page_is_a_lockout_aware_workbench_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/ip')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('ip-config-index', $html);
        $this->assertStringContainsString('当前访问 IP', $html);
        $this->assertStringContainsString('加入当前 IP', $html);
        $this->assertStringContainsString('只允许名单', $html);
        $this->assertStringContainsString('不限制', $html);
        $this->assertStringContainsString('video:admin-ip-clear', $html);
        $this->assertStringContainsString('/admin/system/monitor/login-logs', $html);
        $this->assertStringContainsString('site-save', $html);
        $this->assertStringNotContainsString('placeholder="留空不限制。多个用逗号或换行"', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_save_rejects_list_that_would_lock_current_ip_out(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])->get('/admin/video/config/ip');

        $deny = app(VideoSettingService::class)->save(['admin_ip_allow' => '8.8.8.8']);
        $this->assertSame(1, $deny['code']);
        $this->assertStringContainsString('不在名单里', $deny['msg']);

        $bad = app(VideoSettingService::class)->save(['admin_ip_allow' => '*']);
        $this->assertSame(1, $bad['code']);
        $this->assertStringContainsString('无法识别', $bad['msg']);

        $ok = app(VideoSettingService::class)->save(['admin_ip_allow' => "127.0.0.1\n10.0.0.2"]);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertSame("127.0.0.1\n10.0.0.2", (string) app(VideoSettingService::class)->get('admin_ip_allow'));

        $cidr = app(VideoSettingService::class)->save(['admin_ip_allow' => '127.0.0.0/8']);
        $this->assertSame(0, $cidr['code'], $cidr['msg'] ?? '');
    }

    public function test_middleware_blocks_other_ips_and_artisan_clears(): void
    {
        VideoOption::query()->updateOrCreate(
            ['k' => 'admin_ip_allow'],
            ['v' => '8.8.8.8', 'updated_at' => time()]
        );
        Cache::forget(VideoSettingService::CACHE_KEY);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertForbidden();

        $this->artisan('video:admin-ip-clear')->assertSuccessful();

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk();
    }
}

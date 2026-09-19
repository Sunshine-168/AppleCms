<?php

namespace Tests\Feature;

use App\Models\Monitor\VideoMonitorEvent;
use App\Models\Monitor\VideoMonitorMin;
use App\Models\Monitor\VideoMonitorRule;
use App\Services\Monitor\MonitorState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuntimeMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_welcome_top_bar_does_not_contain_monitor_alert_console(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('监控告警', $html);
    }

    public function test_runtime_is_a_system_workbench(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('runtime-board', $html);
        $this->assertStringContainsString('监控', $html);
        $this->assertStringNotContainsString('nav-fold-nested', $html);
        $this->assertStringContainsString('>性能<', $html);
        $this->assertStringContainsString('>实时<', $html);
        $this->assertStringContainsString('>设置<', $html);
        $this->assertStringContainsString('>规则<', $html);
        $this->assertStringContainsString('>事件<', $html);
        $this->assertStringContainsString('>异常访问<', $html);
        $this->assertStringContainsString('跟计划任务同一条 crontab', $html);
        $this->assertStringContainsString('/admin/system/runtime?desk=live', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('一键封', $html);
        $this->assertStringNotContainsString('钉钉', $html);
        $this->assertStringNotContainsString('微信', $html);
        $this->assertStringNotContainsString('监控告警', $html);
    }

    public function test_tick_writes_heartbeat_and_a_metric(): void
    {
        $this->artisan('monitor:tick')->assertSuccessful();

        $this->assertGreaterThan(0, MonitorState::getNum('cron_heartbeat', 0));
        $keys = VideoMonitorMin::query()->pluck('metric_key')->all();
        $this->assertTrue(
            in_array('sys.disk.used_pct', $keys, true) || in_array('php.memory_pct', $keys, true),
            'expected disk or php.memory metric, got '.implode(',', $keys)
        );
    }

    public function test_front_get_increments_http_req_after_tick(): void
    {
        $this->get('/')->assertOk();
        $this->artisan('monitor:tick')->assertSuccessful();

        $sum = (float) VideoMonitorMin::query()->where('metric_key', 'http.req')->sum('metric_value');
        $this->assertGreaterThanOrEqual(1, $sum);
    }

    public function test_disabled_rules_do_not_open_events(): void
    {
        $this->assertSame(0, (int) VideoMonitorRule::query()->where('status', 1)->count());
        $this->artisan('monitor:tick')->assertSuccessful();
        $this->assertSame(0, VideoMonitorEvent::query()->count());
    }

    public function test_enabled_http_req_rule_can_fire_after_traffic(): void
    {
        $this->get('/')->assertOk();
        $rule = VideoMonitorRule::query()->create([
            'name' => '请求试跑',
            'metric_key' => 'http.req',
            'agg' => 'sum',
            'window_min' => 5,
            'op' => 'gt',
            'threshold' => 0,
            'for_min' => 0,
            'silence_min' => 0,
            'status' => 1,
            'hint' => '',
        ]);
        $this->artisan('monitor:tick')->assertSuccessful();
        $this->assertGreaterThan(
            0,
            VideoMonitorEvent::query()->where('rule_id', $rule->id)->where('status', 1)->count()
        );
    }

    public function test_access_desk_is_a_viewer_not_a_ban(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime?desk=access')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('只看不封', $html);
        $this->assertStringContainsString('不能封', $html);
        $this->assertStringContainsString('流水', $html);
        $this->assertStringContainsString('/admin/system/runtime?desk=access&view=logs', $html);
        $this->assertStringContainsString('/admin/video/config/ip', $html);
        $this->assertStringNotContainsString('一键封', $html);
    }

    public function test_access_desk_embeds_accesslog_board(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime?desk=access&view=logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('accesslog-search', $html);
        $this->assertStringContainsString('还没有前台访问', $html);
        $this->assertStringContainsString('搜 IP、地址或标识', $html);
        $this->assertStringContainsString('/admin/video/accesslogs/list', $html);
        $this->assertStringContainsString('/admin/video/botlogs', $html);
        $this->assertStringContainsString('/admin/video/config/ip', $html);
        $this->assertStringContainsString('runtime-access-links', $html);
        $this->assertStringContainsString('不能封 IP', $html);
    }

    public function test_series_returns_json(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime/series?range=1h')
            ->assertOk()
            ->assertJson(['code' => 0]);
    }

    public function test_series_returns_grouped_charts(): void
    {
        $this->artisan('monitor:tick')->assertSuccessful();
        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime/series?range=1h')
            ->assertOk()
            ->assertJson(['code' => 0])
            ->json();
        $this->assertIsArray($json['data']['groups'] ?? null);
        $this->assertIsArray($json['data']['series'] ?? null);
        $ids = array_column($json['data']['groups'], 'id');
        $this->assertContains('load', $ids);
        $this->assertContains('cpu', $ids);
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime/series?range=72h')
            ->assertOk()
            ->assertJson(['code' => 0]);
    }

    public function test_perf_page_has_grouped_ranges(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-range="72h"', $html);
        $this->assertStringContainsString('data-range="30d"', $html);
    }

    public function test_live_empty_metrics_are_null_not_zero(): void
    {
        $empty = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime/live')
            ->assertOk()
            ->assertJson(['code' => 0])
            ->json('data');
        $this->assertIsArray($empty);
        $this->assertArrayHasKey('qps', $empty);
        $this->assertNull($empty['qps']);

        $this->get('/')->assertOk();
        $this->artisan('monitor:tick')->assertSuccessful();

        $after = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime/live')
            ->assertOk()
            ->json('data');
        $this->assertIsArray($after);
        $this->assertArrayHasKey('qps', $after);
        $this->assertTrue($after['qps'] === null || is_numeric($after['qps']));
        $this->assertNotSame('0', $after['qps']);
    }

    public function test_access_defaults_to_high_filter(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime?desk=access')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('偏高', $html);
        $this->assertStringContainsString('desk=access&only=all', $html);
        $this->assertStringContainsString('只看不封', $html);
    }

    public function test_perf_snapshot_strip(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/runtime')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('runtime-snap', $html);
        $this->assertTrue(
            str_contains($html, '>心跳<') || (str_contains($html, '心跳') && str_contains($html, 'PHP')),
            'snapshot should show heartbeat and PHP'
        );
        $this->assertStringContainsString('跟计划任务同一条 crontab', $html);
        $this->assertStringContainsString('正在读样本', $html);
        $this->assertStringNotContainsString('监控告警', $html);
        $this->assertStringNotContainsString('一键封', $html);
    }
}

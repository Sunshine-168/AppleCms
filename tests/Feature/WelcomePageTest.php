<?php

namespace Tests\Feature;

use App\Services\Admin\VideoDashboardService;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_welcome_shows_system_info_and_disk_not_a_php_ini_essay(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('系统信息', $html);
        $this->assertStringContainsString('磁盘占用', $html);
        $this->assertStringContainsString(PHP_VERSION, $html);
        $this->assertTrue(
            str_contains($html, conf('version')) || str_contains($html, app()->version()),
            'welcome should show app version or Laravel version'
        );
        $this->assertStringContainsString('上传限制', $html);
        $this->assertStringNotContainsString('ThinkPHP', $html);
        $this->assertStringNotContainsString('disable_functions', $html);
        $this->assertStringNotContainsString('shell_exec', $html);
        $this->assertStringContainsString('dash-sys', $html);
        $this->assertStringContainsString('dash-week', $html);
        $this->assertTrue(
            str_contains($html, 'dash-disk-bar') || str_contains($html, '读不到磁盘'),
            'welcome should show a disk bar or say disk is unreadable'
        );
        $this->assertStringNotContainsString('dash-hero', $html);
        $this->assertStringNotContainsString('今日入金', $html);
        $this->assertStringContainsString('今日播放', $html);
        $this->assertStringContainsString('今日访问', $html);
        $this->assertStringContainsString('今日入库', $html);
        $this->assertStringContainsString('待处理', $html);
        $this->assertStringNotContainsString('上次登录', $html);
        $this->assertStringNotContainsString('登录 IP', $html);
        $this->assertStringContainsString('近七日注册', $html);
        $this->assertStringNotContainsString('站点健康', $html);
        $this->assertStringNotContainsString('dash-health', $html);
    }

    public function test_dashboard_board_includes_server_disk(): void
    {
        $board = app(VideoDashboardService::class)->board();

        $this->assertArrayHasKey('server', $board);
        $this->assertArrayNotHasKey('health', $board);
        $this->assertIsArray($board['server']['disk'] ?? null);
        $this->assertArrayHasKey('reg', $board);
        $this->assertCount(7, $board['reg']['days']);
        $this->assertCount(4, $board['kpis']);
        $labels = array_column($board['kpis'], 'label');
        $this->assertSame(['今日播放', '今日访问', '今日入库', '待处理'], $labels);
    }
}

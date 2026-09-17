<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginLogIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_login_log_index_is_a_timeline_not_a_ua_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/monitor/login-logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有登录记录', $html);
        $this->assertStringContainsString('登录日志', $html);
        $this->assertStringContainsString('/admin/system/monitor/operate-logs', $html);
        $this->assertStringContainsString('搜管理员或 IP', $html);
        $this->assertStringContainsString('点 IP 只看这个地址', $html);
        $this->assertStringContainsString('今天', $html);
        $this->assertStringContainsString('近7天', $html);
        $this->assertStringContainsString('我的', $html);
        $this->assertMatchesRegularExpression('/id="login-log-ip-chip"[^>]*\bhidden\b/', $html);
        $this->assertStringContainsString('/admin/user', $html);
        $this->assertStringNotContainsString('sysuserlog-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('UA请求头', $html);
        $this->assertStringNotContainsString('placeholder="用户名"', $html);
        $this->assertStringNotContainsString('title: \'ID\'', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }
}

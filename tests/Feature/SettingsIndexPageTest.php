<?php

namespace Tests\Feature;

use Tests\TestCase;

class SettingsIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_settings_index_is_grouped_tabs_like_laracms(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/settings')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('网站名称', $html);
        $this->assertStringContainsString('暂时关闭网站', $html);
        $this->assertStringContainsString('还没有 Logo', $html);
        $this->assertStringContainsString('允许前台注册', $html);
        $this->assertStringContainsString('新评论要先审再显示', $html);
        $this->assertStringContainsString('保存设置', $html);
        $this->assertStringContainsString('data-pane="site"', $html);
        $this->assertStringContainsString('settings-pane active', $html);
        $this->assertStringContainsString('缓存、采集、发信、密钥在「更多」里', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('SMTP 主机', $html);
        $this->assertStringNotContainsString('S3 Key', $html);
    }

    public function test_more_tab_keeps_advanced_fields(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/settings?tab=more')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-pane="more"', $html);
        $this->assertStringContainsString('邮件服务器', $html);
        $this->assertStringContainsString('密码留空表示不改已保存的值', $html);
        $this->assertStringContainsString('静态生成', $html);
        $this->assertStringContainsString('内容接入', $html);
    }
}

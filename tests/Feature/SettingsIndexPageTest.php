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
        $this->assertStringContainsString('模板 → 外观', $html);
        $this->assertStringContainsString('/admin/video/templates', $html);
        $this->assertStringContainsString('允许前台注册', $html);
        $this->assertStringContainsString('data-pane="member"', $html);
        $this->assertStringContainsString('新评论要先审再显示', $html);
        $this->assertStringContainsString('保存设置', $html);
        $this->assertStringContainsString('data-pane="site"', $html);
        $this->assertStringContainsString('data-pane="play"', $html);
        $this->assertStringContainsString('data-pane="mail"', $html);
        $this->assertStringContainsString('data-pane="seo"', $html);
        $this->assertStringContainsString('data-pane="storage"', $html);
        $this->assertStringContainsString('settings-pane active', $html);
        $this->assertStringContainsString('缓存、采集和密钥在「更多」', $html);
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
        $this->assertStringContainsString('/admin/video/config/ip', $html);
        $this->assertStringContainsString('后台 IP 白名单', $html);
        $this->assertStringContainsString('/admin/video/rewrite', $html);
        $this->assertStringContainsString('伪静态', $html);
        $this->assertStringContainsString('/admin/video/config/api', $html);
        $this->assertStringContainsString('开放 API', $html);
        $this->assertStringContainsString('/admin/video/push', $html);
        $this->assertStringContainsString('搜索推送', $html);
        $this->assertStringNotContainsString('name="admin_ip_allow"', $html);
        $this->assertStringNotContainsString('name="provide_key"', $html);
        $this->assertStringNotContainsString('name="baidu_push_token"', $html);
        $this->assertStringNotContainsString('非空时 /api.php/provide/vod', $html);
        $this->assertStringNotContainsString('上传与后台 IP', $html);
    }
}

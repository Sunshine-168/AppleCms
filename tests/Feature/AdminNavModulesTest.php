<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminNavModulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_top_bar_has_workspaces_not_a_super_console(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('mod-nav-top', $html);
        $this->assertStringContainsString('工作台', $html);
        $this->assertStringContainsString('影片', $html);
        $this->assertStringContainsString('采集', $html);
        $this->assertStringContainsString('会员', $html);
        $this->assertStringContainsString('站点', $html);
        $this->assertStringContainsString('系统', $html);
        $this->assertStringContainsString('插件', $html);
        $this->assertStringContainsString('搜功能', $html);
        $this->assertStringContainsString('href="/admin/plugins"', $html);
        $this->assertStringNotContainsString('>漫画<', $html);
        $this->assertStringNotContainsString('直播', $html);
        $this->assertStringNotContainsString('入金', $html);
        $this->assertStringNotContainsString('监控告警', $html);
    }

    public function test_plugins_are_their_own_workspace(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="is-on">插件</a>', $html);
        $this->assertStringContainsString('插件管理', $html);
        $this->assertStringNotContainsString('>数据库备份<', $html);
    }

    public function test_sidebar_only_shows_the_current_workspace(): void
    {
        $backup = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/backup')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('class="is-on">系统</a>', $backup);
        $this->assertStringContainsString('>数据库<', $backup);
        $this->assertStringContainsString('db-tabs', $backup);
        $this->assertStringContainsString('>管理员<', $backup);
        $this->assertStringContainsString('>角色<', $backup);
        $this->assertStringContainsString('>菜单<', $backup);
        $this->assertStringContainsString('>日志<', $backup);
        $this->assertStringNotContainsString('>操作日志<', $backup);
        $this->assertStringNotContainsString('>系统日志<', $backup);
        $this->assertStringNotContainsString('>分类<', $backup);
        $this->assertStringNotContainsString('>内容质量<', $backup);

        $quality = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/quality')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('class="is-on">影片</a>', $quality);
        $this->assertStringContainsString('内容质量', $quality);
        $this->assertStringContainsString('>分类<', $quality);
        $this->assertStringNotContainsString('>数据库备份<', $quality);
    }
}

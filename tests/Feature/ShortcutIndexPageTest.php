<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShortcutIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_shortcut_index_is_daily_jobs_not_a_cms_system_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/shortcut')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('常用', $html);
        $this->assertStringContainsString('要处理', $html);
        $this->assertStringContainsString('片库', $html);
        $this->assertStringContainsString('全部功能', $html);
        $this->assertStringContainsString('/admin/more', $html);
        $this->assertStringContainsString('/admin/video/comments', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('/admin/video/settings', $html);
        $this->assertStringContainsString('/admin/system/tools/cache', $html);
        $this->assertStringContainsString('/admin/system/database/backup', $html);
        $this->assertStringContainsString('/admin/plugins', $html);
        $this->assertStringContainsString('漫画、商城', $html);
        $this->assertStringContainsString('shortcut-index', $html);
        $this->assertStringNotContainsString('shortcut-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('更新于', $html);
        $this->assertStringNotContainsString('系统用户', $html);
        $this->assertStringNotContainsString('字典管理', $html);
        $this->assertStringNotContainsString('菜单、按钮与接口', $html);
        $this->assertStringNotContainsString('class="tool-grid"', $html);
        $this->assertStringNotContainsString('/admin/system/shortcut/list', $html);
    }
}

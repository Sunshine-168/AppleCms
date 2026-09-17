<?php

namespace Tests\Feature;

use Tests\TestCase;

class MoreIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_more_index_is_a_searchable_catalog_not_a_button_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/more')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('more-index', $html);
        $this->assertStringContainsString('more-tile', $html);
        $this->assertStringContainsString('搜功能', $html);
        $this->assertStringContainsString('没有符合的功能', $html);
        $this->assertStringContainsString('顶栏按工作切开', $html);
        $this->assertStringContainsString('角色库', $html);
        $this->assertStringContainsString('内容质量', $html);
        $this->assertStringContainsString('服务器组', $html);
        $this->assertStringContainsString('备份、恢复、跑 SQL', $html);
        $this->assertStringContainsString('href="/admin/system/database/backup"', $html);
        $this->assertStringNotContainsString('href="/admin/system/database/restore"', $html);
        $this->assertStringNotContainsString('href="/admin/system/database/sql"', $html);
        $this->assertStringNotContainsString('href="/admin/system/database/replace"', $html);
        $this->assertStringNotContainsString('href="/admin/system/database/dict"', $html);
        $this->assertStringNotContainsString('很少用到', $html);
        $this->assertStringNotContainsString('more-fold', $html);
        $this->assertStringNotContainsString('class="tool-grid"', $html);
        $this->assertStringNotContainsString('/admin/video/config/seo', $html);
        $this->assertStringNotContainsString('/admin/video/config/theme', $html);
        $this->assertStringNotContainsString('/admin/video/config/email', $html);
        $this->assertStringNotContainsString('/admin/video/config/user', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class MoreIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
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
        $this->assertStringContainsString('关掉后这里和侧栏都会收走', $html);
        $this->assertStringContainsString('很少用到', $html);
        $this->assertStringContainsString('站点设置', $html);
        $this->assertStringContainsString('失败和成功记录', $html);
        $this->assertStringContainsString('/admin/plugins', $html);
        $this->assertStringContainsString('角色库', $html);
        $this->assertStringContainsString('more-fold', $html);
        $this->assertStringNotContainsString('class="tool-grid"', $html);
        $this->assertStringNotContainsString('/admin/video/config/seo', $html);
        $this->assertStringNotContainsString('/admin/video/config/theme', $html);
        $this->assertStringNotContainsString('/admin/video/config/email', $html);
        $this->assertStringNotContainsString('/admin/video/config/user', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
    }
}

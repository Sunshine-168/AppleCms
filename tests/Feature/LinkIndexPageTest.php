<?php

namespace Tests\Feature;

use Tests\TestCase;

class LinkIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_link_index_is_a_footer_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/links')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有友情链接', $html);
        $this->assertStringContainsString('新增友链', $html);
        $this->assertStringContainsString('link-batch', $html);
        $this->assertStringContainsString('有图', $html);
        $this->assertStringContainsString('显示中', $html);
        $this->assertStringContainsString('@vodLink', $html);
        $this->assertStringContainsString('搜名称或网址', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
    }
}

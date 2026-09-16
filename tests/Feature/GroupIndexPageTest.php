<?php

namespace Tests\Feature;

use Tests\TestCase;

class GroupIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_group_index_is_a_tier_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/groups')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有会员组', $html);
        $this->assertStringContainsString('点播档位', $html);
        $this->assertStringContainsString('group-batch', $html);
        $this->assertStringContainsString('新增分组', $html);
        $this->assertStringContainsString('搜组名', $html);
        $this->assertStringContainsString('试看秒数', $html);
        $this->assertStringContainsString('每天免费条数', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
    }
}

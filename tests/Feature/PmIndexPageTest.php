<?php

namespace Tests\Feature;

use Tests\TestCase;

class PmIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_pm_index_is_a_compose_workbench_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/pms')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('站内信', $html);
        $this->assertStringContainsString('还没有站内信', $html);
        $this->assertStringContainsString('写给会员', $html);
        $this->assertStringContainsString('未读', $html);
        $this->assertStringContainsString('搜标题、内容或会员', $html);
        $this->assertStringContainsString('/admin/video/members', $html);
        $this->assertStringContainsString('/admin/video/notifies', $html);
        $this->assertStringContainsString('pm-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="title"', $html);
        $this->assertStringNotContainsString("title: 'from_id'", $html);
        $this->assertStringNotContainsString("title: 'to_id'", $html);
        $this->assertStringNotContainsString('站内信管理', $html);
    }
}

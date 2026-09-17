<?php

namespace Tests\Feature;

use Tests\TestCase;

class NotifyIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_notify_index_is_a_compose_workbench_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/notifies')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('会员通知', $html);
        $this->assertStringContainsString('还没有通知', $html);
        $this->assertStringContainsString('发通知', $html);
        $this->assertStringContainsString('全站', $html);
        $this->assertStringContainsString('搜标题、内容或会员', $html);
        $this->assertStringContainsString('/admin/video/pms', $html);
        $this->assertStringContainsString('/admin/video/members', $html);
        $this->assertStringContainsString('notify-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="title"', $html);
        $this->assertStringNotContainsString("title: 'member_id'", $html);
        $this->assertStringNotContainsString("title: 'is_read'", $html);
        $this->assertStringNotContainsString('会员通知管理', $html);
    }
}

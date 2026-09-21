<?php

namespace Tests\Feature;

use Tests\TestCase;

class InviteIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_invite_index_is_an_operator_workbench_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/invites')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('邀请码', $html);
        $this->assertStringContainsString('还没有邀请码', $html);
        $this->assertStringContainsString('批量生成', $html);
        $this->assertStringContainsString('未用', $html);
        $this->assertStringContainsString('已用', $html);
        $this->assertStringContainsString('搜邀请码或会员', $html);
        $this->assertStringContainsString('/admin/video/settings?tab=member', $html);
        $this->assertStringContainsString('/admin/video/cards', $html);
        $this->assertStringContainsString('data-desk="rank"', $html);
        $this->assertStringContainsString('id="inviteDesks"', $html);
        $this->assertStringNotContainsString('<a class="btn btn-muted btn-sm" href="/admin/video/members">', $html);
        $this->assertStringNotContainsString("L.members + '</a>'", $html);
        $this->assertStringContainsString('function memberHref', $html);
        $this->assertStringContainsString('invite-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="code"', $html);
        $this->assertStringNotContainsString("title: 'member_id'", $html);
        $this->assertStringNotContainsString("title: 'used_by'", $html);
        $this->assertStringNotContainsString('邀请码管理', $html);
    }
}

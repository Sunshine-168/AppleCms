<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminUserIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_admin_user_index_is_a_people_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/user')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增管理员', $html);
        $this->assertStringContainsString('还没有管理员', $html);
        $this->assertStringContainsString('登录的是后台', $html);
        $this->assertStringContainsString('搜登录名或邮箱', $html);
        $this->assertStringContainsString('创始人', $html);
        $this->assertStringContainsString('从未登录', $html);
        $this->assertStringContainsString('/admin/system/roles', $html);
        $this->assertStringContainsString('/admin/system/menus', $html);
        $this->assertStringContainsString('access-board', $html);
        $this->assertStringContainsString('access-chain', $html);
        $this->assertStringContainsString('class="is-on">管理员</a>', $html);
        $this->assertStringContainsString('密码，至少 6 位', $html);
        $this->assertStringContainsString('>查询<', $html);
        $this->assertStringNotContainsString('套上角色', $html);
        $this->assertStringNotContainsString('data-value="founder"', $html);
        $this->assertStringNotContainsString('data-value="staff"', $html);
        $this->assertStringNotContainsString('sysuser-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('管理员类型', $html);
        $this->assertStringNotContainsString('placeholder="用户名"', $html);
        $this->assertStringNotContainsString('title: \'ID\'', $html);
    }
}

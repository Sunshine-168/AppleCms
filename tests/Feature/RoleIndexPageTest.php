<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoleIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_role_index_is_a_permission_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/roles')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增角色', $html);
        $this->assertStringContainsString('还没有角色', $html);
        $this->assertStringContainsString('点名称去勾能进哪些页', $html);
        $this->assertStringContainsString('名称，如 审核员', $html);
        $this->assertStringContainsString('使用中', $html);
        $this->assertStringContainsString('还没人', $html);
        $this->assertStringContainsString('/admin/user', $html);
        $this->assertStringContainsString('/admin/system/menus', $html);
        $this->assertStringContainsString('class="is-on">角色</a>', $html);
        $this->assertStringContainsString('access-chain', $html);
        $this->assertStringNotContainsString('role-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="角色标识"', $html);
        $this->assertStringNotContainsString('title: \'ID\'', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
    }
}

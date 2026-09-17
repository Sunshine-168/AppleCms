<?php

namespace Tests\Feature;

use Tests\TestCase;

class WithdrawIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_withdraw_index_is_an_audit_queue_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/withdraws')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('提现', $html);
        $this->assertStringContainsString('还没有提现申请', $html);
        $this->assertStringContainsString('待审', $html);
        $this->assertStringContainsString('已打款', $html);
        $this->assertStringContainsString('拒绝', $html);
        $this->assertStringContainsString('搜账号、备注或会员', $html);
        $this->assertStringContainsString('/admin/video/orders', $html);
        $this->assertStringContainsString('/admin/video/plogs', $html);
        $this->assertStringContainsString('withdraw-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="account"', $html);
        $this->assertStringNotContainsString("title: 'member_id'", $html);
        $this->assertStringNotContainsString("title: 'amount'", $html);
        $this->assertStringNotContainsString('提现管理', $html);
    }
}

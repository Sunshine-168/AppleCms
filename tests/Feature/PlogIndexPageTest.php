<?php

namespace Tests\Feature;

use Tests\TestCase;

class PlogIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_plog_index_is_a_ledger_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/plogs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有流水', $html);
        $this->assertStringContainsString('对账单', $html);
        $this->assertStringContainsString('plog-batch', $html);
        $this->assertStringContainsString('调积分', $html);
        $this->assertStringContainsString('搜备注、会员名或 ID', $html);
        $this->assertStringContainsString('收入', $html);
        $this->assertStringContainsString('删掉一行不会改会员积分', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="remark"', $html);
    }
}

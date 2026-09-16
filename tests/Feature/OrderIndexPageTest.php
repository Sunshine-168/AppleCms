<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_order_index_is_a_pay_queue_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/orders')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有订单', $html);
        $this->assertStringContainsString('确认已付', $html);
        $this->assertStringContainsString('order-batch', $html);
        $this->assertStringContainsString('补录订单', $html);
        $this->assertStringContainsString('搜索单号、流水、会员名或 ID', $html);
        $this->assertStringContainsString('待付', $html);
        $this->assertStringContainsString('金额（元）', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="order_no"', $html);
        $this->assertStringNotContainsString('金额分', $html);
    }
}

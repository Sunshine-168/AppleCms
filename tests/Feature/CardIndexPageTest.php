<?php

namespace Tests\Feature;

use Tests\TestCase;

class CardIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_card_index_is_an_inventory_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/cards')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有卡密', $html);
        $this->assertStringContainsString('会员中心兑换', $html);
        $this->assertStringContainsString('card-batch', $html);
        $this->assertStringContainsString('批量生成', $html);
        $this->assertStringContainsString('手输一张', $html);
        $this->assertStringContainsString('搜卡密、使用者或会员 ID', $html);
        $this->assertStringContainsString('每张积分', $html);
        $this->assertStringContainsString('未用', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="code"', $html);
    }
}

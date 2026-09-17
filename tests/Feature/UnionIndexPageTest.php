<?php

namespace Tests\Feature;

use Tests\TestCase;

class UnionIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_union_index_is_a_bookmark_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/unions')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有收藏的资源站', $html);
        $this->assertStringContainsString('接入采集源', $html);
        $this->assertStringContainsString('这里只记接口', $html);
        $this->assertStringContainsString('/admin/video/unions/create', $html);
        $this->assertStringContainsString('搜名称或接口', $html);
        $this->assertStringContainsString('union-batch', $html);
        $this->assertStringContainsString('未接入', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString('title: \'api_url\'', $html);
        $this->assertStringNotContainsString('union-dialog', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
    }
}

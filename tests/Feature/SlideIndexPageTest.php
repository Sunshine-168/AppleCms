<?php

namespace Tests\Feature;

use Tests\TestCase;

class SlideIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_slide_index_is_a_banner_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/slides')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有幻灯片', $html);
        $this->assertStringContainsString('首页轮播', $html);
        $this->assertStringContainsString('slide-batch', $html);
        $this->assertStringContainsString('新增幻灯', $html);
        $this->assertStringContainsString('播放页', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
    }
}

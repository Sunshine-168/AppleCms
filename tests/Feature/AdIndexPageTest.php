<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_ad_index_is_a_slot_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/ads')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有广告', $html);
        $this->assertStringContainsString('页头', $html);
        $this->assertStringContainsString('页脚', $html);
        $this->assertStringContainsString('播放页', $html);
        $this->assertStringContainsString('新增广告', $html);
        $this->assertStringContainsString('ad-batch', $html);
        $this->assertStringContainsString('插入图片', $html);
        $this->assertStringContainsString('@vodAd', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString('>expire_at<', $html);
        $this->assertStringNotContainsString('>type_id<', $html);
    }
}

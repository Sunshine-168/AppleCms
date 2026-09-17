<?php

namespace Tests\Feature;

use Tests\TestCase;

class PlayFailIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_playfail_index_is_a_broken_line_queue_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/playfails')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('播放失败', $html);
        $this->assertStringContainsString('还没有播放失败', $html);
        $this->assertStringContainsString('未处理', $html);
        $this->assertStringContainsString('已处理', $html);
        $this->assertStringContainsString('可下线', $html);
        $this->assertStringContainsString('下线线路', $html);
        $this->assertStringContainsString('不会改播放地址', $html);
        $this->assertStringContainsString('搜片名、线路、地址或影片 ID', $html);
        $this->assertStringContainsString('/admin/video/reports', $html);
        $this->assertStringContainsString('/admin/video/tools/players', $html);
        $this->assertStringContainsString('fail-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="content"', $html);
        $this->assertStringNotContainsString("title: 'video_id'", $html);
        $this->assertStringNotContainsString("title: 'source_id'", $html);
        $this->assertStringNotContainsString('播放失败管理', $html);
    }
}

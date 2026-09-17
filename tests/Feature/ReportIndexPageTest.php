<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_report_index_is_a_broken_play_queue_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/reports')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('报错', $html);
        $this->assertStringContainsString('还没有报错', $html);
        $this->assertStringContainsString('未处理', $html);
        $this->assertStringContainsString('已处理', $html);
        $this->assertStringContainsString('标为已处理', $html);
        $this->assertStringContainsString('不会改播放地址', $html);
        $this->assertStringContainsString('搜内容、片名或影片 ID', $html);
        $this->assertStringContainsString('/admin/video/playfails', $html);
        $this->assertStringContainsString('report-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="content"', $html);
        $this->assertStringNotContainsString("title: 'video_id'", $html);
        $this->assertStringNotContainsString('报错管理', $html);
    }
}

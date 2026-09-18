<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_collect_index_uses_workflow_not_a_log_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collects')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有采集源', $html);
        $this->assertStringContainsString('desk-board', $html);
        $this->assertStringContainsString('collect-source-add-btn', $html);
        $this->assertStringContainsString('新增采集源', $html);
        $this->assertStringContainsString('js-today', $html);
        $this->assertStringContainsString('未绑定', $html);
        $this->assertStringContainsString('/admin/video/tools/hub', $html);
        $this->assertStringContainsString('/admin/video/unions', $html);
        $this->assertStringNotContainsString('collect-source-refresh-btn', $html);
        $this->assertStringNotContainsString('href="/admin/video/collect_logs">采集日志', $html);
        $this->assertStringNotContainsString('href="/admin/video/collect_temps">待审入库', $html);
        $this->assertStringNotContainsString('href="/admin/video/collect_tasks">定时采集', $html);
        $this->assertStringNotContainsString('btn-muted btn-sm" href="/admin/video/tools/hub"', $html);
        $this->assertStringNotContainsString('btn-muted btn-sm" href="/admin/video/unions"', $html);
        $this->assertStringNotContainsString('起始页,采集页数,小时', $html);
    }
}

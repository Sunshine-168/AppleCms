<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectLogIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_collect_log_index_is_a_run_timeline_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collect_logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有采集记录', $html);
        $this->assertStringContainsString('去采集源', $html);
        $this->assertStringContainsString('搜采集源或说明', $html);
        $this->assertStringContainsString('clog-batch', $html);
        $this->assertStringContainsString('失败', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('删掉记录不会改片库', $html);
        $this->assertMatchesRegularExpression('/id="clog-source-chip"[^>]*\bhidden\b/', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('placeholder="msg"', $html);
        $this->assertStringNotContainsString('title: \'collect_source_id\'', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
    }
}

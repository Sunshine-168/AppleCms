<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectTempIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_collect_temp_index_is_a_review_queue_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collect_temps')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('待审入库', $html);
        $this->assertStringContainsString('还没有待审片子', $html);
        $this->assertStringContainsString('采集是直接入库的', $html);
        $this->assertStringContainsString('写入临时表', $html);
        $this->assertStringContainsString('搜片名或采集源', $html);
        $this->assertStringContainsString('ctemp-batch', $html);
        $this->assertStringContainsString('转入选中', $html);
        $this->assertStringContainsString('转入失败', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('/admin/video/config/collect', $html);
        $this->assertMatchesRegularExpression('/id="ctemp-source-chip"[^>]*\bhidden\b/', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('placeholder="title"', $html);
        $this->assertStringNotContainsString("title: 'collect_source_id'", $html);
        $this->assertStringNotContainsString("title: 'type_id'", $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
    }
}

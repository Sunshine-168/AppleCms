<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectConfigPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_collect_config_is_an_ingest_workbench_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/collect')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('内容接入', $html);
        $this->assertStringContainsString('下次采集', $html);
        $this->assertStringContainsString('直接进片库', $html);
        $this->assertStringContainsString('先待审再转入', $html);
        $this->assertStringContainsString('采集后直接上架', $html);
        $this->assertStringContainsString('随机人气', $html);
        $this->assertStringContainsString('地区 / 语言对照', $html);
        $this->assertStringContainsString('站外推送', $html);
        $this->assertStringContainsString('/admin/video/config/interface', $html);
        $this->assertStringContainsString('ingest-mode', $html);
        $this->assertStringContainsString('/admin/video/collect_temps', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('site-save', $html);
        $this->assertStringNotContainsString('>先入临时表<', $html);
        $this->assertStringNotContainsString('placeholder="POST /api.php/receive/vod"', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_inbound_interface_is_a_receive_workbench_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/interface')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('入库接口', $html);
        $this->assertStringContainsString('别的程序', $html);
        $this->assertStringContainsString('/api/receive/vod', $html);
        $this->assertStringNotContainsString('/api.php/receive/vod', $html);
        $this->assertStringNotContainsString('备用', $html);
        $this->assertStringContainsString('inbound-gen-key', $html);
        $this->assertStringContainsString('vod_name', $html);
        $this->assertStringContainsString('怎么推', $html);
        $this->assertStringContainsString('/admin/video/config/collect', $html);
        $this->assertStringContainsString('site-save', $html);
        $this->assertStringNotContainsString('>采集先入临时表<', $html);
        $this->assertStringNotContainsString('>站外入库密钥<', $html);
        $this->assertStringNotContainsString('ingest-mode', $html);
        $this->assertStringNotContainsString('mod-add', $html);
    }
}

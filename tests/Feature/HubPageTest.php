<?php

namespace Tests\Feature;

use Tests\TestCase;

class HubPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_hub_is_a_probe_workbench_not_a_json_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/hub')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('试试资源接口', $html);
        $this->assertStringContainsString('不改片库', $html);
        $this->assertStringContainsString('探测接口', $html);
        $this->assertStringContainsString('接入采集源', $html);
        $this->assertStringContainsString('绑定分类后采集', $html);
        $this->assertStringContainsString('还没探测', $html);
        $this->assertStringContainsString('hub-probe-btn', $html);
        $this->assertStringContainsString('/admin/video/unions', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('已收藏的接口', $html);
        $this->assertStringNotContainsString('hub-steps', $html);
        $this->assertStringNotContainsString('id="out"', $html);
        $this->assertStringNotContainsString('JSON.stringify', $html);
        $this->assertStringNotContainsString('example.com/api.php/provide/vod/', $html);
        $this->assertStringNotContainsString('art-dialog-tpl', $html);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ImagesPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_images_is_a_cover_workbench_not_a_json_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/images')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('远程图片 / 坏图', $html);
        $this->assertStringContainsString('还没扫描', $html);
        $this->assertStringContainsString('扫描封面', $html);
        $this->assertStringContainsString('下载到本站', $html);
        $this->assertStringContainsString('去无封面列表补图', $html);
        $this->assertStringContainsString('/admin/video?empty_pic=1', $html);
        $this->assertStringContainsString('/admin/video/config/collect', $html);
        $this->assertStringContainsString('/uploads/vod/', $html);
        $this->assertStringContainsString('img-scan-btn', $html);
        $this->assertStringContainsString('不会改片子', $html);
        $this->assertStringNotContainsString('id="out"', $html);
        $this->assertStringNotContainsString('JSON.stringify', $html);
        $this->assertStringNotContainsString('本地化远程封面', $html);
        $this->assertStringNotContainsString('art-dialog-tpl', $html);
    }
}

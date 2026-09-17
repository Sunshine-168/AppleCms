<?php

namespace Tests\Feature;

use Tests\TestCase;

class BatchPlayersPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_batch_players_is_a_retarget_workbench_not_a_json_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/players')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('批量更换播放器', $html);
        $this->assertStringContainsString('还没改', $html);
        $this->assertStringContainsString('查看线路标识', $html);
        $this->assertStringContainsString('更换或下线', $html);
        $this->assertStringContainsString('核对播放器', $html);
        $this->assertStringContainsString('不改播放地址', $html);
        $this->assertStringContainsString('更换播放器', $html);
        $this->assertStringContainsString('下线这些线路', $html);
        $this->assertStringContainsString('线路上在用的标识', $html);
        $this->assertStringContainsString('/admin/video/players', $html);
        $this->assertStringContainsString('play-rename-btn', $html);
        $this->assertStringNotContainsString('id="out"', $html);
        $this->assertStringNotContainsString('JSON.stringify', $html);
        $this->assertStringNotContainsString('替换标识', $html);
        $this->assertStringNotContainsString('下线该线路', $html);
        $this->assertStringNotContainsString('art-dialog-tpl', $html);
    }
}

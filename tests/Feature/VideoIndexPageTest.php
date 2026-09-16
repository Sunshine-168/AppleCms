<?php

namespace Tests\Feature;

use Tests\TestCase;

class VideoIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_video_index_uses_queue_chips_and_selection_batch(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('queue-chips', $html);
        $this->assertStringContainsString('无地址', $html);
        $this->assertStringContainsString('无封面', $html);
        $this->assertStringContainsString('video-batch', $html);
        $this->assertStringContainsString('更多筛选', $html);
        $this->assertStringNotContainsString('id="refreshPage"', $html);
        $this->assertStringNotContainsString('批量删除', $html);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/list')
            ->assertOk()
            ->assertJsonPath('code', 0);
    }
}

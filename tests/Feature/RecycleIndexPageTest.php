<?php

namespace Tests\Feature;

use Tests\TestCase;

class RecycleIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_recycle_index_is_a_trash_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/recycle')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('回收站是空的', $html);
        $this->assertStringContainsString('recycle-batch', $html);
        $this->assertStringContainsString('搜索已删影片的标题或 ID', $html);
        $this->assertStringContainsString('返回影片', $html);
        $this->assertStringContainsString('还原所选', $html);
        $this->assertStringContainsString('清空回收站', $html);
        $this->assertStringNotContainsString('btn-restore', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
    }
}

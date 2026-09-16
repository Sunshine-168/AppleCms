<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_art_index_follows_laracms_entry_list(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有内容', $html);
        $this->assertStringContainsString('写文章', $html);
        $this->assertStringContainsString('已发布', $html);
        $this->assertStringContainsString('草稿', $html);
        $this->assertStringContainsString('art-batch', $html);
        $this->assertStringContainsString('搜索标题、正文或 ID', $html);
        $this->assertStringContainsString('发布到前台', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="title"', $html);
    }
}

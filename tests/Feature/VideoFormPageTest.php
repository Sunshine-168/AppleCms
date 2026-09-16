<?php

namespace Tests\Feature;

use Tests\TestCase;

class VideoFormPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_create_video_is_a_page_not_a_dialog(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增影片', $html);
        $this->assertStringContainsString('标题必填', $html);
        $this->assertStringContainsString('先存成草稿', $html);
        $this->assertStringContainsString('保存并加播放地址', $html);
        $this->assertStringContainsString('video-form-page', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('value="2"', $html);
        $this->assertStringContainsString('/admin/video', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('video-dialog-tpl', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_missing_video_edit_is_not_found(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/999999/edit')
            ->assertNotFound();
    }
}

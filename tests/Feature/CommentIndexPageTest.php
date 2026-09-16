<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommentIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_comment_index_is_an_audit_queue(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/comments')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('待审', $html);
        $this->assertStringContainsString('被举报', $html);
        $this->assertStringContainsString('comment-batch', $html);
        $this->assertStringContainsString('审核设置', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }
}

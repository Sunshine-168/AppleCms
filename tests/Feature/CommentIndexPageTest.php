<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommentIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
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
        $this->assertStringContainsString('用户在播放页发的评论', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('文章页发来的评论', $html);
    }
}

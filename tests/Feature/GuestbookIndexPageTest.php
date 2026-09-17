<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuestbookIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_guestbook_index_is_a_reply_audit_queue_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/guestbooks')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('留言', $html);
        $this->assertStringContainsString('还没有留言', $html);
        $this->assertStringContainsString('待审', $html);
        $this->assertStringContainsString('未回复', $html);
        $this->assertStringContainsString('回复', $html);
        $this->assertStringContainsString('审核设置', $html);
        $this->assertStringContainsString('搜内容、昵称或回复', $html);
        $this->assertStringContainsString('/gbook', $html);
        $this->assertStringContainsString('/admin/video/comments', $html);
        $this->assertStringContainsString('/admin/video/config/comment', $html);
        $this->assertStringContainsString('gbook-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="content"', $html);
        $this->assertStringNotContainsString("title: 'author_name'", $html);
        $this->assertStringNotContainsString('留言管理', $html);
    }
}

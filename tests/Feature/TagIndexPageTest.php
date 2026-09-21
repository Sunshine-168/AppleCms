<?php

namespace Tests\Feature;

use Tests\TestCase;

class TagIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_tag_index_is_a_keyword_library_not_a_log_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tags')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有标签', $html);
        $this->assertStringContainsString('未使用', $html);
        $this->assertStringContainsString('tag-batch', $html);
        $this->assertStringContainsString('新增标签', $html);
        $this->assertStringContainsString('搜标签名', $html);
        $this->assertStringNotContainsString('video-tag-refresh-btn', $html);
        $this->assertStringNotContainsString('创建时间', $html);
    }
}

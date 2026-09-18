<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
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
        $this->assertStringContainsString('/admin/video/arts/create', $html);
        $this->assertStringContainsString('/admin/video/art-types', $html);
        $this->assertStringContainsString('/admin/video/art-tags', $html);
        $this->assertStringContainsString('/admin/video/art-recycle', $html);
        $this->assertStringContainsString('复制一份', $html);
        $this->assertStringContainsString('class="is-on">文章</a>', $html);
        $this->assertStringContainsString('栏目只给文章用', $html);
        $this->assertStringContainsString('art-cat-rail', $html);
        $this->assertStringContainsString('未分栏', $html);
        $this->assertStringContainsString('还没有栏目', $html);
        $this->assertStringContainsString('/admin/video/art-types/create', $html);
        $this->assertStringContainsString('发布到前台', $html);
        $this->assertStringContainsString('推荐属性', $html);
        $this->assertStringContainsString('name="flag"', $html);
        $this->assertStringContainsString('设为推荐', $html);
        $this->assertStringContainsString('取消推荐', $html);
        $this->assertStringContainsString('data-queue="published"', $html);
        $this->assertStringNotContainsString('art-dialog-tpl', $html);
        $this->assertStringNotContainsString('openDialog', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('placeholder="title"', $html);
        $this->assertStringNotContainsString('先去分类里建文章栏目', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }
}

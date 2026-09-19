<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtFormPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_create_art_is_a_page_not_a_dialog(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('写文章', $html);
        $this->assertStringContainsString('entry-layout', $html);
        $this->assertStringContainsString('entry-aside', $html);
        $this->assertStringContainsString('读者看到的标题', $html);
        $this->assertStringContainsString('栏目与展示', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('cms-editor', $html);
        $this->assertStringContainsString('name="blurb"', $html);
        $this->assertStringContainsString('name="seo_title"', $html);
        $this->assertStringContainsString('name="seo_key"', $html);
        $this->assertStringContainsString('name="seo_des"', $html);
        $this->assertStringContainsString('name="published_at"', $html);
        $this->assertStringContainsString('datetime-local', $html);
        $this->assertStringContainsString('name="author"', $html);
        $this->assertStringContainsString('name="source"', $html);
        $this->assertStringContainsString('art-tag-pick', $html);
        $this->assertStringContainsString('搜标签名', $html);
        $this->assertStringContainsString('/admin/video/art-tags/list', $html);
        $this->assertStringContainsString('/admin/video/art-tags', $html);
        $this->assertStringNotContainsString('新标签', $html);
        $this->assertStringNotContainsString('js-art-tag', $html);
        $this->assertStringNotContainsString('name="tag_extra"', $html);
        $this->assertStringContainsString('js-art-flag', $html);
        $this->assertStringContainsString('置顶', $html);
        $this->assertStringContainsString('推荐', $html);
        $this->assertStringContainsString('热门', $html);
        $this->assertStringContainsString('name="sort"', $html);
        $this->assertStringContainsString('/admin/video/arts', $html);
        $this->assertStringContainsString('/admin/video/art-types', $html);
        $this->assertStringContainsString('频道和外链不能挂稿', $html);
        $this->assertStringContainsString('class="is-on">文章</a>', $html);
        $this->assertStringNotContainsString('先去分类里建一个', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('art-dialog-tpl', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_missing_art_edit_is_not_found(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/999999/edit')
            ->assertNotFound();
    }
}

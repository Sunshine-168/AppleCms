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

        $this->assertStringContainsString('写内容', $html);
        $this->assertStringContainsString('entry-layout', $html);
        $this->assertStringContainsString('entry-aside', $html);
        $this->assertStringContainsString('读者看到的标题', $html);
        $this->assertStringContainsString('栏目与展示', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('cms-editor', $html);
        $this->assertStringContainsString('/admin/video/arts', $html);
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

<?php

namespace Tests\Feature;

use Tests\TestCase;

class TypeIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_type_index_is_a_tree_not_a_flat_log(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/types')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('搜分类名', $html);
        $this->assertStringContainsString('js-child', $html);
        $this->assertStringContainsString('/admin/video/types/create', $html);
        $this->assertStringContainsString('type-batch', $html);
        $this->assertStringNotContainsString('video-type-dialog-tpl', $html);
        $this->assertStringNotContainsString('openDialog', $html);
        $this->assertStringNotContainsString('video-type-refresh-btn', $html);
        $this->assertStringNotContainsString('创建时间', $html);
    }
}

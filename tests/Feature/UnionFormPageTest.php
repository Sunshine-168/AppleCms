<?php

namespace Tests\Feature;

use Tests\TestCase;

class UnionFormPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_create_union_is_a_page_not_a_dialog(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/unions/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增资源', $html);
        $this->assertStringContainsString('union-form-page', $html);
        $this->assertStringContainsString('这里只记接口', $html);
        $this->assertStringContainsString('保存并接入采集源', $html);
        $this->assertStringContainsString('name="api_url"', $html);
        $this->assertStringContainsString('/admin/video/unions', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('mod-dialog-tpl', $html);
    }

    public function test_missing_union_edit_is_not_found(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/unions/999999/edit')
            ->assertNotFound();
    }
}

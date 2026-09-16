<?php

namespace Tests\Feature;

use Tests\TestCase;

class TypeFormPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_create_type_is_a_page_not_a_dialog(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/types/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增分类', $html);
        $this->assertStringContainsString('type-form-page', $html);
        $this->assertStringContainsString('先建电影、电视剧这种一级目录', $html);
        $this->assertStringContainsString('保存并添加下级', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('顶级（不挂在任何分类下）', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('video-type-dialog-tpl', $html);
    }

    public function test_create_child_type_names_the_parent(): void
    {
        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/types/list')
            ->assertOk()
            ->json();
        $rows = $list['data']['data'] ?? [];
        $parent = $rows[0] ?? null;
        if (! is_array($parent) || empty($parent['id'])) {
            $this->markTestSkipped('no category to hang under');
        }

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/types/create?parent_id='.$parent['id'])
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('添加下级', $html);
        $this->assertStringContainsString((string) $parent['name'], $html);
        $this->assertStringContainsString('将建在', $html);
    }

    public function test_missing_type_edit_is_not_found(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/types/999999/edit')
            ->assertNotFound();
    }
}

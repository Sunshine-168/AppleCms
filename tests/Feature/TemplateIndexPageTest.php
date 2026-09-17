<?php

namespace Tests\Feature;

use Tests\TestCase;

class TemplateIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_template_index_is_a_page_picker_not_a_raw_file_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/templates')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有打开文件', $html);
        $this->assertStringContainsString('首页', $html);
        $this->assertStringContainsString('整站头尾', $html);
        $this->assertStringContainsString('站点设置 → 外观', $html);
        $this->assertStringContainsString('搜页面', $html);
        $this->assertStringContainsString('tpl-group', $html);
        $this->assertStringContainsString('data-fold', $html);
        $this->assertStringContainsString('tpl-group-toggle', $html);
        $this->assertStringContainsString('aria-expanded="true"', $html);
        $this->assertStringContainsString('laravideo.tpl.fold.', $html);
        $this->assertStringContainsString('插入附件', $html);
        $this->assertStringContainsString('/admin/system/attachments', $html);
        $this->assertStringContainsString('hideFoot', $html);
        $this->assertStringContainsString('还没打开页面，点插入只会复制地址', $html);
        $this->assertStringContainsString('把 Logo、海报拖到这里', $html);
        $this->assertStringContainsString('还没有可用的图', $html);
        $this->assertStringContainsString('没有叫这个名字的文件', $html);
        $this->assertStringContainsString('复制地址', $html);
        $this->assertStringContainsString('搜文件名，如 logo、海报', $html);
        $this->assertStringContainsString('去附件库', $html);
        $this->assertStringContainsString('tpl-picker-card', $html);
        $this->assertStringContainsString('codemirror.min.js', $html);
        $this->assertStringContainsString('blade.js', $html);
        $this->assertStringContainsString('TplCodeEditor', $html);
        $this->assertStringContainsString('saveFile', $html);
        $this->assertStringContainsString('没有改动，不用保存', $html);
        $this->assertStringContainsString('绿色是 Blade', $html);
        $this->assertStringContainsString('@vod', $html);
        $this->assertStringContainsString('紫色是', $html);
        $this->assertStringContainsString('@php', $html);
        $this->assertStringContainsString('Ctrl+S 直接保存', $html);
        $this->assertStringContainsString('id="tpl-find"', $html);
        $this->assertStringContainsString('id="tpl-find-bar"', $html);
        $this->assertStringContainsString('查找：', $html);
        $this->assertStringContainsString('可用 /正则/', $html);
        $this->assertStringContainsString('tpl-meta-pos', $html);
        $this->assertStringContainsString('没有匹配的页面', $html);
        $this->assertStringContainsString('sniffEol', $html);
        $this->assertStringContainsString('loadSeq', $html);
        $this->assertStringNotContainsString('hooks.php', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_code_editor_plugin_serves_blade_mode(): void
    {
        $this->get('/plugin-assets/code-editor/blade.js')
            ->assertOk()
            ->assertSee('laravel-blade', false)
            ->assertSee('BLADE_WORDS', false)
            ->assertSee('vod[A-Za-z]*', false)
            ->assertSee('exprToken', false)
            ->assertSee('after-dir', false)
            ->assertSee('after-php', false)
            ->assertDontSee('eatParenArgs', false);

        $this->get('/plugin-assets/code-editor/boot.js')
            ->assertOk()
            ->assertSee('clearHistory', false)
            ->assertSee('查找', false)
            ->assertSee('findPersistent', false)
            ->assertSee('size: fit', false)
            ->assertSee('bottom: false', false)
            ->assertDontSee('bottom: true', false);

        $this->get('/plugin-assets/code-editor/vendor/codemirror.min.js')
            ->assertOk();
    }

    public function test_can_read_detail_template(): void
    {
        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/templates/read?path=vod/detail.blade.php')
            ->assertOk()
            ->json();

        $this->assertSame(0, (int) ($json['code'] ?? 1));
        $content = (string) ($json['data']['content'] ?? '');
        $this->assertStringContainsString('@vodSource', $content);
        $this->assertStringContainsString('@endvodSource', $content);
        $this->assertStringContainsString('@csrf', $content);
        $this->assertStringContainsString('@extends', $content);
    }

    public function test_rejects_path_escape_and_can_read_player(): void
    {
        $bad = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/templates/read?path=../layout.blade.php')
            ->assertOk()
            ->json();
        $this->assertNotSame(0, (int) ($bad['code'] ?? 0));

        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/templates/read?path=vod/player.blade.php')
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($json['code'] ?? 1));
        $content = (string) ($json['data']['content'] ?? '');
        $this->assertStringContainsString('@includeIf', $content);
        $this->assertStringContainsString('@json', $content);
    }

    public function test_backup_existing_and_reject_missing_or_escaped_save(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/templates/backup', ['path' => 'partials/paginate.blade.php'])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1));
        $this->assertGreaterThan(0, (int) ($ok['data']['backup_at'] ?? 0));

        $missing = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/templates/save', [
                'path' => 'vod/not-a-template.blade.php',
                'content' => '@php echo 1; @endphp',
            ])
            ->assertOk()
            ->json();
        $this->assertNotSame(0, (int) ($missing['code'] ?? 0));
        $this->assertFileDoesNotExist(resource_path('views/themes/default/vod/not-a-template.blade.php'));

        $escaped = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/templates/save', [
                'path' => '../layout.blade.php',
                'content' => 'x',
            ])
            ->assertOk()
            ->json();
        $this->assertNotSame(0, (int) ($escaped['code'] ?? 0));
    }
}

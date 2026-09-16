<?php

namespace Tests\Feature;

use Tests\TestCase;

class MakeIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_make_index_is_a_laracms_style_workbench(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/make')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('全页缓存', $html);
        $this->assertStringContainsString('启用，访客看到的是刚生成好的页面', $html);
        $this->assertStringContainsString('保存多久', $html);
        $this->assertStringContainsString('改内容后马上换新', $html);
        $this->assertStringContainsString('预热常用页', $html);
        $this->assertStringContainsString('清空已存页面', $html);
        $this->assertStringContainsString('磁盘静态页', $html);
        $this->assertStringContainsString('开始生成', $html);
        $this->assertStringContainsString('删掉静态文件', $html);
        $this->assertStringContainsString('启用，允许在后台生成静态文件', $html);
        $this->assertStringContainsString('已有文件仍可分批删除', $html);
        $this->assertStringContainsString('生成地图', $html);
        $this->assertStringContainsString('生成 RSS', $html);
        $this->assertStringContainsString('diskHtmlProgress', $html);
        $this->assertStringContainsString('html-cache-page', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('make-result', $html);
        $this->assertStringNotContainsString('JSON.stringify', $html);
        $this->assertStringNotContainsString('生成首页', $html);
        $this->assertStringNotContainsString('全部生成', $html);
        $this->assertStringNotContainsString('TTL（秒', $html);
    }
}

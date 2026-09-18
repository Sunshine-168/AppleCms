<?php

namespace Tests\Feature;

use Tests\TestCase;

class MakeIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_make_index_is_a_laracms_style_workbench(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/make')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('make-index', $html);
        $this->assertStringContainsString('磁盘静态', $html);
        $this->assertStringContainsString('生成选项', $html);
        $this->assertStringContainsString('视频分类', $html);
        $this->assertStringContainsString('全部分类', $html);
        $this->assertStringContainsString('当天内容', $html);
        $this->assertStringContainsString('一键当天', $html);
        $this->assertStringContainsString('生成首页', $html);
        $this->assertStringContainsString('生成地图', $html);
        $this->assertStringContainsString('生成 RSS', $html);
        $this->assertStringContainsString('全页缓存', $html);
        $this->assertStringContainsString('启用，访客看到的是刚生成好的页面', $html);
        $this->assertStringContainsString('保存多久', $html);
        $this->assertStringContainsString('改内容后马上换新', $html);
        $this->assertStringContainsString('预热常用页', $html);
        $this->assertStringContainsString('清空已存页面', $html);
        $this->assertStringContainsString('启用，允许在后台生成静态文件', $html);
        $this->assertStringContainsString('disk-html-enable', $html);
        $this->assertStringContainsString('disk-html-meta', $html);
        $this->assertStringContainsString('diskHtmlProgress', $html);
        $this->assertStringContainsString('html-cache-page', $html);
        $this->assertStringContainsString('请先勾选', $html);
        $this->assertStringContainsString('sitemap.xml', $html);
        $this->assertStringContainsString('含列表首页，不是 WAP', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('make-result', $html);
        $this->assertStringNotContainsString('JSON.stringify', $html);
        $this->assertStringNotContainsString('生成WAP', $html);
        $this->assertStringNotContainsString('wap_index', $html);
        $this->assertStringNotContainsString('模板市场', $html);
        $this->assertStringNotContainsString('全部生成', $html);
        $this->assertStringNotContainsString('TTL（秒', $html);
    }
}

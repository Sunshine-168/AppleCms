<?php

namespace Tests\Feature;

use Tests\TestCase;

class HelpWorkbenchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_usage_guide_is_default(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/help')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('help-index', $html);
        $this->assertStringContainsString('说明', $html);
        $this->assertStringContainsString('怎么用', $html);
        $this->assertStringContainsString('后台', $html);
        $this->assertStringContainsString('模板', $html);
        $this->assertStringContainsString('标签', $html);
        $this->assertStringContainsString('环境', $html);
        $this->assertStringContainsString('伪静态', $html);
        $this->assertStringContainsString('定时', $html);
        $this->assertStringContainsString('Docker', $html);
        $this->assertStringContainsString('第一次建议按这个顺序', $html);
        $this->assertStringContainsString('建分类', $html);
        $this->assertStringContainsString('加影片', $html);
        $this->assertStringContainsString('采集入库', $html);
        $this->assertStringContainsString('常见问题', $html);
        $this->assertStringContainsString('换了模板，前台还是旧样子', $html);
        $this->assertStringContainsString('不想让搜索引擎抓某几页', $html);
        $this->assertStringContainsString('站点地图', $html);
        $this->assertStringContainsString('/sitemap.xml', $html);
        $this->assertStringContainsString('/admin/video/settings', $html);
        $this->assertStringContainsString('/admin/video/types', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringContainsString('/admin/help?topic=admin', $html);
        $this->assertStringContainsString('/admin/help?topic=templates', $html);
        $this->assertStringContainsString('/admin/help?topic=tags', $html);
        $this->assertStringContainsString('/admin/help?topic=rewrite', $html);
        $this->assertStringContainsString('href="/admin/help"', $html);
        $this->assertStringNotContainsString('href="/admin/video/rewrite"', $html);
        $this->assertStringNotContainsString('建栏目', $html);
        $this->assertStringNotContainsString('内容模型', $html);
        $this->assertStringNotContainsString('cms:install', $html);
        $this->assertStringNotContainsString('全页缓存', $html);
    }

    public function test_template_and_tag_guides(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/help?topic=templates')
            ->assertOk()
            ->assertSee('目录长什么样')
            ->assertSee('index/index.blade.php')
            ->assertSee('vod/detail.blade.php')
            ->assertSee('@vod')
            ->assertSee('@vodSeo')
            ->assertSee('/admin/video/wizard');

        $this->get('/admin/help?topic=tags')
            ->assertOk()
            ->assertSee('常用例子')
            ->assertSee('@vodType')
            ->assertSee('@vodArt')
            ->assertSee('@vodPaginate')
            ->assertSee('@manga')
            ->assertSee('必须改名');
    }

    public function test_admin_module_catalog(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/help?topic=admin')
            ->assertOk()
            ->assertSee('仪表盘')
            ->assertSee('采集源')
            ->assertSee('影片')
            ->assertSee('静态生成')
            ->assertSee('/admin/video/collects')
            ->assertSee('/admin/stats')
            ->assertSee('/admin/video/config/ai')
            ->assertSee('AI SEO')
            ->assertSee('说明')
            ->assertSee('不跟运营权限走');
    }

    public function test_env_laravel_and_docker_topics(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/help?topic=env')
            ->assertOk()
            ->assertSee('PHP 环境')
            ->assertSee('MySQL')
            ->assertSee('nginx')
            ->assertSee('CREATE DATABASE video')
            ->assertSee('try_files')
            ->assertSee('搜索引擎 robots.txt')
            ->assertSee('/robots.txt');

        $this->get('/admin/help?topic=schedule')
            ->assertOk()
            ->assertSee('定时任务和队列')
            ->assertSee('video:collect-due')
            ->assertSee('schedule:run')
            ->assertSee('video:hits-reset');

        $this->get('/admin/help?topic=laravel')
            ->assertOk()
            ->assertSee('用 Laravel 命令行安装')
            ->assertSee('php artisan video:install')
            ->assertSee('/admin/help?topic=env');

        $this->get('/admin/help?topic=docker')
            ->assertOk()
            ->assertSee('用 Docker 安装')
            ->assertSee('docker compose up --build')
            ->assertSee('127.0.0.1:8010')
            ->assertDontSee('admin@example.com')
            ->assertDontSee('127.0.0.1:8000');

        $this->get('/admin/help?topic=rewrite')
            ->assertOk()
            ->assertSee('伪静态')
            ->assertSee('当前写法')
            ->assertSee('本站路由')
            ->assertSee('Nginx')
            ->assertSee('Apache')
            ->assertSee('rewrite-index')
            ->assertSee('/admin/video/settings?tab=more');
    }

    public function test_unknown_topic_falls_back_to_usage(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/help?topic=not-a-topic')
            ->assertOk()
            ->assertSee('第一次建议按这个顺序');
    }

    public function test_help_follows_admin_ui_locale(): void
    {
        $session = [
            'admin_uid' => 1,
            'admin_username' => 'admin',
            'admin_ui_locale' => 'en',
        ];

        $this->withSession($session)
            ->get('/admin/help')
            ->assertOk()
            ->assertSee('Help')
            ->assertSee('Getting started')
            ->assertSee('Suggested first steps')
            ->assertSee('What the sidebar is for')
            ->assertSee('/sitemap.xml')
            ->assertDontSee('第一次建议按这个顺序')
            ->assertDontSee('侧栏对应什么');

        $this->withSession($session)
            ->get('/admin/help?topic=admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Collect sources')
            ->assertDontSee('按侧栏把后台每一项说一遍');

        $this->withSession($session)
            ->get('/admin/help?topic=env')
            ->assertOk()
            ->assertSee('What you need to go live')
            ->assertSee('robots.txt')
            ->assertDontSee('上线要准备什么');

        $this->withSession($session)
            ->get('/admin/help?topic=rewrite')
            ->assertOk()
            ->assertSee('Pretty URLs')
            ->assertSee('Nginx')
            ->assertDontSee('当前写法');

        $this->withSession($session)
            ->get('/admin/help?topic=templates')
            ->assertOk()
            ->assertSee('What the folder looks like')
            ->assertSee('@vodSeo')
            ->assertDontSee('目录长什么样');
    }
}

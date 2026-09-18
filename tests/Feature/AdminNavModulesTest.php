<?php

namespace Tests\Feature;

use App\Support\AdminNav;
use App\Support\Plugins\PluginHost;
use App\Support\Plugins\PluginManager;
use Tests\TestCase;

class AdminNavModulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_top_bar_has_workspaces_not_a_super_console(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('mod-nav-top', $html);
        $this->assertStringContainsString('工作台', $html);
        $this->assertStringContainsString('影片', $html);
        $this->assertStringContainsString('文章', $html);
        $this->assertStringContainsString('采集', $html);
        $this->assertStringContainsString('会员', $html);
        $this->assertStringContainsString('站点', $html);
        $this->assertStringContainsString('系统', $html);
        $this->assertStringContainsString('插件', $html);
        $this->assertStringContainsString('搜功能', $html);
        $this->assertStringContainsString('href="/admin/plugins"', $html);
        $this->assertStringNotContainsString('>漫画<', $html);
        $this->assertStringNotContainsString('直播', $html);
        $this->assertStringNotContainsString('入金', $html);
        $this->assertStringNotContainsString('监控告警', $html);
    }

    public function test_plugins_are_their_own_workspace(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="is-on">插件</a>', $html);
        $this->assertStringContainsString('插件管理', $html);
        $this->assertStringNotContainsString('>数据库备份<', $html);
    }

    public function test_sidebar_only_shows_the_current_workspace(): void
    {
        $backup = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/backup')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('class="is-on">系统</a>', $backup);
        $this->assertStringContainsString('>数据库<', $backup);
        $this->assertStringContainsString('db-tabs', $backup);
        $this->assertStringContainsString('>管理员<', $backup);
        $this->assertStringContainsString('>角色<', $backup);
        $this->assertStringContainsString('>菜单<', $backup);
        $this->assertStringContainsString('>日志<', $backup);
        $this->assertStringContainsString('>监控<', $backup);
        $this->assertStringNotContainsString('>操作日志<', $backup);
        $this->assertStringNotContainsString('>系统日志<', $backup);
        $this->assertStringNotContainsString('>分类<', $backup);
        $this->assertStringNotContainsString('>内容质量<', $backup);

        $quality = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/quality')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('class="is-on">影片</a>', $quality);
        $this->assertStringContainsString('内容质量', $quality);
        $this->assertStringContainsString('>分类<', $quality);
        $this->assertStringNotContainsString('>数据库备份<', $quality);
    }

    public function test_vod_sidebar_puts_plugin_boards_in_the_main_list(): void
    {
        $manager = app(PluginManager::class);
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*更多\s*<\/summary>/u',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $html
        );

        $more = $this->topNavFoldChunk($html, '更多');
        $foldEnd = strpos($more, '</details>');
        $fold = $foldEnd === false ? $more : substr($more, 0, $foldEnd);
        $this->assertStringContainsString('<span>标签</span>', $fold);
        $this->assertStringContainsString('<span>回收站</span>', $fold);
        $this->assertStringNotContainsString('/admin/video/arts', $fold);
        $this->assertStringNotContainsString('/admin/video/mangas', $fold);
        if ($manager->isEnabled('chatroom')) {
            $this->assertStringContainsString('/admin/video/chat_messages', $fold);
        } else {
            $this->assertStringNotContainsString('/admin/video/chat_messages', $html);
        }
        if ($manager->isEnabled('danmaku')) {
            $this->assertStringContainsString('/admin/video/danmaku', $fold);
        } else {
            $this->assertStringNotContainsString('/admin/video/danmaku', $html);
        }
        if ($manager->isEnabled('manga')) {
            $this->assertStringContainsString('/admin/video/mangas', $html);
            $this->assertStringNotContainsString('/admin/video/mangas?desk=pics', $html);
            $this->assertStringNotContainsString('nav-fold-nested', $html);
        }
        if ($manager->isEnabled('cj_rule')) {
            $this->assertStringNotContainsString('/admin/video/cj', $html);
        }

        $vod = AdminNav::groupsFor('vod');
        $this->assertSame('nav.more', $vod[0]['fold']['label'] ?? '');
        $moreUrls = array_column($vod[0]['fold']['items'] ?? [], 'url');
        $urls = array_column($vod[0]['items'] ?? [], 'url');
        $this->assertContains('/admin/video/tags', $moreUrls);
        $this->assertNotContains('/admin/video/arts', $moreUrls);
        $this->assertNotContains('/admin/video/arts', $urls);
        $this->assertContains('/admin/video/tools/recycle', $moreUrls);
        $this->assertNotContains('/admin/video/mangas', $moreUrls);
        if ($manager->isEnabled('manga')) {
            $this->assertContains('/admin/video/mangas', $urls);
        }
        if ($manager->isEnabled('chatroom')) {
            $this->assertContains('/admin/video/chat_messages', $moreUrls);
            $this->assertNotContains('/admin/video/chat_messages', $urls);
        }
        if ($manager->isEnabled('danmaku')) {
            $this->assertContains('/admin/video/danmaku', $moreUrls);
            $this->assertNotContains('/admin/video/danmaku', $urls);
        }
        foreach ($vod as $group) {
            $this->assertNotSame('nav.plugins', $group['fold']['label'] ?? '');
        }
        foreach (array_merge($vod[0]['items'] ?? [], $vod[0]['fold']['items'] ?? []) as $item) {
            $this->assertArrayNotHasKey('children', $item);
        }

        $pluginWorkspace = AdminNav::groupsFor('plugin');
        $this->assertArrayNotHasKey('fold', $pluginWorkspace[0]);
        $pluginItemUrls = array_column($pluginWorkspace[0]['items'] ?? [], 'url');
        $this->assertContains('/admin/plugins', $pluginItemUrls);
        foreach ($pluginWorkspace[0]['items'] ?? [] as $item) {
            if (in_array($item['url'] ?? '', ['/admin/video/adverts', '/admin/video/flinks', '/admin/video/publish_pages'], true)) {
                $this->assertArrayNotHasKey('children', $item);
            }
        }

        $content = app(PluginHost::class)->sidebarFoldItems('content');
        $manga = null;
        foreach ($content as $item) {
            $href = explode('?', (string) ($item['url'] ?? ''))[0];
            $this->assertNotSame('/admin/video/manga_chapters', $href);
            $this->assertNotSame('nav.manga_chapters', $item['label'] ?? '');
            if ($href === '/admin/video/mangas') {
                $manga = $item;
            }
        }
        if ($manager->isEnabled('manga')) {
            $this->assertIsArray($manga);
            $this->assertNotEmpty($manga['children'] ?? []);
            $this->assertContains('nav.manga_chapters', array_column($manga['children'], 'label'));
        }
    }

    public function test_member_sidebar_puts_plugin_boards_in_the_main_list(): void
    {
        $manager = app(PluginManager::class);
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/members')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*更多\s*<\/summary>/u',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $html
        );

        $more = $this->topNavFoldChunk($html, '更多');
        $this->assertStringContainsString('/admin/video/activity', $more);
        $this->assertStringNotContainsString('/admin/video/mall_goods', $more);
        if ($manager->isEnabled('mall')) {
            $this->assertStringContainsString('/admin/video/mall_goods', $html);
            $this->assertStringNotContainsString('/admin/video/mall_goods?desk=ship', $html);
            $this->assertStringNotContainsString('nav-fold-nested', $html);
        }
        if ($manager->isEnabled('coupon')) {
            $this->assertStringContainsString('/admin/video/coupons', $more);
            $this->assertStringContainsString('/admin/video/coupons', $html);
        }

        $member = AdminNav::groupsFor('member');
        $this->assertSame('nav.more', $member[0]['fold']['label'] ?? '');
        $urls = array_column($member[0]['items'] ?? [], 'url');
        $foldUrls = array_column($member[0]['fold']['items'] ?? [], 'url');
        $this->assertNotContains('/admin/video/mall_goods', $foldUrls);
        $this->assertNotContains('/admin/video/coupons', $urls);
        $this->assertNotContains('/admin/video/activity', $urls);
        $this->assertContains('/admin/video/activity', $foldUrls);
        if ($manager->isEnabled('mall')) {
            $this->assertContains('/admin/video/mall_goods', $urls);
        }
        if ($manager->isEnabled('coupon')) {
            $this->assertContains('/admin/video/coupons', $foldUrls);
        }
        foreach ($member as $group) {
            $this->assertNotSame('nav.plugins', $group['fold']['label'] ?? '');
        }
        foreach (array_merge($member[0]['items'] ?? [], $member[0]['fold']['items'] ?? []) as $item) {
            $this->assertArrayNotHasKey('children', $item);
        }
    }

    public function test_plugin_boards_stay_in_their_workspace_not_stolen_by_equal_prefix(): void
    {
        $this->assertSame('site', AdminNav::currentModule('/admin/video/adverts'));
        $this->assertSame('site', AdminNav::currentModule('/admin/video/flinks'));
        $this->assertSame('site', AdminNav::currentModule('/admin/video/publish_pages'));
        $this->assertSame('member', AdminNav::currentModule('/admin/video/mall_goods'));
        $this->assertSame('member', AdminNav::currentModule('/admin/video/mall_orders'));
        $this->assertSame('member', AdminNav::currentModule('/admin/video/coupons'));
        $this->assertSame('vod', AdminNav::currentModule('/admin/video/mangas'));
        $this->assertSame('vod', AdminNav::currentModule('/admin/video/manga_chapters'));
        $this->assertSame('vod', AdminNav::currentModule('/admin/video/chat_messages'));
        $this->assertSame('vod', AdminNav::currentModule('/admin/video/danmaku'));
        $this->assertSame('collect', AdminNav::currentModule('/admin/video/cj'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/arts'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/arts/create'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-types'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-tags'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-recycle'));
        $this->assertSame('vod', AdminNav::currentModule('/admin/video/types'));
        $this->assertSame('plugin', AdminNav::currentModule('/admin/plugins'));

        $collectUrls = array_column(AdminNav::groupsFor('collect')[0]['items'] ?? [], 'url');
        if (app(PluginManager::class)->isEnabled('cj_rule')) {
            $this->assertContains('/admin/video/cj', $collectUrls);
        } else {
            $this->assertNotContains('/admin/video/cj', $collectUrls);
        }
    }

    public function test_site_sidebar_puts_plugin_boards_in_the_main_list(): void
    {
        $site = AdminNav::groupsFor('site');
        foreach ($site as $group) {
            $this->assertNotSame('nav.plugins', $group['fold']['label'] ?? '');
        }
        $urls = array_column($site[0]['items'] ?? [], 'url');
        $this->assertContains('/admin/video/adverts', $urls);
        $this->assertContains('/admin/video/flinks', $urls);
        $this->assertContains('/admin/video/publish_pages', $urls);
        $this->assertNotContains('/admin/video/ads', $urls);
        $this->assertNotContains('/admin/video/links', $urls);
    }

    private function topNavFoldChunk(string $html, string $summary): string
    {
        $needle = '<summary>'.$summary.'</summary>';
        $start = strpos($html, $needle);
        $this->assertNotFalse($start, 'missing nav-fold summary '.$summary);
        $from = substr($html, $start);
        if (preg_match('/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>(?!'.preg_quote($summary, '/').')/u', $from, $m, PREG_OFFSET_CAPTURE)) {
            return substr($from, 0, (int) $m[0][1]);
        }

        return $from;
    }
}

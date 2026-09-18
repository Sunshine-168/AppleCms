<?php

namespace Tests\Feature;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoCjRule;
use App\Models\Video\VideoModel;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Plugins\CjRule\Models\CjRuleLog;
use Plugins\CjRule\Services\CjRuleService;
use Plugins\CjRule\Support\CssSelector;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Tests\TestCase;

class CjRulePluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
        Http::preventStrayRequests();
        if (! app(PluginManager::class)->isEnabled('cj_rule')) {
            $this->markTestSkipped('cj_rule plugin disabled');
        }
    }

    public function test_css_selector_to_xpath(): void
    {
        $this->assertSame(
            '//article[contains(concat(\' \', normalize-space(@class), \' \'), \' post \')]',
            CssSelector::toXPath('article.post')
        );
        $this->assertSame('.//h2//a', CssSelector::toXPath('h2 a', true));
        $this->assertSame('//*[@id="list"]', CssSelector::toXPath('#list'));
    }

    public function test_board_is_a_collect_workbench(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/cj')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('cj-board', $html);
        $this->assertStringContainsString('desk-board', $html);
        $this->assertStringContainsString('新增', $html);
        $this->assertStringContainsString('运行日志', $html);
        $this->assertStringContainsString('从网页、RSS 或 JSON', $html);
        $this->assertStringContainsString('网站采集', $html);
        $this->assertStringContainsString('/admin/video/cj?desk=form', $html);
        $this->assertStringContainsString('/admin/video/cj?desk=logs', $html);
        $this->assertStringNotContainsString('自定义规则', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('>ID</th>', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $html
        );
    }

    public function test_create_form_puts_website_first(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/cj?desk=form')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('新建网站采集', $html);
        $this->assertStringContainsString('写入到', $html);
        $this->assertStringContainsString('name="into"', $html);
        $this->assertStringContainsString('影片', $html);
        $this->assertStringContainsString('文章', $html);
        $this->assertStringContainsString('漫画', $html);
        $this->assertStringContainsString('网站页面', $html);
        $this->assertStringContainsString('从列表页抓标题，可翻页、进详情取简介', $html);
        $this->assertStringContainsString('列表条目', $html);
        $this->assertStringContainsString('标题链接', $html);
        $this->assertStringContainsString('详情简介', $html);
        $this->assertStringContainsString('最多翻几页', $html);
        $this->assertStringContainsString('下一页按钮', $html);
        $this->assertStringContainsString('试抓', $html);
        $this->assertStringContainsString('>添加<', $html);
        $this->assertStringContainsString('有播放地址时直接上架', $html);
        $this->assertStringNotContainsString('自定义规则', $html);
        $this->assertStringNotContainsString('新建采集源', $html);
        $this->assertStringNotContainsString('HTML 选择器', $html);
        $this->assertStringNotContainsString('条目 item_selector', $html);
    }

    public function test_store_website_source_keeps_pagination(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/save', [
                'name' => '官网更新',
                'type' => 'html',
                'source_url' => 'https://example.com/news',
                'type_id' => 0,
                'interval_minutes' => 90,
                'limit_items' => 20,
                'status' => '1',
                'item_selector' => 'article.post',
                'link_selector' => 'h2 a',
                'title_selector' => 'h2 a',
                'detail_content_selector' => 'article.body',
                'page_count' => 3,
                'page_url' => 'https://example.com/news?page={page}',
                'next_selector' => 'a.next',
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));

        $row = VideoCjRule::query()->where('name', '官网更新')->first();
        $this->assertNotNull($row);
        $this->assertSame('html', $row->type);
        $this->assertSame('https://example.com/news', $row->url);
        $this->assertSame(3, (int) ($row->options['page_count'] ?? 0));
        $this->assertSame('https://example.com/news?page={page}', $row->options['page_url'] ?? null);
        $this->assertSame('a.next', $row->options['next_selector'] ?? null);
        $this->assertSame('vod', $row->options['into'] ?? null);
        $this->assertSame(0, (int) $row->publish_immediately);
    }

    public function test_store_can_target_arts(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/save', [
                'name' => '资讯列表',
                'type' => 'html',
                'into' => 'art',
                'source_url' => 'https://example.com/arts',
                'item_selector' => 'article.post',
                'link_selector' => 'h2 a',
                'status' => '1',
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $row = VideoCjRule::query()->where('name', '资讯列表')->first();
        $this->assertNotNull($row);
        $this->assertSame('art', $row->options['into'] ?? null);
    }

    public function test_preview_does_not_write_videos_or_logs(): void
    {
        Http::fake([
            'https://example.com/blog' => Http::response(
                '<html><body><article class="post"><h2><a href="/p/one">Preview One</a></h2><p class="excerpt">Sum</p></article></body></html>',
                200
            ),
        ]);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/try', [
                'name' => '试抓',
                'type' => 'html',
                'source_url' => 'https://example.com/blog',
                'item_selector' => 'article.post',
                'link_selector' => 'h2 a',
                'title_selector' => 'h2 a',
                'summary_selector' => '.excerpt',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.items.0.title', 'Preview One');

        $this->assertSame(0, VideoModel::query()->where('title', 'Preview One')->count());
        $this->assertSame(0, CjRuleLog::query()->count());
    }

    public function test_html_list_and_detail_creates_videos_and_dedupes(): void
    {
        Http::fake([
            'https://example.com/blog' => Http::response(
                '<html><body>'.
                '<article class="post"><h2><a href="/p/one">First Film</a></h2><p class="excerpt">Sum one</p></article>'.
                '<article class="post"><h2><a href="/p/two">Second Film</a></h2><p class="excerpt">Sum two</p></article>'.
                '</body></html>',
                200
            ),
            'https://example.com/p/one' => Http::response('<html><article class="body">Hello <strong>world</strong></article>', 200),
            'https://example.com/p/two' => Http::response('<html><article class="body">Second body</article>', 200),
        ]);

        $rule = $this->makeRule('html', 'https://example.com/blog', [
            'item_selector' => 'article.post',
            'link_selector' => 'h2 a',
            'title_selector' => 'h2 a',
            'summary_selector' => '.excerpt',
            'detail_content_selector' => 'article.body',
        ]);

        $first = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($first['code'] ?? 1), (string) ($first['msg'] ?? ''));
        $this->assertSame(2, $first['data']['created']);

        $one = VideoModel::query()->where('title', 'First Film')->first();
        $this->assertNotNull($one);
        $this->assertStringContainsString('Hello', (string) $one->description);
        $this->assertSame(0, (int) $one->status);

        $second = app(CjRuleService::class)->importRule($rule->fresh());
        $this->assertSame(0, (int) ($second['code'] ?? 1));
        $this->assertSame(0, $second['data']['created']);
        $this->assertSame(2, $second['data']['skipped']);
        $this->assertGreaterThanOrEqual(2, CjRuleLog::query()->where('rule_id', $rule->id)->count());
    }

    public function test_json_play_url_can_publish(): void
    {
        Http::fake([
            'https://example.com/api/posts' => Http::response([
                'data' => [
                    [
                        'uuid' => 'u-1',
                        'headline' => 'JSON Film',
                        'href' => 'https://example.com/j/1',
                        'body' => 'JSON body',
                        'thumb' => 'https://example.com/t.jpg',
                        'play' => 'https://cdn.example.com/a.m3u8',
                    ],
                ],
            ], 200),
        ]);

        $rule = $this->makeRule('json', 'https://example.com/api/posts', [
            'list_path' => 'data',
            'title_key' => 'headline',
            'link_key' => 'href',
            'content_key' => 'body',
            'guid_key' => 'uuid',
            'cover_key' => 'thumb',
            'play_key' => 'play',
        ], publish: true);

        $result = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($result['code'] ?? 1), (string) ($result['msg'] ?? ''));
        $video = VideoModel::query()->where('title', 'JSON Film')->first();
        $this->assertNotNull($video);
        $this->assertSame('https://example.com/t.jpg', $video->cover);
        $this->assertSame(1, (int) $video->status);
        $this->assertSame('https://cdn.example.com/a.m3u8', $video->episodes()->value('url'));
    }

    public function test_private_source_url_is_rejected(): void
    {
        $rule = $this->makeRule('rss', 'http://127.0.0.1/feed.xml', []);
        $result = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(1, (int) ($result['code'] ?? 0));
        $this->assertStringContainsString('内网', (string) ($result['msg'] ?? ''));
    }

    public function test_html_paginates_with_page_query(): void
    {
        Http::fake([
            'https://example.com/blog' => Http::response(
                '<html><body><article class="post"><h2><a href="/p/one">First Film</a></h2></article></body></html>',
                200
            ),
            'https://example.com/blog?page=2' => Http::response(
                '<html><body><article class="post"><h2><a href="/p/two">Second Film</a></h2></article></body></html>',
                200
            ),
        ]);

        $rule = $this->makeRule('html', 'https://example.com/blog', [
            'item_selector' => 'article.post',
            'link_selector' => 'h2 a',
            'title_selector' => 'h2 a',
            'page_count' => 2,
        ]);

        $result = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($result['code'] ?? 1), (string) ($result['msg'] ?? ''));
        $this->assertSame(2, $result['data']['created']);
        $this->assertNotNull(VideoModel::query()->where('title', 'First Film')->first());
        $this->assertNotNull(VideoModel::query()->where('title', 'Second Film')->first());
    }

    public function test_html_follows_next_page_link(): void
    {
        Http::fake([
            'https://example.com/news' => Http::response(
                '<html><body>'.
                '<li class="item"><a href="/n/a">Alpha</a></li>'.
                '<a class="next" href="/news?p=2">下一页</a>'.
                '</body></html>',
                200
            ),
            'https://example.com/news?p=2' => Http::response(
                '<html><body><li class="item"><a href="/n/b">Beta</a></li></body></html>',
                200
            ),
        ]);

        $rule = $this->makeRule('html', 'https://example.com/news', [
            'item_selector' => 'li.item',
            'link_selector' => 'a',
            'page_count' => 2,
            'next_selector' => 'a.next',
        ]);

        $result = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($result['code'] ?? 1), (string) ($result['msg'] ?? ''));
        $this->assertSame(2, $result['data']['created']);
        $this->assertNotNull(VideoModel::query()->where('title', 'Alpha')->first());
        $this->assertNotNull(VideoModel::query()->where('title', 'Beta')->first());
    }

    public function test_html_page_url_template(): void
    {
        Http::fake([
            'https://example.com/list_1.html' => Http::response(
                '<html><body><article class="post"><h2><a href="/p/one">One</a></h2></article></body></html>',
                200
            ),
            'https://example.com/list_2.html' => Http::response(
                '<html><body><article class="post"><h2><a href="/p/two">Two</a></h2></article></body></html>',
                200
            ),
        ]);

        $rule = $this->makeRule('html', 'https://example.com/list_1.html', [
            'item_selector' => 'article.post',
            'link_selector' => 'h2 a',
            'page_count' => 2,
            'page_url' => 'https://example.com/list_{page}.html',
        ]);

        $result = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($result['code'] ?? 1), (string) ($result['msg'] ?? ''));
        $this->assertSame(2, $result['data']['created']);
    }

    public function test_html_import_can_write_arts_without_videos(): void
    {
        Http::fake([
            'https://example.com/news' => Http::response(
                '<html><body>'.
                '<article class="post"><h2><a href="/n/one">Art One</a></h2><p class="excerpt">Lead one</p></article>'.
                '<article class="post"><h2><a href="/n/two">Art Two</a></h2><p class="excerpt">Lead two</p></article>'.
                '</body></html>',
                200
            ),
            'https://example.com/n/one' => Http::response('<html><article class="body">Hello <strong>article</strong></article>', 200),
            'https://example.com/n/two' => Http::response('<html><article class="body">Second article</article>', 200),
        ]);

        $rule = $this->makeRule('html', 'https://example.com/news', [
            'into' => 'art',
            'item_selector' => 'article.post',
            'link_selector' => 'h2 a',
            'title_selector' => 'h2 a',
            'summary_selector' => '.excerpt',
            'detail_content_selector' => 'article.body',
        ]);

        $first = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($first['code'] ?? 1), (string) ($first['msg'] ?? ''));
        $this->assertSame(2, $first['data']['created']);
        $this->assertSame(0, VideoModel::query()->count());
        $this->assertSame(2, VideoArt::query()->count());
        $one = VideoArt::query()->where('title', 'Art One')->first();
        $this->assertNotNull($one);
        $this->assertStringContainsString('Hello', (string) $one->content);
        $this->assertSame(0, (int) $one->status);

        $second = app(CjRuleService::class)->importRule($rule->fresh());
        $this->assertSame(0, (int) ($second['code'] ?? 1));
        $this->assertSame(0, $second['data']['created']);
        $this->assertSame(2, $second['data']['skipped']);
    }

    public function test_html_import_can_write_manga_work_without_chapters(): void
    {
        if (! app(CjRuleService::class)->mangaReady()) {
            $this->markTestSkipped('manga plugin disabled');
        }
        Http::fake([
            'https://example.com/comics' => Http::response(
                '<html><body>'.
                '<article class="post"><h2><a href="/c/one">Manga One</a></h2><p class="excerpt">Blurb one</p></article>'.
                '<article class="post"><h2><a href="/c/two">Manga Two</a></h2><p class="excerpt">Blurb two</p></article>'.
                '</body></html>',
                200
            ),
        ]);

        $rule = $this->makeRule('html', 'https://example.com/comics', [
            'into' => 'manga',
            'item_selector' => 'article.post',
            'link_selector' => 'h2 a',
            'title_selector' => 'h2 a',
            'summary_selector' => '.excerpt',
        ]);

        $result = app(CjRuleService::class)->importRule($rule);
        $this->assertSame(0, (int) ($result['code'] ?? 1), (string) ($result['msg'] ?? ''));
        $this->assertSame(2, $result['data']['created']);
        $this->assertSame(0, VideoModel::query()->count());
        $this->assertSame(0, VideoArt::query()->count());
        $this->assertSame(2, Manga::query()->count());
        $one = Manga::query()->where('title', 'Manga One')->first();
        $this->assertNotNull($one);
        $this->assertSame(0, (int) $one->status);
        $this->assertStringContainsString('Blurb one', (string) $one->content);
        $this->assertSame(0, MangaChapter::query()->count());
        if (Schema::hasTable('plugin_manga_pics')) {
            $this->assertSame(0, \Plugins\Manga\Models\MangaPic::query()->count());
        }
    }

    public function test_manga_into_fails_when_plugin_off(): void
    {
        $manager = app(PluginManager::class);
        $was = $manager->isEnabled('manga');
        try {
            $manager->setEnabled('manga', false);
            $fail = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
                ->post('/admin/video/cj/save', [
                    'name' => '漫画列表',
                    'type' => 'html',
                    'into' => 'manga',
                    'source_url' => 'https://example.com/comics',
                    'item_selector' => 'article.post',
                ])
                ->assertOk()
                ->json();
            $this->assertSame(1, (int) ($fail['code'] ?? 0));
            $this->assertStringContainsString('漫画插件未启用', (string) ($fail['msg'] ?? ''));
        } finally {
            $manager->setEnabled('manga', $was);
        }
    }

    public function test_logs_page_is_a_workbench(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/cj?desk=logs')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('运行日志', $html);
        $this->assertStringContainsString('name="desk" value="logs"', $html);
        $this->assertStringContainsString('搜说明、规则编号', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
    }

    public function test_toggle_and_empty_html_selector_fail_honestly(): void
    {
        $rule = $this->makeRule('html', 'https://example.com/blog', [
            'item_selector' => 'article.post',
        ]);
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/toggle', ['id' => $rule->id])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertSame(0, (int) $rule->fresh()->status);

        $fail = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/cj/save', [
                'name' => '缺选择器',
                'type' => 'html',
                'source_url' => 'https://example.com/x',
            ])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($fail['code'] ?? 0));
        $this->assertStringContainsString('列表条目', (string) ($fail['msg'] ?? ''));
    }

    /** @param  array<string, mixed>  $options */
    protected function makeRule(string $type, string $url, array $options, bool $publish = false): VideoCjRule
    {
        return VideoCjRule::query()->create([
            'name' => 'Test '.$type,
            'type' => $type,
            'url' => $url,
            'type_id' => 0,
            'interval_minutes' => 60,
            'limit_items' => 10,
            'status' => 1,
            'publish_immediately' => $publish ? 1 : 0,
            'options' => $options,
            'note' => '',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaPic;
use Tests\TestCase;

class MangaPluginBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_workbench_is_not_generic_crud(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('manga-board', $html);
        $this->assertStringContainsString('独立漫画库', $html);
        $this->assertStringContainsString('不是影片分类', $html);
        $this->assertStringContainsString('作品', $html);
        $this->assertStringContainsString('待审', $html);
        $this->assertStringContainsString('分类', $html);
        $this->assertStringContainsString('章节', $html);
        $this->assertStringContainsString('图片', $html);
        $this->assertStringNotContainsString('nav-fold-nested', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=pending', $html);
        $this->assertStringContainsString("title: '名称'", $html);
        $this->assertStringContainsString("/admin/video/' + module + '/list'", $html);
        $this->assertStringContainsString("/admin/video/' + module + '/save'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="host"', $html);
        $this->assertStringNotContainsString('href="/admin/video/manga_chapters"', $html);

        $this->assertStringContainsString('class="is-on">插件</a>', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);

        $vod = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('nav-fold-nested', $vod);
        $this->assertStringNotContainsString('/admin/video/mangas', $vod);
        $this->assertStringNotContainsString('href="/admin/video/manga_chapters"', $vod);
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $vod
        );
        $this->assertStringContainsString('class="is-on">影片</a>', $vod);
        $morePos = strpos($vod, '<summary>更多</summary>');
        $this->assertNotFalse($morePos);
        $more = substr($vod, $morePos);
        $this->assertStringContainsString('<span>标签</span>', $more);
        $this->assertStringContainsString('<span>回收站</span>', $more);
        $this->assertStringNotContainsString('/admin/video/mangas', $more);
    }

    public function test_other_desks_redirect_to_the_board(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_types')
            ->assertRedirect('/admin/video/mangas?desk=types');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_chapters')
            ->assertRedirect('/admin/video/mangas?desk=chapters');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_pics')
            ->assertRedirect('/admin/video/mangas?desk=pics');
    }

    public function test_save_and_list_decorate_manga_modules(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('mangas', ['title' => '  '], null);
        $this->assertSame(1, $empty['code']);

        $ok = $svc->save('mangas', [
            'title' => ' 一人之下 ',
            'author' => '米二',
            'serialize' => 1,
            'yid' => 0,
            'recommend' => 1,
            'type_id' => 0,
            'status' => 1,
            'tags' => '动作',
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $row = Manga::query()->orderByDesc('id')->first();
        $this->assertNotNull($row);
        $this->assertSame('一人之下', (string) $row->title);
        $this->assertSame(1, (int) $row->serialize);
        $this->assertSame(0, (int) $row->yid);

        $badChapter = $svc->save('manga_chapters', ['manga_id' => 9999, 'name' => '第1话'], null);
        $this->assertSame(1, $badChapter['code']);

        $chapter = $svc->save('manga_chapters', [
            'manga_id' => $row->id,
            'name' => '第1话',
            'sort' => 1,
            'pics' => "/a.jpg\nhttps://example.com/b.jpg\njavascript:alert(1)",
        ], null);
        $this->assertSame(0, $chapter['code'], $chapter['msg'] ?? '');
        $ep = MangaChapter::query()->where('manga_id', $row->id)->first();
        $this->assertNotNull($ep);
        $this->assertSame(2, MangaPic::query()->where('chapter_id', $ep->id)->count());
        $this->assertSame(0, MangaPic::query()->where('url', 'like', 'javascript:%')->count());

        $badPic = $svc->save('manga_pics', [
            'manga_id' => $row->id,
            'chapter_id' => $ep->id,
            'url' => 'javascript:alert(1)',
        ], null);
        $this->assertSame(1, $badPic['code']);

        $type = $svc->save('manga_types', ['name' => '热血', 'status' => 1], null);
        $this->assertSame(0, $type['code'], $type['msg'] ?? '');

        $list = $svc->lists('mangas', ['limit' => 20, 'q' => '一人']);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $this->assertSame('完结', $rows[0]['serialize_label'] ?? '');
        $this->assertSame('已审', $rows[0]['yid_label'] ?? '');
        $this->assertSame(1, (int) ($rows[0]['chapter_count'] ?? 0));
        $this->assertSame(1, (int) ($rows[0]['status'] ?? 0));

        $chapters = $svc->lists('manga_chapters', ['limit' => 20, 'q' => '一人']);
        $this->assertSame(0, $chapters['code']);
        $this->assertSame('一人之下', $chapters['data']['data'][0]['manga_title'] ?? '');
        $this->assertGreaterThan(0, (int) ($chapters['data']['data'][0]['pic_count'] ?? 0));
    }
}

class MangaPluginDisabledTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->restoreMangaPlugin();
        parent::tearDown();
    }

    public function test_disabled_plugin_hides_admin_and_front(): void
    {
        $this->actingAsAdmin();
        app(PluginManager::class)->setEnabled('manga', false);
        $this->refreshApplication();
        $this->actingAsAdmin();
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas')
            ->assertNotFound();
        $this->get('/manga')->assertNotFound();
    }

    private function restoreMangaPlugin(): void
    {
        $file = base_path('plugins/Manga/plugin.json');
        if (! is_file($file)) {
            return;
        }
        $raw = json_decode((string) file_get_contents($file), true);
        if (! is_array($raw)) {
            return;
        }
        $raw['enabled'] = true;
        $json = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            file_put_contents($file, $json.PHP_EOL);
        }
    }
}

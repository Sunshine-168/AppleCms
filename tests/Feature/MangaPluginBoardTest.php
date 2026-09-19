<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaPic;
use Plugins\Manga\Models\MangaType;
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
        $this->assertStringNotContainsString('id="manga-desks"', $html);
        $this->assertStringNotContainsString('queue-chips', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=stats', $html);
        $this->assertStringContainsString('/admin/video/config/manga', $html);
        $this->assertStringContainsString('/admin/video/manga-tags', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=chapters', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=pics', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=favors', $html);
        $this->assertStringNotContainsString('nav-fold-nested', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=pending', $html);
        $this->assertStringContainsString("title: '名称'", $html);
        $this->assertStringContainsString('manga-batch', $html);
        $this->assertStringContainsString("{check: true, width: 36}", $html);
        $this->assertStringContainsString("/admin/video/' + module + '/list'", $html);
        $this->assertStringContainsString("/admin/video/mangas/save", $html);
        $this->assertStringContainsString('parsed.total', $html);
        $this->assertStringContainsString('desk=work&manga_id=', $html);
        $this->assertStringContainsString('/admin/video/mangas/create', $html);
        $this->assertStringContainsString('完整表单', $html);
        $this->assertStringContainsString('manga-work-compose', $html);
        $this->assertStringNotContainsString('manga-work-tpl', $html);
        $this->assertStringNotContainsString('U.dialog', $html);
        $this->assertStringNotContainsString('openDialog', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="host"', $html);
        $this->assertStringNotContainsString('href="/admin/video/manga_chapters"', $html);

        $this->assertStringContainsString('class="is-on">漫画</a>', $html);
        $this->assertStringNotContainsString('class="is-on">插件</a>', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);

        $vod = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('nav-fold-nested', $vod);
        $this->assertStringContainsString('>漫画<', $vod);
        $this->assertStringNotContainsString('href="/admin/video/manga_chapters"', $vod);
        $this->assertDoesNotMatchRegularExpression(
            '/<details class="nav-fold(?: is-open)?"[^>]*>\s*<summary>\s*插件\s*<\/summary>/u',
            $vod
        );
        $this->assertStringContainsString('class="is-on">影片</a>', $vod);
        $morePos = strpos($vod, '<summary>更多</summary>');
        $this->assertNotFalse($morePos);
        $more = substr($vod, $morePos);
        $foldEnd = strpos($more, '</details>');
        if ($foldEnd !== false) {
            $more = substr($more, 0, $foldEnd);
        }
        $this->assertStringContainsString('<span>标签</span>', $more);
        $this->assertStringContainsString('<span>回收站</span>', $more);
        $this->assertStringNotContainsString('/admin/video/mangas', $more);
    }

    public function test_other_desks_redirect_to_the_board(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_types')
            ->assertRedirect('/admin/video/manga-types');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=types')
            ->assertRedirect('/admin/video/manga-types');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_chapters')
            ->assertRedirect('/admin/video/mangas?desk=chapters');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_pics')
            ->assertRedirect('/admin/video/mangas?desk=pics');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga_comments')
            ->assertRedirect('/admin/video/mangas?desk=comments');
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

        $blank = $svc->save('mangas', [
            'title' => '空备注本',
            'cover' => '/uploads/x.png',
            'author' => '',
            'remarks' => null,
            'content' => null,
            'tags' => null,
            'yid' => 1,
            'status' => 1,
        ], null);
        $this->assertSame(0, $blank['code'] ?? 1, $blank['msg'] ?? '');
        $blankRow = Manga::query()->where('title', '空备注本')->first();
        $this->assertNotNull($blankRow);
        $this->assertSame('', (string) $blankRow->remarks);
        $this->assertSame('', (string) ($blankRow->author ?? ''));

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
        $this->assertSame('/manga/'.$row->id, $rows[0]['front_url'] ?? '');

        $chapters = $svc->lists('manga_chapters', ['limit' => 20, 'q' => '一人']);
        $this->assertSame(0, $chapters['code']);
        $this->assertSame('一人之下', $chapters['data']['data'][0]['manga_title'] ?? '');
        $this->assertGreaterThan(0, (int) ($chapters['data']['data'][0]['pic_count'] ?? 0));

        $badType = $svc->save('manga_types', ['name' => '循环', 'parent_id' => $type['data']['id'] ?? 0], null);
        $this->assertSame(0, $badType['code'] ?? 1);
        $self = $svc->save('manga_types', ['parent_id' => $type['data']['id'] ?? 0], (int) ($type['data']['id'] ?? 0));
        $this->assertSame(1, $self['code']);

        $comment = $svc->save('manga_comments', [
            'manga_id' => $row->id,
            'author_name' => '审稿',
            'content' => '后台补的评',
            'status' => 1,
        ], null);
        $this->assertSame(0, $comment['code'], $comment['msg'] ?? '');
        $comments = $svc->lists('manga_comments', ['limit' => 20, 'q' => '一人']);
        $this->assertSame(0, $comments['code']);
        $this->assertSame('一人之下', $comments['data']['data'][0]['manga_title'] ?? '');
        $this->assertSame('显示', $comments['data']['data'][0]['status_label'] ?? '');

        $types = $svc->lists('manga_types', ['limit' => 20]);
        $top = collect($types['data']['data'] ?? [])->firstWhere('name', '热血');
        $this->assertSame('顶级', $top['parent_name'] ?? '');
        $this->assertSame(0, (int) ($top['depth'] ?? -1));
        $this->assertArrayHasKey('manga_count', $top);
        $this->assertArrayHasKey('child_count', $top);
    }

    public function test_types_desk_matches_art_style_tree_and_batch(): void
    {
        $svc = app(SiteModuleService::class);
        $parent = $svc->save('manga_types', ['name' => '少年', 'status' => 1, 'sort' => 10, 'slug' => 'shonen'], null);
        $this->assertSame(0, $parent['code'] ?? 1, $parent['msg'] ?? '');
        $pid = (int) ($parent['data']['id'] ?? 0);
        $child = $svc->save('manga_types', ['name' => '热血', 'parent_id' => $pid, 'status' => 1, 'sort' => 5, 'page_size' => 12], null);
        $this->assertSame(0, $child['code'] ?? 1, $child['msg'] ?? '');
        $cid = (int) ($child['data']['id'] ?? 0);

        $work = $svc->save('mangas', ['title' => '分类里的本', 'type_id' => $cid, 'status' => 1, 'yid' => 0], null);
        $this->assertSame(0, $work['code'] ?? 1, $work['msg'] ?? '');

        $list = $svc->lists('manga_types', ['limit' => 50]);
        $this->assertSame(0, $list['code'] ?? 1);
        $rows = $list['data']['data'] ?? [];
        $this->assertSame(['少年', '热血'], array_column($rows, 'name'));
        $this->assertSame(0, (int) ($rows[0]['depth'] ?? -1));
        $this->assertSame(1, (int) ($rows[1]['depth'] ?? -1));
        $this->assertSame(1, (int) ($rows[0]['child_count'] ?? 0));
        $this->assertSame(1, (int) ($rows[1]['manga_count'] ?? 0));
        $this->assertSame('shonen', (string) ($rows[0]['slug'] ?? ''));

        $cycle = $svc->save('manga_types', ['parent_id' => $cid], $pid);
        $this->assertSame(1, $cycle['code'] ?? 0);
        $this->assertStringContainsString('下级', (string) ($cycle['msg'] ?? ''));

        $block = $svc->delete('manga_types', $pid);
        $this->assertSame(1, $block['code'] ?? 0);
        $this->assertStringContainsString('下级', (string) ($block['msg'] ?? ''));

        $blockWork = $svc->delete('manga_types', $cid);
        $this->assertSame(1, $blockWork['code'] ?? 0);
        $this->assertStringContainsString('作品', (string) ($blockWork['msg'] ?? ''));

        $off = $svc->batch('manga_types', [$cid], 'status', 0);
        $this->assertSame(0, $off['code'] ?? 1, $off['msg'] ?? '');
        $this->assertSame(0, (int) MangaType::query()->find($cid)?->status);

        $move = $svc->batch('manga_types', [$cid], 'parent', 0);
        $this->assertSame(0, $move['code'] ?? 1, $move['msg'] ?? '');
        $this->assertSame(0, (int) MangaType::query()->find($cid)?->parent_id);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-types')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('添加下级', $html);
        $this->assertStringContainsString('manga-type-table', $html);
        $this->assertStringContainsString('下级会缩进', $html);
        $this->assertStringContainsString('/admin/video/manga-types/create', $html);

        $form = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-types/create')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('新增分类', $form);
        $this->assertStringContainsString('网址别名', $form);
        $this->assertStringContainsString('分页条数', $form);
        $this->assertStringContainsString('在前台显示', $form);
        $this->assertStringContainsString('保存并添加下级', $form);

        $edit = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-types/'.$pid.'/edit')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('编辑分类', $edit);
        $this->assertStringContainsString('少年', $edit);
    }

    public function test_batch_status_recommend_and_comments(): void
    {
        $svc = app(SiteModuleService::class);
        $a = $svc->save('mangas', ['title' => '批量甲', 'status' => 1, 'yid' => 1, 'recommend' => 0], null);
        $b = $svc->save('mangas', ['title' => '批量乙', 'status' => 1, 'yid' => 0, 'recommend' => 0], null);
        $this->assertSame(0, $a['code'] ?? 1, $a['msg'] ?? '');
        $this->assertSame(0, $b['code'] ?? 1, $b['msg'] ?? '');
        $idA = (int) ($a['data']['id'] ?? 0);
        $idB = (int) ($b['data']['id'] ?? 0);

        $off = $svc->batch('mangas', [$idA, $idB], 'status', 0);
        $this->assertSame(0, $off['code'] ?? 1, $off['msg'] ?? '');
        $this->assertSame(0, (int) Manga::query()->find($idA)?->status);
        $this->assertSame(0, (int) Manga::query()->find($idB)?->status);

        $rec = $svc->batch('mangas', [$idB], 'recommend', 1);
        $this->assertSame(0, $rec['code'] ?? 1, $rec['msg'] ?? '');
        $this->assertSame(1, (int) Manga::query()->find($idB)?->recommend);

        $pass = $svc->batch('mangas', [$idA], 'yid', 0);
        $this->assertSame(0, $pass['code'] ?? 1, $pass['msg'] ?? '');
        $this->assertSame(0, (int) Manga::query()->find($idA)?->yid);

        $on = $svc->batch('mangas', [$idB], 'status', 1);
        $this->assertSame(0, $on['code'] ?? 1, $on['msg'] ?? '');
        $comment = $svc->save('manga_comments', [
            'manga_id' => $idB,
            'author_name' => '审',
            'content' => '先过再藏',
            'status' => 1,
        ], null);
        $this->assertSame(0, $comment['code'] ?? 1, $comment['msg'] ?? '');
        $cid = (int) ($comment['data']['id'] ?? 0);
        $hide = $svc->batch('manga_comments', [$cid], 'status', 0);
        $this->assertSame(0, $hide['code'] ?? 1, $hide['msg'] ?? '');
        $this->assertSame(0, (int) \Plugins\Manga\Models\MangaComment::query()->find($cid)?->status);

        $del = $svc->batch('manga_comments', [$cid], 'delete', '');
        $this->assertSame(0, $del['code'] ?? 1, $del['msg'] ?? '');
        $this->assertNull(\Plugins\Manga\Models\MangaComment::query()->find($cid));

        $commentsDesk = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=comments')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('manga-batch-on', $commentsDesk);
        $this->assertStringContainsString('通过', $commentsDesk);
        $this->assertStringContainsString('隐藏', $commentsDesk);
        $this->assertStringContainsString('manga-comment-queues', $commentsDesk);
        $this->assertStringContainsString('待审', $commentsDesk);
        $this->assertStringContainsString('已通过', $commentsDesk);
        $this->assertStringContainsString('前台', $commentsDesk);

        $filtered = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?serialize=1&recommend=1&q='.rawurlencode('人气筛选'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('value="人气筛选"', $filtered);
        $this->assertMatchesRegularExpression('/name="serialize"[\s\S]*?<option value="1"[^>]*selected/u', $filtered);
        $this->assertMatchesRegularExpression('/name="recommend"[\s\S]*?<option value="1"[^>]*selected/u', $filtered);
    }

    public function test_delete_cascades_and_work_filter_banner(): void
    {
        $svc = app(SiteModuleService::class);
        $ok = $svc->save('mangas', ['title' => '级联本', 'status' => 1, 'yid' => 0], null);
        $this->assertSame(0, $ok['code'] ?? 1, $ok['msg'] ?? '');
        $id = (int) ($ok['data']['id'] ?? 0);
        $ch = $svc->save('manga_chapters', [
            'manga_id' => $id,
            'name' => '第1话',
            'pics' => "/x.jpg\n/y.jpg",
        ], null);
        $this->assertSame(0, $ch['code'] ?? 1, $ch['msg'] ?? '');
        $this->assertSame(2, MangaPic::query()->where('manga_id', $id)->count());

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=chapters&manga_id='.$id)
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('正在看作品', $html);
        $this->assertStringContainsString('级联本', $html);
        $this->assertStringContainsString('/manga/'.$id, $html);
        $this->assertStringContainsString('作品工作台', $html);
        $this->assertStringContainsString('manga-chapter-compose', $html);
        $this->assertStringContainsString('name="manga_id"', $html);
        $this->assertStringContainsString('完整表单', $html);
        $this->assertStringNotContainsString('U.dialog', $html);

        $work = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=work&manga_id='.$id)
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('作品工作台', $work);
        $this->assertStringContainsString('级联本', $work);
        $this->assertStringContainsString('新增章节', $work);
        $this->assertStringContainsString('manga_chapters', $work);
        $this->assertStringContainsString('/admin/video/mangas/'.$id.'/edit', $work);
        $this->assertStringContainsString('manga-chapter-compose', $work);
        $this->assertStringContainsString('/admin/video/manga-chapters/create', $work);
        $this->assertStringContainsString('完整表单', $work);
        $this->assertStringNotContainsString('U.dialog', $work);
        $this->assertStringNotContainsString('manga-edit-work', $work);

        $stats = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=stats')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('漫画统计', $stats);
        $this->assertStringContainsString('今日阅读', $stats);
        $this->assertStringContainsString('近 14 日趋势', $stats);
        $this->assertStringContainsString('人气 TOP', $stats);
        $this->assertStringContainsString('manga-stats-split', $stats);
        $this->assertStringContainsString('manga-stats-grid', $stats);
        $this->assertStringContainsString('stat-grid', $stats);
        $this->assertStringContainsString('打开书架台', $stats);
        $this->assertStringNotContainsString('阅读次数来自会员阅读历史', $stats);

        $del = $svc->delete('mangas', $id);
        $this->assertSame(0, $del['code'] ?? 1, $del['msg'] ?? '');
        $this->assertNull(Manga::query()->find($id));
        $this->assertSame(0, MangaChapter::query()->where('manga_id', $id)->count());
        $this->assertSame(0, MangaPic::query()->where('manga_id', $id)->count());
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
        $this->get('/manga/history')->assertNotFound();
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

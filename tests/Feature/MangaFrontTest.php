<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Video\VideoOption;
use App\Services\Video\VideoSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaComment;
use Plugins\Manga\Models\MangaFavor;
use Plugins\Manga\Models\MangaType;
use Tests\TestCase;

class MangaFrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_list_filters_rank_update_and_reader_neighbors(): void
    {
        $parent = MangaType::query()->create([
            'name' => '热血',
            'parent_id' => 0,
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
        ]);
        $child = MangaType::query()->create([
            'name' => '少年',
            'parent_id' => $parent->id,
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
        ]);
        $hot = $this->manga('人气本', [
            'hits' => 90,
            'serialize' => 1,
            'recommend' => 1,
            'tags' => '动作,热血',
            'type_id' => $child->id,
            'author' => '作者甲',
            'updated_at' => 100,
        ]);
        $fresh = $this->manga('新连载', [
            'hits' => 3,
            'serialize' => 0,
            'recommend' => 0,
            'tags' => '日常',
            'updated_at' => 200,
        ]);
        $this->manga('完结冷门', [
            'hits' => 1,
            'serialize' => 1,
            'recommend' => 0,
            'updated_at' => 50,
        ]);

        $this->manga('同类本', [
            'hits' => 8,
            'tags' => '热血',
            'updated_at' => 80,
        ]);

        $list = $this->get('/manga')->assertOk();
        $list->assertSee('人气本')->assertSee('新连载')->assertSee('热血')->assertSee('排行')->assertSee('书架')->assertSee('历史');
        $list->assertSee('全部作品')->assertSee('推荐')->assertSee('热门')->assertSee('最近更新');
        $this->assertStringContainsString('搜漫画', $list->getContent());
        $this->assertStringNotContainsString('内容折叠菜单', $list->getContent());

        $this->get('/manga?serialize=1')->assertOk()->assertSee('人气本')->assertDontSee('新连载')->assertDontSee('全部作品');
        $this->get('/manga?recommend=1')->assertOk()->assertSee('人气本')->assertDontSee('新连载');
        $this->get('/manga?wd=人气')->assertOk()->assertSee('人气本')->assertDontSee('新连载');
        $this->get('/manga?author=作者甲')->assertOk()->assertSee('人气本')->assertDontSee('新连载');
        $this->get('/manga?tag=热血')->assertOk()->assertSee('人气本')->assertSee('同类本')->assertDontSee('新连载');
        $this->get('/manga?type='.$parent->id)->assertOk()->assertSee('人气本')->assertDontSee('新连载')->assertSee('少年');
        $this->get('/manga?order=hits')->assertOk()->assertSee('人气本');
        $this->get('/manga?wd=没有这本')->assertOk()->assertSee('没有符合条件的漫画');

        $rank = $this->get('/manga/rank')->assertOk();
        $rank->assertSee('漫画排行')->assertSee('人气本')->assertSee('完结');
        $this->get('/manga/rank?board=end')->assertOk()->assertSee('人气本')->assertDontSee('新连载');
        $this->get('/manga/update')->assertOk()->assertSee('最近更新')->assertSee('新连载')->assertSee('今天');
        $this->get('/manga/history')->assertOk()->assertSee('阅读历史')->assertSee('未登录时记在这台浏览器');
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<h2>漫画', $home);
        $this->assertStringContainsString('人气本', $home);

        $ep1 = MangaChapter::query()->create([
            'manga_id' => $hot->id,
            'name' => '第1话',
            'sort' => 1,
            'pics' => '/img/1.jpg',
            'created_at' => time(),
        ]);
        $ep2 = MangaChapter::query()->create([
            'manga_id' => $hot->id,
            'name' => '第2话',
            'sort' => 2,
            'pics' => '/img/2.jpg',
            'created_at' => time(),
        ]);

        $show = $this->get('/manga/'.$hot->id)->assertOk();
        $show->assertSee('第1话')->assertSee('第2话')->assertSee('完结')->assertSee('动作')->assertSee('登录后收藏');
        $show->assertSee('相关漫画')->assertSee('同类本')->assertDontSee('新连载');
        $show->assertSee('倒序')->assertSee('条评论')->assertSee('作者甲');

        $this->get('/manga')->assertOk()->assertSee('第2话');
        $this->get('/manga/rank')->assertOk()->assertSee('第2话');

        $read1 = $this->get('/manga/'.$hot->id.'/'.$ep1->id)->assertOk();
        $read1->assertSee('没有上一话')->assertSee('下一话')->assertSee('/img/1.jpg')->assertSee('夜间')->assertSee('本话目录');
        $read1->assertSee('页漫')->assertSee('manga-progress')->assertSee('加载失败，点此重试');
        $read2 = $this->get('/manga/'.$hot->id.'/'.$ep2->id)->assertOk();
        $read2->assertSee('上一话')->assertSee('没有下一话')->assertSee('/img/2.jpg');
        $this->assertStringContainsString('manga_read_mode', $read1->getContent());
        $this->assertStringContainsString('data-mode', $read1->getContent());

        $this->get('/manga/shelf')->assertRedirect('/member/login');
        $this->post('/manga/'.$hot->id.'/favor')->assertRedirect('/member/login');
        $this->assertSame(0, MangaFavor::query()->count());
    }

    public function test_member_read_history_and_seo_sitemap_provide(): void
    {
        $row = $this->manga('续看本');
        $ep1 = MangaChapter::query()->create([
            'manga_id' => $row->id,
            'name' => '第1话',
            'sort' => 1,
            'pics' => '/a.jpg',
            'created_at' => time(),
        ]);
        $ep2 = MangaChapter::query()->create([
            'manga_id' => $row->id,
            'name' => '第2话',
            'sort' => 2,
            'pics' => '/b.jpg',
            'created_at' => time(),
        ]);
        $member = Member::query()->create([
            'name' => '读者',
            'email' => 'manga-his-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->actingAs($member, 'member')
            ->get('/manga/'.$row->id.'/'.$ep2->id)
            ->assertOk()
            ->assertSee('续看本');

        $this->assertDatabaseHas('plugin_manga_histories', [
            'member_id' => $member->id,
            'manga_id' => $row->id,
            'chapter_id' => $ep2->id,
        ]);

        $show = $this->actingAs($member, 'member')
            ->get('/manga/'.$row->id)
            ->assertOk();
        $show->assertSee('继续阅读');
        $show->assertSee('/manga/'.$row->id.'/'.$ep2->id);

        $his = $this->actingAs($member, 'member')
            ->get('/manga/history')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('账号记录', $his);
        $this->assertStringContainsString('续看本', $his);

        $center = $this->actingAs($member, 'member')
            ->get('/member')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('/manga/history', $center);
        $this->assertStringContainsString('漫画历史', $center);

        $map = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('/manga', $map);
        $this->assertStringContainsString('/manga/'.$row->id, $map);

        $detail = $this->getJson('/api/provide/manga?ac=detail&ids='.$row->id)
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($detail['code'] ?? 0));
        $this->assertSame('续看本', $detail['list'][0]['manga_name'] ?? '');
        $this->assertStringContainsString('第2话', (string) ($detail['list'][0]['manga_play_url'] ?? ''));

        unset($ep1);
    }

    public function test_empty_list_points_to_plugin_board(): void
    {
        $html = $this->get('/manga')->assertOk()->getContent();
        $this->assertStringContainsString('插件工作台添加', $html);
        $this->assertStringNotContainsString('内容折叠菜单', $html);
    }

    public function test_manga_is_first_header_nav_item(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression(
            '/<nav class="main">\s*<a href="[^"]*\/manga">漫画<\/a>/u',
            $html
        );
        $mangaPos = strpos($html, '>漫画</a>');
        $latestPos = strpos($html, '>最新</a>');
        $this->assertNotFalse($mangaPos);
        if ($latestPos !== false) {
            $this->assertLessThan($latestPos, $mangaPos);
        }
    }

    public function test_comment_and_shelf_use_plugin_tables(): void
    {
        $row = $this->manga('可评本');
        $this->post('/manga/'.$row->id.'/comment', ['content' => '好看', 'author_name' => '路人'])
            ->assertRedirect();
        $this->assertSame(1, MangaComment::query()->where('manga_id', $row->id)->where('status', 1)->count());
        $this->get('/manga/'.$row->id)->assertOk()->assertSee('好看')->assertSee('路人');

        VideoOption::query()->updateOrCreate(['k' => 'comment_audit'], ['v' => '1']);
        Cache::forget(VideoSettingService::CACHE_KEY);
        $this->post('/manga/'.$row->id.'/comment', ['content' => '待审句', 'author_name' => '乙'])
            ->assertRedirect()
            ->assertSessionHas('status', '评论已提交，等待审核');
        $this->get('/manga/'.$row->id)->assertOk()->assertDontSee('待审句');
        $this->assertSame(1, MangaComment::query()->where('content', '待审句')->where('status', 0)->count());

        $member = Member::query()->create([
            'name' => '藏家',
            'email' => 'manga-fav-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->actingAs($member, 'member')
            ->post('/manga/'.$row->id.'/favor')
            ->assertRedirect()
            ->assertSessionHas('status', '已加入书架');
        $this->assertSame(1, MangaFavor::query()->where('member_id', $member->id)->where('manga_id', $row->id)->count());
        $this->actingAs($member, 'member')
            ->get('/manga/shelf')
            ->assertOk()
            ->assertSee('可评本');

        $ep1 = MangaChapter::query()->create([
            'manga_id' => $row->id,
            'name' => '第1话',
            'sort' => 1,
            'pics' => '/a.jpg',
            'created_at' => time(),
        ]);
        $this->actingAs($member, 'member')
            ->get('/manga/'.$row->id.'/'.$ep1->id)
            ->assertOk();
        MangaChapter::query()->create([
            'manga_id' => $row->id,
            'name' => '第2话',
            'sort' => 2,
            'pics' => '/b.jpg',
            'created_at' => time(),
        ]);
        $shelf = $this->actingAs($member, 'member')
            ->get('/manga/shelf')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('有更新', $shelf);
        $this->assertStringContainsString('更新至 第2话', $shelf);

        $this->actingAs($member, 'member')
            ->post('/manga/'.$row->id.'/favor')
            ->assertRedirect()
            ->assertSessionHas('status', '已移出书架');
        $this->actingAs($member, 'member')
            ->get('/manga/shelf')
            ->assertOk()
            ->assertSee('书架是空的');
    }

    /** @param array<string, mixed> $over */
    private function manga(string $title, array $over = []): Manga
    {
        return Manga::query()->create(array_merge([
            'title' => $title,
            'cover' => '',
            'author' => '作者',
            'remarks' => '',
            'content' => '',
            'hits' => 0,
            'sort' => 1,
            'status' => 1,
            'yid' => 0,
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'tags' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ], $over));
    }
}

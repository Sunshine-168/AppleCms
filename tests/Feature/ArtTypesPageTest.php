<?php

namespace Tests\Feature;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoTypeModel;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtTypesPageTest extends TestCase
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
    }

    public function test_art_column_board_is_not_the_film_type_page(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-types')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新建栏目', $html);
        $this->assertStringContainsString('搜栏目名', $html);
        $this->assertStringContainsString('还没有栏目', $html);
        $this->assertStringContainsString('/admin/video/art-types/create', $html);
        $this->assertStringContainsString('写文章', $html);
        $this->assertStringContainsString('class="is-on">文章</a>', $html);
        $this->assertStringContainsString('文章自己的栏目', $html);
        $this->assertStringContainsString('频道', $html);
        $this->assertStringContainsString('"kind":"类型"', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
        $this->assertStringNotContainsString('用来放什么', $html);
        $this->assertStringNotContainsString('title: \'模型\'', $html);
        $this->assertStringNotContainsString('openDialog', $html);
    }

    public function test_create_art_column_does_not_ask_module(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-types/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新建栏目', $html);
        $this->assertStringContainsString('name="mid" value="2"', $html);
        $this->assertStringContainsString('和影片分类不是同一棵树', $html);
        $this->assertStringContainsString('name="kind"', $html);
        $this->assertStringContainsString('频道（只做目录，下面再挂列表）', $html);
        $this->assertStringContainsString('单页（打开栏目即那一篇）', $html);
        $this->assertStringContainsString('外链', $html);
        $this->assertStringContainsString('name="jump_url"', $html);
        $this->assertStringContainsString('name="page_size"', $html);
        $this->assertStringContainsString('name="pic"', $html);
        $this->assertStringContainsString('media-preview', $html);
        $this->assertStringContainsString('type-pic-preview', $html);
        $this->assertStringContainsString('name="tpl_list"', $html);
        $this->assertStringContainsString('保存并添加下级', $html);
        $this->assertStringContainsString('保存并写文章', $html);
        $this->assertStringNotContainsString('用来放什么', $html);
        $this->assertStringNotContainsString('name="mid" value="1"', $html);
    }

    public function test_art_column_save_writes_mid_two_and_rejects_film_type_on_article(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/art-types/save', [
                'name' => '资讯',
                'slug' => 'news',
                'parent_id' => 0,
                'status' => 1,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $id = (int) ($ok['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $id);
        $row = VideoTypeModel::query()->find($id);
        $this->assertNotNull($row);
        $this->assertSame(2, (int) $row->mid);
        $this->assertSame('list', $row->kind());

        $film = VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '电影',
            'slug' => 'movie',
            'mid' => 1,
            'sort' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $fail = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/save', [
                'title' => '误绑影片分类',
                'type_id' => $film->id,
                'status' => 0,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($fail['code'] ?? 0));
        $this->assertStringContainsString('文章栏目', (string) ($fail['msg'] ?? ''));
        $this->assertSame(0, VideoArt::query()->count());

        $okArt = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/save', [
                'title' => '栏目里的稿',
                'type_id' => $id,
                'status' => 0,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($okArt['code'] ?? 1), (string) ($okArt['msg'] ?? ''));
        $this->assertSame(1, VideoArt::query()->where('title', '栏目里的稿')->where('type_id', $id)->count());
    }

    public function test_film_type_list_hides_article_columns(): void
    {
        $now = time();
        VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '电影',
            'slug' => 'movie',
            'mid' => 1,
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '资讯',
            'slug' => 'news',
            'mid' => 2,
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $vod = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/types/list')
            ->assertOk()
            ->json();
        $vodNames = array_column($vod['data']['data'] ?? [], 'name');
        $this->assertContains('电影', $vodNames);
        $this->assertNotContains('资讯', $vodNames);

        $art = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-types/list')
            ->assertOk()
            ->json();
        $artNames = array_column($art['data']['data'] ?? [], 'name');
        $this->assertContains('资讯', $artNames);
        $this->assertNotContains('电影', $artNames);
    }

    public function test_art_workspace_nav(): void
    {
        $urls = array_column(AdminNav::groupsFor('art')[0]['items'] ?? [], 'url');
        $fold = array_column(AdminNav::groupsFor('art')[0]['fold']['items'] ?? [], 'url');
        $this->assertContains('/admin/video/arts', $urls);
        $this->assertContains('/admin/video/art-types', $urls);
        $this->assertContains('/admin/video/art-tags', $urls);
        $this->assertContains('/admin/video/art-media', $urls);
        $this->assertContains('/admin/video/art-comments', $urls);
        $this->assertContains('/admin/video/art-flags', $fold);
        $this->assertContains('/admin/video/art-recycle', $fold);
        $this->assertNotContains('/admin/video/art-recycle', $urls);
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-types/create'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-media'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-comments'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-flags'));
    }

    public function test_art_list_parent_column_includes_child_articles(): void
    {
        $now = time();
        $parent = VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '资讯',
            'slug' => 'news',
            'mid' => 2,
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $child = VideoTypeModel::query()->create([
            'parent_id' => $parent->id,
            'name' => '公告',
            'slug' => 'notice',
            'mid' => 2,
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoArt::query()->create([
            'type_id' => $child->id,
            'title' => '挂在下级',
            'cover' => '',
            'content' => '',
            'status' => 0,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoArt::query()->create([
            'type_id' => 0,
            'title' => '没分栏',
            'cover' => '',
            'content' => '',
            'status' => 0,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts?type_id='.$parent->id)
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('art-cat-rail', $html);
        $this->assertStringContainsString('公告', $html);
        $this->assertStringContainsString('/admin/video/arts/create?type_id='.$parent->id, $html);

        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/list?type_id='.$parent->id)
            ->assertOk()
            ->json();
        $titles = array_column($list['data']['data'] ?? [], 'title');
        $this->assertContains('挂在下级', $titles);
        $this->assertNotContains('没分栏', $titles);

        $loose = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/list?type_id=0')
            ->assertOk()
            ->json();
        $looseTitles = array_column($loose['data']['data'] ?? [], 'title');
        $this->assertContains('没分栏', $looseTitles);
        $this->assertNotContains('挂在下级', $looseTitles);
    }

    public function test_art_column_kinds_save_and_reject_hub_link_articles(): void
    {
        $hub = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/art-types/save', [
                'name' => '新闻中心',
                'slug' => 'news-hub',
                'kind' => 'hub',
                'parent_id' => 0,
                'status' => 1,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($hub['code'] ?? 1), (string) ($hub['msg'] ?? ''));
        $hubId = (int) ($hub['data']['id'] ?? 0);
        $this->assertSame('hub', VideoTypeModel::query()->find($hubId)?->kind());

        $linkFail = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/art-types/save', [
                'name' => '坏外链',
                'kind' => 'link',
                'jump_url' => 'javascript:alert(1)',
                'parent_id' => 0,
                'status' => 1,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($linkFail['code'] ?? 0));

        $link = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/art-types/save', [
                'name' => '友站',
                'kind' => 'link',
                'jump_url' => '/arts',
                'parent_id' => 0,
                'status' => 1,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($link['code'] ?? 1), (string) ($link['msg'] ?? ''));
        $linkId = (int) ($link['data']['id'] ?? 0);
        $linkRow = VideoTypeModel::query()->find($linkId);
        $this->assertSame('link', $linkRow?->kind());
        $this->assertSame('/arts', $linkRow?->jumpUrl());

        $denyHub = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/save', [
                'title' => '挂到频道',
                'type_id' => $hubId,
                'status' => 0,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($denyHub['code'] ?? 0));
        $this->assertStringContainsString('频道', (string) ($denyHub['msg'] ?? ''));

        $denyLink = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/save', [
                'title' => '挂到外链',
                'type_id' => $linkId,
                'status' => 0,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(1, (int) ($denyLink['code'] ?? 0));
        $this->assertStringContainsString('外链', (string) ($denyLink['msg'] ?? ''));

        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-types/list')
            ->assertOk()
            ->json();
        $byName = [];
        foreach ($list['data']['data'] ?? [] as $row) {
            $byName[(string) ($row['name'] ?? '')] = $row;
        }
        $this->assertSame('频道', $byName['新闻中心']['kind_label'] ?? '');
        $this->assertSame('外链', $byName['友站']['kind_label'] ?? '');
    }
}

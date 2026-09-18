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
        $this->assertContains('/admin/video/arts', $urls);
        $this->assertContains('/admin/video/art-types', $urls);
        $this->assertContains('/admin/video/art-tags', $urls);
        $this->assertContains('/admin/video/art-recycle', $urls);
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-types/create'));
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
}

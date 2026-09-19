<?php

namespace Tests\Feature;

use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaAuthor;
use Tests\TestCase;

class MangaAuthorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_author_board_follows_tag_compose(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-authors')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增作者', $html);
        $this->assertStringContainsString('<span>作者', $html);
        $this->assertStringContainsString('只给漫画用，不是会员或影人库', $html);
        $this->assertStringContainsString('未使用', $html);
        $this->assertStringContainsString('打开完整表单', $html);
        $this->assertStringContainsString('/admin/video/manga-authors/create', $html);
        $this->assertStringContainsString('class="is-on">漫画</a>', $html);
        $this->assertStringContainsString('href="/admin/video/manga-authors"', $html);
        $this->assertStringNotContainsString('class="is-on">文章</a>', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_create_author_form_has_slug(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-authors/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新建作者', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="slug"', $html);
        $this->assertStringContainsString('/manga?author=', $html);
    }

    public function test_save_author_lists_and_filters_works(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/manga-authors/save', ['name' => '尾田', 'status' => 1])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $authorId = (int) ($ok['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $authorId);

        $now = time();
        $live = Manga::query()->create([
            'title' => '挂了作者',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'tags' => '',
            'content' => '',
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'yid' => 0,
            'status' => 1,
            'hits' => 0,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        Manga::query()->create([
            'title' => '没挂作者',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'tags' => '',
            'content' => '',
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'yid' => 0,
            'status' => 1,
            'hits' => 0,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/mangas/save', [
                'id' => $live->id,
                'title' => '挂了作者',
                'author_ids' => [$authorId],
                'author_extra' => '',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);

        $live->refresh();
        $this->assertSame('尾田', (string) $live->author);
        $this->assertTrue($live->authorRels()->where('plugin_manga_authors.id', $authorId)->exists());

        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas/list?author_id='.$authorId)
            ->assertOk()
            ->json();
        $titles = array_column($list['data']['data'] ?? [], 'title');
        $this->assertContains('挂了作者', $titles);
        $this->assertNotContains('没挂作者', $titles);
        $row = collect($list['data']['data'] ?? [])->firstWhere('title', '挂了作者');
        $this->assertContains($authorId, array_map('intval', $row['author_ids'] ?? []));

        $board = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?author_id='.$authorId)
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('作者 尾田', $board);
        $this->assertStringContainsString('name="author_id"', $board);
        $this->assertStringContainsString('/admin/video/manga-authors', $board);
        $this->assertStringContainsString('/admin/video/mangas/create', $board);
        $this->assertStringNotContainsString('manga-work-tpl', $board);
        $this->assertStringNotContainsString('id="manga-add-btn"', $board);

        $form = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas/'.$live->id.'/edit')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('work-author-pick', $form);
        $this->assertStringContainsString('搜作者名', $form);
        $this->assertStringContainsString('/admin/video/manga-authors/list', $form);
        $this->assertStringContainsString('尾田', $form);
        $this->assertStringNotContainsString('choice-grid', $form);
        $this->assertStringNotContainsString('js-manga-author', $form);
        $this->assertStringContainsString('会出现在首页推荐区', $form);
        $this->assertStringNotContainsString('U.dialog', $form);
    }

    public function test_harvest_comma_authors_and_front_filter(): void
    {
        $now = time();
        $manga = Manga::query()->create([
            'title' => '同人甲',
            'cover' => '',
            'author' => '作者甲,助手乙',
            'remarks' => '',
            'tags' => '',
            'content' => '',
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'yid' => 0,
            'status' => 1,
            'hits' => 1,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        Manga::query()->create([
            'title' => '其它作者',
            'cover' => '',
            'author' => '别人',
            'remarks' => '',
            'tags' => '',
            'content' => '',
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'yid' => 0,
            'status' => 1,
            'hits' => 0,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        Manga::query()->create([
            'title' => '同作者乙',
            'cover' => '',
            'author' => '作者甲',
            'remarks' => '',
            'tags' => '',
            'content' => '',
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'yid' => 0,
            'status' => 1,
            'hits' => 2,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-authors/list')
            ->assertOk()
            ->assertJsonPath('code', 0);

        $author = MangaAuthor::query()->where('name', '作者甲')->first();
        $this->assertNotNull($author);
        $this->assertTrue($manga->authorRels()->where('plugin_manga_authors.id', $author->id)->exists());

        $this->get('/manga?author='.$author->slug)
            ->assertOk()
            ->assertSee('同人甲')
            ->assertSee('同作者乙')
            ->assertDontSee('其它作者');

        $this->get('/manga?author=作者甲')
            ->assertOk()
            ->assertSee('同人甲')
            ->assertDontSee('其它作者');

        $show = $this->get('/manga/'.$manga->id)->assertOk();
        $show->assertSee('同作者')->assertSee('同作者乙')->assertDontSee('其它作者');
    }

    public function test_workspace_nav_points_to_manga_authors(): void
    {
        $this->assertSame('manga', AdminNav::currentModule('/admin/video/manga-authors'));
        $this->assertSame('manga', AdminNav::currentModule('/admin/video/manga-authors/create'));
        $this->assertSame('manga', AdminNav::currentModule('/admin/video/manga-authors/3/edit'));
    }
}

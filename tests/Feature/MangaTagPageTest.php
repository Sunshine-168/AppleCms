<?php

namespace Tests\Feature;

use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaTag;
use Tests\TestCase;

class MangaTagPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_tag_board_follows_art_tags_compose(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-tags')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增标签', $html);
        $this->assertStringContainsString('<span>标签', $html);
        $this->assertStringContainsString('只给漫画用，不会进文章或影片标签库', $html);
        $this->assertStringContainsString('未使用', $html);
        $this->assertStringContainsString('打开完整表单', $html);
        $this->assertStringContainsString('/admin/video/manga-tags/create', $html);
        $this->assertStringContainsString('class="is-on">漫画</a>', $html);
        $this->assertStringContainsString('href="/admin/video/manga-tags"', $html);
        $this->assertStringNotContainsString('class="is-on">文章</a>', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_create_tag_form_has_slug(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/manga-tags/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新建标签', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="slug"', $html);
        $this->assertStringContainsString('/manga?tag=', $html);
    }

    public function test_save_tag_lists_and_filters_works(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/manga-tags/save', ['name' => '热血', 'status' => 1])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $tagId = (int) ($ok['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $tagId);

        $now = time();
        $live = Manga::query()->create([
            'title' => '打了标签',
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
            'title' => '没打标签',
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
                'title' => '打了标签',
                'tags' => '热血',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);

        $live->refresh();
        $this->assertSame('热血', (string) $live->tags);
        $this->assertTrue($live->tagRels()->where('plugin_manga_tags.id', $tagId)->exists());

        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas/list?tag_id='.$tagId)
            ->assertOk()
            ->json();
        $titles = array_column($list['data']['data'] ?? [], 'title');
        $this->assertContains('打了标签', $titles);
        $this->assertNotContains('没打标签', $titles);

        $board = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?tag_id='.$tagId)
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('标签 热血', $board);
        $this->assertStringContainsString('name="tag_id"', $board);
        $this->assertStringContainsString('/admin/video/manga-tags', $board);
    }

    public function test_harvest_comma_tags_and_front_filter(): void
    {
        $now = time();
        $manga = Manga::query()->create([
            'title' => '热血本',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'tags' => '热血,日常',
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
            'title' => '其它本',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'tags' => '恋爱',
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
            ->get('/admin/video/manga-tags/list')
            ->assertOk()
            ->assertJsonPath('code', 0);

        $tag = MangaTag::query()->where('name', '热血')->first();
        $this->assertNotNull($tag);
        $this->assertTrue($manga->tagRels()->where('plugin_manga_tags.id', $tag->id)->exists());

        $this->get('/manga?tag='.$tag->slug)
            ->assertOk()
            ->assertSee('热血本')
            ->assertDontSee('其它本');

        $this->get('/manga?tag=热血')
            ->assertOk()
            ->assertSee('热血本')
            ->assertDontSee('其它本');
    }

    public function test_workspace_nav_points_to_manga_tags(): void
    {
        $this->assertSame('manga', AdminNav::currentModule('/admin/video/manga-tags'));
        $this->assertSame('manga', AdminNav::currentModule('/admin/video/manga-tags/create'));
        $this->assertSame('manga', AdminNav::currentModule('/admin/video/manga-tags/3/edit'));
    }
}

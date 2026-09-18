<?php

namespace Tests\Feature;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoArtTag;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtTagPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_tag_board_follows_laracms_compose(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-tags')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新增标签', $html);
        $this->assertStringContainsString('<span>标签', $html);
        $this->assertStringContainsString('只给文章用，不会进影片标签库', $html);
        $this->assertStringContainsString('影片标签在影片', $html);
        $this->assertStringContainsString('漫画词写在漫画作品上', $html);
        $this->assertStringNotContainsString('贺岁、院线', $html);
        $this->assertStringContainsString('未使用', $html);
        $this->assertStringContainsString('打开完整表单', $html);
        $this->assertStringContainsString('/admin/video/art-tags/create', $html);
        $this->assertStringContainsString('class="is-on">文章</a>', $html);
        $this->assertStringContainsString('href="/admin/video/art-tags"', $html);
        $this->assertStringContainsString('href="/admin/video/art-recycle"', $html);
        $this->assertStringNotContainsString('video-tag-dialog-tpl', $html);
        $this->assertStringNotContainsString('openDialog', $html);
        $this->assertStringNotContainsString('class="is-on">影片</a>', $html);
    }

    public function test_create_tag_form_has_slug_not_locales(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-tags/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('新建标签', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="slug"', $html);
        $this->assertStringContainsString('/art/tag/', $html);
        $this->assertStringNotContainsString('locales[', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
    }

    public function test_save_tag_lists_and_filters_articles(): void
    {
        $ok = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/art-tags/save', ['name' => '影讯', 'status' => 1])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $tagId = (int) ($ok['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $tagId);

        $now = time();
        $live = VideoArt::query()->create([
            'type_id' => 0,
            'title' => '打了标签',
            'cover' => '',
            'content' => '',
            'tag' => '',
            'status' => 1,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $plain = VideoArt::query()->create([
            'type_id' => 0,
            'title' => '没打标签',
            'cover' => '',
            'content' => '',
            'tag' => '',
            'status' => 1,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/save', [
                'id' => $live->id,
                'title' => '打了标签',
                'tag_ids' => [$tagId],
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);

        $live->refresh();
        $this->assertSame('影讯', (string) $live->tag);
        $this->assertTrue($live->tags()->where('video_art_tags.id', $tagId)->exists());

        $list = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/list?tag_id='.$tagId)
            ->assertOk()
            ->json();
        $titles = array_column($list['data']['data'] ?? [], 'title');
        $this->assertContains('打了标签', $titles);
        $this->assertNotContains('没打标签', $titles);

        $board = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts?tag_id='.$tagId)
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('标签 影讯', $board);
        $this->assertStringContainsString('name="tag_id"', $board);

        $form = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/'.$live->id.'/edit')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('name="tag_ids[]"', $form);
        $this->assertStringContainsString('value="'.$tagId.'"', $form);
        $this->assertStringContainsString('新标签', $form);
    }

    public function test_harvest_comma_tags_and_front_page(): void
    {
        $now = time();
        $art = VideoArt::query()->create([
            'type_id' => 0,
            'title' => '院线稿',
            'cover' => '',
            'content' => '<p>正文</p>',
            'tag' => '院线,影讯',
            'status' => 1,
            'hits' => 0,
            'published_at' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-tags/list')
            ->assertOk()
            ->assertJsonPath('code', 0);

        $tag = VideoArtTag::query()->where('name', '院线')->first();
        $this->assertNotNull($tag);
        $this->assertTrue($art->tags()->where('video_art_tags.id', $tag->id)->exists());

        $html = $this->get('/art/tag/'.$tag->slug)->assertOk()->getContent();
        $this->assertStringContainsString('<h1>院线</h1>', $html);
        $this->assertStringContainsString('院线稿', $html);

        $detail = $this->get('/art/'.$art->id)->assertOk()->getContent();
        $this->assertStringContainsString('院线', $detail);
        $this->assertStringContainsString('/art/tag/'.$tag->slug, $detail);
    }

    public function test_recycle_copy_and_workspace_nav(): void
    {
        $now = time();
        $art = VideoArt::query()->create([
            'type_id' => 0,
            'title' => '要删的稿',
            'cover' => '',
            'content' => '',
            'tag' => '',
            'status' => 1,
            'hits' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $copy = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/batch', ['ids' => (string) $art->id, 'action' => 'copy'])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($copy['code'] ?? 1), (string) ($copy['msg'] ?? ''));
        $this->assertTrue(VideoArt::query()->where('title', '要删的稿 副本')->where('status', 0)->exists());

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/delete', ['id' => $art->id])
            ->assertOk()
            ->assertJsonPath('code', 0);

        $this->assertNull(VideoArt::query()->find($art->id));
        $trashed = VideoArt::query()->withoutGlobalScope('alive')->find($art->id);
        $this->assertNotNull($trashed);
        $this->assertGreaterThan(0, (int) $trashed->deleted_at);

        $alive = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/list')
            ->assertOk()
            ->json();
        $aliveTitles = array_column($alive['data']['data'] ?? [], 'title');
        $this->assertNotContains('要删的稿', $aliveTitles);

        $trash = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts/list?trash=1')
            ->assertOk()
            ->json();
        $trashTitles = array_column($trash['data']['data'] ?? [], 'title');
        $this->assertContains('要删的稿', $trashTitles);

        $this->get('/art/'.$art->id)->assertNotFound();

        $recycle = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-recycle')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('还原所选', $recycle);
        $this->assertStringContainsString('彻底删除', $recycle);
        $this->assertStringContainsString('href="/admin/video/art-recycle"', $recycle);
        $this->assertStringContainsString('class="is-on">文章</a>', $recycle);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/arts/batch', ['ids' => (string) $art->id, 'action' => 'restore'])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertNotNull(VideoArt::query()->find($art->id));

        $urls = array_column(AdminNav::groupsFor('art')[0]['items'] ?? [], 'url');
        $fold = array_column(AdminNav::groupsFor('art')[0]['fold']['items'] ?? [], 'url');
        $this->assertContains('/admin/video/art-tags', $urls);
        $this->assertContains('/admin/video/art-recycle', $fold);
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-tags/create'));
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-recycle'));
    }
}

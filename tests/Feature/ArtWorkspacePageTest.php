<?php

namespace Tests\Feature;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoModel;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtWorkspacePageTest extends TestCase
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

    public function test_art_sidebar_has_media_comments_and_more_fold(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/arts')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('href="/admin/video/art-media"', $html);
        $this->assertStringContainsString('href="/admin/video/art-comments"', $html);
        $this->assertStringContainsString('href="/admin/video/art-flags"', $html);
        $this->assertStringContainsString('href="/admin/video/art-recycle"', $html);
        $this->assertStringContainsString('<span>媒体</span>', $html);
        $this->assertStringContainsString('<span>评论</span>', $html);
        $this->assertStringContainsString('<span>推荐属性</span>', $html);
        $this->assertStringContainsString('<span>回收站</span>', $html);
        $this->assertStringContainsString('nav-fold', $html);
        $this->assertStringNotContainsString('href="/admin/video/art-models"', $html);

        $group = AdminNav::groupsFor('art')[0] ?? [];
        $this->assertNotContains('/admin/video/art-recycle', array_column($group['items'] ?? [], 'url'));
    }

    public function test_art_media_stays_in_article_workspace_and_shares_attachment_library(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-media')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('file-index', $html);
        $this->assertStringContainsString('媒体', $html);
        $this->assertStringContainsString('和系统「附件」是同一库', $html);
        $this->assertStringContainsString('class="is-on">文章</a>', $html);
        $this->assertStringContainsString('/admin/system/attachments/list', $html);
        $this->assertStringNotContainsString('class="is-on">系统</a>', $html);
        $this->assertStringNotContainsString('/admin/video/templates', $html);
        $this->assertStringNotContainsString('/admin/video/tools/annex', $html);
        $this->assertSame('art', AdminNav::currentModule('/admin/video/art-media'));
        $this->assertSame('system', AdminNav::currentModule('/admin/system/attachments'));
    }

    public function test_art_comments_are_not_film_comments(): void
    {
        $now = time();
        $film = VideoModel::query()->create([
            'title' => '测试片',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $art = VideoArt::query()->create([
            'type_id' => 0,
            'title' => '测试稿',
            'cover' => '',
            'content' => '<p>正文</p>',
            'status' => 1,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoComment::query()->create([
            'video_id' => $film->id,
            'mid' => 1,
            'member_id' => 0,
            'parent_id' => 0,
            'author_name' => '影评人',
            'content' => '片子不错',
            'status' => 1,
            'ip' => '127.0.0.1',
            'created_at' => $now,
        ]);
        VideoComment::query()->create([
            'video_id' => $art->id,
            'mid' => 2,
            'member_id' => 0,
            'parent_id' => 0,
            'author_name' => '读者',
            'content' => '稿件写得好',
            'status' => 1,
            'ip' => '127.0.0.1',
            'created_at' => $now,
        ]);

        $page = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-comments')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('文章页发来的评论', $page);
        $this->assertStringContainsString('/admin/video/art-comments/list', $page);
        $this->assertStringContainsString('class="is-on">文章</a>', $page);
        $this->assertStringNotContainsString('用户在播放页发的评论', $page);

        $artList = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-comments/list')
            ->assertOk()
            ->json();
        $this->assertSame(0, $artList['code'] ?? 1);
        $artContents = array_column($artList['data']['data'] ?? [], 'content');
        $this->assertContains('稿件写得好', $artContents);
        $this->assertNotContains('片子不错', $artContents);

        $filmList = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/comments/list')
            ->assertOk()
            ->json();
        $this->assertSame(0, $filmList['code'] ?? 1);
        $filmContents = array_column($filmList['data']['data'] ?? [], 'content');
        $this->assertContains('片子不错', $filmContents);
        $this->assertNotContains('稿件写得好', $filmContents);
    }

    public function test_art_flags_board_lists_wired_codes_only(): void
    {
        $now = time();
        VideoArt::query()->create([
            'type_id' => 0,
            'title' => '置顶稿',
            'cover' => '',
            'content' => '',
            'flags' => 'top,hot',
            'status' => 1,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/art-flags')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('写稿勾选这三项', $html);
        $this->assertStringContainsString('不能自己加标识码', $html);
        $this->assertStringContainsString('/admin/video/arts?flag=top', $html);
        $this->assertStringContainsString('/admin/video/arts?flag=recommend', $html);
        $this->assertStringContainsString('/admin/video/arts?flag=hot', $html);
        $this->assertStringContainsString('1 篇', $html);
        $this->assertStringContainsString('class="is-on">文章</a>', $html);
        $this->assertStringNotContainsString('href="/admin/cms/recommends"', $html);
        $this->assertStringNotContainsString('cms_flags', $html);
    }
}

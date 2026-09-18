<?php

namespace Tests\Feature;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoTypeModel;
use App\Services\Video\Tags\ArtTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtFrontTest extends TestCase
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

    public function test_draft_and_future_are_hidden(): void
    {
        $live = $this->makeArt(['title' => '已见稿', 'status' => 1, 'published_at' => 0]);
        $draft = $this->makeArt(['title' => '草稿稿', 'status' => 0]);
        $future = $this->makeArt(['title' => '定时稿', 'status' => 1, 'published_at' => time() + 86400]);

        $list = $this->get('/arts')->assertOk()->getContent();
        $this->assertStringContainsString('已见稿', $list);
        $this->assertStringNotContainsString('草稿稿', $list);
        $this->assertStringNotContainsString('定时稿', $list);

        $this->get('/art/'.$live->id)->assertOk()->assertSee('已见稿', false);
        $this->get('/art/'.$draft->id)->assertNotFound();
        $this->get('/art/'.$future->id)->assertNotFound();
    }

    public function test_published_shows_cover_blurb_hits_source(): void
    {
        $art = $this->makeArt([
            'title' => '封面稿',
            'cover' => '/uploads/art-cover.png',
            'blurb' => '这一句摘要',
            'content' => '<p>正文段落</p>',
            'source' => '转载出处',
            'author' => '编辑甲',
            'hits' => 7,
            'tag' => '影讯,院线',
        ]);

        $html = $this->get('/art/'.$art->id)->assertOk()->getContent();
        $this->assertStringContainsString('封面稿', $html);
        $this->assertStringContainsString('/uploads/art-cover.png', $html);
        $this->assertStringContainsString('这一句摘要', $html);
        $this->assertStringContainsString('转载出处', $html);
        $this->assertStringContainsString('编辑甲', $html);
        $this->assertStringContainsString('正文段落', $html);
        $this->assertStringContainsString('8 次', $html);
        $this->assertStringContainsString('影讯', $html);
        $this->assertStringContainsString('/arts?wd=', $html);
        $this->assertStringContainsString('资讯', $html);

        $this->assertSame(8, (int) VideoArt::query()->find($art->id)->hits);
    }

    public function test_prev_next_and_related(): void
    {
        $older = $this->makeArt(['title' => '更早一篇']);
        $mid = $this->makeArt(['title' => '当前这篇', 'content' => '<p>当前正文</p>']);
        $newer = $this->makeArt(['title' => '更后一篇']);
        $this->makeArt(['title' => '相关一篇']);

        $html = $this->get('/art/'.$mid->id)->assertOk()->getContent();
        $this->assertStringContainsString('上一篇：更早一篇', $html);
        $this->assertStringContainsString('下一篇：更后一篇', $html);
        $this->assertStringContainsString('相关阅读', $html);
        $this->assertStringContainsString('相关一篇', $html);
        $this->assertStringContainsString($older->url, $html);
        $this->assertStringContainsString($newer->url, $html);
    }

    public function test_type_list_includes_descendants(): void
    {
        $now = time();
        $parent = VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '新闻',
            'slug' => 'news',
            'mid' => 2,
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $child = VideoTypeModel::query()->create([
            'parent_id' => $parent->id,
            'name' => '国内',
            'slug' => 'china',
            'mid' => 2,
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->makeArt(['title' => '子栏目稿', 'type_id' => $child->id]);
        $this->makeArt(['title' => '别的栏目', 'type_id' => 0]);

        $html = $this->get('/art/type/'.$parent->id)->assertOk()->getContent();
        $this->assertStringContainsString('子栏目稿', $html);
        $this->assertStringNotContainsString('别的栏目', $html);
        $this->assertStringContainsString('新闻', $html);
        $this->assertStringContainsString('<h1>新闻</h1>', $html);
        $this->assertStringContainsString('国内', $html);

        $this->get('/art/type/'.$child->id)->assertOk()->assertSee('子栏目稿', false);
    }

    public function test_art_tag_flag_recommend_skips_others(): void
    {
        $rec = $this->makeArt(['title' => '推荐资讯', 'flags' => 'recommend']);
        $plain = $this->makeArt(['title' => '普通资讯', 'flags' => '']);
        $hot = $this->makeArt(['title' => '热门资讯', 'flags' => 'hot']);

        $items = app(ArtTag::class)->get(['flag' => 'recommend', 'num' => 10]);
        $ids = $items->pluck('id')->all();
        $this->assertContains($rec->id, $ids);
        $this->assertNotContains($plain->id, $ids);
        $this->assertNotContains($hot->id, $ids);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('资讯', $home);
        $this->assertStringContainsString('推荐资讯', $home);
        $this->assertStringContainsString('普通资讯', $home);
    }

    /** @param  array<string, mixed>  $attrs */
    private function makeArt(array $attrs): VideoArt
    {
        $now = time();

        return VideoArt::query()->create(array_merge([
            'type_id' => 0,
            'title' => '文章',
            'cover' => '',
            'blurb' => '',
            'content' => '',
            'source' => '',
            'author' => '',
            'tag' => '',
            'flags' => '',
            'sort' => 0,
            'seo_title' => '',
            'seo_key' => '',
            'seo_des' => '',
            'hits' => 0,
            'status' => 1,
            'published_at' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $attrs));
    }
}

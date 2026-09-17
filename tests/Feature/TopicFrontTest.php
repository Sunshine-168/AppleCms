<?php

namespace Tests\Feature;

use App\Models\Video\VideoTopicModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicFrontTest extends TestCase
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

    public function test_topics_index_lists_published_topics_and_search(): void
    {
        $this->makeTopic(['name' => '贺岁档', 'slug' => 'new-year', 'status' => 1, 'sort' => 9]);

        $html = $this->get('/topics')->assertOk()->getContent();
        $this->assertStringContainsString('贺岁档', $html);
        $this->assertTrue(
            str_contains($html, '搜专题') || str_contains($html, 'topic_search') || str_contains($html, '/topics/search'),
            'topics index should expose topic search'
        );
    }

    public function test_topic_detail_shows_content_and_hides_unpublished(): void
    {
        $on = $this->makeTopic([
            'name' => '冷门佳片',
            'slug' => 'hidden-gems',
            'status' => 1,
            'content' => '专题介绍正文',
            'blurb' => '一句话',
        ]);
        $off = $this->makeTopic(['name' => '未上架', 'slug' => 'draft-topic', 'status' => 0]);

        $this->get('/topic/'.$on->id)
            ->assertOk()
            ->assertSee('冷门佳片', false)
            ->assertSee('专题介绍正文', false);

        $this->get('/topic/'.$off->id)->assertNotFound();
    }

    public function test_topic_search_finds_matching_name(): void
    {
        $this->makeTopic(['name' => '暑假必看', 'slug' => 'summer', 'status' => 1]);

        $this->get('/topics/search?wd=')->assertOk();
        $this->get('/topics/search?wd='.rawurlencode('暑假'))
            ->assertOk()
            ->assertSee('暑假必看', false);
    }

    public function test_layout_vod_topic_tag_still_renders(): void
    {
        $this->makeTopic(['name' => '导航专题', 'slug' => 'nav-topic', 'status' => 1]);

        $this->get('/topics')->assertOk();
    }

    /** @param  array<string, mixed>  $attrs */
    private function makeTopic(array $attrs): VideoTopicModel
    {
        $now = time();

        return VideoTopicModel::query()->create(array_merge([
            'name' => '专题',
            'slug' => '',
            'cover' => '',
            'blurb' => '',
            'content' => '',
            'status' => 1,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $attrs));
    }
}

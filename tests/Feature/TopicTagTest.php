<?php

namespace Tests\Feature;

use App\Cms\CmsViewContext;
use App\Models\Video\VideoTopicModel;
use App\Services\Video\Tags\TopicTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicTagTest extends TestCase
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

    public function test_num_limits_results(): void
    {
        $this->seedTopics();
        $tag = app(TopicTag::class);
        $this->assertCount(2, $tag->get(['num' => 2]));
    }

    public function test_ids_filters_to_given_topics(): void
    {
        $rows = $this->seedTopics();
        $tag = app(TopicTag::class);
        $ids = [(int) $rows[0]->id, (int) $rows[2]->id];
        $got = $tag->get(['ids' => implode(',', $ids), 'num' => 10])->pluck('id')->map(fn ($id) => (int) $id)->all();
        sort($got);
        sort($ids);
        $this->assertSame($ids, $got);
    }

    public function test_order_asc_by_id(): void
    {
        $rows = $this->seedTopics();
        $tag = app(TopicTag::class);
        $got = $tag->get(['order' => 'asc', 'by' => 'id', 'num' => 10])->pluck('id')->map(fn ($id) => (int) $id)->all();
        $expect = array_map(fn ($row) => (int) $row->id, $rows);
        sort($expect);
        $this->assertSame($expect, $got);
    }

    public function test_wd_filters_name(): void
    {
        $this->seedTopics();
        $tag = app(TopicTag::class);
        $got = $tag->get(['wd' => '乙专题', 'num' => 10]);
        $this->assertCount(1, $got);
        $this->assertSame('乙专题', $got->first()->name);
    }

    public function test_paging_yes_sets_paginator(): void
    {
        $this->seedTopics();
        $context = app(CmsViewContext::class);
        $tag = app(TopicTag::class);
        $result = $tag->get(['paging' => 'yes', 'num' => 2]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertNotNull($context->paginator());
        $this->assertSame(2, $context->paginator()->perPage());
        $this->assertSame(3, $context->paginator()->total());
    }

    /** @return list<VideoTopicModel> */
    private function seedTopics(): array
    {
        $now = time();
        $rows = [];
        foreach (['甲专题' => 'a-topic', '乙专题' => 'b-topic', '丙专题' => 'c-topic'] as $name => $slug) {
            $rows[] = VideoTopicModel::query()->create([
                'name' => $name,
                'slug' => $slug,
                'cover' => '',
                'blurb' => '',
                'content' => '',
                'status' => 1,
                'sort' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $rows;
    }
}

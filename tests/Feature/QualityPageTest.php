<?php

namespace Tests\Feature;

use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Services\Admin\Video\VideoService;
use App\Services\Video\SiteToolsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_quality_board_is_not_a_json_dump_or_score(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/tools/quality')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('quality-index', $html);
        $this->assertStringContainsString('不会打分', $html);
        $this->assertStringContainsString('无地址', $html);
        $this->assertStringContainsString('无封面', $html);
        $this->assertStringContainsString('无简介', $html);
        $this->assertStringContainsString('无演员', $html);
        $this->assertStringContainsString('重名', $html);
        $this->assertStringContainsString('集数不齐', $html);
        $this->assertStringContainsString('刷新数字', $html);
        $this->assertStringContainsString('/admin/video?empty_url=1', $html);
        $this->assertStringContainsString('data-issue="missing_ep"', $html);
        $this->assertStringContainsString('missing_ep=1', $html);
        $this->assertStringContainsString('/admin/video/list', $html);
        $this->assertStringContainsString('片库还是空的', $html);
        $this->assertStringNotContainsString('开始体检', $html);
        $this->assertStringNotContainsString('id="out"', $html);
        $this->assertStringNotContainsString('JSON.stringify', $html);
        $this->assertStringNotContainsString('推荐位', $html);
        $this->assertStringNotContainsString('score_total', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
    }

    public function test_quality_counts_and_missing_ep_list_are_honest(): void
    {
        $now = time();
        VideoModel::query()->create([
            'title' => '缺封面',
            'cover' => '',
            'description' => '有简介',
            'status' => 1,
            'total' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $gap = VideoModel::query()->create([
            'title' => '缺集',
            'cover' => '/uploads/vod/a.jpg',
            'description' => '有简介',
            'status' => 1,
            'total' => 12,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoEpisodeModel::query()->create([
            'video_id' => $gap->id,
            'source_id' => 0,
            'episode_name' => '1',
            'episode_num' => 1,
            'url' => 'ep1.m3u8',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoModel::query()->create([
            'title' => '撞名',
            'cover' => '/uploads/vod/b.jpg',
            'description' => '有简介',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        VideoModel::query()->create([
            'title' => '撞名',
            'cover' => '/uploads/vod/c.jpg',
            'description' => '有简介',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $counts = app(VideoService::class)->qualityCounts();
        $this->assertSame(4, (int) $counts['all']);
        $this->assertSame(1, (int) $counts['empty_pic']);
        $this->assertGreaterThanOrEqual(1, (int) $counts['empty_url']);
        $this->assertSame(1, (int) $counts['missing_ep']);
        $this->assertSame(2, (int) $counts['repeat']);
        $this->assertSame(1, (int) $counts['repeat_groups']);

        $scan = app(SiteToolsService::class)->quality();
        $this->assertSame(0, $scan['code']);
        $this->assertArrayNotHasKey('missing_ep_rows', $scan['data'] ?? []);
        $this->assertStringNotContainsString('体检完成', (string) ($scan['msg'] ?? ''));

        $list = app(VideoService::class)->getVideoLists(['missing_ep' => '1', 'limit' => 20]);
        $this->assertSame(0, $list['code']);
        $titles = array_column($list['data']['data'] ?? [], 'title');
        $this->assertSame(['缺集'], $titles);
        $this->assertSame(1, (int) (($list['data']['data'][0]['episode_count'] ?? 0)));
        $this->assertSame(11, (int) (($list['data']['data'][0]['ep_gap'] ?? 0)));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoPlotIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_plot_library_is_an_episode_board_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/plots')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('plot-index', $html);
        $this->assertStringContainsString('还没有分集剧情', $html);
        $this->assertStringContainsString('新增剧情', $html);
        $this->assertStringContainsString('搜剧情、片名或集数', $html);
        $this->assertStringContainsString('没写正文', $html);
        $this->assertStringContainsString('不会从播放地址自动生成', $html);
        $this->assertStringContainsString('/admin/video?has_plot=1', $html);
        $this->assertStringContainsString("title: '剧情'", $html);
        $this->assertStringContainsString("title: '集数'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="title"', $html);
        $this->assertStringNotContainsString("title: 'video_id'", $html);
        $this->assertStringNotContainsString("title: 'episode_num'", $html);
        $this->assertStringNotContainsString("title: 'created_at'", $html);
    }

    public function test_save_needs_video_episode_and_content(): void
    {
        $svc = app(SiteModuleService::class);

        $noVideo = $svc->save('plots', ['video_id' => 0, 'episode_num' => 1, 'content' => '开场'], null);
        $this->assertSame(1, $noVideo['code']);
        $this->assertStringContainsString('影片 ID', $noVideo['msg']);

        $missingVideo = $svc->save('plots', ['video_id' => 99, 'episode_num' => 1, 'content' => '开场'], null);
        $this->assertSame(1, $missingVideo['code']);
        $this->assertStringContainsString('影片不存在', $missingVideo['msg']);

        $now = time();
        VideoModel::query()->insert([
            'title' => '大话西游',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $videoId = (int) VideoModel::query()->orderByDesc('id')->value('id');

        $noEp = $svc->save('plots', ['video_id' => $videoId, 'episode_num' => 0, 'content' => '开场'], null);
        $this->assertSame(1, $noEp['code']);
        $this->assertStringContainsString('集数', $noEp['msg']);

        $noContent = $svc->save('plots', ['video_id' => $videoId, 'episode_num' => 1, 'content' => ''], null);
        $this->assertSame(1, $noContent['code']);
        $this->assertStringContainsString('剧情', $noContent['msg']);
    }

    public function test_list_shows_video_title_and_rejects_duplicate_episode(): void
    {
        $now = time();
        VideoModel::query()->insert([
            'title' => '大话西游',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $videoId = (int) VideoModel::query()->orderByDesc('id')->value('id');

        $svc = app(SiteModuleService::class);
        $ok = $svc->save('plots', [
            'video_id' => $videoId,
            'episode_num' => 1,
            'title' => '月光宝盒',
            'content' => '至尊宝回到五百年前。',
            'sort' => 0,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $dup = $svc->save('plots', [
            'video_id' => $videoId,
            'episode_num' => 1,
            'content' => '又写一遍。',
        ], null);
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('已经写过剧情', $dup['msg']);

        $list = $svc->lists('plots', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $row = $rows[0];
        $this->assertSame('月光宝盒', $row['title'] ?? '');
        $this->assertSame('第1集', $row['episode_label'] ?? '');
        $this->assertSame('大话西游', $row['video_title'] ?? '');
        $this->assertSame(0, (int) ($row['video_missing'] ?? 1));
        $this->assertSame(1, (int) ($row['has_content'] ?? 0));

        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/plots/list?q=大话')
            ->assertOk()
            ->json();
        $this->assertSame(0, $json['code'] ?? 1);
        $found = $json['data']['data'] ?? [];
        $this->assertCount(1, $found);
        $this->assertSame('大话西游', $found[0]['video_title'] ?? '');
    }
}

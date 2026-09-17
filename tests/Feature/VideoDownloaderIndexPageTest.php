<?php

namespace Tests\Feature;

use App\Models\Video\VideoDownloader;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoDownloaderIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_downloader_board_is_not_a_job_queue_or_player_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/downloaders')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('downer-index', $html);
        $this->assertStringContainsString('还没有下载器', $html);
        $this->assertStringContainsString('新增下载器', $html);
        $this->assertStringContainsString('搜名称、标识或模板', $html);
        $this->assertStringContainsString('不是后台去把文件下下来', $html);
        $this->assertStringContainsString('/admin/video/players', $html);
        $this->assertStringContainsString('/admin/video/servers', $html);
        $this->assertStringContainsString('/admin/video/downloaders/try', $html);
        $this->assertStringContainsString("title: '名称'", $html);
        $this->assertStringContainsString("title: '标识'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString("title: 'code'", $html);
        $this->assertStringNotContainsString("title: 'parse'", $html);
        $this->assertStringNotContainsString("title: 'status'", $html);
    }

    public function test_save_needs_name_code_and_rejects_bad_parse_or_duplicate(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('downloaders', ['name' => '', 'code' => 'http'], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('名称', $empty['msg']);

        $noCode = $svc->save('downloaders', ['name' => '直链', 'code' => ''], null);
        $this->assertSame(1, $noCode['code']);
        $this->assertStringContainsString('标识', $noCode['msg']);

        $bad = $svc->save('downloaders', ['name' => '坏', 'code' => '1http'], null);
        $this->assertSame(1, $bad['code']);

        $js = $svc->save('downloaders', [
            'name' => '坏链',
            'code' => 'evil',
            'parse' => 'javascript:alert(1)',
        ], null);
        $this->assertSame(1, $js['code']);
        $this->assertStringContainsString('协议', $js['msg']);

        $ok = $svc->save('downloaders', [
            'name' => '模板',
            'code' => 'DL',
            'parse' => 'https://dl.example/get?u={url}',
            'status' => 1,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertSame('dl', VideoDownloader::query()->value('code'));

        $dup = $svc->save('downloaders', ['name' => '另一份', 'code' => 'dl'], null);
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('已经有了', $dup['msg']);
    }

    public function test_list_kinds_and_try_uses_saved_enabled_templates(): void
    {
        $svc = app(SiteModuleService::class);
        $this->assertSame(0, $svc->save('downloaders', [
            'name' => '模板',
            'code' => 'dl',
            'parse' => 'https://dl.example/get?u={url}',
            'status' => 1,
        ], null)['code']);
        $this->assertSame(0, $svc->save('downloaders', [
            'name' => '前缀',
            'code' => 'cdn',
            'parse' => 'https://cdn.example/',
            'status' => 1,
        ], null)['code']);
        $this->assertSame(0, $svc->save('downloaders', [
            'name' => '直链',
            'code' => 'raw',
            'parse' => '',
            'status' => 0,
        ], null)['code']);

        $now = time();
        VideoModel::query()->insert([
            'title' => '大话西游',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $videoId = (int) VideoModel::query()->orderByDesc('id')->value('id');
        VideoSourceModel::query()->insert([
            'video_id' => $videoId,
            'name' => '下载',
            'type' => 'down',
            'downer' => 'dl',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $list = $svc->lists('downloaders', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $byCode = [];
        foreach ($list['data']['data'] ?? [] as $row) {
            $byCode[(string) ($row['code'] ?? '')] = $row;
        }
        $this->assertSame('tpl', $byCode['dl']['parse_kind'] ?? '');
        $this->assertSame('prefix', $byCode['cdn']['parse_kind'] ?? '');
        $this->assertSame('empty', $byCode['raw']['parse_kind'] ?? '');
        $this->assertFalse((bool) ($byCode['raw']['is_on'] ?? true));
        $this->assertSame(1, (int) ($byCode['dl']['source_count'] ?? 0));

        $tplOnly = $svc->lists('downloaders', ['limit' => 20, 'kind' => 'tpl']);
        $this->assertSame(['dl'], array_column($tplOnly['data']['data'] ?? [], 'code'));

        $try = $svc->tryDownloader(['code' => 'dl', 'url' => 'ep1.mp4', 'video_id' => $videoId]);
        $this->assertSame(0, $try['code'], $try['msg'] ?? '');
        $this->assertTrue((bool) ($try['data']['changed'] ?? false));
        $this->assertStringContainsString('dl.example', (string) ($try['data']['to'] ?? ''));
        $this->assertStringContainsString(rawurlencode('ep1.mp4'), (string) ($try['data']['to'] ?? ''));

        $raw = $svc->tryDownloader(['code' => 'raw', 'url' => 'ep1.mp4']);
        $this->assertSame(0, $raw['code']);
        $this->assertFalse((bool) ($raw['data']['changed'] ?? true));

        $empty = $svc->tryDownloader(['url' => '']);
        $this->assertSame(1, $empty['code']);
    }
}

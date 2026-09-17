<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Models\Video\VideoServer;
use App\Models\Video\VideoSourceModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoServerIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_server_board_is_not_a_downloader_or_player_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/servers')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('server-index', $html);
        $this->assertStringContainsString('还没有服务器组', $html);
        $this->assertStringContainsString('新增服务器组', $html);
        $this->assertStringContainsString('搜名称或前缀', $html);
        $this->assertStringContainsString('相对路径', $html);
        $this->assertStringContainsString('不是下载页模板', $html);
        $this->assertStringContainsString('/admin/video/players', $html);
        $this->assertStringContainsString('/admin/video/downloaders', $html);
        $this->assertStringContainsString('/admin/video/servers/try', $html);
        $this->assertStringContainsString("title: '名称'", $html);
        $this->assertStringContainsString("title: '前缀'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString("title: 'name'", $html);
        $this->assertStringNotContainsString("title: 'url'", $html);
        $this->assertStringNotContainsString("title: 'status'", $html);
    }

    public function test_save_needs_name_and_rejects_bad_prefix_or_duplicate(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('servers', ['name' => '', 'url' => 'https://play.example.com'], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('名称', $empty['msg']);

        $js = $svc->save('servers', [
            'name' => '坏链',
            'url' => 'javascript:alert(1)',
        ], null);
        $this->assertSame(1, $js['code']);
        $this->assertStringContainsString('协议', $js['msg']);

        $ftp = $svc->save('servers', [
            'name' => 'FTP',
            'url' => 'ftp://files.example.com',
        ], null);
        $this->assertSame(1, $ftp['code']);

        $bare = $svc->save('servers', [
            'name' => '主站',
            'url' => 'https://',
        ], null);
        $this->assertSame(1, $bare['code']);

        $ok = $svc->save('servers', [
            'name' => '线路A',
            'url' => 'play.example.com/hls',
            'status' => 1,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertSame('https://play.example.com/hls', VideoServer::query()->value('url'));

        $path = $svc->save('servers', [
            'name' => '路径组',
            'url' => '/vod/hls',
            'status' => 1,
        ], null);
        $this->assertSame(0, $path['code'], $path['msg'] ?? '');
        $this->assertSame('/vod/hls', VideoServer::query()->where('name', '路径组')->value('url'));

        $emptyUrl = $svc->save('servers', [
            'name' => '空前缀',
            'url' => '',
            'status' => 1,
        ], null);
        $this->assertSame(0, $emptyUrl['code'], $emptyUrl['msg'] ?? '');
        $this->assertSame('', VideoServer::query()->where('name', '空前缀')->value('url'));

        $dup = $svc->save('servers', ['name' => '线路A', 'url' => 'https://other.example'], null);
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('已经有了', $dup['msg']);
    }

    public function test_list_try_and_in_use_cannot_delete(): void
    {
        $svc = app(SiteModuleService::class);
        $this->assertSame(0, $svc->save('servers', [
            'name' => '线路A',
            'url' => 'https://play.example.com/hls',
            'status' => 1,
        ], null)['code']);
        $this->assertSame(0, $svc->save('servers', [
            'name' => '停用组',
            'url' => 'https://off.example.com',
            'status' => 0,
        ], null)['code']);
        $this->assertSame(0, $svc->save('servers', [
            'name' => '空着',
            'url' => '',
            'status' => 1,
        ], null)['code']);

        $onId = (int) VideoServer::query()->where('name', '线路A')->value('id');
        $offId = (int) VideoServer::query()->where('name', '停用组')->value('id');
        $emptyId = (int) VideoServer::query()->where('name', '空着')->value('id');

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
            'name' => '高清',
            'type' => 'm3u8',
            'server_id' => $onId,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $list = $svc->lists('servers', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $byName = [];
        foreach ($list['data']['data'] ?? [] as $row) {
            $byName[(string) ($row['name'] ?? '')] = $row;
        }
        $this->assertTrue((bool) ($byName['线路A']['is_on'] ?? false));
        $this->assertTrue((bool) ($byName['线路A']['has_url'] ?? false));
        $this->assertSame(1, (int) ($byName['线路A']['source_count'] ?? 0));
        $this->assertFalse((bool) ($byName['停用组']['is_on'] ?? true));
        $this->assertFalse((bool) ($byName['空着']['has_url'] ?? true));

        $emptyOnly = $svc->lists('servers', ['limit' => 20, 'empty_url' => '1']);
        $this->assertSame(['空着'], array_column($emptyOnly['data']['data'] ?? [], 'name'));

        $try = $svc->tryServer(['server_id' => $onId, 'url' => 'ep1.m3u8']);
        $this->assertSame(0, $try['code'], $try['msg'] ?? '');
        $this->assertTrue((bool) ($try['data']['changed'] ?? false));
        $this->assertSame('https://play.example.com/hls/ep1.m3u8', (string) ($try['data']['to'] ?? ''));

        $abs = $svc->tryServer(['server_id' => $onId, 'url' => 'https://cdn.example.com/a.m3u8']);
        $this->assertSame(0, $abs['code']);
        $this->assertFalse((bool) ($abs['data']['changed'] ?? true));
        $this->assertStringContainsString('完整地址', (string) ($abs['msg'] ?? ''));

        $proto = $svc->tryServer(['server_id' => $onId, 'url' => '//cdn.example.com/a.m3u8']);
        $this->assertSame(0, $proto['code']);
        $this->assertFalse((bool) ($proto['data']['changed'] ?? true));

        $off = $svc->tryServer(['server_id' => $offId, 'url' => 'ep1.m3u8']);
        $this->assertSame(0, $off['code']);
        $this->assertFalse((bool) ($off['data']['changed'] ?? true));

        $empty = $svc->tryServer(['server_id' => $emptyId, 'url' => 'ep1.m3u8']);
        $this->assertSame(0, $empty['code']);
        $this->assertFalse((bool) ($empty['data']['changed'] ?? true));

        $none = $svc->tryServer(['url' => 'ep1.m3u8']);
        $this->assertSame(0, $none['code']);
        $this->assertFalse((bool) ($none['data']['changed'] ?? true));

        $blank = $svc->tryServer(['url' => '']);
        $this->assertSame(1, $blank['code']);

        $del = $svc->delete('servers', $onId);
        $this->assertSame(1, $del['code']);
        $this->assertStringContainsString('线路', $del['msg']);
        $this->assertTrue(VideoServer::query()->where('id', $onId)->exists());
    }
}

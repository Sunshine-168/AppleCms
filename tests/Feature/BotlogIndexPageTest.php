<?php

namespace Tests\Feature;

use App\Models\Video\VideoAccessLog;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotlogIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_botlog_index_is_a_spider_hit_board_not_a_generic_crud(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/botlogs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('botlog-index', $html);
        $this->assertStringContainsString('爬虫日志', $html);
        $this->assertStringContainsString('还没有爬虫进来', $html);
        $this->assertStringContainsString('搜 IP、地址或标识', $html);
        $this->assertStringContainsString('不能封 IP', $html);
        $this->assertStringContainsString('同一张前台页面流水', $html);
        $this->assertStringContainsString('/admin/stats/spiders', $html);
        $this->assertStringContainsString('/admin/stats/logs?visitor=spider', $html);
        $this->assertStringContainsString('/admin/video/accesslogs', $html);
        $this->assertStringContainsString('/admin/video/push', $html);
        $this->assertStringContainsString('/robots.txt', $html);
        $this->assertStringContainsString('botlog-batch', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="ua"', $html);
        $this->assertStringNotContainsString("title: 'is_bot'", $html);
        $this->assertStringNotContainsString('class="hint"', $html);
    }

    public function test_save_is_rejected_and_list_names_baidu(): void
    {
        $svc = app(SiteModuleService::class);

        $deny = $svc->save('botlogs', [
            'ip' => '1.1.1.1',
            'url' => 'https://example.com/',
            'ua' => 'Baiduspider',
            'is_bot' => 1,
        ]);
        $this->assertSame(1, $deny['code']);
        $this->assertStringContainsString('不能手添', $deny['msg']);

        VideoAccessLog::query()->create([
            'ip' => '203.0.113.8',
            'url' => 'https://video.example.com/vod/1',
            'ua' => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
            'is_bot' => 1,
            'created_at' => time(),
        ]);
        VideoAccessLog::query()->create([
            'ip' => '198.51.100.2',
            'url' => 'https://video.example.com/',
            'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            'is_bot' => 0,
            'created_at' => time(),
        ]);

        $list = $svc->lists('botlogs', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $this->assertSame(1, (int) ($list['data']['total'] ?? 0));
        $row = $list['data']['data'][0] ?? [];
        $this->assertSame('百度', $row['spider_label'] ?? '');
        $this->assertSame('搜索引擎', $row['group_label'] ?? '');
        $this->assertSame('203.0.113.8', $row['ip'] ?? '');

        $baidu = $svc->lists('botlogs', ['limit' => 20, 'engine' => 'baidu']);
        $this->assertSame(1, (int) ($baidu['data']['total'] ?? 0));
        $google = $svc->lists('botlogs', ['limit' => 20, 'engine' => 'google']);
        $this->assertSame(0, (int) ($google['data']['total'] ?? 0));

        $queues = $svc->botlogQueues();
        $this->assertSame(1, $queues['all']);
        $this->assertSame(1, $queues['baidu']);
        $this->assertSame(0, $queues['google']);
    }
}

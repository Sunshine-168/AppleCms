<?php

namespace Tests\Unit;

use App\Services\Video\SiteToolsService;
use Tests\TestCase;

class SiteToolsProbeTest extends TestCase
{
    public function test_probe_hub_asks_for_url(): void
    {
        $res = app(SiteToolsService::class)->probeHub('   ');
        $this->assertSame(1, $res['code']);
        $this->assertStringContainsString('粘贴', $res['msg']);
    }

    public function test_probe_hub_rejects_javascript_url(): void
    {
        $res = app(SiteToolsService::class)->probeHub('javascript:alert(1)');
        $this->assertSame(1, $res['code']);
        $this->assertStringContainsString('粘贴', $res['msg']);
    }

    public function test_images_page_has_stock_counts(): void
    {
        $page = app(SiteToolsService::class)->imagesPage();
        $this->assertArrayHasKey('remote_n', $page);
        $this->assertArrayHasKey('local_n', $page);
        $this->assertArrayHasKey('empty_n', $page);
        $this->assertArrayHasKey('pic_local', $page);
        $this->assertArrayHasKey('watermark', $page);
    }

    public function test_quality_page_has_issue_counts(): void
    {
        $page = app(SiteToolsService::class)->qualityPage();
        $this->assertContains($page['focus'] ?? '', ['empty_url', 'empty_pic', 'empty_content', 'no_actor', 'repeat', 'missing_ep']);
        $this->assertArrayHasKey('empty_url', $page['counts'] ?? []);
        $this->assertArrayHasKey('missing_ep', $page['counts'] ?? []);
        $this->assertArrayHasKey('repeat_groups', $page['counts'] ?? []);
    }

    public function test_replace_player_asks_for_from(): void
    {
        $res = app(SiteToolsService::class)->replacePlayer('  ', 'dplayer');
        $this->assertSame(1, $res['code']);
        $this->assertStringContainsString('原标识', $res['msg']);
    }

    public function test_replace_player_rejects_same_from_and_to(): void
    {
        $res = app(SiteToolsService::class)->replacePlayer('dplayer', 'dplayer');
        $this->assertSame(1, $res['code']);
        $this->assertTrue(
            str_contains((string) $res['msg'], '一样')
            || str_contains((string) $res['msg'], '没有线路')
            || str_contains((string) $res['msg'], '还没有线路')
        );
    }
}

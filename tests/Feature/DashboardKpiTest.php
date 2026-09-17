<?php

namespace Tests\Feature;

use App\Models\Stat\StatHit;
use App\Services\Admin\VideoDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_play_kpi_counts_play_pages_not_home_or_player_iframe(): void
    {
        $this->hit('/play/12');
        $this->hit('/index.php/vod/play/id/12/sid/1/nid/1.html');
        $this->hit('/');
        $this->hit('/player/12');
        $this->hit('/play/12', spider: true);

        $kpis = app(VideoDashboardService::class)->board()['kpis'];

        $this->assertSame('今日播放', $kpis[0]['label']);
        $this->assertSame(2, $kpis[0]['value']);
        $this->assertSame('今日访问', $kpis[1]['label']);
        $this->assertSame(4, $kpis[1]['value']);
        $this->assertSame('今日入库', $kpis[2]['label']);
        $this->assertSame('待处理', $kpis[3]['label']);
        $this->assertSame(0, $kpis[3]['value']);
    }

    private function hit(string $path, bool $spider = false): void
    {
        StatHit::query()->create([
            'path' => $path,
            'visitor_hash' => hash('sha256', $path.($spider ? '|bot' : '|human')),
            'is_spider' => $spider,
            'spider_name' => $spider ? 'Googlebot' : null,
            'status_code' => 200,
            'created_at' => now(),
        ]);
    }
}

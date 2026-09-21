<?php

namespace Tests\Feature;

use App\Models\Stat\StatHit;
use App\Services\Stats\PageTitleResolver;
use App\Services\Stats\SpiderDetector;
use App\Services\Stats\StatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_stats_and_overview_scan_stat_hits_once_each(): void
    {
        $this->seedHits();
        $stats = app(StatService::class);
        $today = now()->toDateString();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $period = $stats->periodStats($today, $today);
        $this->assertSame(2, $period['pv']);
        $this->assertSame(2, $period['uv']);
        $this->assertSame(1, $period['ip']);
        $this->assertSame(1, $period['spider_pv']);
        $this->assertCount(1, $this->statHitSelects());

        DB::flushQueryLog();
        $overview = $stats->overview();
        $this->assertSame(2, $overview['today']['pv']);
        $this->assertSame(1, $overview['today']['spider_pv']);
        $this->assertCount(1, $this->statHitSelects());
    }

    public function test_play_page_pair_and_search_presence_stay_constant_query(): void
    {
        $this->seedHits();
        $stats = app(StatService::class);
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $bots = count(app(SpiderDetector::class)->watchEngines());

        DB::flushQueryLog();
        DB::enableQueryLog();
        $plays = $stats->playPagePair($today, $yesterday);
        $this->assertSame(1, $plays['today']['pv']);
        $this->assertCount(1, $this->statHitSelects());

        DB::flushQueryLog();
        $stats->searchEnginePresence();
        $this->assertLessThan($bots, count($this->statHitSelects()));
        $this->assertLessThanOrEqual(2, count($this->statHitSelects()));
    }

    public function test_page_titles_batch_video_lookups(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('videos')) {
            $this->markTestSkipped('videos missing');
        }
        \Illuminate\Support\Facades\DB::table('videos')->insert([
            ['id' => 11, 'title' => '甲片', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['id' => 12, 'title' => '乙片', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
        ]);
        $resolver = app(PageTitleResolver::class);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $titles = $resolver->forPaths(['/play/11', '/vod/12']);
        $this->assertSame('甲片', $titles['/play/11']);
        $this->assertSame('乙片', $titles['/vod/12']);
        $videoSelects = array_values(array_filter(
            DB::getQueryLog(),
            fn (array $q) => str_contains($q['query'], 'from "videos"') || str_contains($q['query'], 'from `videos`')
        ));
        $this->assertCount(1, $videoSelects);
    }

    private function seedHits(): void
    {
        $now = now();
        StatHit::query()->create([
            'path' => '/play/9',
            'ip' => '1.1.1.1',
            'visitor_hash' => 'h1',
            'is_spider' => false,
            'status_code' => 200,
            'created_at' => $now,
        ]);
        StatHit::query()->create([
            'path' => '/',
            'ip' => '1.1.1.1',
            'visitor_hash' => 'h2',
            'is_spider' => false,
            'status_code' => 200,
            'created_at' => $now,
        ]);
        StatHit::query()->create([
            'path' => '/',
            'ip' => null,
            'visitor_hash' => 'bot',
            'is_spider' => true,
            'spider_name' => 'Googlebot',
            'status_code' => 200,
            'created_at' => $now,
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function statHitSelects(): array
    {
        return array_values(array_filter(
            DB::getQueryLog(),
            fn (array $q) => str_contains($q['query'], 'stat_hits')
        ));
    }
}

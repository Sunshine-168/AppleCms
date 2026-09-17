<?php

namespace Tests\Feature;

use App\Models\Stat\StatHit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CompactPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_blade_pager_is_count_and_page_boxes_not_numbered_links(): void
    {
        $page = new LengthAwarePaginator(
            [['id' => 1]],
            1,
            20,
            1,
            ['path' => '/admin/stats/logs']
        );
        $html = $page->links()->toHtml();
        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('共1条', $html);
        $this->assertStringContainsString('>1/1</span>', $html);
        $this->assertStringNotContainsString('上一页', $html);
        $this->assertStringNotContainsString('下一页', $html);
        $this->assertStringNotContainsString('>2</a>', $html);
    }

    public function test_blade_pager_shows_prev_next_without_page_numbers(): void
    {
        $page = new LengthAwarePaginator(
            range(1, 20),
            45,
            20,
            2,
            ['path' => '/admin/stats/logs']
        );
        $html = $page->links()->toHtml();
        $this->assertStringContainsString('共45条', $html);
        $this->assertStringContainsString('>2/3</span>', $html);
        $this->assertStringContainsString('上一页', $html);
        $this->assertStringContainsString('下一页', $html);
        $this->assertStringNotContainsString('>1</a>', $html);
        $this->assertStringNotContainsString('>3</a>', $html);
    }

    public function test_empty_list_has_no_pager(): void
    {
        $page = new LengthAwarePaginator([], 0, 20, 1, ['path' => '/admin/stats/logs']);
        $this->assertSame('', trim($page->links()->toHtml()));
    }

    public function test_stats_logs_uses_compact_pager(): void
    {
        StatHit::query()->create([
            'path' => '/',
            'query' => '',
            'ip' => '127.0.0.1',
            'visitor_hash' => 'abc123',
            'user_agent' => 'Mozilla/5.0',
            'referer' => '',
            'locale' => 'zh-CN',
            'is_spider' => false,
            'spider_name' => null,
            'status_code' => 200,
            'created_at' => now(),
        ]);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/stats/logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('共1条', $html);
        $this->assertStringContainsString('>1/1</span>', $html);
        $this->assertStringNotContainsString('>2</a>', $html);
    }
}

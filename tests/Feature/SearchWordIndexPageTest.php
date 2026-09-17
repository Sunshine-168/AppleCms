<?php

namespace Tests\Feature;

use Tests\TestCase;

class SearchWordIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_searchword_index_is_a_hot_search_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/searchwords')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('搜索词', $html);
        $this->assertStringContainsString('还没有人搜过', $html);
        $this->assertStringContainsString('搜关键词', $html);
        $this->assertStringContainsString('sword-batch', $html);
        $this->assertStringContainsString('加一条', $html);
        $this->assertStringContainsString('只搜过一次', $html);
        $this->assertStringContainsString('热搜', $html);
        $this->assertStringContainsString('/search', $html);
        $this->assertStringContainsString('/admin/video/synonyms', $html);
        $this->assertStringContainsString('删掉不会改影片', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('placeholder="word"', $html);
        $this->assertStringNotContainsString("title: 'hits'", $html);
        $this->assertStringNotContainsString("title: 'updated_at'", $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('搜索词统计', $html);
    }
}

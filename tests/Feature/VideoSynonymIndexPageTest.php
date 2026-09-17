<?php

namespace Tests\Feature;

use App\Models\Video\VideoSynonym;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoSynonymIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_synonym_board_is_not_search_hits_or_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/synonyms')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('synonym-index', $html);
        $this->assertStringContainsString('还没有同义词', $html);
        $this->assertStringContainsString('新增规则', $html);
        $this->assertStringContainsString('搜原词或当成的词', $html);
        $this->assertStringContainsString('已经在片库里的名字不会改', $html);
        $this->assertStringContainsString('/admin/video/searchwords', $html);
        $this->assertStringContainsString('/admin/video/synonyms/try', $html);
        $this->assertStringContainsString("title: '原词'", $html);
        $this->assertStringContainsString("title: '当成'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="from_word"', $html);
        $this->assertStringNotContainsString("title: 'from_word'", $html);
        $this->assertStringNotContainsString("title: 'to_word'", $html);
        $this->assertStringNotContainsString("title: 'status'", $html);
    }

    public function test_save_needs_from_and_to_and_rejects_same_or_duplicate(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('synonyms', ['from_word' => '', 'to_word' => '第一季'], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('原词', $empty['msg']);

        $noTo = $svc->save('synonyms', ['from_word' => '第1季', 'to_word' => ''], null);
        $this->assertSame(1, $noTo['code']);
        $this->assertStringContainsString('当成', $noTo['msg']);

        $same = $svc->save('synonyms', ['from_word' => '第一季', 'to_word' => '第一季'], null);
        $this->assertSame(1, $same['code']);
        $this->assertStringContainsString('不能一样', $same['msg']);

        $ok = $svc->save('synonyms', ['from_word' => '第1季', 'to_word' => '第一季', 'status' => 1], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $dup = $svc->save('synonyms', ['from_word' => '第1季', 'to_word' => '第一季'], null);
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('已经有了', $dup['msg']);
    }

    public function test_list_preview_and_try_uses_saved_enabled_rules_only(): void
    {
        $svc = app(SiteModuleService::class);
        $this->assertSame(0, $svc->save('synonyms', ['from_word' => '第1季', 'to_word' => '第一季', 'status' => 1], null)['code']);
        $this->assertSame(0, $svc->save('synonyms', ['from_word' => '美剧', 'to_word' => '美剧集', 'status' => 0], null)['code']);

        VideoSynonym::query()->insert([
            'from_word' => '坏词',
            'to_word' => '',
            'status' => 1,
        ]);

        $list = $svc->lists('synonyms', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $byFrom = [];
        foreach ($rows as $row) {
            $byFrom[(string) ($row['from_word'] ?? '')] = $row;
        }
        $this->assertSame('「第1季」当成「第一季」', (string) ($byFrom['第1季']['preview'] ?? ''));
        $this->assertTrue((bool) ($byFrom['第1季']['is_on'] ?? false));
        $this->assertFalse((bool) ($byFrom['美剧']['is_on'] ?? true));
        $this->assertTrue((bool) ($byFrom['坏词']['empty_to'] ?? false));

        $emptyTo = $svc->lists('synonyms', ['limit' => 20, 'empty_to' => '1']);
        $names = array_column($emptyTo['data']['data'] ?? [], 'from_word');
        $this->assertContains('坏词', $names);
        $this->assertNotContains('第1季', $names);

        $tryOn = $svc->trySynonym(['kw' => '复仇者联盟第1季']);
        $this->assertSame(0, $tryOn['code']);
        $this->assertSame('复仇者联盟第一季', $tryOn['data']['to'] ?? '');
        $this->assertTrue((bool) ($tryOn['data']['changed'] ?? false));

        $tryOff = $svc->trySynonym(['kw' => '美剧']);
        $this->assertSame(0, $tryOff['code']);
        $this->assertSame('美剧', $tryOff['data']['to'] ?? '');
        $this->assertFalse((bool) ($tryOff['data']['changed'] ?? true));

        $tryEmpty = $svc->trySynonym(['kw' => '']);
        $this->assertSame(1, $tryEmpty['code']);
    }
}

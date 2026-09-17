<?php

namespace Tests\Feature;

use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoWebsite;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoWebsiteIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_website_board_is_not_friend_links_or_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/websites')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('website-index', $html);
        $this->assertStringContainsString('还没有网址导航', $html);
        $this->assertStringContainsString('新增站点', $html);
        $this->assertStringContainsString('搜名称、网址或简介', $html);
        $this->assertStringContainsString('友情链接只出现在页脚', $html);
        $this->assertStringContainsString('/admin/video/links', $html);
        $this->assertStringContainsString("title: '站点'", $html);
        $this->assertStringContainsString("title: '分类'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString("title: 'type_id'", $html);
        $this->assertStringNotContainsString("title: 'url'", $html);
    }

    public function test_save_needs_name_url_and_rejects_film_category(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('websites', ['name' => '', 'url' => 'https://example.com'], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('站点名称', $empty['msg']);

        $noUrl = $svc->save('websites', ['name' => '某某站', 'url' => ''], null);
        $this->assertSame(1, $noUrl['code']);
        $this->assertStringContainsString('网址', $noUrl['msg']);

        $missingType = $svc->save('websites', ['name' => '某某站', 'url' => 'example.com', 'type_id' => 99], null);
        $this->assertSame(1, $missingType['code']);
        $this->assertStringContainsString('分类不存在', $missingType['msg']);

        $now = time();
        VideoTypeModel::query()->insert([
            'name' => '电影',
            'mid' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $filmTypeId = (int) VideoTypeModel::query()->orderByDesc('id')->value('id');

        $wrong = $svc->save('websites', [
            'name' => '某某站',
            'url' => 'example.com',
            'type_id' => $filmTypeId,
        ], null);
        $this->assertSame(1, $wrong['code']);
        $this->assertStringContainsString('不是网址导航', $wrong['msg']);
    }

    public function test_list_shows_type_name_and_normalizes_url(): void
    {
        $now = time();
        VideoTypeModel::query()->insert([
            'name' => '资源站',
            'mid' => 3,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $typeId = (int) VideoTypeModel::query()->orderByDesc('id')->value('id');

        $svc = app(SiteModuleService::class);
        $ok = $svc->save('websites', [
            'name' => '某某资源',
            'url' => 'example.com',
            'type_id' => $typeId,
            'blurb' => '采集用',
            'status' => 1,
            'sort' => 2,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $row = VideoWebsite::query()->orderByDesc('id')->first();
        $this->assertNotNull($row);
        $this->assertSame('https://example.com', (string) $row->url);

        $list = $svc->lists('websites', ['limit' => 20, 'q' => '某某']);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $this->assertSame('某某资源', $rows[0]['name'] ?? '');
        $this->assertSame('资源站', $rows[0]['type_name'] ?? '');
        $this->assertSame(0, (int) ($rows[0]['type_wrong'] ?? 1));
        $this->assertSame('https://example.com', $rows[0]['url'] ?? '');

        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/websites/list?q=资源站')
            ->assertOk()
            ->json();
        $this->assertSame(0, $json['code'] ?? 1);
        $found = $json['data']['data'] ?? [];
        $this->assertCount(1, $found);
        $this->assertSame('资源站', $found[0]['type_name'] ?? '');
    }
}

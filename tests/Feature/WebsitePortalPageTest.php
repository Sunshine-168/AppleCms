<?php

namespace Tests\Feature;

use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoWebsite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePortalPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedPortal(): array
    {
        $now = time();
        VideoTypeModel::query()->insert([
            'name' => '工具',
            'mid' => 3,
            'parent_id' => 0,
            'status' => 1,
            'sort' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $parentId = (int) VideoTypeModel::query()->where('name', '工具')->value('id');
        VideoTypeModel::query()->insert([
            'name' => '画图',
            'mid' => 3,
            'parent_id' => $parentId,
            'status' => 1,
            'sort' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $childId = (int) VideoTypeModel::query()->where('name', '画图')->value('id');

        $hotId = VideoWebsite::query()->insertGetId([
            'name' => '热门站',
            'url' => 'https://hot.example',
            'logo' => '',
            'blurb' => '人气最高',
            'type_id' => $parentId,
            'sort' => 1,
            'status' => 1,
            'hits' => 20,
            'created_at' => $now,
        ]);
        $childSiteId = VideoWebsite::query()->insertGetId([
            'name' => '画图助手',
            'url' => 'https://draw.example',
            'logo' => '',
            'blurb' => '子类站点',
            'type_id' => $childId,
            'sort' => 1,
            'status' => 1,
            'hits' => 2,
            'created_at' => $now,
        ]);

        return compact('parentId', 'childId', 'hotId', 'childSiteId');
    }

    public function test_portal_shows_sidebar_groups_hot_and_blurb_search(): void
    {
        $ids = $this->seedPortal();

        $html = $this->get('/website')->assertOk()->getContent();
        $this->assertStringContainsString('web-portal', $html);
        $this->assertStringContainsString('web-portal-side', $html);
        $this->assertStringContainsString('工具', $html);
        $this->assertStringContainsString('画图', $html);
        $this->assertStringContainsString('热门', $html);
        $this->assertStringContainsString('热门站', $html);
        $this->assertStringContainsString('搜站名、简介或网址', $html);

        $this->get('/website?wd='.urlencode('人气最高'))
            ->assertOk()
            ->assertSee('热门站', false);

        $childHtml = $this->get('/website?type_id='.$ids['parentId'])->assertOk()->getContent();
        $this->assertStringContainsString('画图助手', $childHtml);
        $this->assertStringContainsString('热门站', $childHtml);

        $onlyChild = $this->get('/website?type_id='.$ids['childId'])->assertOk()->getContent();
        $this->assertStringContainsString('画图助手', $onlyChild);
        $this->assertStringNotContainsString('热门站', $onlyChild);
    }

    public function test_go_increments_hits_and_detail_shows_related(): void
    {
        $ids = $this->seedPortal();
        VideoWebsite::query()->insertGetId([
            'name' => '同分类乙',
            'url' => 'https://b.example',
            'logo' => '',
            'blurb' => '',
            'type_id' => $ids['parentId'],
            'sort' => 0,
            'status' => 1,
            'hits' => 1,
            'created_at' => time(),
        ]);

        $before = (int) VideoWebsite::query()->find($ids['hotId'])->hits;
        $this->get('/website/'.$ids['hotId'].'/go')
            ->assertRedirect('https://hot.example');
        $this->assertSame($before + 1, (int) VideoWebsite::query()->find($ids['hotId'])->hits);

        $detail = $this->get('/website/'.$ids['hotId'])->assertOk()->getContent();
        $this->assertStringContainsString('人气', $detail);
        $this->assertStringContainsString('同分类推荐', $detail);
        $this->assertStringContainsString('同分类乙', $detail);
        $this->assertStringContainsString('/website/'.$ids['hotId'].'/go', $detail);
    }
}

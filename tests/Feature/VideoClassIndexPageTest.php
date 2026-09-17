<?php

namespace Tests\Feature;

use App\Models\Video\VideoClass;
use App\Models\Video\VideoModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoClassIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_class_board_is_not_the_type_tree_or_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/classes')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('extclass-index', $html);
        $this->assertStringContainsString('还没有扩展分类', $html);
        $this->assertStringContainsString('新增类型词', $html);
        $this->assertStringContainsString('搜类型词', $html);
        $this->assertStringContainsString('栏目树在「分类」', $html);
        $this->assertStringContainsString('/admin/video/types', $html);
        $this->assertStringContainsString('/admin/video/tags', $html);
        $this->assertStringContainsString("title: '类型词'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString("title: 'status'", $html);
    }

    public function test_save_needs_one_word_and_rejects_comma_or_duplicate(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('classes', ['name' => ''], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('类型词', $empty['msg']);

        $comma = $svc->save('classes', ['name' => '喜剧,动作'], null);
        $this->assertSame(1, $comma['code']);
        $this->assertStringContainsString('一个词', $comma['msg']);

        $ok = $svc->save('classes', ['name' => '喜剧', 'status' => 1], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $dup = $svc->save('classes', ['name' => '喜剧'], null);
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('已经有了', $dup['msg']);
    }

    public function test_list_counts_films_that_use_the_word(): void
    {
        $now = time();
        VideoModel::query()->insert([
            'title' => '大话西游',
            'class' => '喜剧,奇幻',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $svc = app(SiteModuleService::class);
        $this->assertSame(0, $svc->save('classes', ['name' => '喜剧', 'status' => 1, 'sort' => 2], null)['code']);
        $this->assertSame(0, $svc->save('classes', ['name' => '动作', 'status' => 1], null)['code']);

        $list = $svc->lists('classes', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $byName = [];
        foreach ($rows as $row) {
            $byName[(string) ($row['name'] ?? '')] = $row;
        }
        $this->assertSame(1, (int) ($byName['喜剧']['used_count'] ?? 0));
        $this->assertSame(0, (int) ($byName['动作']['used_count'] ?? 1));

        $unused = $svc->lists('classes', ['limit' => 20, 'unused' => '1']);
        $names = array_column($unused['data']['data'] ?? [], 'name');
        $this->assertContains('动作', $names);
        $this->assertNotContains('喜剧', $names);
    }
}

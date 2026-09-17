<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Admin\System\SysDatabaseReplaceService;
use App\Services\Admin\System\SysDatabaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplaceIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_replace_index_is_a_catalog_phrase_board_not_a_raw_sql_form(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/replace')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('批量替换', $html);
        $this->assertStringContainsString('影片', $html);
        $this->assertStringContainsString('播放地址', $html);
        $this->assertStringContainsString('看看会改几条', $html);
        $this->assertStringContainsString('确认替换', $html);
        $this->assertStringContainsString('不能撤销', $html);
        $this->assertStringContainsString('/admin/system/database/backup', $html);
        $this->assertStringContainsString('/admin/system/database/sql', $html);
        $this->assertStringNotContainsString('SHOW TABLE STATUS', $html);
        $this->assertStringNotContainsString('尚未选择', $html);
        $this->assertStringNotContainsString('old_text', $html);
        $this->assertStringNotContainsString('id > 100', $html);
        $this->assertStringNotContainsString('请选择表', $html);
        $this->assertStringNotContainsString('留空则对整张表', $html);
        $this->assertStringNotContainsString('dict/tables', $html);
    }

    public function test_tables_list_works_on_sqlite_without_show_table_status(): void
    {
        $list = app(SysDatabaseService::class)->getDatabaseTables();
        $this->assertSame(0, $list['code']);
        $names = array_column($list['data']['data'] ?? [], 'name');
        $this->assertContains('videos', $names);
        $this->assertNotContains('sqlite_sequence', $names);
    }

    public function test_preview_and_replace_only_whitelisted_catalog_fields(): void
    {
        $svc = app(SysDatabaseReplaceService::class);

        $deny = $svc->run('sys_user', ['username'], 'a', 'b');
        $this->assertSame(1, $deny['code']);
        $this->assertStringContainsString('片库', $deny['msg']);

        $this->assertSame('', $svc->targetIdFromTable('sys_user'));
        $this->assertSame('videos', $svc->targetIdFromTable('videos'));

        VideoModel::query()->create([
            'title' => '旧站 old.com 测试片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        VideoModel::query()->create([
            'title' => '无关影片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $empty = $svc->preview('videos', ['title'], '');
        $this->assertSame(1, $empty['code']);

        $same = $svc->run('videos', ['title'], 'old.com', 'old.com');
        $this->assertSame(1, $same['code']);

        $preview = $svc->preview('videos', ['title'], 'old.com');
        $this->assertSame(0, $preview['code']);
        $this->assertSame(1, $preview['data']['matched'] ?? 0);

        $miss = $svc->run('videos', ['title'], '没有这段', 'x');
        $this->assertSame(0, $miss['code']);
        $this->assertSame(0, $miss['data']['matched'] ?? -1);
        $this->assertStringContainsString('库没改', $miss['msg']);

        $run = $svc->run('videos', ['title'], 'old.com', 'new.com');
        $this->assertSame(0, $run['code']);
        $this->assertSame(1, $run['data']['matched'] ?? 0);
        $this->assertSame('旧站 new.com 测试片', VideoModel::query()->orderBy('id')->value('title'));
        $this->assertSame('无关影片', VideoModel::query()->orderByDesc('id')->value('title'));
    }
}

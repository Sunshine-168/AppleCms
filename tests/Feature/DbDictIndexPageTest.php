<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysDatabaseDictCatalog;
use App\Services\Admin\System\SysDatabaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DbDictIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_dict_index_is_a_purpose_board_not_a_raw_schema_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/dict')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('schema-index', $html);
        $this->assertStringContainsString('字段', $html);
        $this->assertStringContainsString('db-tabs', $html);
        $this->assertStringContainsString('class="is-on">字段</a>', $html);
        $this->assertStringContainsString('片库', $html);
        $this->assertStringContainsString('影片', $html);
        $this->assertStringContainsString('漫画', $html);
        $this->assertStringContainsString('小说', $html);
        $this->assertStringContainsString('图集', $html);
        $this->assertStringContainsString('直播', $html);
        $this->assertStringContainsString('插件', $html);
        $this->assertStringContainsString('片名', $html);
        $this->assertStringContainsString('每一列干什么', $html);
        $this->assertStringContainsString('搜表名、中文用途或字段', $html);
        $this->assertStringContainsString('/admin/system/dicts', $html);
        $this->assertStringContainsString('/admin/system/database/backup', $html);
        $this->assertStringContainsString('/admin/system/database/replace', $html);
        $this->assertStringContainsString('/admin/system/database/sql', $html);
        $this->assertStringNotContainsString('请选择表', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('排序规则', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString("title: '字段'", $html);
        $this->assertStringNotContainsString('dbdict-refresh', $html);
    }

    public function test_every_live_table_and_column_has_a_purpose(): void
    {
        $svc = app(SysDatabaseService::class);
        $list = $svc->getDatabaseTables();
        $this->assertSame(0, $list['code']);
        $tables = $list['data']['data'] ?? [];
        $this->assertNotEmpty($tables);
        $names = array_column($tables, 'name');
        $this->assertContains('videos', $names);
        $this->assertContains('sys_user', $names);
        $this->assertNotContains('sqlite_sequence', $names);

        foreach ($tables as $row) {
            $this->assertNotSame('', trim((string) ($row['label'] ?? '')));
            $this->assertNotSame('', trim((string) ($row['hint'] ?? '')));
            $this->assertNotSame('', trim((string) ($row['group_label'] ?? '')));

            $cols = $svc->getDatabaseColumns((string) $row['name']);
            $this->assertSame(0, $cols['code'], $cols['msg'] ?? $row['name']);
            $fields = $cols['data']['data'] ?? [];
            $this->assertNotEmpty($fields, $row['name']);
            foreach ($fields as $col) {
                $purpose = trim((string) ($col['purpose'] ?? ''));
                $this->assertNotSame('', $purpose, $row['name'].'.'.($col['field'] ?? ''));
                $this->assertNotSame((string) ($col['field'] ?? ''), $purpose);
                $this->assertStringNotContainsString('目录按列名推断', $purpose, $row['name'].'.'.($col['field'] ?? ''));
            }
        }

        $videos = $svc->getDatabaseColumns('videos');
        $map = [];
        foreach ($videos['data']['data'] ?? [] as $col) {
            $map[$col['field']] = $col['purpose'];
        }
        $this->assertSame('片名', $map['title'] ?? '');
        $this->assertStringContainsString('回收站', (string) ($map['deleted_at'] ?? ''));
    }

    public function test_unknown_table_is_labeled_honestly_and_bad_name_is_rejected(): void
    {
        $catalog = new SysDatabaseDictCatalog();
        $meta = $catalog->tableMeta('zzz_not_a_real_table');
        $this->assertFalse($meta['known']);
        $this->assertStringContainsString('没有写死用途', $meta['hint']);

        $svc = app(SysDatabaseService::class);
        $bad = $svc->getDatabaseColumns('videos;drop');
        $this->assertSame(1, $bad['code']);
        $missing = $svc->getDatabaseColumns('not_a_real_table');
        $this->assertSame(1, $missing['code']);
    }
}

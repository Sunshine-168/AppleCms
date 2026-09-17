<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Admin\System\SysDatabaseSqlService;
use App\Support\AdminOpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SqlIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_sql_index_is_a_single_statement_board_not_a_waiting_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/sql')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('sql-index', $html);
        $this->assertStringContainsString('执行 SQL', $html);
        $this->assertStringContainsString('db-tabs', $html);
        $this->assertStringContainsString('class="is-on">SQL</a>', $html);
        $this->assertStringContainsString('先备份', $html);
        $this->assertStringContainsString('输入「执行」', $html);
        $this->assertStringContainsString('最多 200 行', $html);
        $this->assertStringContainsString('还没跑过', $html);
        $this->assertStringContainsString('最近片子', $html);
        $this->assertStringContainsString('FROM videos', $html);
        $this->assertStringContainsString('/admin/system/database/backup', $html);
        $this->assertStringContainsString('/admin/system/database/replace', $html);
        $this->assertStringContainsString('/admin/system/database/dict', $html);
        $this->assertStringNotContainsString('hub-steps', $html);
        $this->assertStringNotContainsString('等待执行', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('FROM users', $html);
        $this->assertStringNotContainsString('请输入一条SQL语句', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('刷新列表', $html);
        $this->assertStringNotContainsString('TRUNCATE', $html);
    }

    public function test_select_without_from_returns_rows(): void
    {
        $svc = app(SysDatabaseSqlService::class);
        $res = $svc->run('SELECT 1 AS v');
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertSame('query', $res['data']['type'] ?? '');
        $this->assertSame(1, (int) ($res['data']['count'] ?? 0));
        $this->assertSame('1', (string) ($res['data']['rows'][0]['v'] ?? ''));
        $this->assertStringContainsString('查到 1 行', (string) ($res['msg'] ?? ''));
    }

    public function test_select_from_without_limit_is_rejected(): void
    {
        $svc = app(SysDatabaseSqlService::class);
        $res = $svc->run('SELECT id, title FROM videos');
        $this->assertSame(1, $res['code']);
        $this->assertStringContainsString('LIMIT', $res['msg']);
    }

    public function test_write_needs_confirm_word_and_drop_is_blocked(): void
    {
        $svc = app(SysDatabaseSqlService::class);
        $this->app['session']->put('admin_uid', 1);
        $this->app['session']->put('admin_username', 'admin');

        $noWord = $svc->run("UPDATE videos SET title = title WHERE id = -1");
        $this->assertSame(1, $noWord['code']);
        $this->assertStringContainsString('执行', $noWord['msg']);

        $ok = $svc->run("UPDATE videos SET title = title WHERE id = -1", '执行');
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertSame('affecting', $ok['data']['type'] ?? '');
        $this->assertSame(0, (int) ($ok['data']['affected'] ?? -1));
        $this->assertStringContainsString('改了 0 行', (string) ($ok['msg'] ?? ''));

        $drop = $svc->run('DROP TABLE videos', '执行');
        $this->assertSame(1, $drop['code']);
        $this->assertStringContainsString('不能', $drop['msg']);

        $two = $svc->run('SELECT 1; SELECT 2');
        $this->assertSame(1, $two['code']);
        $this->assertStringContainsString('一条', $two['msg']);
    }

    public function test_delete_is_only_for_admin_one_and_op_log_mentions_sql(): void
    {
        $svc = app(SysDatabaseSqlService::class);
        $this->app['session']->put('admin_uid', 2);
        $this->app['session']->put('admin_username', 'editor');
        $deny = $svc->run('DELETE FROM videos WHERE id = -1', '执行');
        $this->assertSame(1, $deny['code']);
        $this->assertStringContainsString('1 号', $deny['msg']);

        $this->assertStringContainsString(
            '查了 SQL：SELECT id FROM videos LIMIT 1',
            (string) AdminOpLog::labelFromPath('POST', 'admin/system/database/sql/run', [
                'sql' => 'SELECT id FROM videos LIMIT 1',
            ])
        );
    }

    public function test_videos_select_with_limit_works(): void
    {
        VideoModel::query()->create([
            'title' => 'sql-board-film',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $svc = app(SysDatabaseSqlService::class);
        $res = $svc->run('SELECT id, title FROM videos ORDER BY id DESC LIMIT 10');
        $this->assertSame(0, $res['code'], $res['msg'] ?? '');
        $this->assertGreaterThanOrEqual(1, (int) ($res['data']['count'] ?? 0));
        $titles = array_column($res['data']['rows'] ?? [], 'title');
        $this->assertContains('sql-board-film', $titles);
    }
}

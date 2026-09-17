<?php

namespace Tests\Feature;

use App\Models\System\SysScheduleModel;
use App\Services\Admin\System\SysDatabaseBackupService;
use App\Services\Admin\System\SysScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_backup_index_is_a_timed_workbench_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/backup')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('backup-index', $html);
        $this->assertStringContainsString('数据库备份', $html);
        $this->assertStringContainsString('db-tabs', $html);
        $this->assertStringContainsString('class="is-on">备份</a>', $html);
        $this->assertStringContainsString('>恢复</a>', $html);
        $this->assertStringContainsString('>SQL</a>', $html);
        $this->assertStringContainsString('>替换</a>', $html);
        $this->assertStringContainsString('>字段</a>', $html);
        $this->assertStringContainsString('立刻备份', $html);
        $this->assertStringContainsString('到点自动备份', $html);
        $this->assertStringContainsString('还没有备份', $html);
        $this->assertStringContainsString('schedule:run', $html);
        $this->assertStringContainsString('内存 SQLite', $html);
        $this->assertStringContainsString('/admin/system/tools/schedule', $html);
        $this->assertStringContainsString('/admin/system/database/restore', $html);
        $this->assertStringNotContainsString('去恢复', $html);
        $this->assertStringContainsString('留最近几份', $html);
        $this->assertStringNotContainsString('>刷新', $html);
        $this->assertStringNotContainsString('刷新列表', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('大小(B)', $html);
        $this->assertStringNotContainsString('立即备份', $html);
        $this->assertStringNotContainsString('db:backup', $html);
    }

    public function test_restore_index_is_a_cover_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/database/restore')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('restore-index', $html);
        $this->assertStringContainsString('db-tabs', $html);
        $this->assertStringContainsString('class="is-on">恢复</a>', $html);
        $this->assertStringContainsString('盖回当前库', $html);
        $this->assertStringContainsString('还没有可恢复的备份', $html);
        $this->assertStringContainsString('盖回这份', $html);
        $this->assertStringContainsString('输入「盖回」', $html);
        $this->assertStringContainsString('没法盖回去', $html);
        $this->assertStringContainsString('/admin/system/database/backup', $html);
        $this->assertStringContainsString('/admin/system/tools/cache', $html);
        $this->assertStringNotContainsString('刷新列表', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('大小(B)', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('恢复会覆盖当前数据库数据', $html);
    }

    public function test_memory_sqlite_backup_fails_honestly(): void
    {
        $svc = app(SysDatabaseBackupService::class);
        $res = $svc->runBackup(7);
        $this->assertSame(1, $res['code']);
        $this->assertStringContainsString('内存库', $res['msg']);
    }

    public function test_sqlite_file_backup_keeps_last_n_and_can_restore(): void
    {
        $src = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lv_src_'.uniqid('', true).'.sqlite';
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lv_bak_'.uniqid('', true);
        mkdir($dir, 0777, true);
        $pdo = new \PDO('sqlite:'.$src);
        $pdo->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, name TEXT)');
        $pdo->exec("INSERT INTO t (name) VALUES ('keep-me')");
        $pdo = null;

        $originalDb = config('database.connections.sqlite.database');
        $originalDir = config('database.backup_path');
        config([
            'database.connections.sqlite.database' => $src,
            'database.backup_path' => $dir,
        ]);

        try {
            $svc = app(SysDatabaseBackupService::class);
            $first = $svc->runBackup(7);
            $this->assertSame(0, $first['code'], $first['msg'] ?? '');
            $this->assertNotEmpty($first['data']['name'] ?? '');
            $this->assertFileExists($dir.DIRECTORY_SEPARATOR.$first['data']['name']);

            $second = $svc->runBackup(1);
            $this->assertSame(0, $second['code'], $second['msg'] ?? '');
            $list = $svc->listBackupFiles();
            $this->assertSame(0, $list['code']);
            $this->assertSame(1, (int) ($list['data']['total'] ?? 0));
            $name = (string) ($list['data']['data'][0]['name'] ?? '');
            $this->assertStringEndsWith('.sqlite', $name);
            $this->assertNotEmpty($list['data']['data'][0]['size_text'] ?? '');

            $pdo = new \PDO('sqlite:'.$src);
            $pdo->exec("UPDATE t SET name = 'changed'");
            $pdo = null;

            $restore = $svc->restoreBackup($name);
            $this->assertSame(0, $restore['code'], $restore['msg'] ?? '');
            $pdo = new \PDO('sqlite:'.$src);
            $val = (string) $pdo->query('SELECT name FROM t LIMIT 1')->fetchColumn();
            $pdo = null;
            $this->assertSame('keep-me', $val);

            $pdo = new \PDO('sqlite:'.$src);
            $pdo->exec("UPDATE t SET name = 'changed-again'");
            $pdo = null;
            $again = $svc->restoreBackup($name, true);
            $this->assertSame(0, $again['code'], $again['msg'] ?? '');
            $this->assertNotSame('', (string) ($again['data']['snapshot'] ?? ''));
            $this->assertFileExists($dir.DIRECTORY_SEPARATOR.$again['data']['snapshot']);
            $this->assertStringContainsString('另存', (string) ($again['msg'] ?? ''));
            $pdo = new \PDO('sqlite:'.$src);
            $val = (string) $pdo->query('SELECT name FROM t LIMIT 1')->fetchColumn();
            $pdo = null;
            $this->assertSame('keep-me', $val);
        } finally {
            config([
                'database.connections.sqlite.database' => $originalDb,
                'database.backup_path' => $originalDir,
            ]);
            @unlink($src);
            foreach (glob($dir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
    }

    public function test_schedule_accepts_backup_command_and_rejects_db_prefix(): void
    {
        $svc = app(SysScheduleService::class);
        $deny = $svc->saveSchedule(
            0, '清库', '', 'artisan', 'db:backup', '', '0 3 * * *',
            'Asia/Shanghai', 1, 1, 0, 0, 0, 1, '', 0
        );
        $this->assertSame(1, $deny['code']);
        $this->assertStringContainsString('不能当定时任务', $deny['msg']);

        $ok = $svc->saveSchedule(
            0, '每天备份库', '', 'artisan', 'video:db-backup', '--keep=7', '0 3 * * *',
            'Asia/Shanghai', 1, 1, 0, 0, 0, 1, '', 0
        );
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertGreaterThan(0, (int) ($ok['data']['id'] ?? 0));

        $backup = app(SysDatabaseBackupService::class);
        $same = $backup->saveBackupSchedule(true, '0 2 * * *', 7);
        $this->assertSame(1, $same['code']);
        $this->assertStringContainsString('内存库', $same['msg']);

        $noop = $backup->saveBackupSchedule(false, '0 3 * * *', 7);
        $this->assertSame(0, $noop['code'], $noop['msg'] ?? '');
        $row = SysScheduleModel::query()->where('command', 'video:db-backup')->first();
        $this->assertNotNull($row);
        $this->assertSame(0, (int) $row->status);
    }
}

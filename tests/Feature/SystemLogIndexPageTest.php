<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemLogIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_system_log_index_is_an_error_timeline_not_a_request_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/monitor/system-logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('system-log-index', $html);
        $this->assertStringContainsString('系统日志', $html);
        $this->assertStringContainsString('报错', $html);
        $this->assertStringContainsString('还没有程序报错', $html);
        $this->assertStringContainsString('搜报错内容、地址或管理员', $html);
        $this->assertStringContainsString('程序抛错会记一行', $html);
        $this->assertStringContainsString('laravel.log', $html);
        $this->assertStringContainsString('后台', $html);
        $this->assertStringContainsString('今天', $html);
        $this->assertStringContainsString('全部时间', $html);
        $this->assertStringContainsString('仅报错', $html);
        $this->assertStringContainsString('全部位置', $html);
        $this->assertStringContainsString('log-tabs', $html);
        $this->assertStringNotContainsString('queue-chips', $html);
        $this->assertStringContainsString('>查询<', $html);
        $this->assertStringContainsString('>重置<', $html);
        $this->assertStringContainsString('/admin/system/monitor/operate-logs', $html);
        $this->assertStringContainsString('/admin/system/monitor/login-logs', $html);
        $this->assertMatchesRegularExpression('/id="system-log-ip-chip"[^>]*\bhidden\b/', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="级别 info/error', $html);
        $this->assertStringNotContainsString('placeholder="RequestId"', $html);
        $this->assertStringNotContainsString('placeholder="通道"', $html);
        $this->assertStringNotContainsString("title: 'RequestId'", $html);
        $this->assertStringNotContainsString("title: 'UID'", $html);
        $this->assertStringNotContainsString('system-log-refresh-btn', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
    }

    public function test_list_names_query_exception_as_database_and_hides_host(): void
    {
        DB::table('sys_system_log')->insert([
            'level' => 'error',
            'channel' => 'system',
            'module' => 'admin',
            'message' => 'SQLSTATE[HY000]: no such table: dict_tables',
            'context' => '',
            'extra' => 'QueryTrait->paginates',
            'exception_class' => 'Illuminate\\Database\\QueryException',
            'exception_message' => 'SQLSTATE[HY000]: no such table: dict_tables',
            'file' => 'D:\\Project\\LaraVideo\\app\\Services\\Admin\\System\\SysDatabaseService.php',
            'line' => 88,
            'trace' => '#0 {main}',
            'request_id' => 'c61b6805-c0f5-419d-9d4e-9afa3248e046',
            'method' => 'GET',
            'url' => 'http://127.0.0.1:8010/admin/system/database/dict/tables',
            'ip' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'uid' => 1,
            'username' => 'admin',
            'create_time' => time(),
            'update_time' => time(),
        ]);

        $res = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/monitor/system-logs/list?limit=20')
            ->assertOk()
            ->json();

        $this->assertSame(0, $res['code'] ?? 1);
        $row = $res['data']['data'][0] ?? [];
        $this->assertSame('数据库', $row['kind_text'] ?? '');
        $this->assertStringContainsString('dict_tables', (string) ($row['summary'] ?? ''));
        $this->assertSame('/admin/system/database/dict/tables', $row['path_text'] ?? '');
        $this->assertSame('SysDatabaseService.php:88', $row['file_text'] ?? '');
        $this->assertSame('后台', $row['area_text'] ?? '');
        $this->assertSame('admin', $row['who_text'] ?? '');
        $this->assertStringNotContainsString('127.0.0.1:8010', (string) ($row['path_text'] ?? ''));

        $svc = app(SysUserService::class);
        $admin = $svc->getSysSystemLogLists(['area' => 'admin', 'limit' => 20]);
        $this->assertSame(1, (int) ($admin['data']['total'] ?? 0));
        $front = $svc->getSysSystemLogLists(['area' => 'front', 'limit' => 20]);
        $this->assertSame(0, (int) ($front['data']['total'] ?? 0));
        $q = $svc->getSysSystemLogLists(['q' => 'dict_tables', 'limit' => 20]);
        $this->assertSame(1, (int) ($q['data']['total'] ?? 0));
    }
}

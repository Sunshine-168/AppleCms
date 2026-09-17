<?php

namespace Tests\Feature;

use App\Support\AdminOpLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperateLogIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_operate_log_index_is_a_timeline_not_a_route_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/monitor/operate-logs')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('操作日志', $html);
        $this->assertStringContainsString('还没有操作记录', $html);
        $this->assertStringContainsString('搜操作人、内容或模块', $html);
        $this->assertStringContainsString('后台改数据会记一行', $html);
        $this->assertStringContainsString('log-tabs', $html);
        $this->assertStringContainsString('全部时间', $html);
        $this->assertStringContainsString('今天', $html);
        $this->assertStringContainsString('近7天', $html);
        $this->assertStringContainsString('只看我', $html);
        $this->assertStringNotContainsString('queue-chips', $html);
        $this->assertStringContainsString('>查询<', $html);
        $this->assertStringContainsString('>重置<', $html);
        $this->assertStringContainsString('/admin/system/monitor/login-logs', $html);
        $this->assertStringContainsString('/admin/system/monitor/system-logs', $html);
        $this->assertMatchesRegularExpression('/id="operate-log-ip-chip"[^>]*\bhidden\b/', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('title: \'ID\'', $html);
        $this->assertStringNotContainsString('placeholder="用户名"', $html);
        $this->assertStringNotContainsString('placeholder="URL"', $html);
        $this->assertStringNotContainsString('耗时ms', $html);
        $this->assertStringNotContainsString('操作日志管理', $html);
    }

    public function test_written_summary_shows_on_operate_log_list(): void
    {
        $this->ensureOperateLogTable();
        AdminOpLog::beginRequest();
        AdminOpLog::write('save', '保存了影片《测试片》', [
            'module' => '影片',
            'target_type' => 'videos',
            'target_id' => 12,
            'uid' => 1,
            'username' => 'admin',
            'payload' => ['id' => 12, 'password' => 'secret'],
        ]);

        $res = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/monitor/operate-logs/list')
            ->assertOk()
            ->json();

        $this->assertSame(0, $res['code'] ?? 1);
        $rows = $res['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $this->assertStringContainsString('保存了影片《测试片》', (string) ($rows[0]['summary'] ?? $rows[0]['title'] ?? ''));
        $this->assertSame('影片', $rows[0]['module_text'] ?? $rows[0]['module'] ?? '');
        $payload = (string) ($rows[0]['request_data'] ?? '');
        $this->assertStringNotContainsString('secret', $payload);
    }

    private function ensureOperateLogTable(): void
    {
        if (Schema::hasTable('sys_operate_log')) {
            return;
        }
        Schema::create('sys_operate_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('uid')->default(0);
            $table->string('username', 50)->default('');
            $table->string('title', 100)->default('');
            $table->string('action', 50)->default('');
            $table->string('permission', 100)->default('');
            $table->string('module', 50)->default('');
            $table->string('method', 10)->default('');
            $table->string('url', 255)->default('');
            $table->string('route', 150)->default('');
            $table->mediumText('request_data')->nullable();
            $table->integer('response_code')->default(0);
            $table->string('response_msg', 255)->default('');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->string('login_ip', 45)->default('');
            $table->string('ip_address', 255)->default('');
            $table->string('user_agent', 255)->default('');
            $table->string('referer', 255)->default('');
            $table->string('target_type', 50)->default('');
            $table->unsignedInteger('target_id')->default(0);
            $table->unsignedInteger('create_time')->default(0);
            $table->unsignedInteger('update_time')->default(0);
        });
    }
}

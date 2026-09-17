<?php

namespace Tests\Feature;

use App\Models\System\SysScheduleLogModel;
use App\Models\System\SysScheduleModel;
use App\Services\Admin\System\SysScheduleService;
use App\Support\Plugins\PluginHost;
use App\Support\Plugins\PluginScheduleHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_schedule_index_is_a_crontab_workbench_not_a_generic_crud(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/tools/schedule')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('schedule-index', $html);
        $this->assertStringContainsString('定时任务', $html);
        $this->assertStringContainsString('系统自带', $html);
        $this->assertStringContainsString('还没有自己加的任务', $html);
        $this->assertStringContainsString('schedule:run', $html);
        $this->assertStringContainsString('先装计划', $html);
        $this->assertStringContainsString('加百度推送', $html);
        $this->assertStringContainsString('到期采集', $html);
        $this->assertStringContainsString('artisan serve', $html);
        $this->assertStringContainsString('成败和耗时', $html);
        $this->assertStringContainsString('/admin/video/collect_tasks', $html);
        $this->assertStringContainsString('/admin/video/push', $html);
        $this->assertStringContainsString('/admin/system/tools/cache', $html);
        $this->assertStringContainsString('采集片子', $html);
        $this->assertStringContainsString('备份 / 推送 / 插件', $html);
        $this->assertStringContainsString('hub-tabs', $html);
        $this->assertStringNotContainsString('清过期短信码', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('未知', $html);
        $this->assertStringNotContainsString('cache:clear', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('新增任务', $html);
    }

    public function test_save_rejects_dangerous_artisan_and_non_http_urls(): void
    {
        $svc = app(SysScheduleService::class);

        $migrate = $this->saveJob($svc, [
            'name' => '清库',
            'type' => 'artisan',
            'command' => 'migrate:fresh',
        ]);
        $this->assertSame(1, $migrate['code']);
        $this->assertStringContainsString('不能当定时任务', $migrate['msg']);

        $clear = $this->saveJob($svc, [
            'name' => '清缓存',
            'type' => 'artisan',
            'command' => 'cache:clear',
        ]);
        $this->assertSame(1, $clear['code']);
        $this->assertStringContainsString('只接受本站', $clear['msg']);

        $js = $this->saveJob($svc, [
            'name' => '坏网址',
            'type' => 'http',
            'command' => 'javascript:alert(1)',
        ]);
        $this->assertSame(1, $js['code']);
        $this->assertStringContainsString('http', $js['msg']);

        $ok = $this->saveJob($svc, [
            'name' => '每天推百度',
            'type' => 'artisan',
            'command' => 'video:baidu-push',
            'params' => '--limit=50',
            'cron' => '0 4 * * *',
        ]);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertGreaterThan(0, (int) ($ok['data']['id'] ?? 0));
    }

    public function test_added_task_shows_on_the_board_and_can_run_once(): void
    {
        $svc = app(SysScheduleService::class);
        $saved = $this->saveJob($svc, [
            'name' => '人气日清试跑',
            'type' => 'artisan',
            'command' => 'video:hits-reset',
            'cron' => '0 5 * * *',
        ]);
        $this->assertSame(0, $saved['code'], $saved['msg'] ?? '');
        $id = (int) ($saved['data']['id'] ?? 0);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/tools/schedule')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('人气日清试跑', $html);
        $this->assertStringContainsString('立刻跑', $html);
        $this->assertStringContainsString('schedule-switch', $html);
        $this->assertStringContainsString('js-edit', $html);
        $this->assertStringContainsString('还没跑过', $html);
        $this->assertStringNotContainsString('schedule-state', $html);
        $this->assertStringNotContainsString('js-toggle">停用', $html);
        $this->assertStringNotContainsString('还没有自己加的任务', $html);

        $off = $svc->updateScheduleStatus($id, 0);
        $this->assertSame(0, $off['code']);
        $this->assertStringContainsString('已停', $off['msg']);
        $on = $svc->updateScheduleStatus($id, 1);
        $this->assertSame(0, $on['code']);
        $this->assertStringContainsString('开着', $on['msg']);

        $run = $svc->runScheduleOnce($id);
        $this->assertSame(0, $run['code'], $run['msg'] ?? '');
        $this->assertArrayHasKey('duration_ms', $run['data'] ?? []);
        $this->assertGreaterThanOrEqual(0, (int) ($run['data']['duration_ms'] ?? -1));
        $this->assertStringContainsString('秒', (string) ($run['data']['duration_text'] ?? $run['msg'] ?? ''));
        $row = SysScheduleModel::query()->find($id);
        $this->assertNotNull($row);
        $this->assertGreaterThan(0, (int) $row->last_run_time);
        $this->assertSame(1, (int) $row->last_status);
        $this->assertGreaterThanOrEqual(0, (int) $row->last_duration_ms);
        $log = SysScheduleLogModel::query()->where('schedule_id', $id)->orderByDesc('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(1, (int) $log->status);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/tools/schedule')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('上次成功', $html);
        $this->assertStringContainsString('最近几次', $html);
        $this->assertStringNotContainsString('未知', $html);
        $this->assertStringNotContainsString('还没跑过', $html);

        $legacy = $svc->presentTask([
            'id' => $id,
            'name' => '旧记录',
            'type' => 'artisan',
            'command' => 'video:hits-reset',
            'params' => '',
            'cron_expression' => '0 5 * * *',
            'status' => 1,
            'last_run_time' => time(),
            'last_status' => 0,
            'last_error' => '',
            'last_duration_ms' => 0,
        ]);
        $this->assertStringContainsString('没记下成败和耗时', $legacy['last_text']);
        $this->assertStringNotContainsString('未知', $legacy['last_text']);
    }

    public function test_plugin_declared_jobs_can_be_saved_and_run(): void
    {
        app(PluginHost::class)->scheduleJob([
            'plugin' => 'demo',
            'plugin_label' => '演示插件',
            'id' => 'stats',
            'label' => '数据统计',
            'hint' => '插件在 plugin.json 里声明的任务',
            'cron' => '0 5 * * *',
            'handler' => ScheduleDemoStatsJob::class,
        ]);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/tools/schedule')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('数据统计', $html);
        $this->assertStringContainsString('plugin:run demo stats', $html);
        $this->assertStringContainsString('加数据统计', $html);

        $svc = app(SysScheduleService::class);
        $ghost = $this->saveJob($svc, [
            'name' => '假插件',
            'type' => 'artisan',
            'command' => 'plugin:run',
            'params' => 'ghost stats',
        ]);
        $this->assertSame(1, $ghost['code']);
        $this->assertStringContainsString('没有这条插件任务', $ghost['msg']);

        $saved = $this->saveJob($svc, [
            'name' => '每天统计',
            'type' => 'artisan',
            'command' => 'plugin:run demo stats',
            'cron' => '0 5 * * *',
        ]);
        $this->assertSame(0, $saved['code'], $saved['msg'] ?? '');
        $id = (int) ($saved['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $id);
        $row = SysScheduleModel::query()->find($id);
        $this->assertNotNull($row);
        $this->assertSame('plugin:run', (string) $row->command);
        $this->assertSame('demo stats', trim((string) $row->params));

        $run = $svc->runScheduleOnce($id);
        $this->assertSame(0, $run['code'], $run['msg'] ?? '');
        $this->assertStringContainsString('插件统计跑完了', (string) ($run['data']['output'] ?? ''));
    }

    /** @param  array<string, mixed>  $job */
    private function saveJob(SysScheduleService $svc, array $job): array
    {
        return $svc->saveSchedule(
            0,
            (string) ($job['name'] ?? ''),
            '',
            (string) ($job['type'] ?? 'artisan'),
            (string) ($job['command'] ?? ''),
            (string) ($job['params'] ?? ''),
            (string) ($job['cron'] ?? '0 4 * * *'),
            'Asia/Shanghai',
            1,
            1,
            0,
            0,
            0,
            1,
            '',
            0
        );
    }
}

class ScheduleDemoStatsJob implements PluginScheduleHandler
{
    public function handle(): string
    {
        return '插件统计跑完了';
    }
}

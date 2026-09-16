<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectTaskIndexPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_collect_task_index_is_a_schedule_board_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collect_tasks')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('还没有定时任务', $html);
        $this->assertStringContainsString('跑到期任务', $html);
        $this->assertStringContainsString('/admin/video/collect_tasks/create', $html);
        $this->assertStringContainsString('搜任务或采集源', $html);
        $this->assertStringContainsString('ctask-batch', $html);
        $this->assertStringContainsString('从未跑', $html);
        $this->assertStringContainsString('php artisan schedule:run', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertMatchesRegularExpression('/id="ctask-source-chip"[^>]*\bhidden\b/', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('执行到期采集', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString('title: \'cron_expression\'', $html);
        $this->assertStringNotContainsString('title: \'collect_source_id\'', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
    }

    public function test_collect_task_create_picks_a_source_not_a_numeric_id(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collect_tasks/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('请选择采集源', $html);
        $this->assertStringContainsString('每小时', $html);
        $this->assertStringContainsString('当天更新', $html);
        $this->assertStringContainsString('启用，到期会跑', $html);
        $this->assertStringContainsString('/admin/video/collects', $html);
        $this->assertStringNotContainsString('采集源ID', $html);
        $this->assertStringNotContainsString('ui-dialog', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('mod-add', $html);
    }
}

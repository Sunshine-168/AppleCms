<?php

namespace Tests\Feature;

use App\Services\Admin\Video\SiteModuleService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_workbench_is_nested_not_three_siblings(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/activity')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('activity-board', $html);
        $this->assertMatchesRegularExpression(
            '/<details class="nav-fold is-open"[^>]*>\s*<summary>\s*更多\s*<\/summary>/u',
            $html
        );
        $this->assertStringContainsString('对照苹果：每日签到当场入账', $html);
        $this->assertStringContainsString('分享不是微信', $html);
        $this->assertStringContainsString('绑定邮箱默认关闭', $html);
        $this->assertStringContainsString('任务', $html);
        $this->assertStringContainsString('记录', $html);
        $this->assertStringContainsString('签到', $html);
        $this->assertStringContainsString('里程碑', $html);
        $this->assertStringNotContainsString('nav-fold-nested', $html);
        $this->assertStringContainsString('/admin/video/activity?desk=logs', $html);
        $this->assertStringContainsString('/admin/video/activity?desk=signs', $html);
        $this->assertStringContainsString('/admin/video/activity?desk=milestones', $html);
        $this->assertStringContainsString("/admin/video/' + module + '/list'", $html);
        $this->assertStringContainsString("/admin/video/' + module + '/save'", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('href="/admin/video/task_logs"', $html);
        $this->assertStringNotContainsString('href="/admin/video/sign_milestones"', $html);

        $members = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/members')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('nav-fold-nested', $members);
        $this->assertStringContainsString('/admin/video/activity', $members);
        $this->assertStringNotContainsString('/admin/video/activity?desk=signs', $members);
        $this->assertStringNotContainsString('href="/admin/video/task_logs"', $members);
    }

    public function test_missing_activity_tables_keep_the_migration_error(): void
    {
        Schema::dropIfExists('member_tasks');
        $list = app(SiteModuleService::class)->lists('activity', ['limit' => 15]);
        $this->assertSame(1, $list['code']);
        $this->assertSame('请先执行数据库迁移', $list['msg']);
    }

    public function test_old_plugin_signs_url_redirects_to_activity(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/signs')
            ->assertRedirect('/admin/video/activity?desk=signs');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/task_logs')
            ->assertRedirect('/admin/video/activity?desk=logs');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/sign_milestones')
            ->assertRedirect('/admin/video/activity?desk=milestones');
    }

    public function test_save_and_list_decorate_activity_modules(): void
    {
        $svc = app(SiteModuleService::class);
        $empty = $svc->save('activity', ['name' => '  '], null);
        $this->assertSame(1, $empty['code']);

        $dup = $svc->save('activity', [
            'name' => '再签一次',
            'type' => 1,
            'action' => 'daily_sign',
            'points' => 9,
            'target' => 1,
            'status' => 1,
        ], null);
        $this->assertSame(1, $dup['code']);

        $ok = $svc->save('activity', [
            'name' => ' 自定义 ',
            'type' => 1,
            'action' => 'watch_art',
            'hint' => '本站没有文章任务接线',
            'points' => 4,
            'target' => 2,
            'status' => 0,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $list = $svc->lists('activity', ['limit' => 20, 'q' => '自定义', 'status' => 0]);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $this->assertSame('自定义', $rows[0]['name'] ?? '');
        $this->assertSame('每日', $rows[0]['type_label'] ?? '');
        $this->assertSame('未启用', $rows[0]['status_label'] ?? '');

        $fakeLog = $svc->save('task_logs', ['member_id' => 1, 'action' => 'daily_sign'], null);
        $this->assertSame(1, $fakeLog['code']);
        $fakeSign = $svc->save('signs', ['member_id' => 1, 'day_key' => '20260101'], null);
        $this->assertSame(1, $fakeSign['code']);
    }

    public function test_disabled_member_social_keeps_core_activity(): void
    {
        app(PluginManager::class)->setEnabled('member_social', false);
        $this->refreshApplication();
        $this->actingAsAdmin();
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/activity')
            ->assertOk()
            ->assertSee('activity-board', false);
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/signs')
            ->assertRedirect('/admin/video/activity?desk=signs');
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/follows')
            ->assertNotFound();
        $this->restoreMemberSocial();
    }

    protected function tearDown(): void
    {
        $this->restoreMemberSocial();
        parent::tearDown();
    }

    private function restoreMemberSocial(): void
    {
        $file = base_path('plugins/MemberSocial/plugin.json');
        if (! is_file($file)) {
            return;
        }
        $raw = json_decode((string) file_get_contents($file), true);
        if (! is_array($raw)) {
            return;
        }
        $raw['enabled'] = true;
        $json = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            file_put_contents($file, $json.PHP_EOL);
        }
    }
}

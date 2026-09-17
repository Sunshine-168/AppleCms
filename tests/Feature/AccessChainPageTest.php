<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysPermService;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessChainPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_menus_page_is_the_first_step_of_the_access_chain(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/menus')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('access-chain', $html);
        $this->assertStringContainsString('class="is-on">菜单</a>', $html);
        $this->assertStringContainsString('对齐当前工作区', $html);
        $this->assertStringContainsString('/admin/system/roles', $html);
        $this->assertStringContainsString('/admin/user', $html);
        $this->assertStringNotContainsString('有哪些后台页', $html);
        $this->assertStringNotContainsString('menu-refresh-btn', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString("title: 'ID'", $html);
    }

    public function test_workspace_pages_include_admins_roles_and_menus(): void
    {
        $urls = [];
        foreach (AdminNav::workspacePages() as $mod) {
            foreach ($mod['items'] as $item) {
                $urls[] = $item['url'];
            }
        }

        $this->assertContains('/admin/user', $urls);
        $this->assertContains('/admin/system/roles', $urls);
        $this->assertContains('/admin/system/menus', $urls);
        $this->assertContains('/admin/system/database/backup', $urls);
        $this->assertContains('/admin/system/monitor/login-logs', $urls);
        $this->assertNotContains('/admin/system/database/restore', $urls);
        $this->assertNotContains('/admin/system/database/sql', $urls);
        $this->assertNotContains('/admin/system/database/replace', $urls);
        $this->assertNotContains('/admin/system/database/dict', $urls);
        $this->assertNotContains('/admin/system/monitor/operate-logs', $urls);
        $this->assertNotContains('/admin/welcome', $urls);
        $this->assertNotContains('/admin/plugins', $urls);
    }

    public function test_sync_writes_sidebar_pages_and_staff_without_a_role_cannot_open_admins(): void
    {
        $res = (new SysPermService())->syncFromWorkspaces();
        $this->assertSame(0, $res['code']);
        $this->assertGreaterThan(0, DB::table('sys_perm')->where('api', '/admin/user')->count());

        $now = time();
        DB::table('sys_user')->insert([
            'id' => 2,
            'username' => 'staff',
            'password' => 'x',
            'email' => '',
            'remark' => '',
            'role' => 0,
            'role_id' => 0,
            'status' => 1,
            'token' => '',
            'create_time' => $now,
            'update_time' => $now,
        ]);
        Cache::put('admin_user_2', [
            'id' => 2,
            'username' => 'staff',
            'role_id' => 0,
            'status' => 1,
        ], 300);

        $this->withSession(['admin_uid' => 2, 'admin_username' => 'staff'])
            ->get('/admin/user')
            ->assertRedirect('/admin/welcome');

        $welcome = $this->withSession(['admin_uid' => 2, 'admin_username' => 'staff'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();
        preg_match('/<nav class="side-nav">(.*?)<div class="side-foot"/s', $welcome, $side);
        $this->assertStringNotContainsString('href="/admin/user"', $side[1] ?? '');
    }

    public function test_staff_with_a_ticked_role_can_open_admins(): void
    {
        (new SysPermService())->syncFromWorkspaces();
        $permId = (int) DB::table('sys_perm')->where('api', '/admin/user')->value('id');
        $this->assertGreaterThan(0, $permId);

        $now = time();
        $roleId = (int) DB::table('sys_role')->insertGetId([
            'name' => '审核员',
            'code' => 'reviewer',
            'remark' => '',
            'status' => 1,
            'sort' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        DB::table('sys_role_perm')->insert([
            'role_id' => $roleId,
            'perm_id' => $permId,
        ]);
        DB::table('sys_user')->insert([
            'id' => 3,
            'username' => 'reviewer',
            'password' => 'x',
            'email' => '',
            'remark' => '',
            'role' => 0,
            'role_id' => $roleId,
            'status' => 1,
            'token' => '',
            'create_time' => $now,
            'update_time' => $now,
        ]);
        Cache::put('admin_user_3', [
            'id' => 3,
            'username' => 'reviewer',
            'role_id' => $roleId,
            'status' => 1,
        ], 300);

        $this->withSession(['admin_uid' => 3, 'admin_username' => 'reviewer'])
            ->get('/admin/user')
            ->assertOk()
            ->assertSee('access-chain', false);
    }

    public function test_menu_list_paginates_and_search_is_fuzzy(): void
    {
        (new SysPermService())->syncFromWorkspaces();
        $total = (int) DB::table('sys_perm')->count();
        $this->assertGreaterThan(20, $total);

        $page1 = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/menus/list?page=1&limit=15')
            ->assertOk()
            ->json();
        $this->assertSame(0, $page1['code']);
        $this->assertGreaterThan(15, $page1['data']['total']);
        $this->assertSame(1, $page1['data']['current_page']);
        $this->assertSame(15, $page1['data']['per_page']);
        $this->assertCount(15, $page1['data']['data']);
        $firstId = $page1['data']['data'][0]['id'] ?? null;

        $page2 = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/menus/list?page=2&limit=15')
            ->assertOk()
            ->json();
        $this->assertSame(2, $page2['data']['current_page']);
        $this->assertSame($page1['data']['total'], $page2['data']['total']);
        $this->assertNotSame($firstId, $page2['data']['data'][0]['id'] ?? null);

        $hit = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/menus/list?name=IP&limit=15')
            ->assertOk()
            ->json();
        $this->assertSame(0, $hit['code']);
        $this->assertGreaterThan(0, $hit['data']['total']);
        $blob = strtolower(json_encode($hit['data']['data'], JSON_UNESCAPED_UNICODE) ?: '');
        $this->assertTrue(
            str_contains($blob, 'ip') || str_contains($blob, '白名单'),
            'searching IP should find the whitelist page'
        );
    }
}

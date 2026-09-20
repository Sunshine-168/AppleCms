<?php

namespace Tests\Feature;

use App\Models\Video\ActorModel;
use App\Models\Video\VideoModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoRoleIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_role_library_is_a_cast_board_not_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/roles')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('role-index', $html);
        $this->assertStringContainsString('还没有角色', $html);
        $this->assertStringContainsString('新增角色', $html);
        $this->assertStringContainsString('搜角色名、片名或演员', $html);
        $this->assertStringContainsString('没挂影片', $html);
        $this->assertStringContainsString('这不是后台管理员', $html);
        $this->assertStringContainsString('/admin/video/actors', $html);
        $this->assertStringContainsString("title: L.cast", $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="name"', $html);
        $this->assertStringNotContainsString("title: 'video_id'", $html);
        $this->assertStringNotContainsString("title: 'actor_id'", $html);
        $this->assertStringNotContainsString("title: 'slug'", $html);
    }

    public function test_save_needs_a_name_and_rejects_missing_video(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('roles', ['name' => '', 'video_id' => 0], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('角色名', $empty['msg']);

        $missingVideo = $svc->save('roles', ['name' => '周星星', 'video_id' => 99], null);
        $this->assertSame(1, $missingVideo['code']);
        $this->assertStringContainsString('影片不存在', $missingVideo['msg']);

        $missingActor = $svc->save('roles', ['name' => '周星星', 'video_id' => 0, 'actor_id' => 88], null);
        $this->assertSame(1, $missingActor['code']);
        $this->assertStringContainsString('演员不存在', $missingActor['msg']);
    }

    public function test_list_shows_video_title_and_actor_name(): void
    {
        $now = time();
        VideoModel::query()->insert([
            'title' => '大话西游',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $videoId = (int) VideoModel::query()->orderByDesc('id')->value('id');
        ActorModel::query()->insert([
            'name' => '周星驰',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $actorId = (int) ActorModel::query()->orderByDesc('id')->value('id');

        $svc = app(SiteModuleService::class);
        $ok = $svc->save('roles', [
            'name' => '至尊宝',
            'video_id' => $videoId,
            'actor_id' => $actorId,
            'status' => 1,
            'sort' => 8,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $list = $svc->lists('roles', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $row = $rows[0];
        $this->assertSame('至尊宝', $row['name'] ?? '');
        $this->assertTrue((bool) ($row['is_on'] ?? false));
        $this->assertSame('大话西游', $row['video_title'] ?? '');
        $this->assertSame('周星驰', $row['actor_name'] ?? '');
        $this->assertSame(0, (int) ($row['video_missing'] ?? 1));

        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/roles/list?q=至尊')
            ->assertOk()
            ->json();
        $this->assertSame(0, $json['code'] ?? 1);
        $found = $json['data']['data'] ?? [];
        $this->assertCount(1, $found);
        $this->assertSame('大话西游', $found[0]['video_title'] ?? '');
    }
}

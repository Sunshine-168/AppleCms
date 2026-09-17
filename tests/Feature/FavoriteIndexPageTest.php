<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Member\MemberFavorite;
use App\Models\Video\VideoModel;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FavoriteIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_favorite_index_is_a_member_save_log_not_a_generic_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/favorites')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('收藏', $html);
        $this->assertStringContainsString('还没有收藏', $html);
        $this->assertStringContainsString('搜会员、片名或 ID', $html);
        $this->assertStringContainsString('影片已删', $html);
        $this->assertStringContainsString('后台不能代收藏', $html);
        $this->assertStringContainsString('fav-batch', $html);
        $this->assertStringContainsString('/admin/video/members', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('mod-add', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('placeholder="member_id"', $html);
        $this->assertStringNotContainsString("title: 'member_id'", $html);
        $this->assertStringNotContainsString("title: 'video_id'", $html);
        $this->assertStringNotContainsString('收藏管理', $html);
    }

    public function test_list_shows_member_and_title_and_save_cannot_create(): void
    {
        $member = Member::query()->create([
            'name' => '小明',
            'email' => 'ming@example.com',
            'password' => Hash::make('secret12'),
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $video = VideoModel::query()->create([
            'title' => '测试片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        MemberFavorite::query()->create([
            'member_id' => $member->id,
            'video_id' => $video->id,
            'created_at' => time(),
        ]);

        $list = app(SiteModuleService::class)->lists('favorites', ['limit' => 20]);
        $this->assertSame(0, $list['code']);
        $row = $list['data']['data'][0] ?? [];
        $this->assertSame('小明', $row['member_name'] ?? '');
        $this->assertSame('测试片', $row['video_title'] ?? '');

        $save = app(SiteModuleService::class)->save('favorites', [
            'member_id' => $member->id,
            'video_id' => $video->id,
        ]);
        $this->assertSame(1, $save['code']);
        $this->assertStringContainsString('后台只查看和删除', $save['msg']);
    }
}

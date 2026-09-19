<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Services\Admin\Video\SiteModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaFavor;
use Tests\TestCase;

class MangaFavorAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_favors_desk_lists_and_blocks_save(): void
    {
        $now = time();
        $manga = Manga::query()->create([
            'title' => '书架本',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'tags' => '',
            'content' => '',
            'type_id' => 0,
            'serialize' => 0,
            'recommend' => 0,
            'yid' => 0,
            'status' => 1,
            'hits' => 0,
            'sort' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $member = Member::query()->create([
            'name' => '书架会员',
            'email' => 'manga-shelf-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 0,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        MangaFavor::query()->create([
            'member_id' => $member->id,
            'manga_id' => $manga->id,
            'created_at' => $now,
        ]);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=favors')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('书架', $html);
        $this->assertStringContainsString('后台不能代收藏', $html);
        $this->assertStringContainsString('manga_favors', $html);
        $this->assertStringContainsString('/admin/video/mangas?desk=favors', $html);
        $this->assertStringContainsString('manga-favor-queues', $html);
        $this->assertStringContainsString('今天', $html);
        $this->assertStringContainsString('作品已删', $html);

        $list = app(SiteModuleService::class)->lists('manga_favors', ['limit' => 20]);
        $this->assertSame(0, (int) ($list['code'] ?? 1));
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $row = $rows[0];
        $this->assertSame('书架本', $row['manga_title'] ?? '');
        $this->assertSame('书架会员', $row['member_name'] ?? '');

        $save = app(SiteModuleService::class)->save('manga_favors', [
            'member_id' => $member->id,
            'manga_id' => $manga->id,
        ]);
        $this->assertNotSame(0, (int) ($save['code'] ?? 0));
        $this->assertStringContainsString('后台只查看和取消', (string) ($save['msg'] ?? ''));

        $batch = app(SiteModuleService::class)->batch('manga_favors', [(int) $row['id']], 'delete');
        $this->assertSame(0, (int) ($batch['code'] ?? 1));
        $this->assertSame(0, MangaFavor::query()->count());

        $stats = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/mangas?desk=stats')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('打开书架台', $stats);
        $this->assertStringContainsString('/admin/video/mangas?desk=favors', $stats);
    }
}

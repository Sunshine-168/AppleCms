<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Video\VideoModel;
use App\Services\Video\VideoSettingService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Plugins\Chatroom\Models\ChatMessage;
use Plugins\Danmaku\Models\Danmaku;
use Tests\TestCase;

class PlayInteractPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_chatroom_board_and_front_are_honest(): void
    {
        if (! app(PluginManager::class)->isEnabled('chatroom')) {
            $this->markTestSkipped('chatroom plugin disabled');
        }
        $this->assertTrue(Schema::hasColumn('plugin_chat_messages', 'report'));

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/chat_messages')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('chat-board', $html);
        $this->assertStringContainsString('被举报', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);

        $save = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/chat_messages/save', ['text' => '手添', 'video_id' => 1, 'desk' => 'messages']);
        $save->assertOk()->assertJsonPath('code', 1);
        $this->assertStringContainsString('不能手添', (string) $save->json('msg'));

        $video = $this->video('聊天片');
        $this->postJson('/chatroom/'.$video->id, ['text' => '好看'])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $row = ChatMessage::query()->where('video_id', $video->id)->first();
        $this->assertNotNull($row);

        $this->get('/chatroom/'.$video->id.'?after_id='.$row->id)
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.list', []);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/chat_messages/batch', [
                'ids' => (string) $row->id,
                'action' => 'status',
                'value' => 0,
                'desk' => 'messages',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertSame(0, (int) $row->fresh()->status);

        $this->postJson('/chatroom/report', ['id' => $row->id])
            ->assertOk()
            ->assertJsonPath('code', 1);

        $off = $this->video('下架片', 0);
        $this->postJson('/chatroom/'.$off->id, ['text' => '还想聊'])
            ->assertOk()
            ->assertJsonPath('code', 1);

        app(VideoSettingService::class)->saveOptions(['chatroom_login' => '1']);
        $this->postJson('/chatroom/'.$video->id, ['text' => '游客'])
            ->assertOk()
            ->assertJsonPath('code', 1);
    }

    public function test_chatroom_report_needs_login_and_counts_once(): void
    {
        if (! app(PluginManager::class)->isEnabled('chatroom')) {
            $this->markTestSkipped('chatroom plugin disabled');
        }
        $video = $this->video('举报片');
        $this->postJson('/chatroom/'.$video->id, ['text' => '内容'])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $row = ChatMessage::query()->where('video_id', $video->id)->first();
        $member = $this->member('举报人');
        $this->actingAs($member, 'member')
            ->postJson('/chatroom/report', ['id' => $row->id])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertSame(1, (int) $row->fresh()->report);
        $this->actingAs($member, 'member')
            ->postJson('/chatroom/report', ['id' => $row->id])
            ->assertOk()
            ->assertJsonPath('code', 1);
    }

    public function test_danmaku_board_mode_and_unpublished(): void
    {
        if (! app(PluginManager::class)->isEnabled('danmaku')) {
            $this->markTestSkipped('danmaku plugin disabled');
        }
        $this->assertTrue(Schema::hasColumn('video_danmaku', 'report'));

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/danmaku')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('danmaku-board', $html);
        $this->assertStringContainsString('滚动', $html);
        $this->assertStringNotContainsString('>新增<', $html);
        $this->assertStringNotContainsString('>刷新<', $html);

        $save = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/danmaku/save', ['text' => '手添', 'desk' => 'messages']);
        $save->assertOk()->assertJsonPath('code', 1);
        $this->assertStringContainsString('不能手添', (string) $save->json('msg'));

        $video = $this->video('弹幕片');
        $this->postJson('/danmaku/'.$video->id, [
            'text' => '顶部弹',
            'mode' => 1,
            'color' => '#ff6600',
            'time' => 8,
        ])->assertOk()->assertJsonPath('code', 0)->assertJsonPath('data.mode', 1);
        $this->assertSame(1, Danmaku::query()->where('video_id', $video->id)->count());

        $off = $this->video('下架弹幕', 0);
        $this->postJson('/danmaku/'.$off->id, ['text' => '不该发出'])
            ->assertOk()
            ->assertJsonPath('code', 1);

        app(VideoSettingService::class)->saveOptions(['danmaku_login' => '1']);
        $this->postJson('/danmaku/'.$video->id, ['text' => '游客'])
            ->assertOk()
            ->assertJsonPath('code', 1);

        $row = Danmaku::query()->where('video_id', $video->id)->first();
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/danmaku/batch', [
                'action' => 'clear',
                'desk' => 'messages',
            ])
            ->assertOk()
            ->assertJsonPath('code', 0);
        $this->assertNull($row?->fresh());
    }

    private function video(string $title, int $status = 1): VideoModel
    {
        return VideoModel::query()->create([
            'title' => $title,
            'status' => $status,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    private function member(string $name): Member
    {
        return Member::query()->create([
            'name' => $name,
            'email' => $name.'-'.uniqid().'@test.local',
            'password' => Hash::make('secret12'),
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }
}

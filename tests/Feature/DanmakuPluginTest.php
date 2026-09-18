<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\Danmaku\Models\Danmaku;
use Tests\TestCase;

class DanmakuPluginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_can_list_and_send_danmaku(): void
    {
        $video = VideoModel::query()->create([
            'title' => '弹幕测试片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->get('/danmaku/'.$video->id)
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.list', []);

        $this->postJson('/danmaku/'.$video->id, [
            'text' => '好看',
            'color' => '#ff6600',
            'time' => 12.5,
            'episode_id' => 0,
        ])->assertOk()->assertJsonPath('code', 0);

        $this->assertSame(1, Danmaku::query()->where('video_id', $video->id)->count());
        $this->get('/danmaku/'.$video->id)
            ->assertOk()
            ->assertJsonPath('data.list.0.text', '好看');
    }

    public function test_rejects_empty_danmaku(): void
    {
        $video = VideoModel::query()->create([
            'title' => '空弹幕',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->postJson('/danmaku/'.$video->id, ['text' => ' '])
            ->assertOk()
            ->assertJsonPath('code', 1);
    }
}

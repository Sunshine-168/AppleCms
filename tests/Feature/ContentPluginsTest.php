<?php

namespace Tests\Feature;

use App\Models\Member\Member;
use App\Models\Video\VideoModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Plugins\Chatroom\ChatroomServiceProvider;
use Plugins\Chatroom\Models\ChatMessage;
use Plugins\Mall\MallServiceProvider;
use Plugins\Mall\Models\MallGood;
use Plugins\Mall\Models\MallOrder;
use Plugins\Manga\MangaServiceProvider;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaPic;
use Plugins\Manga\Models\MangaType;
use Tests\TestCase;

class ContentPluginsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
        $this->app->register(MangaServiceProvider::class);
        $this->app->register(MallServiceProvider::class);
        $this->app->register(ChatroomServiceProvider::class);
    }

    public function test_manga_list_and_read(): void
    {
        $row = Manga::query()->create([
            'title' => '测试漫画',
            'cover' => '',
            'author' => '作者甲',
            'remarks' => '连载',
            'content' => '简介',
            'hits' => 0,
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $ep = MangaChapter::query()->create([
            'manga_id' => $row->id,
            'name' => '第1话',
            'sort' => 1,
            'pics' => "/img/1.jpg\nhttps://example.com/2.jpg\njavascript:alert(1)",
            'created_at' => time(),
        ]);

        $this->get('/manga')->assertOk()->assertSee('测试漫画');
        $this->get('/manga/'.$row->id)->assertOk()->assertSee('第1话');
        $html = $this->get('/manga/'.$row->id.'/'.$ep->id)->assertOk()->getContent();
        $this->assertStringContainsString('/img/1.jpg', $html);
        $this->assertStringContainsString('https://example.com/2.jpg', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);

        Manga::query()->where('id', $row->id)->update(['status' => 0]);
        $this->get('/manga/'.$row->id)->assertNotFound();
    }

    public function test_pending_manga_is_not_listed_or_readable(): void
    {
        $row = Manga::query()->create([
            'title' => '待审本',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'content' => '',
            'hits' => 0,
            'sort' => 1,
            'status' => 1,
            'yid' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->get('/manga')->assertOk()->assertDontSee('待审本');
        $this->get('/manga/'.$row->id)->assertNotFound();
    }

    public function test_manga_type_filter_and_pic_table_rows(): void
    {
        $type = MangaType::query()->create([
            'name' => '热血',
            'parent_id' => 0,
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
        ]);
        $in = Manga::query()->create([
            'title' => '分类里的',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'content' => '',
            'hits' => 0,
            'sort' => 1,
            'status' => 1,
            'yid' => 0,
            'type_id' => $type->id,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        Manga::query()->create([
            'title' => '分类外的',
            'cover' => '',
            'author' => '',
            'remarks' => '',
            'content' => '',
            'hits' => 0,
            'sort' => 1,
            'status' => 1,
            'yid' => 0,
            'type_id' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->get('/manga')->assertOk()->assertSee('分类里的')->assertSee('分类外的')->assertSee('热血');
        $this->get('/manga?type='.$type->id)->assertOk()->assertSee('分类里的')->assertDontSee('分类外的');

        $ep = MangaChapter::query()->create([
            'manga_id' => $in->id,
            'name' => '第1话',
            'sort' => 1,
            'pics' => "/img/old.jpg\njavascript:alert(1)",
            'created_at' => time(),
        ]);
        MangaPic::query()->where('chapter_id', $ep->id)->delete();
        MangaPic::query()->create([
            'manga_id' => $in->id,
            'chapter_id' => $ep->id,
            'url' => 'https://cdn.example/from-table.jpg',
            'sort' => 1,
            'created_at' => time(),
        ]);
        MangaPic::query()->create([
            'manga_id' => $in->id,
            'chapter_id' => $ep->id,
            'url' => 'javascript:alert(2)',
            'sort' => 2,
            'created_at' => time(),
        ]);
        $html = $this->get('/manga/'.$in->id.'/'.$ep->id)->assertOk()->getContent();
        $this->assertStringContainsString('https://cdn.example/from-table.jpg', $html);
        $this->assertStringNotContainsString('/img/old.jpg', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
    }

    public function test_mall_redeems_points_without_payment(): void
    {
        $member = Member::query()->create([
            'name' => '兑换人',
            'email' => 'mall-plugin-'.uniqid().'@test.local',
            'password' => Hash::make('secret'),
            'points' => 80,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $goods = MallGood::query()->create([
            'name' => '徽章',
            'cover' => '',
            'points' => 30,
            'stock' => 2,
            'hint' => '积分兑',
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->get('/mall')->assertOk()->assertSee('徽章')->assertSee('没有在线支付');
        $this->actingAs($member, 'member')
            ->post('/mall/'.$goods->id.'/buy', [
                'contact' => '13800000000',
                'address' => '前台自取',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', '兑换成功，等待发货');

        $member->refresh();
        $goods->refresh();
        $this->assertSame(50, (int) $member->points);
        $this->assertSame(1, (int) $goods->stock);
        $order = MallOrder::query()->where('member_id', $member->id)->first();
        $this->assertNotNull($order);
        $this->assertSame(1, MallOrder::query()->where('member_id', $member->id)->count());
        $this->assertSame(1, (int) $order->status);
    }

    public function test_chatroom_lists_and_sends(): void
    {
        $video = VideoModel::query()->create([
            'title' => '聊天测试片',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->get('/chatroom/'.$video->id)
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.list', []);

        $this->postJson('/chatroom/'.$video->id, ['text' => '好看'])
            ->assertOk()
            ->assertJsonPath('code', 0);

        $this->assertSame(1, ChatMessage::query()->where('video_id', $video->id)->count());
        $this->get('/chatroom/'.$video->id)
            ->assertOk()
            ->assertJsonPath('data.list.0.text', '好看');

        $this->postJson('/chatroom/'.$video->id, ['text' => ' '])
            ->assertOk()
            ->assertJsonPath('code', 1);
    }
}

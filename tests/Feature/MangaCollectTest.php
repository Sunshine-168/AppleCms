<?php

namespace Tests\Feature;

use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoOption;
use App\Services\Collect\CollectIngestService;
use App\Services\Video\VideoSettingService;
use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaPic;
use Plugins\Manga\Models\MangaType;
use Plugins\Manga\Services\MangaCollectService;
use Tests\TestCase;

class MangaCollectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        if (! is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0777, true);
        }
        file_put_contents(storage_path('app/install.lock'), 'test');
    }

    public function test_collect_source_can_be_manga_and_binds_manga_types(): void
    {
        if (! app(MangaCollectService::class)->ready()) {
            $this->markTestSkipped('manga plugin disabled');
        }
        $type = MangaType::query()->create([
            'name' => '热血',
            'parent_id' => 0,
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
        ]);

        $save = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/video/collects/save', [
                'name' => '漫画站',
                'api_url' => 'https://example.com/api.php/provide/manga/',
                'api_type' => 'json',
                'mid' => 2,
                'status' => 1,
            ])
            ->assertOk()
            ->json();
        $this->assertSame(0, (int) ($save['code'] ?? 1), (string) ($save['msg'] ?? ''));
        $source = CollectSourceModel::query()->where('name', '漫画站')->first();
        $this->assertNotNull($source);
        $this->assertSame(2, (int) $source->mid);

        Http::fake([
            'https://example.com/api.php/provide/manga/*' => Http::response([
                'code' => 1,
                'page' => 1,
                'pagecount' => 1,
                'limit' => 20,
                'total' => 0,
                'class' => [['type_id' => 9, 'type_name' => '热血']],
                'list' => [],
            ], 200),
        ]);

        $classes = app(CollectIngestService::class)->fetchClasses((int) $source->id);
        $this->assertSame(0, (int) ($classes['code'] ?? 1), (string) ($classes['msg'] ?? ''));
        $this->assertSame(2, (int) ($classes['data']['mid'] ?? 0));
        $locals = $classes['data']['local_types'] ?? [];
        $this->assertNotEmpty($locals);
        $this->assertSame('热血', $locals[0]['name'] ?? '');

        app(CollectIngestService::class)->saveBind((int) $source->id, ['9' => (int) $type->id]);
        $source->refresh();
        $bind = json_decode((string) $source->bind_json, true);
        $this->assertSame((int) $type->id, (int) ($bind['9'] ?? 0));
    }

    public function test_manga_source_ingests_work_and_chapters(): void
    {
        if (! app(MangaCollectService::class)->ready()) {
            $this->markTestSkipped('manga plugin disabled');
        }
        $type = MangaType::query()->create([
            'name' => '少年',
            'parent_id' => 0,
            'sort' => 1,
            'status' => 1,
            'created_at' => time(),
        ]);
        $source = CollectSourceModel::query()->create([
            'name' => '漫画资源',
            'api_url' => 'https://manga.test/api.php/provide/manga/',
            'api_type' => 'json',
            'mid' => 2,
            'param' => '',
            'bind_json' => json_encode(['3' => (int) $type->id], JSON_UNESCAPED_UNICODE),
            'status' => 1,
            'sort' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        Http::fake([
            'https://manga.test/api.php/provide/manga/*' => Http::response([
                'code' => 1,
                'page' => 1,
                'pagecount' => 1,
                'limit' => 20,
                'total' => 1,
                'class' => [['type_id' => 3, 'type_name' => '少年']],
                'list' => [[
                    'manga_id' => '88',
                    'type_id' => 3,
                    'manga_name' => '一人之下',
                    'manga_pic' => 'https://cdn.test/cover.jpg',
                    'manga_author' => '米二',
                    'manga_content' => '简介',
                    'manga_serial' => '连载',
                    'manga_isend' => 0,
                    'manga_play_from' => 'default',
                    'manga_play_url' => "第1话$/img/1.jpg###/img/2.jpg#第2话$/img/3.jpg",
                ]],
            ], 200),
        ]);

        $run = app(CollectIngestService::class)->run((int) $source->id, ['page' => 1, 'pages' => 1]);
        $this->assertSame(0, (int) ($run['code'] ?? 1), (string) ($run['msg'] ?? ''));
        $this->assertSame(1, (int) ($run['data']['created'] ?? 0));

        $row = Manga::query()->where('title', '一人之下')->first();
        $this->assertNotNull($row);
        $this->assertSame((int) $type->id, (int) $row->type_id);
        $this->assertSame('88', (string) $row->collect_id);
        $this->assertSame((int) $source->id, (int) $row->collect_source_id);
        $this->assertSame(0, (int) $row->serialize);
        $this->assertSame(2, MangaChapter::query()->where('manga_id', $row->id)->count());
        $ep1 = MangaChapter::query()->where('manga_id', $row->id)->where('name', '第1话')->first();
        $this->assertNotNull($ep1);
        $this->assertStringContainsString('/img/1.jpg', (string) $ep1->pics);
        $this->assertGreaterThanOrEqual(2, MangaPic::query()->where('chapter_id', $ep1->id)->count());

        $more = app(MangaCollectService::class)->upsert($source, [
            'manga_id' => '88',
            'type_id' => 3,
            'manga_name' => '一人之下',
            'manga_play_from' => 'default',
            'manga_play_url' => "第1话$/img/1.jpg###/img/2.jpg#第2话$/img/3.jpg#第3话$/img/4.jpg",
        ], true);
        $this->assertSame('updated', $more['action'] ?? '');
        $this->assertSame(3, MangaChapter::query()->where('manga_id', $row->id)->count());
    }

    public function test_receive_manga_api_and_rejects_without_key(): void
    {
        if (! app(MangaCollectService::class)->ready()) {
            $this->markTestSkipped('manga plugin disabled');
        }
        $this->postJson('/api/receive/manga', ['manga_name' => '推送本'])
            ->assertOk()
            ->assertJsonPath('code', 1);

        VideoOption::query()->updateOrCreate(['k' => 'inbound_key'], ['v' => 'manga-key-1']);
        Cache::forget(VideoSettingService::CACHE_KEY);

        $ok = $this->postJson('/api/receive/manga', [
            'key' => 'manga-key-1',
            'manga_name' => '推送本',
            'manga_id' => 'bot-1',
            'manga_author' => '甲',
            'type_id' => 0,
            'chapters' => [
                ['name' => '第1话', 'pics' => "/a.jpg\nhttps://cdn.test/b.jpg\njavascript:alert(1)"],
            ],
        ])->assertOk()->json();
        $this->assertSame(0, (int) ($ok['code'] ?? 1), (string) ($ok['msg'] ?? ''));
        $row = Manga::query()->where('title', '推送本')->first();
        $this->assertNotNull($row);
        $this->assertSame('bot-1', (string) $row->collect_id);
        $this->assertSame(1, MangaChapter::query()->where('manga_id', $row->id)->count());
        $this->assertSame(2, MangaPic::query()->where('manga_id', $row->id)->count());
        $this->assertSame(0, MangaPic::query()->where('url', 'like', 'javascript:%')->count());
    }

    public function test_collects_page_exposes_manga_mid_when_plugin_on(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/collects')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('写入到', $html);
        $this->assertStringContainsString('name="mid"', $html);
        if (app(MangaCollectService::class)->ready()) {
            $this->assertStringContainsString('data-queue="mid" data-value="2"', $html);
            $this->assertStringContainsString('provide/manga', $html);
        }
    }

    public function test_interface_page_lists_manga_receive_when_ready(): void
    {
        if (! app(MangaCollectService::class)->ready()) {
            $this->markTestSkipped('manga plugin disabled');
        }
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/interface')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('/api/receive/manga', $html);
        $this->assertStringContainsString('manga_name', $html);
    }

    public function test_save_manga_source_fails_when_plugin_off(): void
    {
        $manager = app(PluginManager::class);
        $was = $manager->isEnabled('manga');
        try {
            $manager->setEnabled('manga', false);
            $this->refreshApplication();
            $this->actingAsAdmin();
            $fail = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
                ->post('/admin/video/collects/save', [
                    'name' => '关了还采',
                    'api_url' => 'https://example.com/api.php/provide/manga/',
                    'mid' => 2,
                ])
                ->assertOk()
                ->json();
            $this->assertSame(1, (int) ($fail['code'] ?? 0));
            $this->assertStringContainsString('漫画插件未启用', (string) ($fail['msg'] ?? ''));
        } finally {
            $file = base_path('plugins/Manga/plugin.json');
            if (is_file($file)) {
                $raw = json_decode((string) file_get_contents($file), true);
                if (is_array($raw)) {
                    $raw['enabled'] = $was;
                    file_put_contents($file, json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);
                }
            }
        }
    }
}

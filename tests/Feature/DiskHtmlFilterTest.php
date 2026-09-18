<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use App\Services\Video\DiskHtmlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DiskHtmlFilterTest extends TestCase
{
    use RefreshDatabase;

    private string $htmlRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->htmlRoot = storage_path('framework/testing/disk-html');
        File::deleteDirectory($this->htmlRoot);
        File::ensureDirectoryExists($this->htmlRoot);
        config(['video.disk_html.root' => $this->htmlRoot]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->htmlRoot);
        parent::tearDown();
    }

    public function test_collect_urls_type_respects_ids(): void
    {
        $now = time();
        $picked = VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '只生成我',
            'slug' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $other = VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '不要我',
            'slug' => '',
            'sort' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $urls = app(DiskHtmlService::class)->collectUrls('type', ['ids' => [$picked->id]]);

        $this->assertSame(['/type/'.$picked->id], $urls);
        $this->assertNotContains('/type/'.$other->id, $urls);
        $this->assertFalse($this->urlsHavePlayOrWap($urls));
    }

    public function test_collect_urls_when_today_uses_updated_at(): void
    {
        $old = time() - 3 * 86400;
        VideoTypeModel::query()->update(['updated_at' => $old]);
        $today = VideoTypeModel::query()->create([
            'parent_id' => 0,
            'name' => '今日分类',
            'slug' => '',
            'sort' => 0,
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $urls = app(DiskHtmlService::class)->collectUrls('type', ['when' => 'today']);

        $this->assertContains('/type/'.$today->id, $urls);
        foreach (VideoTypeModel::query()->where('id', '!=', $today->id)->pluck('id') as $id) {
            $this->assertNotContains('/type/'.$id, $urls);
        }
    }

    public function test_collect_urls_when_missing_skips_existing_files(): void
    {
        $now = time();
        $has = VideoModel::query()->create([
            'title' => '已有文件',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $miss = VideoModel::query()->create([
            'title' => '还没文件',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $dir = $this->htmlRoot.DIRECTORY_SEPARATOR.'vod'.DIRECTORY_SEPARATOR.$has->id;
        File::ensureDirectoryExists($dir);
        File::put($dir.DIRECTORY_SEPARATOR.'index.html', '<html></html>');

        $urls = app(DiskHtmlService::class)->collectUrls('detail', ['when' => 'missing']);

        $this->assertContains('/vod/'.$miss->id, $urls);
        $this->assertNotContains('/vod/'.$has->id, $urls);
        $this->assertFalse($this->urlsHavePlayOrWap($urls));
    }

    /** @param list<string> $urls */
    private function urlsHavePlayOrWap(array $urls): bool
    {
        foreach ($urls as $url) {
            if (str_contains($url, '/play/') || str_contains($url, 'wap')) {
                return true;
            }
        }

        return false;
    }
}

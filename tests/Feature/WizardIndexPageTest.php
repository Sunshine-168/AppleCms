<?php

namespace Tests\Feature;

use App\Models\Video\VideoModel;
use App\Services\Admin\Video\ThemeTagWizardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WizardIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_wizard_is_a_blade_composer_not_a_snippet_dump_or_film_tags(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/wizard')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('wizard-index', $html);
        $this->assertStringContainsString('生成主题里能跑的 Blade 标签', $html);
        $this->assertStringContainsString('模板编辑', $html);
        $this->assertStringContainsString('标签', $html);
        $this->assertStringContainsString('wiz-group-chips', $html);
        $this->assertStringContainsString('wiz-tag-chips', $html);
        $this->assertStringContainsString('wiz-work', $html);
        $this->assertStringContainsString('更多条件', $html);
        $this->assertStringContainsString('/admin/video/wizard/try', $html);
        $this->assertStringContainsString('{maccms:vod}', $html);
        $this->assertStringContainsString("selectTag('vod')", $html);
        $this->assertStringContainsString('"name":"manga"', $html);
        $this->assertStringContainsString('"name":"gallery"', $html);
        $this->assertStringContainsString('"name":"novel"', $html);
        $this->assertStringContainsString('"name":"live"', $html);
        preg_match('/id="wizard-index"[\s\S]*?<div class="card-header">([\s\S]*?)<\/div>/', $html, $header);
        $this->assertStringNotContainsString('href="/admin/video/templates"', $header[1] ?? '');
        $this->assertStringNotContainsString('href="/admin/video/tags"', $header[1] ?? '');
        $this->assertStringNotContainsString('保存前会备份', $html);
        $this->assertStringNotContainsString("['by'=>'hits'", $html);
        $this->assertStringNotContainsString("['by' => 'hits'", $html);
        $this->assertStringNotContainsString('批量给片子打标签', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
    }

    public function test_snippet_uses_order_not_maccms_by_and_rejects_unknown_tag(): void
    {
        $svc = app(ThemeTagWizardService::class);

        $unknown = $svc->snippet('maccmsvod', ['num' => 12]);
        $this->assertSame(1, $unknown['code']);

        $vod = $svc->snippet('vod', ['num' => 12, 'order' => 'hits', 'flag' => 'hot']);
        $this->assertSame(0, $vod['code'], $vod['msg'] ?? '');
        $snippet = (string) ($vod['data']['snippet'] ?? '');
        $this->assertStringContainsString("@vod(['num' => 12, 'order' => 'hits', 'flag' => 'hot'])", $snippet);
        $this->assertStringContainsString('$item->title', $snippet);
        $this->assertStringContainsString('@endvod', $snippet);
        $this->assertStringNotContainsString("'by'", $snippet);

        $mapped = $svc->snippet('vod', ['num' => 8, 'by' => 'hits']);
        $this->assertSame(0, $mapped['code']);
        $this->assertStringContainsString("'order' => 'hits'", (string) ($mapped['data']['snippet'] ?? ''));

        $seo = $svc->snippet('vodSeo', []);
        $this->assertSame(0, $seo['code']);
        $this->assertSame('@vodSeo', $seo['data']['snippet'] ?? '');
    }

    public function test_try_counts_real_rows_and_refuses_page_only_tags(): void
    {
        $svc = app(ThemeTagWizardService::class);
        $now = time();
        VideoModel::query()->insert([
            'title' => '大话西游',
            'status' => 1,
            'is_recommend' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $emptyKw = $svc->tryTag('', []);
        $this->assertSame(1, $emptyKw['code']);

        $ok = $svc->tryTag('vod', ['num' => 12, 'flag' => 'recommend']);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');
        $this->assertSame(1, (int) ($ok['data']['count'] ?? 0));
        $this->assertContains('大话西游', $ok['data']['samples'] ?? []);

        $source = $svc->tryTag('vodSource', ['type' => 'play']);
        $this->assertSame(1, $source['code']);
        $this->assertStringContainsString('播放页', $source['msg']);

        $comment = $svc->tryTag('vodComment', ['num' => 10]);
        $this->assertSame(1, $comment['code']);
        $this->assertStringContainsString('影片 ID', $comment['msg']);
    }

    public function test_plugin_content_tags_are_in_the_catalog_and_can_try(): void
    {
        $svc = app(ThemeTagWizardService::class);
        $now = time();
        \Plugins\Manga\Models\Manga::query()->create([
            'title' => '一人之下',
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

        $snippet = $svc->snippet('manga', ['num' => 8, 'order' => 'hits']);
        $this->assertSame(0, $snippet['code'], $snippet['msg'] ?? '');
        $text = (string) ($snippet['data']['snippet'] ?? '');
        $this->assertStringContainsString("@manga(['num' => 8, 'order' => 'hits'])", $text);
        $this->assertStringContainsString('$item->title', $text);
        $this->assertStringContainsString('@endmanga', $text);

        $try = $svc->tryTag('manga', ['num' => 8]);
        $this->assertSame(0, $try['code'], $try['msg'] ?? '');
        $this->assertGreaterThanOrEqual(1, (int) ($try['data']['count'] ?? 0));
        $this->assertContains('一人之下', $try['data']['samples'] ?? []);

        foreach (['gallery', 'novel', 'live', 'mangaType', 'galleryType', 'novelType', 'liveCate'] as $name) {
            $row = $svc->snippet($name, ['num' => 6]);
            $this->assertSame(0, $row['code'], $name.': '.($row['msg'] ?? ''));
            $this->assertStringContainsString('@'.$name, (string) ($row['data']['snippet'] ?? ''));
        }
    }
}

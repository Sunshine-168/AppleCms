<?php

namespace Tests\Feature;

use App\Models\Video\VideoDomain;
use App\Services\Admin\Video\SiteModuleService;
use App\Services\Video\DomainBindService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DomainIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_domain_board_is_not_generic_crud_or_a_field_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/domains')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('domain-index', $html);
        $this->assertStringContainsString('还没有绑定域名', $html);
        $this->assertStringContainsString('新增绑定', $html);
        $this->assertStringContainsString('搜域名', $html);
        $this->assertStringContainsString('同一套片库', $html);
        $this->assertStringContainsString("title: '域名'", $html);
        $this->assertStringContainsString("title: '站点名'", $html);
        $this->assertStringContainsString("title: '模板'", $html);
        $this->assertStringContainsString('/admin/video/domains/list', $html);
        $this->assertStringContainsString('/admin/video/domains/save', $html);
        $this->assertStringNotContainsString('不能封', $html);
        $this->assertStringNotContainsString('mod-refresh', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('placeholder="host"', $html);
        $this->assertStringNotContainsString("title: 'host'", $html);
        $this->assertStringNotContainsString("title: 'theme'", $html);
        $this->assertStringNotContainsString("title: 'ID'", $html);
        if (preg_match('/id="domain-search"[^>]*>(.*?)<\/form>/s', $html, $m)) {
            $this->assertStringNotContainsString('<select name="status">', $m[1]);
            $this->assertStringContainsString('type="hidden" name="status"', $m[1]);
        }

        $currentHost = trim((string) DomainBindService::currentHost());
        if ($currentHost !== '') {
            $this->assertStringContainsString('当前访问', $html);
            $this->assertStringContainsString($currentHost, $html);
        }
    }

    public function test_save_rejects_empty_duplicate_unknown_theme_and_normalizes_host(): void
    {
        $svc = app(SiteModuleService::class);

        $empty = $svc->save('domains', ['host' => '', 'theme' => 'default'], null);
        $this->assertSame(1, $empty['code']);
        $this->assertStringContainsString('域名', $empty['msg']);

        $unknown = $svc->save('domains', ['host' => 'example.com', 'theme' => 'no-such-theme'], null);
        $this->assertSame(1, $unknown['code']);
        $this->assertStringContainsString('模板', $unknown['msg']);

        $ok = $svc->save('domains', [
            'host' => 'https://WWW.Example.com/x',
            'theme' => 'default',
            'site_name' => '分站甲',
            'status' => 1,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $row = VideoDomain::query()->orderByDesc('id')->first();
        $this->assertNotNull($row);
        $this->assertSame('example.com', (string) $row->host);
        $this->assertSame('default', (string) $row->theme);
        $this->assertSame('分站甲', (string) $row->site_name);

        $dup = $svc->save('domains', ['host' => 'https://www.example.com/', 'theme' => 'default'], null);
        $this->assertSame(1, $dup['code']);
        $this->assertStringContainsString('已经绑过', $dup['msg']);
    }

    public function test_list_decorates_is_current_for_matching_host(): void
    {
        $svc = app(SiteModuleService::class);
        $ok = $svc->save('domains', [
            'host' => 'https://WWW.Example.com/x',
            'theme' => 'default',
            'site_name' => '分站甲',
            'status' => 1,
        ], null);
        $this->assertSame(0, $ok['code'], $ok['msg'] ?? '');

        $this->app->instance('request', Request::create('http://example.com/', 'GET', [], [], [], [
            'HTTP_HOST' => 'example.com',
        ]));

        $list = $svc->lists('domains', ['limit' => 20, 'q' => 'example']);
        $this->assertSame(0, $list['code']);
        $rows = $list['data']['data'] ?? [];
        $this->assertNotEmpty($rows);
        $this->assertSame('example.com', $rows[0]['host'] ?? '');
        $this->assertTrue((bool) ($rows[0]['is_current'] ?? false));
        $this->assertSame('分站甲', $rows[0]['site_name_label'] ?? '');
        $this->assertSame(DomainBindService::themeTitle('default'), $rows[0]['theme_label'] ?? '');
        $this->assertFalse((bool) ($rows[0]['theme_missing'] ?? true));
        $this->assertSame(1, (int) ($rows[0]['status'] ?? 0));
    }
}

<?php

namespace Tests\Feature;

use App\Support\Plugins\PluginManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class PluginAdminTest extends TestCase
{
    use DatabaseTransactions;

    /** @var list<string> */
    private array $zipFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        $this->cleanupUploadFixtures();
    }

    protected function tearDown(): void
    {
        $this->cleanupUploadFixtures();
        foreach ($this->zipFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_lists_local_plugins_and_hides_disabled_pages(): void
    {
        $manager = app(PluginManager::class);
        $ids = array_column($manager->listForAdmin(), 'id');
        $this->assertContains('danmaku', $ids);
        $this->assertContains('sms', $ids);
        $this->assertContains('pay', $ids);
        $this->assertContains('coupon', $ids);
        $this->assertContains('code_editor', $ids);
        $this->assertFalse($manager->isEnabled('sms'));

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins')
            ->assertOk()
            ->assertSee('弹幕')
            ->assertSee('短信网关')
            ->assertSee('代码编辑器')
            ->assertSee('只存配置')
            ->assertSee('/admin/plugins/sms', false);

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins/sms')
            ->assertOk()
            ->assertSee('AccessKey')
            ->assertSee('保存参数');

        $hostPages = array_keys(app(\App\Support\Plugins\PluginHost::class)->extraPages());
        $extraPages = array_keys(app(\App\Services\Video\VideoSettingService::class)->extraPages());
        $this->assertNotContains('sms', $hostPages, json_encode($hostPages));
        $this->assertNotContains('sms', $extraPages, json_encode($extraPages));

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/config/sms')
            ->assertNotFound();

        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/coupons')
            ->assertNotFound();

        $danmaku = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/danmaku');
        if ($manager->isEnabled('danmaku')) {
            $danmaku->assertOk();
        } else {
            $danmaku->assertNotFound();
        }
    }

    public function test_plugins_page_offers_upload_not_a_store(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/plugins')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('上传插件', $html);
        $this->assertStringContainsString('没有插件商店', $html);
        $this->assertStringContainsString('可以把 zip 自己传上来', $html);
        $this->assertStringContainsString('/admin/plugins/upload', $html);
        $this->assertStringNotContainsString('插件市场', $html);
        $this->assertStringNotContainsString('购买插件', $html);
        $this->assertStringNotContainsString('远程安装', $html);
        $this->assertStringNotContainsString('评分', $html);
    }

    public function test_upload_without_file_fails(): void
    {
        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/plugins/upload')
            ->assertOk()
            ->json();

        $this->assertSame(1, (int) ($json['code'] ?? 0));
        $this->assertStringContainsString('请选择 zip', (string) ($json['msg'] ?? ''));
    }

    public function test_uploads_minimal_zip_into_plugins_folder(): void
    {
        $zip = $this->makeZip([
            'plugin.json' => $this->manifestJson('zz_upload_probe', '上传探测'),
            'readme.txt' => 'stub',
        ]);

        $json = $this->postPluginZip($zip)->assertOk()->json();
        $this->assertSame(0, (int) ($json['code'] ?? 1), (string) ($json['msg'] ?? ''));
        $this->assertStringContainsString('已上传', (string) ($json['msg'] ?? ''));
        $this->assertFileExists(base_path('plugins/zz_upload_probe/plugin.json'));
        $this->assertFileExists(base_path('plugins/zz_upload_probe/readme.txt'));

        $meta = json_decode((string) file_get_contents(base_path('plugins/zz_upload_probe/plugin.json')), true);
        $this->assertIsArray($meta);
        $this->assertSame('zz_upload_probe', $meta['id'] ?? null);
        $this->assertFalse((bool) ($meta['enabled'] ?? true));
        $this->assertSame('upload', $meta['source'] ?? null);

        $row = app(PluginManager::class)->findForAdmin('zz_upload_probe');
        $this->assertNotNull($row);
        $this->assertTrue($row['uploaded'] ?? false);
        $this->assertFalse($row['enabled'] ?? true);

        $nested = $this->makeZip([
            'Pack/plugin.json' => $this->manifestJson('zz_upload_nested', '嵌套探测'),
            'Pack/note.txt' => 'ok',
        ]);
        $nestedJson = $this->postPluginZip($nested)->assertOk()->json();
        $this->assertSame(0, (int) ($nestedJson['code'] ?? 1), (string) ($nestedJson['msg'] ?? ''));
        $this->assertFileExists(base_path('plugins/zz_upload_nested/plugin.json'));
        $this->assertFileExists(base_path('plugins/zz_upload_nested/note.txt'));
        $this->assertFileDoesNotExist(base_path('plugins/zz_upload_nested/Pack/plugin.json'));
    }

    public function test_rejects_zip_slip_and_missing_plugin_json(): void
    {
        $missing = $this->makeZip(['readme.txt' => 'no manifest']);
        $missingJson = $this->postPluginZip($missing)->assertOk()->json();
        $this->assertNotSame(0, (int) ($missingJson['code'] ?? 0));
        $this->assertStringContainsString('plugin.json', (string) ($missingJson['msg'] ?? ''));
        $this->assertDirectoryDoesNotExist(base_path('plugins/zz_upload_probe'));

        $slip = $this->makeZip([
            '../zz_upload_slip/plugin.json' => $this->manifestJson('zz_upload_probe', '越权'),
            '../zz_upload_evil.php' => '<?php',
        ]);
        $slipJson = $this->postPluginZip($slip)->assertOk()->json();
        $this->assertNotSame(0, (int) ($slipJson['code'] ?? 0));
        $this->assertStringContainsString('路径', (string) ($slipJson['msg'] ?? ''));
        $this->assertDirectoryDoesNotExist(base_path('plugins/zz_upload_probe'));
        $this->assertDirectoryDoesNotExist(base_path('zz_upload_slip'));
        $this->assertFileDoesNotExist(base_path('zz_upload_evil.php'));
        $this->assertDirectoryDoesNotExist(base_path('plugins/../zz_upload_slip'));
    }

    /** @param array<string, string> $files */
    private function makeZip(array $files): string
    {
        $path = tempnam(sys_get_temp_dir(), 'plg');
        if ($path === false) {
            $this->fail('tempnam failed');
        }
        @unlink($path);
        $path .= '.zip';
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE) === true);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        $this->zipFiles[] = $path;

        return $path;
    }

    private function manifestJson(string $id, string $name): string
    {
        return json_encode([
            'id' => $id,
            'name' => $name,
            'version' => '1.0.0',
            'enabled' => true,
            'capability' => 'stub',
            'group' => 'other',
            'description' => '测试上传',
        ], JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    private function postPluginZip(string $zipPath, array $extra = [])
    {
        return $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->post('/admin/plugins/upload', array_merge([
                'file' => new UploadedFile($zipPath, 'plugin.zip', 'application/zip', null, true),
            ], $extra));
    }

    private function cleanupUploadFixtures(): void
    {
        $root = base_path('plugins');
        foreach ([
            'zz_upload_probe',
            'zz_upload_nested',
            'zz_upload_probe__installing',
            'zz_upload_probe__old',
            'zz_upload_nested__installing',
            'zz_upload_nested__old',
        ] as $name) {
            $dir = $root.DIRECTORY_SEPARATOR.$name;
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
        foreach ([base_path('zz_upload_slip'), base_path('zz_upload_evil.php')] as $extra) {
            if (is_dir($extra)) {
                File::deleteDirectory($extra);
            } elseif (is_file($extra)) {
                @unlink($extra);
            }
        }
    }
}

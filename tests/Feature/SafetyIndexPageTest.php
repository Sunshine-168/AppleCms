<?php

namespace Tests\Feature;

use App\Services\Admin\System\SysSafetyScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SafetyIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
        Cache::forget('admin.safety.last_scan');
    }

    public function test_safety_index_is_a_report_board_not_a_dump(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/safety')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('safety-index', $html);
        $this->assertStringContainsString('挂马扫描', $html);
        $this->assertStringContainsString('不是杀毒软件', $html);
        $this->assertStringContainsString('不删文件', $html);
        $this->assertStringContainsString('还没扫过', $html);
        $this->assertStringContainsString('扫一遍', $html);
        $this->assertStringContainsString('连程序目录一起扫', $html);
        $this->assertStringContainsString('safety-progress', $html);
        $this->assertStringContainsString('正在列出要扫的文件', $html);
        $this->assertStringContainsString('停下来', $html);
        $this->assertStringContainsString('要人工看', $html);
        $this->assertStringContainsString('本站已知', $html);
        $this->assertStringContainsString('public/', $html);
        $this->assertStringContainsString('plugins/', $html);
        $this->assertStringContainsString('/admin/video/config/ip', $html);
        $this->assertStringContainsString('/admin/plugins', $html);
        $this->assertStringContainsString('/admin/system/attachments', $html);
        $this->assertStringContainsString('/admin/system/monitor/operate-logs', $html);
        $this->assertStringNotContainsString('hub-steps', $html);
        $this->assertStringNotContainsString('开始扫描', $html);
        $this->assertStringNotContainsString('class="hint"', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('一键修复', $html);
        $this->assertStringNotContainsString('一键清', $html);
        $this->assertStringNotContainsString('官方文件', $html);
        $this->assertStringNotContainsString('未发现可疑调用', $html);
        $this->assertStringNotContainsString('扫上传目录有没有挂马', $html);
        $this->assertStringNotContainsString('id="safety-result"', $html);
    }

    public function test_scan_endpoint_returns_structured_json(): void
    {
        $json = $this->scanUntilDone(['with_app' => 0]);

        $this->assertSame(0, $json['code']);
        $this->assertSame(1, (int) ($json['data']['done'] ?? 0));
        $this->assertArrayHasKey('hits', $json['data'] ?? []);
        $this->assertArrayHasKey('other_count', $json['data'] ?? []);
        $this->assertArrayHasKey('groups', $json['data'] ?? []);
        $this->assertIsArray($json['data']['hits']);
        $this->assertStringNotContainsString('未发现可疑调用', (string) ($json['msg'] ?? ''));
    }

    public function test_scan_reports_structured_hits_and_skips_vendor(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lv_safety_'.uniqid('', true);
        mkdir($dir, 0777, true);
        mkdir($dir.DIRECTORY_SEPARATOR.'vendor', 0777, true);
        $ok = $dir.DIRECTORY_SEPARATOR.'ok.php';
        $hit = $dir.DIRECTORY_SEPARATOR.'hit.php';
        $skip = $dir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'skip.php';
        $note = $dir.DIRECTORY_SEPARATOR.'note.txt';
        $sample = "<?php base64_decode('YQ==');\n";
        file_put_contents($ok, "<?php echo 1;\n");
        file_put_contents($hit, $sample);
        file_put_contents($skip, $sample);
        file_put_contents($note, "base64_decode('YQ==');\n");

        try {
            $res = app(SysSafetyScanService::class)->malwareScan(false, [$dir]);
            $this->assertSame(0, $res['code'], $res['msg'] ?? '');
            $data = $res['data'] ?? [];
            $this->assertSame(1, (int) ($data['other_count'] ?? 0));
            $this->assertSame(0, (int) ($data['known_count'] ?? 0));
            $hits = $data['hits'] ?? [];
            $this->assertCount(1, $hits);
            $this->assertStringContainsString('hit.php', (string) ($hits[0]['file'] ?? ''));
            $this->assertSame(1, (int) ($hits[0]['line'] ?? 0));
            $this->assertSame('base64_decode', (string) ($hits[0]['needle'] ?? ''));
            $this->assertNotEmpty($hits[0]['snippet'] ?? '');
            $this->assertFalse((bool) ($hits[0]['known'] ?? true));
            $this->assertArrayHasKey('other', $data['groups'] ?? []);
        } finally {
            @unlink($ok);
            @unlink($hit);
            @unlink($note);
            @unlink($skip);
            @rmdir($dir.DIRECTORY_SEPARATOR.'vendor');
            @rmdir($dir);
        }
    }

    public function test_app_scan_marks_known_site_code_and_default_scan_skips_it(): void
    {
        $svc = app(SysSafetyScanService::class);
        $default = $svc->malwareScan(false);
        $this->assertSame(0, $default['code'], $default['msg'] ?? '');
        foreach ($default['data']['hits'] ?? [] as $hit) {
            $this->assertStringNotContainsString('app/Support/Utils/Password.php', (string) ($hit['file'] ?? ''));
        }

        $withApp = $svc->malwareScan(true);
        $this->assertSame(0, $withApp['code'], $withApp['msg'] ?? '');
        $this->assertSame(0, (int) ($withApp['data']['other_count'] ?? -1), $withApp['msg'] ?? '');
        $this->assertGreaterThan(0, (int) ($withApp['data']['known_count'] ?? 0));
        $files = [];
        foreach ($withApp['data']['hits'] ?? [] as $hit) {
            $this->assertTrue((bool) ($hit['known'] ?? false), ($hit['file'] ?? '').':'.($hit['line'] ?? ''));
            $files[] = (string) ($hit['file'] ?? '');
        }
        $this->assertContains('app/Support/Utils/Password.php', $files);
        $this->assertContains('app/Http/Middleware/RateLimit.php', $files);
        $this->assertContains('app/Services/Admin/System/SysDatabaseBackupService.php', $files);
        $this->assertContains('plugins/Pay/Services/PayService.php', $files);

        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/video/safety')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('缓存里的上次结果', $html);
        $this->assertStringContainsString('Password.php', $html);
        $this->assertStringContainsString('本站密码解密', $html);
    }

    public function test_scan_reports_progress_across_batches(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lv_safety_batch_'.uniqid('', true);
        mkdir($dir, 0777, true);
        $paths = [];
        try {
            for ($i = 0; $i < 5; $i++) {
                $path = $dir.DIRECTORY_SEPARATOR.'f'.$i.'.php';
                $body = $i === 0 ? "<?php base64_decode('YQ==');\n" : "<?php echo ".$i.";\n";
                file_put_contents($path, $body);
                $paths[] = $path;
            }
            $svc = app(SysSafetyScanService::class);
            $first = $svc->beginScan(false, [$dir], 2);
            $this->assertSame(0, $first['code'], $first['msg'] ?? '');
            $this->assertSame(0, (int) ($first['data']['done'] ?? 1));
            $this->assertSame(5, (int) ($first['data']['total'] ?? 0));
            $this->assertSame(2, (int) ($first['data']['scanned'] ?? 0));
            $this->assertNotSame('', (string) ($first['data']['token'] ?? ''));
            $this->assertStringContainsString('正在扫', (string) ($first['msg'] ?? ''));

            $json = $first;
            $guard = 0;
            while ((int) ($json['data']['done'] ?? 0) !== 1 && $guard < 10) {
                $json = $svc->continueScan((string) ($json['data']['token'] ?? ''));
                $this->assertSame(0, $json['code'], $json['msg'] ?? '');
                $guard++;
            }
            $this->assertSame(1, (int) ($json['data']['done'] ?? 0));
            $this->assertSame(1, (int) ($json['data']['other_count'] ?? 0));
            $this->assertSame(5, (int) ($json['data']['files_scanned'] ?? 0));
        } finally {
            foreach ($paths as $path) {
                @unlink($path);
            }
            @rmdir($dir);
        }
    }

    /** @param  array<string, mixed>  $start */
    private function scanUntilDone(array $start): array
    {
        $payload = $start;
        $json = [];
        for ($i = 0; $i < 200; $i++) {
            $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
                ->post('/admin/video/safety/scan', $payload)
                ->assertOk()
                ->json();
            $this->assertSame(0, $json['code'] ?? 1, (string) ($json['msg'] ?? ''));
            if ((int) ($json['data']['done'] ?? 0) === 1) {
                return $json;
            }
            $token = (string) ($json['data']['token'] ?? '');
            $this->assertNotSame('', $token);
            $payload = ['token' => $token];
        }
        $this->fail('扫描没有在分批请求里结束');
    }
}

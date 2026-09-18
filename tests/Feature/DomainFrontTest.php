<?php

namespace Tests\Feature;

use App\Models\Video\VideoDomain;
use App\Services\Video\DomainBindService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainFrontTest extends TestCase
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

    public function test_active_bind_overrides_site_name_on_matching_host(): void
    {
        $host = $this->frontHost();
        $this->bindHost($host, 1);

        $html = $this->withServerVariables(['HTTP_HOST' => $host])
            ->get('/')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('分站甲', $html);
    }

    public function test_disabled_bind_does_not_override_site_name(): void
    {
        $host = $this->frontHost();
        $this->bindHost($host, 0);

        $html = $this->withServerVariables(['HTTP_HOST' => $host])
            ->get('/')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('分站甲', $html);
    }

    private function frontHost(): string
    {
        $host = DomainBindService::normalizeHost((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        return $host !== '' ? $host : '127.0.0.1';
    }

    private function bindHost(string $host, int $status): void
    {
        VideoDomain::query()->create([
            'host' => $host,
            'theme' => 'default',
            'site_name' => '分站甲',
            'site_keyword' => '',
            'site_description' => '',
            'remark' => '',
            'status' => $status,
        ]);
    }
}

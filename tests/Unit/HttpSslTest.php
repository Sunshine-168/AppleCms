<?php

namespace Tests\Unit;

use App\Support\HttpSsl;
use Tests\TestCase;

class HttpSslTest extends TestCase
{
    public function test_app_ships_a_ca_bundle_windows_php_can_use(): void
    {
        $bundle = resource_path('certs/cacert.pem');
        $this->assertFileExists($bundle);
        $this->assertStringContainsString('BEGIN CERTIFICATE', (string) file_get_contents($bundle));
        $this->assertSame($bundle, HttpSsl::verify());
    }
}

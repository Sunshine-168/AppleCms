<?php

namespace Tests\Unit;

use App\Support\PhpCli;
use Tests\TestCase;

class PhpCliTest extends TestCase
{
    public function test_baota_fpm_becomes_cli_php(): void
    {
        $php = PhpCli::resolve(
            '/www/server/php/84/sbin/php-fpm',
            '/www/server/php/84/sbin',
            '8.4.25',
            'Linux'
        );
        $this->assertSame('/www/server/php/84/bin/php', $php);
        $this->assertStringNotContainsString('php-fpm', $php);
        $this->assertContains(
            '/www/server/php/84/bin/php',
            PhpCli::candidates('/www/server/php/84/sbin/php-fpm', '/www/server/php/84/sbin', '8.4.25')
        );
    }

    public function test_schedule_cron_line_is_ready_to_paste(): void
    {
        $line = PhpCli::scheduleCronLine();
        $this->assertStringStartsWith('* * * * * ', $line);
        $this->assertStringContainsString('artisan schedule:run >> /dev/null 2>&1', $line);
        $this->assertStringNotContainsString('php-fpm', $line);
        $this->assertStringNotContainsString('/sbin/php', $line);
    }
}

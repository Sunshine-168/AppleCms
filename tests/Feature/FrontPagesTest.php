<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $lock = storage_path('app/install.lock');
        if (is_file($lock)) {
            unlink($lock);
        }
    }

    public function test_install_page_is_reachable(): void
    {
        $this->get('/install')->assertOk();
    }

    public function test_robots_txt_is_plain_text(): void
    {
        $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    }
}

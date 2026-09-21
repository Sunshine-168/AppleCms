<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $lock = storage_path('app/install.lock');
        if (is_file($lock)) {
            @unlink($lock);
        }
    }

    public function test_install_wizard_is_a_three_step_workbench(): void
    {
        $html = $this->get('/install')->assertOk()->getContent();

        $this->assertStringContainsString('install-index', $html);
        $this->assertStringContainsString('三步即可用', $html);
        $this->assertStringContainsString('环境检查', $html);
        $this->assertStringContainsString('数据库', $html);
        $this->assertStringContainsString('网站和管理员', $html);
        $this->assertStringContainsString('运行环境', $html);
        $this->assertStringContainsString('install-checks', $html);
        $this->assertStringContainsString('PHP 8.4', $html);
        $this->assertStringContainsString('文件数据库', $html);
        $this->assertStringContainsString('SQLite', $html);
        $this->assertStringContainsString('database/database.sqlite', $html);
        $this->assertStringContainsString('MySQL', $html);
        $this->assertStringContainsString('开始安装', $html);
        $this->assertStringContainsString('installFlash', $html);
        $this->assertStringContainsString('installError', $html);
        $this->assertStringContainsString('测试并继续', $html);
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringNotContainsString('class="checks"', $html);
        $this->assertStringNotContainsString('影视系统安装', $html);
    }

    public function test_done_page_without_flash_goes_back_to_wizard(): void
    {
        $this->get('/install/done')->assertRedirect('/install');
    }
}

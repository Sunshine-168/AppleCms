<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallPageTest extends TestCase
{
    protected ?string $lockBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        $lock = storage_path('app/install.lock');
        $this->lockBackup = is_file($lock) ? (string) file_get_contents($lock) : null;
        if (is_file($lock)) {
            @unlink($lock);
        }
    }

    protected function tearDown(): void
    {
        $lock = storage_path('app/install.lock');
        if ($this->lockBackup !== null) {
            file_put_contents($lock, $this->lockBackup);
        } elseif (is_file($lock)) {
            @unlink($lock);
        }
        parent::tearDown();
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
        $this->assertStringContainsString('网页安装', $html);
        $this->assertStringContainsString('Shell', $html);
        $this->assertStringContainsString('Docker', $html);
        $this->assertStringContainsString('上线部署', $html);
        $this->assertStringContainsString('/install?way=deploy', $html);
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringNotContainsString('class="checks"', $html);
        $this->assertStringNotContainsString('影视系统安装', $html);
        $this->assertStringNotContainsString('php artisan video:install', $html);
    }

    public function test_laravel_docker_and_deploy_guides(): void
    {
        $this->get('/install?way=laravel')
            ->assertOk()
            ->assertSee('用 Shell 安装')
            ->assertSee('bash install.sh')
            ->assertSee('php artisan video:install')
            ->assertSee('php artisan serve')
            ->assertSee('/install?way=deploy');

        $this->get('/install?way=docker')
            ->assertOk()
            ->assertSee('用 Docker 安装')
            ->assertSee('docker compose up --build')
            ->assertSee('127.0.0.1:8010')
            ->assertDontSee('admin@example.com')
            ->assertDontSee('127.0.0.1:8000');

        $this->get('/install?way=deploy')
            ->assertOk()
            ->assertSee('上线要准备什么')
            ->assertSee('PHP 环境')
            ->assertSee('CREATE DATABASE video')
            ->assertSee('try_files')
            ->assertSee('APP_DEBUG=false');
    }

    public function test_locked_web_redirects_but_guides_stay_open(): void
    {
        $lock = storage_path('app/install.lock');
        file_put_contents($lock, 'test');

        $this->get('/install')->assertRedirect('/admin/login');
        $this->get('/install?way=deploy')
            ->assertOk()
            ->assertSee('上线要准备什么')
            ->assertSee('网页安装');
    }

    public function test_done_page_without_flash_goes_back_to_wizard(): void
    {
        $this->get('/install/done')->assertRedirect('/install');
    }

    public function test_done_page_links_to_deploy_guide(): void
    {
        $this->withSession(['install_done' => [
            'username' => 'admin',
            'site_name' => '测试站',
            'demo' => false,
        ]])->get('/install/done')
            ->assertOk()
            ->assertSee('安装完毕')
            ->assertSee('上线部署说明')
            ->assertSee('/install?way=deploy');
    }

    public function test_fresh_finish_retry_opens_done_page(): void
    {
        $lock = storage_path('app/install.lock');
        file_put_contents($lock, 'installed_at='.date('c'));

        $this->postJson('/install/task', [
            'phase' => 'finish',
            'site_name' => '重试站',
            'admin_name' => 'admin',
        ])->assertOk()->assertJsonPath('ok', true);

        $this->get('/install/done')
            ->assertOk()
            ->assertSee('安装完毕')
            ->assertSee('重试站');
    }

    public function test_old_lock_still_rejects_finish(): void
    {
        $lock = storage_path('app/install.lock');
        file_put_contents($lock, 'installed_at=old');
        touch($lock, time() - 600);

        $this->postJson('/install/task', [
            'phase' => 'finish',
            'site_name' => '重试站',
            'admin_name' => 'admin',
        ])->assertStatus(422)->assertJsonPath('message', '已经安装过了');
    }
}

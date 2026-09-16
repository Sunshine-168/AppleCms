<?php

namespace App\Console\Commands;

use App\Services\Video\VideoInstallService;
use Illuminate\Console\Command;

class VideoInstallCommand extends Command
{
    protected $signature = 'video:install {--name=苹果v12} {--admin=admin} {--password=} {--demo}';

    protected $description = '命令行安装：迁移、默认分类、管理员';

    public function handle(VideoInstallService $install): int
    {
        $password = (string) $this->option('password');
        if ($password === '') {
            $password = (string) $this->secret('管理员密码（至少 6 位）');
        }
        if (strlen($password) < 6) {
            $this->error('密码至少 6 位');

            return self::FAILURE;
        }
        $install->run([
            'site_name' => (string) $this->option('name'),
            'admin_name' => (string) $this->option('admin'),
            'admin_password' => $password,
            'admin_email' => '',
            'app_url' => (string) config('app.url'),
            'seed_demo' => (bool) $this->option('demo'),
            'db_connection' => '',
        ]);
        $this->info('安装完成，后台账号：'.$this->option('admin'));

        return self::SUCCESS;
    }
}

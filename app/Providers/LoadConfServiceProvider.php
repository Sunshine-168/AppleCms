<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * 载入配置
 */
class LoadConfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $helper = app_path('Support/helpers.php');

        if (file_exists($helper))
        {
            require_once $helper;
        }

        $path = config_path('system');

        if (!is_dir($path))
        {
            return;
        }

        $files = glob($path.'/*.php') ?: [];

        foreach ($files as $file)
        {
            $name = pathinfo($file, PATHINFO_FILENAME);
            config()->set("system.$name", require $file);
        }
    }
}

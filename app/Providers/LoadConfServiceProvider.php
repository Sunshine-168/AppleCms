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
        $path = config_path('system');

        if (!is_dir($path))
        {
            return;
        }

        foreach (glob($path.'/*.php') as $file)
        {
            $name = pathinfo($file, PATHINFO_FILENAME);
            config(["system.$name" => require $file]);
        }
    }
}

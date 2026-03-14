<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Theme\ThemeService;

/**
 * 模板发布
 */
class ThemePublish extends Command
{
    protected $signature = 'theme:publish {theme=default}';

    protected $description = 'Publish theme assets';

    public function handle(ThemeService $themeService): void
    {
        $theme = $this->argument('theme');

        $result = $themeService->publish($theme);

        if (!$result) {
            $this->error("Theme {$theme} assets not found.");
            return;
        }

        $this->info("Theme {$theme} published successfully.");
    }
}

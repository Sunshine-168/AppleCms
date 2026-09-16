<?php

namespace App\Services\Admin\Theme;

use Illuminate\Support\Facades\File;

class ThemeService
{
    public function publish(string $theme): bool
    {
        $source = resource_path("themes/{$theme}/assets");
        $target = public_path("themes/{$theme}/assets");

        if (!File::exists($source))
        {
            return false;
        }

        File::copyDirectory($source, $target);

        return true;
    }
}

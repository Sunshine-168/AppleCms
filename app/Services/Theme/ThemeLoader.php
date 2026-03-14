<?php

namespace App\Services\Theme;

use Illuminate\Support\Facades\View;

class ThemeLoader
{
    public function load(string $theme): void
    {
        $path = resource_path("views/themes/".$theme);

        View::addNamespace('theme', $path);
    }
}

<?php

namespace App\Services\Admin\Theme;

use Illuminate\Support\Facades\View;
use function App\Services\Theme\resource_path;

class ThemeLoader
{
    public function load(string $theme): void
    {
        $path = resource_path("views/themes/".$theme);

        View::addNamespace('theme', $path);
    }
}

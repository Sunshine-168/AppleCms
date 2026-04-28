<?php

namespace App\Services\Admin\Theme;

use function App\Services\Theme\config;
use function App\Services\Theme\resource_path;

class ThemeManager
{
    protected string $theme;

    public function __construct()
    {
        $this->theme = config('theme.default','default');
    }

    public function get(): string
    {
        return $this->theme;
    }

    public function path(): string
    {
        return resource_path("views/themes/".$this->theme);
    }

    public function asset(string $path): string
    {
        return "/themes/".$this->theme."/assets/".$path;
    }
}

<?php

namespace App\Services\Theme;

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

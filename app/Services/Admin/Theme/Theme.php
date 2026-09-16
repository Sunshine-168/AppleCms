<?php

namespace App\Services\Admin\Theme;

class Theme
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function viewPath(): string
    {
        return resource_path("views/themes/".$this->name);
    }

    public function asset(string $path): string
    {
        return "/themes/".$this->name."/assets/".$path;
    }

    public function configPath(): string
    {
        return $this->viewPath()."/config.json";
    }
}

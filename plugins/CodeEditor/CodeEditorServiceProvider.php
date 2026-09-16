<?php

namespace Plugins\CodeEditor;

use Illuminate\Support\ServiceProvider;

class CodeEditorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'code-editor');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}

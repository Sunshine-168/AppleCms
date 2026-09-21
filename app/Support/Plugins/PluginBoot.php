<?php

namespace App\Support\Plugins;

use App\Cms\Blade\CmsDirectiveRegistrar;

class PluginBoot
{
    public static function migrateFile(string $file): void
    {
        if (! is_file($file)) {
            return;
        }
        $migration = require $file;
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        }
    }

    /** @param  array<string, class-string>  $loops */
    public static function cmsLoops(array $loops): void
    {
        $reg = app(CmsDirectiveRegistrar::class);
        foreach ($loops as $name => $class) {
            if (is_string($name) && $name !== '' && is_string($class) && class_exists($class)) {
                $reg->registerLoop($name, $class);
            }
        }
    }
}

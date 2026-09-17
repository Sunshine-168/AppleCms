<?php

namespace App\Support\Plugins;

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
}

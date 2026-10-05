<?php

namespace App\Support\Plugins;

use App\Cms\Blade\CmsDirectiveRegistrar;
use Illuminate\Support\Facades\DB;

class PluginBoot
{
    public static function migrateFile(string $file): void
    {
        if (! is_file($file) || ! self::databaseAvailable()) {
            return;
        }
        $migration = require $file;
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        }
    }

    /**
     * composer 的 package:discover 和第一次打开站点都会启动插件。
     * 这时 SQLite 文件或数据库往往还没建好，查表会把安装直接打断。
     */
    private static function databaseAvailable(): bool
    {
        try {
            if (app()->bound('plugin.boot.database')) {
                return app('plugin.boot.database') === 'yes';
            }
            $ready = self::probeDatabase() ? 'yes' : 'no';
            app()->instance('plugin.boot.database', $ready);

            return $ready === 'yes';
        } catch (\Throwable) {
            try {
                app()->instance('plugin.boot.database', 'no');
            } catch (\Throwable) {
            }

            return false;
        }
    }

    private static function probeDatabase(): bool
    {
        $name = (string) config('database.default');
        $config = config('database.connections.'.$name);
        if (! is_array($config)) {
            return false;
        }
        $driver = (string) ($config['driver'] ?? '');
        $database = (string) ($config['database'] ?? '');
        if ($driver === 'sqlite' && $database !== ':memory:' && ! is_file($database)) {
            return false;
        }
        DB::connection()->getPdo();

        return true;
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

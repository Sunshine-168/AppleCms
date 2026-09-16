<?php

namespace App\Plugins;

class PluginManager
{
    private static bool $autoload = false;

    public function registerAutoload(): void
    {
        if (self::$autoload) {
            return;
        }
        self::$autoload = true;
        spl_autoload_register(static function (string $class): void {
            if (! str_starts_with($class, 'Plugins\\')) {
                return;
            }
            $path = base_path('plugins/'.str_replace('\\', '/', substr($class, 8)).'.php');
            if (is_file($path)) {
                require $path;
            }
        });
    }

    /** @return list<class-string> */
    public function providers(): array
    {
        $this->registerAutoload();
        $dir = base_path('plugins');
        if (! is_dir($dir)) {
            return [];
        }

        $out = [];
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'plugin.json') ?: [] as $file) {
            $meta = json_decode((string) file_get_contents($file), true);
            if (! is_array($meta) || empty($meta['enabled']) || empty($meta['provider'])) {
                continue;
            }
            $provider = (string) $meta['provider'];
            if (class_exists($provider)) {
                $out[] = $provider;
            }
        }

        return $out;
    }
}

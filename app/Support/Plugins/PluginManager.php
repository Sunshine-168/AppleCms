<?php

namespace App\Support\Plugins;

use App\Support\AdminUi;
use RuntimeException;

class PluginManager
{
    public const ID_PATTERN = '/^[a-z][a-z0-9_]{0,62}$/';

    public const SOURCE_UPLOAD = 'upload';

    private static bool $autoload = false;

    /** @var list<array<string, mixed>>|null */
    private ?array $manifests = null;

    public static function isValidId(string $id): bool
    {
        return (bool) preg_match(self::ID_PATTERN, $id);
    }

    public function root(): string
    {
        return base_path('plugins');
    }

    public function refresh(): void
    {
        $this->manifests = null;
    }

    public function directoryFor(string $id): ?string
    {
        foreach ($this->manifests() as $meta) {
            if ((string) ($meta['id'] ?? '') !== $id) {
                continue;
            }
            $file = $meta['_file'] ?? null;
            if (is_string($file) && $file !== '') {
                return dirname($file);
            }
        }

        return null;
    }

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

    public function hydrate(PluginHost $host): void
    {
        $this->registerAutoload();
        foreach ($this->enabledManifests() as $meta) {
            $admin = is_array($meta['admin'] ?? null) ? $meta['admin'] : [];
            foreach ($admin['extra_pages'] ?? [] as $key => $page) {
                if (is_string($key) && is_array($page)) {
                    $host->extraPage($key, $page);
                }
            }
            foreach ($admin['modules'] ?? [] as $name => $config) {
                if (is_string($name) && is_array($config)) {
                    $host->module($name, $config);
                }
            }
            foreach ($admin['sidebar_fold'] ?? [] as $group => $items) {
                if (! is_string($group) || ! is_array($items)) {
                    continue;
                }
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $host->sidebarFold($group, $item);
                    }
                }
            }
            foreach ($admin['catalog'] ?? [] as $block => $items) {
                if (! is_string($block) || ! is_array($items)) {
                    continue;
                }
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $host->catalogItem($block, $item);
                    }
                }
            }
            foreach ($admin['settings_links'] ?? [] as $link) {
                if (is_array($link) && ! empty($link['url']) && ! empty($link['label'])) {
                    $host->settingsLink([
                        'url' => (string) $link['url'],
                        'label' => (string) $link['label'],
                    ]);
                }
            }
            $pluginId = (string) ($meta['id'] ?? '');
            $pluginName = (string) ($meta['name'] ?? $pluginId);
            foreach ($admin['schedule'] ?? [] as $job) {
                if (! is_array($job) || $pluginId === '') {
                    continue;
                }
                $handler = trim((string) ($job['handler'] ?? ''));
                if ($handler === '' || ! str_starts_with($handler, 'Plugins\\') || ! class_exists($handler)) {
                    continue;
                }
                $host->scheduleJob([
                    'plugin' => $pluginId,
                    'plugin_label' => $pluginName,
                    'id' => (string) ($job['id'] ?? ''),
                    'label' => (string) ($job['label'] ?? ''),
                    'hint' => (string) ($job['hint'] ?? ''),
                    'cron' => (string) ($job['cron'] ?? '0 4 * * *'),
                    'handler' => $handler,
                ]);
            }
        }
    }

    /** @return list<class-string> */
    public function providers(): array
    {
        $this->registerAutoload();
        $out = [];
        foreach ($this->enabledManifests() as $meta) {
            $provider = (string) ($meta['provider'] ?? '');
            if ($provider === '' || ! class_exists($provider)) {
                continue;
            }
            $out[] = $provider;
        }

        return $out;
    }

    public function isEnabled(string $id): bool
    {
        foreach ($this->manifests() as $meta) {
            if ((string) ($meta['id'] ?? '') === $id) {
                return self::flagOn($meta['enabled'] ?? false);
            }
        }

        return false;
    }

    public function setEnabled(string $id, bool $enabled): void
    {
        $file = $this->manifestFile($id);
        $raw = json_decode((string) file_get_contents($file), true);
        if (! is_array($raw)) {
            throw new RuntimeException('插件清单无法解析');
        }
        $raw['enabled'] = $enabled;
        $json = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('插件清单写入失败');
        }
        if (file_put_contents($file, $json.PHP_EOL) === false) {
            throw new RuntimeException('插件目录不可写');
        }
        $this->manifests = null;
    }

    /** @return array<string, mixed>|null */
    public function findForAdmin(string $id): ?array
    {
        foreach ($this->listForAdmin() as $row) {
            if ($row['id'] === $id) {
                return $row;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function listForAdmin(): array
    {
        $zh = AdminUi::isChinese();
        $out = [];
        foreach ($this->manifests() as $meta) {
            $out[] = [
                'id' => (string) ($meta['id'] ?? ''),
                'name' => $zh
                    ? (string) ($meta['name'] ?? '')
                    : (string) ($meta['name_en'] ?? $meta['name'] ?? ''),
                'description' => $zh
                    ? (string) ($meta['description'] ?? '')
                    : (string) ($meta['description_en'] ?? $meta['description'] ?? ''),
                'version' => (string) ($meta['version'] ?? '1.0.0'),
                'enabled' => self::flagOn($meta['enabled'] ?? false),
                'capability' => (string) ($meta['capability'] ?? 'stub'),
                'group' => (string) ($meta['group'] ?? 'other'),
                'pages' => $this->configPages($meta),
                'manage' => $this->manageLinks($meta),
                'uploaded' => ($meta['source'] ?? '') === self::SOURCE_UPLOAD,
            ];
        }
        usort($out, static function (array $a, array $b): int {
            if ($a['enabled'] !== $b['enabled']) {
                return $a['enabled'] ? -1 : 1;
            }

            return strcmp($a['name'], $b['name']);
        });

        return $out;
    }

    /** @param array<string, mixed> $meta @return list<array<string, mixed>> */
    private function configPages(array $meta): array
    {
        $admin = is_array($meta['admin'] ?? null) ? $meta['admin'] : [];
        $out = [];
        foreach ($admin['extra_pages'] ?? [] as $key => $page) {
            if (! is_string($key) || ! is_array($page)) {
                continue;
            }
            $out[] = [
                'key' => $key,
                'title' => (string) ($page['title'] ?? $key),
                'hint' => (string) ($page['hint'] ?? ''),
                'fields' => is_array($page['fields'] ?? null) ? $page['fields'] : [],
            ];
        }

        return $out;
    }

    /** @param array<string, mixed> $meta @return list<array{url:string,label:string}> */
    private function manageLinks(array $meta): array
    {
        $admin = is_array($meta['admin'] ?? null) ? $meta['admin'] : [];
        $out = [];
        foreach ($admin['modules'] ?? [] as $name => $config) {
            if (! is_string($name) || ! is_array($config)) {
                continue;
            }
            $out[] = [
                'url' => '/admin/video/'.$name,
                'label' => (string) ($config['title'] ?? $name),
            ];
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    public function enabledManifests(): array
    {
        return array_values(array_filter(
            $this->manifests(),
            static fn (array $meta): bool => self::flagOn($meta['enabled'] ?? false)
        ));
    }

    /** @return list<array<string, mixed>> */
    public function manifests(): array
    {
        if ($this->manifests !== null) {
            return $this->manifests;
        }
        $this->registerAutoload();
        $dir = base_path('plugins');
        $out = [];
        if (is_dir($dir)) {
            foreach (glob($dir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'plugin.json') ?: [] as $file) {
                $meta = json_decode((string) file_get_contents($file), true);
                if (! is_array($meta) || empty($meta['id'])) {
                    continue;
                }
                $meta['_file'] = $file;
                $out[] = $meta;
            }
        }
        $this->manifests = $out;

        return $this->manifests;
    }

    private function manifestFile(string $id): string
    {
        if (! self::isValidId($id)) {
            throw new RuntimeException('无效插件');
        }
        foreach ($this->manifests() as $meta) {
            if ((string) ($meta['id'] ?? '') === $id && is_string($meta['_file'] ?? null)) {
                return (string) $meta['_file'];
            }
        }

        throw new RuntimeException('插件不存在');
    }

    private static function flagOn(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
}

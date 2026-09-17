<?php

namespace App\Plugins;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class PluginInstaller
{
    public const MAX_ZIP_BYTES = 10485760;

    private const MAX_FILES = 500;

    private const MAX_UNCOMPRESSED = 83886080;

    private const MAX_ONE_FILE = 20971520;

    private const ZIP_MIMES = [
        'application/zip',
        'application/x-zip',
        'application/x-zip-compressed',
        'application/octet-stream',
        'multipart/x-zip',
    ];

    public function __construct(private PluginManager $plugins)
    {
    }

    /**
     * @return array{status: 'ok'|'confirm', id: string, name?: string, msg?: string}
     */
    public function install(UploadedFile $file, bool $replace = false): array
    {
        $this->assertZipUpload($file);

        $zipPath = (string) $file->getRealPath();
        if ($zipPath === '' || ! is_file($zipPath)) {
            throw new RuntimeException(admin_t('plugin.err_file'));
        }

        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException(admin_t('plugin.err_zip_ext'));
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException(admin_t('plugin.err_zip_open'));
        }

        $work = storage_path('app/plugin-install-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($work);
        $opened = true;

        try {
            [$prefix, $jsonIndex] = $this->inspectZip($zip);
            $meta = $this->readManifest($zip, $jsonIndex);
            $id = (string) $meta['id'];
            $name = (string) $meta['name'];

            $existingDir = $this->plugins->directoryFor($id);
            if (is_string($existingDir) && is_dir($existingDir)) {
                if ($this->plugins->isEnabled($id)) {
                    throw new RuntimeException(admin_t('plugin.err_enabled'));
                }
                if (! $replace) {
                    return [
                        'status' => 'confirm',
                        'id' => $id,
                        'msg' => admin_t('plugin.err_exists'),
                    ];
                }
            }

            $payload = $work.DIRECTORY_SEPARATOR.'payload';
            File::ensureDirectoryExists($payload);
            $this->extractZip($zip, $prefix, $payload);
            $zip->close();
            $opened = false;

            $this->writeInstalledManifest($payload, $meta);

            $dest = $this->plugins->root().DIRECTORY_SEPARATOR.$id;
            $this->swapIntoPlace($payload, $dest, $existingDir);
            $this->plugins->refresh();

            return ['status' => 'ok', 'id' => $id, 'name' => $name];
        } finally {
            if ($opened) {
                @$zip->close();
            }
            if (is_dir($work)) {
                File::deleteDirectory($work);
            }
        }
    }

    public function uninstall(string $id): void
    {
        if (! PluginManager::isValidId($id)) {
            throw new RuntimeException(admin_t('plugin.err_bad_id'));
        }
        $row = $this->plugins->findForAdmin($id);
        if ($row === null) {
            throw new RuntimeException(admin_t('plugin.err_missing'));
        }
        if (empty($row['uploaded'])) {
            throw new RuntimeException(admin_t('plugin.err_not_upload'));
        }
        if (! empty($row['enabled'])) {
            throw new RuntimeException(admin_t('plugin.err_uninstall_on'));
        }
        $dir = $this->plugins->directoryFor($id);
        if (! is_string($dir) || ! is_dir($dir)) {
            throw new RuntimeException(admin_t('plugin.err_missing'));
        }
        $this->removePluginDir($dir);
        $this->plugins->refresh();
    }

    private function assertZipUpload(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new RuntimeException(admin_t('plugin.err_file'));
        }
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext !== 'zip') {
            throw new RuntimeException(admin_t('plugin.err_zip_only'));
        }
        if ((int) $file->getSize() > self::MAX_ZIP_BYTES) {
            throw new RuntimeException(admin_t('plugin.err_zip_size'));
        }
        $mime = strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType()));
        if ($mime !== '' && ! in_array($mime, self::ZIP_MIMES, true)) {
            throw new RuntimeException(admin_t('plugin.err_zip_only'));
        }
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function inspectZip(ZipArchive $zip): array
    {
        $count = $zip->numFiles;
        if ($count < 1) {
            throw new RuntimeException(admin_t('plugin.err_zip_empty'));
        }
        if ($count > self::MAX_FILES) {
            throw new RuntimeException(admin_t('plugin.err_zip_path'));
        }

        $files = [];
        $uncompressed = 0;
        for ($i = 0; $i < $count; $i++) {
            $raw = (string) $zip->getNameIndex($i);
            $name = $this->normalizeZipName($raw);
            if ($name === '' || str_ends_with($raw, '/') || str_ends_with($raw, '\\')) {
                continue;
            }
            $stat = $zip->statIndex($i) ?: [];
            $size = (int) ($stat['size'] ?? 0);
            if ($size > self::MAX_ONE_FILE) {
                throw new RuntimeException(admin_t('plugin.err_zip_size'));
            }
            $uncompressed += $size;
            if ($uncompressed > self::MAX_UNCOMPRESSED) {
                throw new RuntimeException(admin_t('plugin.err_zip_size'));
            }
            $files[] = ['index' => $i, 'name' => $name];
        }
        if ($files === []) {
            throw new RuntimeException(admin_t('plugin.err_zip_empty'));
        }

        $jsonAt = [];
        foreach ($files as $file) {
            if (basename($file['name']) === 'plugin.json') {
                $jsonAt[] = $file;
            }
        }
        if ($jsonAt === []) {
            throw new RuntimeException(admin_t('plugin.err_no_json'));
        }

        foreach ($jsonAt as $hit) {
            if ($hit['name'] === 'plugin.json') {
                return ['', $hit['index']];
            }
        }

        $depthOne = array_values(array_filter(
            $jsonAt,
            static fn (array $hit): bool => substr_count($hit['name'], '/') === 1
        ));
        if (count($depthOne) !== 1) {
            throw new RuntimeException(admin_t('plugin.err_no_json'));
        }

        $folder = explode('/', $depthOne[0]['name'], 2)[0];
        foreach ($files as $file) {
            if (! str_starts_with($file['name'], $folder.'/')) {
                throw new RuntimeException(admin_t('plugin.err_no_json'));
            }
        }

        return [$folder.'/', $depthOne[0]['index']];
    }

    /** @return array<string, mixed> */
    private function readManifest(ZipArchive $zip, int $index): array
    {
        $raw = $zip->getFromIndex($index);
        if (! is_string($raw) || trim($raw) === '') {
            throw new RuntimeException(admin_t('plugin.err_bad_json'));
        }
        if (strlen($raw) > 262144) {
            throw new RuntimeException(admin_t('plugin.err_bad_json'));
        }
        $meta = json_decode($raw, true);
        if (! is_array($meta) || ($meta !== [] && array_is_list($meta))) {
            throw new RuntimeException(admin_t('plugin.err_bad_json'));
        }
        $id = trim((string) ($meta['id'] ?? ''));
        $name = trim((string) ($meta['name'] ?? ''));
        $version = trim((string) ($meta['version'] ?? ''));
        if ($id === '' || $name === '' || $version === '') {
            throw new RuntimeException(admin_t('plugin.err_need_fields'));
        }
        if (! PluginManager::isValidId($id)) {
            throw new RuntimeException(admin_t('plugin.err_bad_id'));
        }
        if (mb_strlen($name) > 80 || mb_strlen($version) > 32) {
            throw new RuntimeException(admin_t('plugin.err_need_fields'));
        }
        $meta['id'] = $id;
        $meta['name'] = $name;
        $meta['version'] = $version;

        return $meta;
    }

    private function extractZip(ZipArchive $zip, string $prefix, string $dest): void
    {
        $count = $zip->numFiles;
        $wrote = 0;
        for ($i = 0; $i < $count; $i++) {
            $raw = (string) $zip->getNameIndex($i);
            if ($raw === '' || str_ends_with($raw, '/') || str_ends_with($raw, '\\')) {
                continue;
            }
            $name = $this->normalizeZipName($raw);
            if ($name === '') {
                continue;
            }
            if ($prefix !== '') {
                if (! str_starts_with($name, $prefix)) {
                    continue;
                }
                $name = substr($name, strlen($prefix));
            }
            if ($name === '') {
                continue;
            }
            $target = $this->safeDest($dest, $name);
            File::ensureDirectoryExists(dirname($target));
            $data = $zip->getFromIndex($i);
            if (! is_string($data)) {
                throw new RuntimeException(admin_t('plugin.err_zip_open'));
            }
            if (file_put_contents($target, $data) === false) {
                throw new RuntimeException(admin_t('plugin.err_write'));
            }
            $wrote++;
        }
        if ($wrote < 1 || ! is_file($dest.DIRECTORY_SEPARATOR.'plugin.json')) {
            throw new RuntimeException(admin_t('plugin.err_no_json'));
        }
    }

    /** @param array<string, mixed> $meta */
    private function writeInstalledManifest(string $dir, array $meta): void
    {
        $meta['enabled'] = false;
        $meta['source'] = PluginManager::SOURCE_UPLOAD;
        unset($meta['_file']);
        $json = json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException(admin_t('plugin.err_bad_json'));
        }
        $file = $dir.DIRECTORY_SEPARATOR.'plugin.json';
        if (file_put_contents($file, $json.PHP_EOL) === false) {
            throw new RuntimeException(admin_t('plugin.err_write'));
        }
    }

    private function swapIntoPlace(string $payload, string $dest, ?string $existingDir): void
    {
        $root = $this->plugins->root();
        File::ensureDirectoryExists($root);
        $this->assertInsidePlugins($dest);

        $staging = $root.DIRECTORY_SEPARATOR.basename($dest).'__installing';
        $backup = $root.DIRECTORY_SEPARATOR.basename($dest).'__old';
        if (is_dir($staging)) {
            $this->removePluginDir($staging);
        }
        if (is_dir($backup)) {
            $this->removePluginDir($backup);
        }

        $this->moveDir($payload, $staging);

        $movedBackup = false;
        $backupFrom = is_string($existingDir) && is_dir($existingDir) ? $existingDir : (is_dir($dest) ? $dest : null);
        try {
            if (is_string($backupFrom)) {
                $this->assertInsidePlugins($backupFrom);
                $this->moveDir($backupFrom, $backup);
                $movedBackup = true;
            }
            $this->moveDir($staging, $dest);
            if ($movedBackup && is_dir($backup)) {
                $this->removePluginDir($backup);
            }
        } catch (\Throwable $e) {
            if (! is_dir($dest) && $movedBackup && is_dir($backup) && is_string($backupFrom)) {
                @$this->moveDir($backup, $backupFrom);
            }
            if (is_dir($staging)) {
                File::deleteDirectory($staging);
            }
            throw $e;
        }
    }

    private function moveDir(string $from, string $to): void
    {
        if (@rename($from, $to)) {
            return;
        }
        if (! File::moveDirectory($from, $to)) {
            throw new RuntimeException(admin_t('plugin.err_write'));
        }
    }

    private function removePluginDir(string $dir): void
    {
        $this->assertInsidePlugins($dir);
        $root = $this->realPluginsRoot();
        $real = realpath($dir);
        if ($real === false) {
            return;
        }
        if ($this->norm($real) === $this->norm($root)) {
            throw new RuntimeException(admin_t('plugin.err_zip_path'));
        }
        if (! File::deleteDirectory($real)) {
            throw new RuntimeException(admin_t('plugin.err_write'));
        }
    }

    private function safeDest(string $root, string $relative): string
    {
        $relative = str_replace('\\', '/', $relative);
        $relative = ltrim($relative, '/');
        $this->normalizeZipName($relative);
        if (preg_match('#[<>:"|?*]#', $relative)) {
            throw new RuntimeException(admin_t('plugin.err_zip_path'));
        }
        $dest = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $this->assertInside($root, $dest);

        return $dest;
    }

    private function normalizeZipName(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        if ($name === '' || str_contains($name, "\0")) {
            throw new RuntimeException(admin_t('plugin.err_zip_path'));
        }
        if (str_starts_with($name, '/') || preg_match('#^[A-Za-z]:/#', $name) === 1) {
            throw new RuntimeException(admin_t('plugin.err_zip_path'));
        }
        $parts = [];
        foreach (explode('/', $name) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                throw new RuntimeException(admin_t('plugin.err_zip_path'));
            }
            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    private function assertInsidePlugins(string $path): void
    {
        $this->assertInside($this->plugins->root(), $path);
    }

    private function assertInside(string $root, string $path): void
    {
        $rootN = $this->norm($root);
        $pathN = $this->norm($path);
        if ($pathN !== $rootN && ! str_starts_with($pathN, $rootN.'/')) {
            throw new RuntimeException(admin_t('plugin.err_zip_path'));
        }
    }

    private function realPluginsRoot(): string
    {
        $root = $this->plugins->root();
        File::ensureDirectoryExists($root);
        $real = realpath($root);
        if ($real === false) {
            throw new RuntimeException(admin_t('plugin.err_write'));
        }

        return $real;
    }

    private function norm(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = rtrim($path, '/');
        if (PHP_OS_FAMILY === 'Windows') {
            return strtolower($path);
        }

        return $path;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;
use ZipArchive;

class UpdateController extends BaseController
{
    protected $updateUrl;
    protected $savePath;

    public function __construct()
    {
        parent::__construct();
        $this->updateUrl = base64_decode("aHR0cDovL3VwZGF0ZS5tYWNjbXMubGEv") . "v10/";
        $this->savePath = storage_path('app/update');
        
        if (!File::exists($this->savePath)) {
            File::makeDirectory($this->savePath, 0755, true);
        }
    }

    public function index()
    {
        $version = config('version.code', 'unknown');
        return view('admin.update.index', compact('version'));
    }

    public function step1(Request $request, $file = '')
    {
        if (empty($file)) {
            return $this->error('参数错误');
        }

        $version = config('version.code', '10.0');
        $url = $this->updateUrl . $file . '.zip?t=' . time();
        $saveFile = $this->savePath . DIRECTORY_SEPARATOR . $version . '.zip';
        $logs = [__('admin/update/step1_b')];

        try {
            $response = Http::timeout(60)->withOptions(['verify' => false])->get($url);
            if (!$response->successful() || $response->body() === '') {
                throw new \RuntimeException(__('admin/update/download_err'));
            }

            File::put($saveFile, $response->body());
            if (!File::exists($saveFile) || (File::size($saveFile) ?? 0) < 1) {
                throw new \RuntimeException(__('admin/update/download_err'));
            }

            $logs[] = __('admin/update/download_ok');
            $logs[] = __('admin/update/upgrade_package_processed');
            $this->extractPackage($saveFile, base_path());
            File::delete($saveFile);
        } catch (Throwable $e) {
            if (File::exists($saveFile)) {
                File::delete($saveFile);
            }
            $logs[] = $e->getMessage();
            $logs[] = __('admin/update/upgrade_err');

            return view('admin.update.result', [
                'title' => __('admin/update/step1_a'),
                'logs' => $logs,
                'nextUrl' => null,
                'nextText' => null,
            ]);
        }

        return view('admin.update.result', [
            'title' => __('admin/update/step1_a'),
            'logs' => $logs,
            'nextUrl' => route('admin.update.step2'),
            'nextText' => __('admin/update/step2_a'),
        ]);
    }

    public function step2()
    {
        $logs = [];
        $sqlFile = $this->resolveUpdateSqlFile();

        if ($sqlFile && File::exists($sqlFile)) {
            $logs[] = __('admin/update/upgrade_sql');
            $sql = $this->loadUpdateSql($sqlFile);
            if (!empty($sql) && function_exists('mac_parse_sql')) {
                $prefix = config('database.connections.mysql.prefix', 'mac_');
                $sqlList = array_filter(mac_parse_sql($sql, 0, ['mac_' => $prefix]));

                foreach ($sqlList as $statement) {
                    try {
                        DB::statement($statement);
                        $logs[] = trim($statement) . ' --- ' . __('admin.success');
                    } catch (Throwable $e) {
                        $logs[] = trim($statement) . ' --- ' . __('admin.fail') . '：' . $e->getMessage();
                    }
                }
            }
            File::delete($sqlFile);
        } else {
            $logs[] = __('admin/update/no_sql');
        }

        return view('admin.update.result', [
            'title' => __('admin/update/step2_a'),
            'logs' => $logs,
            'nextUrl' => route('admin.update.step3'),
            'nextText' => __('admin/update/step3_a'),
        ]);
    }

    public function step3()
    {
        $logs = [];
        $this->clearCache();
        $logs[] = __('admin/update/update_cache');
        $logs[] = __('admin/update/upgrade_complete');

        $sqlFile = $this->resolveUpdateSqlFile();
        if ($sqlFile && File::exists($sqlFile)) {
            $logs[] = __('admin/update/not_delete') . ': ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $sqlFile);
        }

        return view('admin.update.result', [
            'title' => __('admin/update/step3_a'),
            'logs' => $logs,
            'nextUrl' => null,
            'nextText' => null,
        ]);
    }

    public function check()
    {
        $version = config('version.code', 'unknown');
        $candidates = [
            $this->updateUrl . 'check.json?version=' . urlencode($version) . '&t=' . time(),
            $this->updateUrl . 'version.json?version=' . urlencode($version) . '&t=' . time(),
        ];

        foreach ($candidates as $url) {
            try {
                $response = Http::timeout(20)->withOptions(['verify' => false])->get($url);
                if (!$response->successful()) {
                    continue;
                }

                $json = $response->json();
                if (!is_array($json)) {
                    continue;
                }

                $latest = (string) ($json['version'] ?? $json['code'] ?? '');
                $file = (string) ($json['file'] ?? $json['package'] ?? '');
                $hasUpdate = $latest !== '' && version_compare($latest, $version, '>');

                return response()->json([
                    'code' => 1,
                    'msg' => $hasUpdate ? __('obtain_ok') : __('admin/update/upgrade_complete'),
                    'has_update' => $hasUpdate,
                    'version' => $latest,
                    'file' => $file,
                    'data' => $json,
                ]);
            } catch (Throwable $e) {
                continue;
            }
        }

        return response()->json([
            'code' => 1001,
            'msg' => __('obtain_err'),
            'has_update' => false,
            'version' => $version,
        ]);
    }

    protected function extractPackage(string $zipFile, string $destination): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new \RuntimeException(__('admin/update/upgrade_err'));
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if ($entryName === false) {
                continue;
            }
            $entryName = str_replace(['\\', '..'], ['/', ''], $entryName);
            if ($entryName === '') {
                continue;
            }

            $target = $destination . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $entryName);
            if (str_ends_with($entryName, '/')) {
                if (!File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
                continue;
            }

            $dir = dirname($target);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $stream = $zip->getStream($zip->getNameIndex($i));
            if (!$stream) {
                continue;
            }
            $contents = stream_get_contents($stream);
            fclose($stream);
            File::put($target, $contents);
        }

        $zip->close();
    }

    protected function loadUpdateSql(string $sqlFile): string
    {
        $sql = '';
        try {
            include $sqlFile;
        } catch (Throwable $e) {
            return '';
        }

        if (is_string($sql) && $sql !== '') {
            return $sql;
        }

        return (string) File::get($sqlFile);
    }

    protected function resolveUpdateSqlFile(): ?string
    {
        $candidates = [
            $this->savePath . DIRECTORY_SEPARATOR . 'database.php',
            base_path('application/data/update/database.php'),
            base_path('storage/app/update/database.php'),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        foreach (File::allFiles(base_path()) as $file) {
            $match = $file->getPathname();
            if (str_contains(str_replace('\\', '/', $match), '/update/database.php')) {
                return $match;
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TemplateController extends BaseController
{
    public function index(Request $request)
    {
        $path = $request->input('path', '.@template');
        $path = $this->normalizeTemplateToken((string) $path);

        $uppath = str_contains($path, '@') ? substr($path, 0, strrpos($path, "@")) : '.@template';
        $ischild = ($path != ".@template") ? 1 : 0;

        $config = config('maccms.site', []);
        $current = $request->input('current');
        $label = $request->input('label');
        $ads = $request->input('ads');

        if ($current == 1) {
            $path = '.@template@' . $config['template_dir'] . '@' . $config['html_dir'];
            $ischild = 0;
        } elseif ($label == 1) {
            $path = '.@template@' . $config['template_dir'] . '@' . $config['html_dir'] . '@label';
            $ischild = 0;
        } elseif ($ads == 1) {
            $path = '.@template@' . $config['template_dir'] . '@' . ($config['ads_dir'] ?? 'ads');
            $ischild = 0;
        }

        $uppath = str_contains($path, '@') ? substr($path, 0, strrpos($path, "@")) : '.@template';
        $pp = $this->tokenToFullPath($path);

        $files = [];
        $num_path = 0;
        $num_file = 0;
        $sum_size = 0;

        if (File::isDirectory($pp)) {
            foreach (File::directories($pp) as $directory) {
                $num_path++;
                $files[] = [
                    'isfile' => 0,
                    'name' => basename($directory),
                    'path' => $this->fullPathToToken($directory),
                    'fullname' => $directory,
                    'note' => __('admin.dir'),
                    'time' => filemtime($directory) ?: 0,
                ];
            }
            foreach (File::files($pp) as $item) {
                $num_file++;
                $sum_size += $item->getSize();
                $files[] = [
                    'isfile' => 1,
                    'name' => $item->getFilename(),
                    'path' => $this->fullPathToToken($item->getPath()),
                    'fullname' => $item->getPathname(),
                    'note' => __('admin.file'),
                    'size' => $this->formatSize($item->getSize()),
                    'time' => $item->getMTime(),
                ];
            }
        }

        usort($files, static function (array $left, array $right): int {
            if ($left['isfile'] !== $right['isfile']) {
                return $left['isfile'] <=> $right['isfile'];
            }

            return strnatcasecmp($left['name'], $right['name']);
        });

        $curpath = $path;
        $sum_size = $this->formatSize($sum_size);
        return view('admin.template.index', compact('files', 'curpath', 'uppath', 'ischild', 'num_path', 'num_file', 'sum_size'));
    }

    public function info(Request $request)
    {
        $fpath = $this->normalizeTemplateToken((string) $request->input('fpath', '.@template'));
        $fname = trim((string) $request->input('fname', ''));
        $filter = '<\?|php|eval|server|assert|get|post|request|cookie|session|input|env|config|call|global|dump|print|phpinfo|fputs|fopen|global|chr|strtr|pack|system|gzuncompress|shell|base64|file|proc|preg|call|ini|{:|{$|{~|{-|{+|{/';
        $directory = $this->tokenToFullPath($fpath);

        if (!File::isDirectory($directory)) {
            return $this->error('目录不存在');
        }

        if ($request->isMethod('post')) {
            if ($fname === '') {
                return $this->error('文件名不能为空');
            }

            if (!preg_match('/^[A-Za-z0-9_\-.]+$/', $fname)) {
                return $this->error('文件名格式不正确');
            }

            $extension = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
            if (!in_array($extension, ['html', 'htm', 'js', 'xml'], true)) {
                return $this->error('仅允许编辑 html/htm/js/xml 文件');
            }

            $content = (string) $request->input('fcontent', '');
            if (preg_match('/' . $filter . '/i', $content)) {
                return $this->error('检测到危险内容，请使用其他方式修改');
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $fname;
            File::put($filePath, $content);
            return $this->success('保存成功');
        }

        $filePath = $fname !== '' ? $directory . DIRECTORY_SEPARATOR . $fname : null;
        $fcontent = ($filePath && File::exists($filePath)) ? File::get($filePath) : '';
        $fcontent = str_replace('</textarea>', '<&#47textarea>', $fcontent);

        return view('admin.template.info', compact('filter', 'fpath', 'fname', 'fcontent'));
    }

    public function ads(Request $request)
    {
        $config = config('maccms.site', []);
        $adsDirName = $config['ads_dir'] ?? 'ads';
        $adsDir = base_path('template/' . $config['template_dir'] . '/' . $adsDirName);
        if (!File::exists($adsDir)) {
            File::makeDirectory($adsDir, 0755, true);
        }

        $files = [];
        $num_file = 0;
        $sum_size = 0;
        foreach (File::files($adsDir) as $item) {
            if (strtolower($item->getExtension()) !== 'js') {
                continue;
            }

            $num_file++;
            $sum_size += $item->getSize();
            $files[] = [
                'isfile' => 1,
                'name' => $item->getFilename(),
                'path' => $this->fullPathToToken($item->getPath()),
                'fullname' => $item->getPathname(),
                'note' => __('admin.file'),
                'size' => $this->formatSize($item->getSize()),
                'time' => $item->getMTime(),
            ];
        }

        usort($files, static fn (array $left, array $right): int => strnatcasecmp($left['name'], $right['name']));

        $curpath = $this->fullPathToToken($adsDir);
        $pathAds = '/' . trim('template/' . $config['template_dir'] . '/' . $adsDirName . '/', '/');
        $sum_size = $this->formatSize($sum_size);

        return view('admin.template.ads', compact('files', 'curpath', 'num_file', 'sum_size', 'pathAds'));
    }

    public function wizard(Request $request)
    {
        return view('admin.template.wizard');
    }

    public function del(Request $request)
    {
        $names = $request->input('fname', []);
        if (!is_array($names)) {
            $names = [$names];
        }

        foreach ($names as $name) {
            $name = str_replace('\\', '/', (string) $name);
            $fullPath = $this->resolveDeletePath($name);
            if ($fullPath && File::exists($fullPath) && File::isFile($fullPath)) {
                File::delete($fullPath);
            }
        }

        return $this->success('删除成功');
    }

    protected function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function normalizeTemplateToken(string $path): string
    {
        $path = trim($path);
        $path = str_replace(['\\', '/'], '', $path);
        if ($path === '') {
            $path = '.@template';
        }
        if (!str_starts_with($path, '.@template')) {
            $path = '.@template';
        }
        if (substr_count($path, '.@') > 2 || str_contains($path, '..')) {
            abort(403, '非法请求');
        }

        return $path;
    }

    protected function tokenToFullPath(string $path): string
    {
        return str_replace(['.@template', '@'], [base_path('template'), DIRECTORY_SEPARATOR], $path);
    }

    protected function fullPathToToken(string $path): string
    {
        $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $templateBase = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, base_path('template'));
        $relative = ltrim(str_replace($templateBase, '', $normalized), DIRECTORY_SEPARATOR);

        return '.@template' . ($relative === '' ? '' : '@' . str_replace(DIRECTORY_SEPARATOR, '@', $relative));
    }

    protected function resolveDeletePath(string $name): ?string
    {
        $name = str_replace(['..'], '', $name);
        if (str_starts_with($name, '.@template')) {
            return $this->tokenToFullPath($this->normalizeTemplateToken($name));
        }

        if (str_starts_with($name, './template') || str_starts_with($name, 'template/')) {
            $relative = ltrim(str_replace(['./template', 'template'], '', $name), '/');
            return base_path('template/' . $relative);
        }

        $templateBase = str_replace('\\', '/', base_path('template'));
        $normalized = str_replace('\\', '/', $name);
        if (str_starts_with($normalized, $templateBase)) {
            return $normalized;
        }

        return null;
    }
}

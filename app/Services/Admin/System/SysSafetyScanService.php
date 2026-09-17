<?php

namespace App\Services\Admin\System;

use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Cache;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use SplFileInfo;

class SysSafetyScanService
{
    private const LAST_KEY = 'admin.safety.last_scan';

    private const LAST_TTL = 21600;

    private const MAX_FILE_BYTES = 1048576;

    private const MAX_FILES = 8000;

    private const MAX_HITS = 400;

    private const JOB_TTL = 600;

    private const BATCH_FILES = 80;

    private const BATCH_SECONDS = 1.0;

    /**
     * @return array<string, mixed>
     */
    public function pageBoard(): array
    {
        return [
            'ui' => $this->ui(),
            'needles' => $this->needlePublic(),
            'dirs' => $this->dirCards(false),
            'dirs_app' => $this->dirCards(true),
            'last' => $this->lastScan(),
        ];
    }

    /**
     * @param  list<string>|null  $roots
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function malwareScan(bool $withApp = false, ?array $roots = null): array
    {
        return $this->runToEnd($this->beginScan($withApp, $roots));
    }

    /**
     * @param  list<string>|null  $roots
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function beginScan(bool $withApp = false, ?array $roots = null, int $batchFiles = 0): array
    {
        @set_time_limit(30);
        $custom = $roots !== null;
        $scanRoots = $custom ? $this->normalizeRoots($roots) : $this->defaultRoots($withApp);
        [$files, $listTruncated] = $this->collectFiles($scanRoots);
        $token = bin2hex(random_bytes(16));
        $job = [
            'token' => $token,
            'uid' => (int) session('admin_uid', 0),
            'with_app' => $withApp && ! $custom,
            'custom' => $custom,
            'roots' => $scanRoots,
            'files' => $files,
            'offset' => 0,
            'hits' => [],
            'files_scanned' => 0,
            'truncated' => $listTruncated,
            'started' => microtime(true),
            'batch_files' => $batchFiles > 0 ? min(200, $batchFiles) : self::BATCH_FILES,
        ];
        if ($files === []) {
            return $this->finishJob($job);
        }
        $this->putJob($job);

        return $this->tickJob($job);
    }

    /**
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function continueScan(string $token): array
    {
        @set_time_limit(30);
        $job = $this->getJob($token);
        if ($job === null) {
            return Result::fail('这次扫描已经没了，请再点扫一遍');
        }
        $uid = (int) session('admin_uid', 0);
        if ((int) ($job['uid'] ?? 0) > 0 && $uid > 0 && (int) $job['uid'] !== $uid) {
            return Result::fail('这不是你点的扫描');
        }

        return $this->tickJob($job);
    }

    /**
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    public function cancelScan(string $token): array
    {
        $token = $this->cleanToken($token);
        if ($token === '') {
            return Result::fail('没有这次扫描');
        }
        $job = $this->getJob($token);
        if ($job === null) {
            return Result::success([], '已停');
        }
        $uid = (int) session('admin_uid', 0);
        if ((int) ($job['uid'] ?? 0) > 0 && $uid > 0 && (int) $job['uid'] !== $uid) {
            return Result::fail('这不是你点的扫描');
        }
        Cache::forget($this->jobKey($token));

        return Result::success([], '已停');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lastScan(): ?array
    {
        $last = Cache::get(self::LAST_KEY);
        if (! is_array($last) || trim((string) ($last['scanned_at'] ?? '')) === '') {
            return null;
        }
        $hits = is_array($last['hits'] ?? null) ? $last['hits'] : [];
        $last['groups'] = $this->groupHits($hits);

        return $last;
    }

    /**
     * @return array<string, string>
     */
    private function ui(): array
    {
        return [
            'title' => '挂马扫描',
            'lead' => '在 PHP 文件里查找危险函数。命中只说明出现了函数名，要对照这一行。',
            'note' => '不是杀毒软件。只扫 .php，不删文件。不扫数据库，也不能和官方包对比。改了后缀或藏在图里的扫不到。',
            'needles' => '会找这些调用',
            'where' => '默认扫这些目录',
            'where_app' => '程序目录默认不扫',
            'where_app_hint' => '程序里本来就有这些函数。怀疑被改过再连着扫。',
            'scan' => '扫一遍',
            'scan_app' => '连程序目录一起扫',
            'confirm_app' => '程序目录也会命中备份、加密这些函数。继续扫？',
            'listing' => '正在列出要扫的文件',
            'progress' => '正在扫',
            'stop' => '停下来',
            'empty' => '还没扫过',
            'empty_hint' => '点上面扫一遍。结果只出路径和行号。',
            'stale' => '这是缓存里的上次结果，文件可能已经改过。清缓存会没有。',
            'result' => '扫描结果',
            'other' => '要人工看',
            'known' => '本站已知代码',
            'none_other' => '没有额外可疑调用',
            'ip' => 'IP 白名单',
            'plugins' => '插件',
            'files' => '附件',
            'logs' => '操作日志',
            'missing' => '目录还不存在，扫的时候会跳过',
        ];
    }

    /**
     * @return list<array{id:string,label:string,hint:string,code:string}>
     */
    private function needlePublic(): array
    {
        $out = [];
        foreach ($this->needleCatalog() as $row) {
            $out[] = [
                'id' => $row['id'],
                'code' => $row['id'],
                'label' => $row['label'],
                'hint' => $row['hint'],
            ];
        }

        return $out;
    }

    /**
     * @return list<array{id:string,fn:string,label:string,hint:string}>
     */
    private function needleCatalog(): array
    {
        return [
            ['id' => 'eval', 'fn' => 'eval', 'label' => '动态执行代码', 'hint' => '一句话木马常用'],
            ['id' => 'assert', 'fn' => 'assert', 'label' => '断言执行', 'hint' => '老环境里也能当执行用'],
            ['id' => 'base64_decode', 'fn' => 'base64_decode', 'label' => '解码', 'hint' => '常被用来藏代码，本站加密也会用'],
            ['id' => 'system', 'fn' => 'system', 'label' => '调系统命令', 'hint' => ''],
            ['id' => 'passthru', 'fn' => 'passthru', 'label' => '输出系统命令', 'hint' => ''],
            ['id' => 'shell_exec', 'fn' => 'shell_exec', 'label' => '执行系统命令', 'hint' => '本站备份调 mysqldump 会用'],
            ['id' => 'proc_open', 'fn' => 'proc_open', 'label' => '打开进程', 'hint' => ''],
            ['id' => 'popen', 'fn' => 'popen', 'label' => '管道执行', 'hint' => ''],
            ['id' => 'create_function', 'fn' => 'create'.'_function', 'label' => '动态建函数', 'hint' => '已废弃，马里还常见'],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function knownMap(): array
    {
        return [
            'app/Support/Utils/Password.php' => ['base64_decode' => '本站密码解密'],
            'app/Support/Utils/Encryption.php' => ['base64_decode' => '本站加解密'],
            'app/Http/Middleware/RateLimit.php' => ['eval' => '本站限流用 Redis 脚本，不是 PHP eval'],
            'app/Services/Admin/System/SysDatabaseBackupService.php' => ['shell_exec' => '本站备份调 mysqldump'],
            'plugins/Pay/Services/PayService.php' => ['base64_decode' => '支付宝验签解码'],
        ];
    }

    /**
     * @return list<array{path:string,label:string,hint:string,exists:bool}>
     */
    private function dirCards(bool $app): array
    {
        $rows = $app
            ? [
                ['path' => 'app/', 'label' => '程序代码', 'hint' => '本站功能在这里，误报会比较多。', 'abs' => app_path()],
                ['path' => 'bootstrap/', 'label' => '启动文件', 'hint' => '跳过已打包的 cache。', 'abs' => base_path('bootstrap')],
                ['path' => 'resources/views/', 'label' => '模板', 'hint' => '模板里一般不该有这些调用。', 'abs' => resource_path('views')],
            ]
            : [
                ['path' => 'public/', 'label' => '网站入口', 'hint' => '访客能直接请求的 PHP。', 'abs' => public_path()],
                ['path' => 'plugins/', 'label' => '插件', 'hint' => '后台上传或自己装的插件。', 'abs' => base_path('plugins')],
                ['path' => 'storage/app/', 'label' => '本地存储', 'hint' => '上传落盘的地方，通常不该有 PHP。', 'abs' => storage_path('app')],
            ];
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'path' => $row['path'],
                'label' => $row['label'],
                'hint' => $row['hint'],
                'exists' => is_dir($row['abs']),
            ];
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    private function defaultRoots(bool $withApp): array
    {
        $roots = [public_path(), base_path('plugins'), storage_path('app')];
        if ($withApp) {
            $roots[] = app_path();
            $roots[] = base_path('bootstrap');
            $roots[] = resource_path('views');
        }

        return $this->normalizeRoots($roots);
    }

    /**
     * @param  list<string>  $roots
     * @return list<string>
     */
    private function normalizeRoots(array $roots): array
    {
        $out = [];
        $seen = [];
        foreach ($roots as $root) {
            $path = str_replace('\\', '/', rtrim((string) $root, '/\\'));
            if ($path === '' || ! is_dir($path)) {
                continue;
            }
            $key = strtolower($path);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $path;
        }

        return $out;
    }

    /**
     * @param  array{code:int,msg:string,data?:array<string,mixed>}  $res
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    private function runToEnd(array $res): array
    {
        $guard = 0;
        while ((int) ($res['code'] ?? 1) === 0 && (int) ($res['data']['done'] ?? 0) !== 1 && $guard < 400) {
            $token = (string) ($res['data']['token'] ?? '');
            if ($token === '') {
                break;
            }
            $res = $this->continueScan($token);
            $guard++;
        }

        return $res;
    }

    /**
     * @param  array<string, mixed>  $job
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    private function tickJob(array $job): array
    {
        $files = is_array($job['files'] ?? null) ? $job['files'] : [];
        $total = count($files);
        $offset = (int) ($job['offset'] ?? 0);
        $hits = is_array($job['hits'] ?? null) ? $job['hits'] : [];
        $batch = max(1, (int) ($job['batch_files'] ?? self::BATCH_FILES));
        $deadline = microtime(true) + self::BATCH_SECONDS;
        $n = 0;
        while ($offset < $total && $n < $batch && microtime(true) < $deadline) {
            $path = (string) ($files[$offset] ?? '');
            $offset++;
            $n++;
            $job['files_scanned'] = (int) ($job['files_scanned'] ?? 0) + 1;
            if ($path === '') {
                continue;
            }
            foreach ($this->scanPath($path) as $hit) {
                $hits[] = $hit;
                if (count($hits) >= self::MAX_HITS) {
                    $job['truncated'] = true;
                    $offset = $total;
                    break 2;
                }
            }
        }
        $job['offset'] = $offset;
        $job['hits'] = $hits;
        if ($offset >= $total) {
            return $this->finishJob($job);
        }
        $this->putJob($job);

        return Result::success($this->progressPayload($job), $this->progressText($job));
    }

    /**
     * @param  array<string, mixed>  $job
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    private function finishJob(array $job): array
    {
        $hits = is_array($job['hits'] ?? null) ? $job['hits'] : [];
        $roots = is_array($job['roots'] ?? null) ? $job['roots'] : [];
        $payload = $this->presentPayload(
            $hits,
            (int) ($job['files_scanned'] ?? 0),
            (bool) ($job['truncated'] ?? false),
            (bool) ($job['with_app'] ?? false),
            (bool) ($job['custom'] ?? false),
            $roots,
            (float) ($job['started'] ?? microtime(true))
        );
        $msg = $this->summary($payload);
        $payload['summary'] = $msg;
        $payload['done'] = 1;
        $payload['token'] = '';
        $payload['total'] = is_array($job['files'] ?? null) ? count($job['files']) : 0;
        $payload['scanned'] = (int) ($job['offset'] ?? $payload['files_scanned']);
        $payload['percent'] = 100;
        $result = Result::success($payload, $msg);
        $token = $this->cleanToken((string) ($job['token'] ?? ''));
        if ($token !== '') {
            Cache::forget($this->jobKey($token));
        }
        if (empty($job['custom'])) {
            Cache::put(self::LAST_KEY, $payload, self::LAST_TTL);
            AdminOpLog::ifOk($result, 'scan', $msg, ['module' => 'safety']);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $job
     * @return array<string, mixed>
     */
    private function progressPayload(array $job): array
    {
        $total = is_array($job['files'] ?? null) ? count($job['files']) : 0;
        $scanned = (int) ($job['offset'] ?? 0);
        $hits = is_array($job['hits'] ?? null) ? $job['hits'] : [];
        $known = 0;
        $other = 0;
        foreach ($hits as $hit) {
            if (! empty($hit['known'])) {
                $known++;
            } else {
                $other++;
            }
        }

        return [
            'token' => (string) ($job['token'] ?? ''),
            'done' => 0,
            'total' => $total,
            'scanned' => $scanned,
            'percent' => $total < 1 ? 100 : (int) floor($scanned * 100 / $total),
            'files_scanned' => (int) ($job['files_scanned'] ?? $scanned),
            'hit_count' => count($hits),
            'known_count' => $known,
            'other_count' => $other,
            'with_app' => ! empty($job['with_app']),
            'msg' => $this->progressText($job),
        ];
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function progressText(array $job): string
    {
        $total = is_array($job['files'] ?? null) ? count($job['files']) : 0;
        $scanned = (int) ($job['offset'] ?? 0);

        return '正在扫 '.$scanned.' / '.$total;
    }

    /**
     * @param  list<string>  $roots
     * @return array{0:list<string>,1:bool}
     */
    private function collectFiles(array $roots): array
    {
        $files = [];
        $truncated = false;
        foreach ($roots as $root) {
            foreach ($this->phpFiles($root) as $file) {
                if (count($files) >= self::MAX_FILES) {
                    $truncated = true;
                    break 2;
                }
                $rel = $this->rel($file->getPathname());
                if ($this->skipRel($rel)) {
                    continue;
                }
                $files[] = $file->getPathname();
            }
        }

        return [$files, $truncated];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function scanPath(string $path): array
    {
        if ($path === '' || ! is_file($path)) {
            return [];
        }
        try {
            $size = (int) filesize($path);
        } catch (\Throwable) {
            return [];
        }
        if ($size < 1 || $size > self::MAX_FILE_BYTES) {
            return [];
        }
        $content = @file_get_contents($path);
        if (! is_string($content) || $content === '' || str_contains(substr($content, 0, 4096), "\0")) {
            return [];
        }
        $rel = $this->rel($path);
        $hits = [];
        $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];
        foreach ($lines as $num => $line) {
            $matched = $this->matchLine((string) $line);
            if ($matched === []) {
                continue;
            }
            $hits[] = $this->presentHit($rel, $num + 1, (string) $line, $matched);
            if (count($hits) >= self::MAX_HITS) {
                break;
            }
        }

        return $hits;
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function putJob(array $job): void
    {
        $token = $this->cleanToken((string) ($job['token'] ?? ''));
        if ($token === '') {
            return;
        }
        Cache::put($this->jobKey($token), $job, self::JOB_TTL);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getJob(string $token): ?array
    {
        $token = $this->cleanToken($token);
        if ($token === '') {
            return null;
        }
        $job = Cache::get($this->jobKey($token));

        return is_array($job) ? $job : null;
    }

    private function jobKey(string $token): string
    {
        return 'admin.safety.job.'.$token;
    }

    private function cleanToken(string $token): string
    {
        $token = strtolower(trim($token));
        if ($token === '' || ! preg_match('/^[a-f0-9]{32}$/', $token)) {
            return '';
        }

        return $token;
    }

    /**
     * @return \Generator<SplFileInfo>
     */
    private function phpFiles(string $root): \Generator
    {
        try {
            $inner = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
            $filter = new RecursiveCallbackFilterIterator($inner, function (SplFileInfo $current): bool {
                $name = strtolower($current->getFilename());
                if ($current->isDir()) {
                    if (in_array($name, ['vendor', 'node_modules', '.git', 'cankao', '.idea'], true)) {
                        return false;
                    }
                    $pathname = str_replace('\\', '/', $current->getPathname());
                    if ($name === 'storage' && preg_match('#/public/storage$#i', $pathname)) {
                        return false;
                    }
                    if ($name === 'cache' && preg_match('#/bootstrap/cache$#i', $pathname)) {
                        return false;
                    }
                    if (in_array($name, ['framework', 'logs'], true) && preg_match('#/storage/(framework|logs)$#i', $pathname)) {
                        return false;
                    }
                }

                return true;
            });
            $it = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
        } catch (\Throwable) {
            return;
        }

        foreach ($it as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }
            try {
                if (! $file->isFile()) {
                    continue;
                }
            } catch (\Throwable) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if ($ext !== 'php' && $ext !== 'phtml') {
                continue;
            }
            yield $file;
        }
    }

    /**
     * @return list<array{id:string,fn:string,label:string,hint:string}>
     */
    private function matchLine(string $line): array
    {
        $matched = [];
        foreach ($this->needleCatalog() as $needle) {
            $fn = $needle['fn'];
            if ($fn === '') {
                continue;
            }
            if (preg_match('/(?<![A-Za-z0-9_])'.preg_quote($fn, '/').'\s*\(/', $line)) {
                $matched[] = $needle;
            }
        }

        return $matched;
    }

    /**
     * @param  list<array{id:string,fn:string,label:string,hint:string}>  $matched
     * @return array<string, mixed>
     */
    private function presentHit(string $rel, int $line, string $source, array $matched): array
    {
        $ids = [];
        $labels = [];
        $unknown = false;
        $hints = [];
        $known = $this->knownMap()[$rel] ?? [];
        foreach ($matched as $row) {
            $ids[] = $row['id'];
            $labels[] = $row['label'];
            $hint = (string) ($known[$row['id']] ?? '');
            if ($hint === '') {
                $unknown = true;
            } else {
                $hints[] = $hint;
            }
        }

        return [
            'file' => $rel,
            'line' => $line,
            'needle' => $ids[0] ?? '',
            'needles' => $ids,
            'needle_label' => implode('、', array_unique($labels)),
            'snippet' => $this->snippet($source),
            'known' => ! $unknown,
            'known_hint' => implode('；', array_unique($hints)),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $hits
     * @param  list<string>  $roots
     * @return array<string, mixed>
     */
    private function presentPayload(array $hits, int $files, bool $truncated, bool $withApp, bool $custom, array $roots, float $started): array
    {
        $knownCount = 0;
        $otherCount = 0;
        foreach ($hits as $hit) {
            if (! empty($hit['known'])) {
                $knownCount++;
            } else {
                $otherCount++;
            }
        }

        return [
            'with_app' => $withApp && ! $custom,
            'custom' => $custom,
            'scanned_at' => date('Y-m-d H:i:s'),
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'files_scanned' => $files,
            'hit_count' => count($hits),
            'known_count' => $knownCount,
            'other_count' => $otherCount,
            'truncated' => $truncated,
            'dirs' => array_map(fn (string $root): string => $this->rel($root).'/', $roots),
            'hits' => $hits,
            'groups' => $this->groupHits($hits),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $hits
     * @return array{other: list<array<string, mixed>>, known: list<array<string, mixed>>}
     */
    private function groupHits(array $hits): array
    {
        $files = [];
        foreach ($hits as $hit) {
            $file = (string) ($hit['file'] ?? '');
            if ($file === '') {
                continue;
            }
            if (! isset($files[$file])) {
                $files[$file] = [
                    'file' => $file,
                    'known' => true,
                    'hits' => [],
                ];
            }
            if (empty($hit['known'])) {
                $files[$file]['known'] = false;
            }
            $files[$file]['hits'][] = $hit;
        }
        $other = [];
        $known = [];
        foreach ($files as $group) {
            if ($group['known']) {
                $known[] = $group;
            } else {
                $other[] = $group;
            }
        }

        return ['other' => $other, 'known' => $known];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function summary(array $payload): string
    {
        $other = (int) ($payload['other_count'] ?? 0);
        $known = (int) ($payload['known_count'] ?? 0);
        if (! empty($payload['custom'])) {
            $where = '扫完指定目录';
        } elseif (! empty($payload['with_app'])) {
            $where = '连程序目录扫了一遍';
        } else {
            $where = '扫了入口、插件和存储';
        }
        if ($other > 0) {
            $msg = $where.'：'.$other.' 处要人工看';
            if ($known > 0) {
                $msg .= '，另有 '.$known.' 处是本站已知代码';
            }
        } elseif ($known > 0) {
            $msg = $where.'：没有额外可疑调用，只有本站已知代码 '.$known.' 处';
        } else {
            $msg = $where.'：这些目录里没扫到这些函数。不等于没有别的马。';
        }
        if (! empty($payload['truncated'])) {
            $msg .= ' 扫到上限就停了，结果可能不全。';
        }

        return $msg;
    }

    private function snippet(string $line): string
    {
        $line = trim((string) preg_replace('/\s+/u', ' ', $line));
        $line = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $line);
        if (function_exists('mb_strlen') && mb_strlen($line) > 140) {
            return mb_substr($line, 0, 140).'…';
        }
        if (strlen($line) > 140) {
            return substr($line, 0, 140).'…';
        }

        return $line;
    }

    private function rel(string $path): string
    {
        $base = str_replace('\\', '/', rtrim(base_path(), '/\\'));
        $path = str_replace('\\', '/', $path);
        if (str_starts_with($path, $base.'/')) {
            return substr($path, strlen($base) + 1);
        }
        if (strcasecmp($path, $base) === 0) {
            return '';
        }

        return $path;
    }

    private function skipRel(string $rel): bool
    {
        $norm = strtolower(str_replace('\\', '/', $rel));
        $parts = explode('/', $norm);
        foreach (['vendor', 'node_modules', '.git', 'cankao', '.idea'] as $skip) {
            if (in_array($skip, $parts, true)) {
                return true;
            }
        }
        foreach (['storage/framework/', 'storage/logs/', 'bootstrap/cache/', 'public/storage/'] as $prefix) {
            if (str_starts_with($norm, $prefix) || str_contains($norm, '/'.$prefix)) {
                return true;
            }
        }

        return false;
    }
}

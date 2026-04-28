<?php
namespace App\Services\Admin\System;

use App\Support\Utils\Result;
use function App\Services\System\config;
use function App\Services\System\storage_path;

/**
 * 数据库备份服务
 */
class SysDatabaseBackupService
{
    public function runBackup(): array
    {
        $binRes = $this->resolveMysqldumpBinary();
        if ($binRes['code'] !== 0)
        {
            return $binRes;
        }

        $mysqldump = (string) ($binRes['data']['path'] ?? 'mysqldump');

        $connection = (string) config('database.default', 'mysql');
        $cfg        = (array) config('database.connections.' . $connection, []);

        $driver     = (string) ($cfg['driver'] ?? '');
        if ($driver !== 'mysql' && $driver !== 'mariadb')
        {
            return Result::fail('仅支持 MySQL/MariaDB 备份');
        }

        $host     = (string) ($cfg['host'] ?? '127.0.0.1');
        $port     = (string) ($cfg['port'] ?? '3306');
        $database = (string) ($cfg['database'] ?? '');
        $username = (string) ($cfg['username'] ?? '');
        $password = (string) ($cfg['password'] ?? '');

        if ($database === '' || $username === '')
        {
            return Result::fail('数据库配置不完整');
        }

        $dir = $this->getBackupDir();
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir))
        {
            return Result::fail('备份目录创建失败');
        }

        $time     = date('Ymd_His');
        $filename = $database . '_' . $time . '.sql';
        $path     = $dir . DIRECTORY_SEPARATOR . $filename;

        $tmpCnf = $dir . DIRECTORY_SEPARATOR . '.mysqldump_' . uniqid('', true) . '.cnf';
        $cnf    = "[client]\nuser={$username}\npassword={$password}\nhost={$host}\nport={$port}\n";

        if (@file_put_contents($tmpCnf, $cnf) === false)
        {
            return Result::fail('临时配置写入失败');
        }

        try {
            $sslMode = strtoupper(trim((string) env('MYSQLDUMP_SSL_MODE', 'AUTO')));
            $sslMode = $sslMode === '' ? 'AUTO' : $sslMode;

            $disabledSslArgs = [];
            $disabledRes = $this->resolveMysqldumpSslArgs($mysqldump, 'DISABLED');
            if (($disabledRes['code'] ?? 1) === 0)
            {
                $disabledSslArgs = (array) ($disabledRes['data']['args'] ?? []);
            }
            $explicitSslArgs = [];
            if ($sslMode !== 'AUTO')
            {
                $sslRes = $this->resolveMysqldumpSslArgs($mysqldump, $sslMode);
                if ($sslRes['code'] !== 0)
                {
                    return $sslRes;
                }
                $explicitSslArgs = (array) ($sslRes['data']['args'] ?? []);
            }

            $commandBase = [
                $mysqldump,
                '--defaults-extra-file=' . $tmpCnf,
                '--default-character-set=utf8mb4',
                '--single-transaction',
                '--routines',
                '--events',
                '--set-gtid-purged=OFF',
                '--databases',
                $database,
                '--result-file=' . $path,
            ];

            if (class_exists(\Symfony\Component\Process\Process::class))
            {
                $command = array_values(array_merge($commandBase, $explicitSslArgs));
                $process = new \Symfony\Component\Process\Process($command);
                $process->setTimeout(null);
                $process->run();

                if (!$process->isSuccessful())
                {
                    $err = trim((string) $process->getErrorOutput());
                    if ($err === '')
                    {
                        $err = trim((string) $process->getOutput());
                    }
                    $err = $this->toUtf8($err);

                    if ($sslMode === 'AUTO' && $this->isMysqldumpSslError($err) && $disabledSslArgs !== [])
                    {
                        @unlink($path);

                        $command2 = array_values(array_merge($commandBase, $disabledSslArgs));
                        $process2 = new \Symfony\Component\Process\Process($command2);
                        $process2->setTimeout(null);
                        $process2->run();

                        if (!$process2->isSuccessful())
                        {
                            $err2 = trim((string) $process2->getErrorOutput());
                            if ($err2 === '')
                            {
                                $err2 = trim((string) $process2->getOutput());
                            }
                            $err2 = $this->toUtf8($err2);
                            return Result::fail($err2 !== '' ? $err2 : ($err !== '' ? $err : '备份失败'));
                        }
                    }
                    else
                    {
                        return Result::fail($err !== '' ? $err : '备份失败');
                    }
                }
            }
            else
            {
                $cmd = '';
                $command = array_values(array_merge($commandBase, $explicitSslArgs));

                foreach ($command as $arg)
                {
                    $cmd .= ($cmd === '' ? '' : ' ') . escapeshellarg($arg);
                }

                $output = @shell_exec($cmd . ' 2>&1');
                if (!is_file($path) || filesize($path) < 1)
                {
                    $err = is_string($output) ? $this->toUtf8(trim($output)) : '';

                    if ($sslMode === 'AUTO' && $this->isMysqldumpSslError($err) && $disabledSslArgs !== [])
                    {
                        @unlink($path);

                        $cmd2 = '';
                        $command2 = array_values(array_merge($commandBase, $disabledSslArgs));
                        foreach ($command2 as $arg)
                        {
                            $cmd2 .= ($cmd2 === '' ? '' : ' ') . escapeshellarg($arg);
                        }

                        $output2 = @shell_exec($cmd2 . ' 2>&1');
                        if (!is_file($path) || filesize($path) < 1)
                        {
                            $err2 = is_string($output2) ? $this->toUtf8(trim($output2)) : '';
                            return Result::fail($err2 !== '' ? $err2 : ($err !== '' ? $err : '备份失败'));
                        }
                    }
                    else
                    {
                        return Result::fail($err !== '' ? $err : '备份失败');
                    }
                }
            }
        } finally {
            @unlink($tmpCnf);
        }

        $size = is_file($path) ? (int) filesize($path) : 0;

        return Result::success([
            'name' => $filename,
            'size' => $size,
        ], '备份成功');
    }

    public function restoreBackup(string $file): array
    {
        $fileRes = $this->resolveBackupFilePath($file);
        if (($fileRes['code'] ?? 1) !== 0)
        {
            return $fileRes;
        }

        $binRes = $this->resolveMysqlBinary();
        if (($binRes['code'] ?? 1) !== 0)
        {
            return $binRes;
        }

        $mysql = (string) ($binRes['data']['path'] ?? 'mysql');

        $connection = (string) config('database.default', 'mysql');
        $cfg        = (array) config('database.connections.' . $connection, []);

        $driver     = (string) ($cfg['driver'] ?? '');
        if ($driver !== 'mysql' && $driver !== 'mariadb')
        {
            return Result::fail('仅支持 MySQL/MariaDB 恢复');
        }

        $host     = (string) ($cfg['host'] ?? '127.0.0.1');
        $port     = (string) ($cfg['port'] ?? '3306');
        $database = (string) ($cfg['database'] ?? '');
        $username = (string) ($cfg['username'] ?? '');
        $password = (string) ($cfg['password'] ?? '');

        if ($database === '' || $username === '')
        {
            return Result::fail('数据库配置不完整');
        }

        $dir = $this->getBackupDir();
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir))
        {
            return Result::fail('备份目录不存在且创建失败');
        }

        $lockFile = storage_path('app' . DIRECTORY_SEPARATOR . 'db_restore.lock');
        if (is_file($lockFile))
        {
            return Result::fail('恢复任务正在执行中，请稍后再试');
        }

        if (@file_put_contents($lockFile, (string) time()) === false)
        {
            return Result::fail('恢复锁创建失败');
        }

        $sqlPath = (string) ($fileRes['data']['path'] ?? '');
        if ($sqlPath === '' || !is_file($sqlPath))
        {
            @unlink($lockFile);
            return Result::fail('SQL 文件不存在');
        }

        $tmpCnf = $dir . DIRECTORY_SEPARATOR . '.mysql_' . uniqid('', true) . '.cnf';
        $cnf    = "[client]\nuser={$username}\npassword={$password}\nhost={$host}\nport={$port}\n";

        if (@file_put_contents($tmpCnf, $cnf) === false)
        {
            @unlink($lockFile);
            return Result::fail('临时配置写入失败');
        }

        try {
            $sslMode = strtoupper(trim((string) env('MYSQL_SSL_MODE', 'AUTO')));
            $sslMode = $sslMode === '' ? 'AUTO' : $sslMode;

            $disabledSslArgs = [];
            $disabledRes = $this->resolveMysqlSslArgs($mysql, 'DISABLED');
            if (($disabledRes['code'] ?? 1) === 0)
            {
                $disabledSslArgs = (array) ($disabledRes['data']['args'] ?? []);
            }

            $explicitSslArgs = [];
            if ($sslMode !== 'AUTO')
            {
                $sslRes = $this->resolveMysqlSslArgs($mysql, $sslMode);
                if (($sslRes['code'] ?? 1) !== 0)
                {
                    return $sslRes;
                }
                $explicitSslArgs = (array) ($sslRes['data']['args'] ?? []);
            }

            $commandBase = [
                $mysql,
                '--defaults-extra-file=' . $tmpCnf,
                '--default-character-set=utf8mb4',
                '--binary-mode=1',
                '--database=' . $database,
            ];

            $ok = $this->runMysqlImportCommand($commandBase, $explicitSslArgs, $sqlPath);
            if (($ok['code'] ?? 1) === 0)
            {
                return Result::success([], '恢复成功');
            }

            $err = (string) ($ok['msg'] ?? '');
            if ($sslMode === 'AUTO' && $this->isMysqldumpSslError($err) && $disabledSslArgs !== [])
            {
                $ok2 = $this->runMysqlImportCommand($commandBase, $disabledSslArgs, $sqlPath);
                if (($ok2['code'] ?? 1) === 0)
                {
                    return Result::success([], '恢复成功');
                }

                $err2 = (string) ($ok2['msg'] ?? '');
                return Result::fail($err2 !== '' ? $err2 : ($err !== '' ? $err : '恢复失败'));
            }

            return Result::fail($err !== '' ? $err : '恢复失败');
        } finally {
            @unlink($tmpCnf);
            @unlink($lockFile);
        }
    }
    /**
     * 获取备份文件列表
     * @return array
     */
    public function listBackupFiles(): array
    {
        $dir = $this->getBackupDir();
        if (!is_dir($dir))
        {
            return Result::success([
                'total' => 0,
                'data'  => [],
            ]);
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];

        $data  = [];
        foreach ($files as $path)
        {
            $name = basename($path);
            $mtime = (int) @filemtime($path);
            $data[] = [
                'name' => $name,
                'size' => (int) @filesize($path),
                'time' => $mtime > 0 ? date('Y-m-d H:i:s', $mtime) : '',
                'mtime' => $mtime,
            ];
        }

        usort($data, function ($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });

        foreach ($data as &$item)
        {
            unset($item['mtime']);
        }

        return Result::success([
            'total' => count($data),
            'data'  => $data,
        ]);
    }
    /**
     * 解析备份文件路径
     * @param string $file
     * @return array
     */
    public function resolveBackupFilePath(string $file): array
    {
        $file = trim($file);
        if ($file === '')
        {
            return Result::fail('请选择文件');
        }

        if (!preg_match('/^[A-Za-z0-9_.-]+\\.sql$/', $file))
        {
            return Result::fail('非法文件名');
        }

        $dir  = $this->getBackupDir();
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (!is_file($path))
        {
            return Result::fail('文件不存在');
        }

        return Result::success([
            'name' => $file,
            'path' => $path,
        ]);
    }
    /**
     * 删除备份文件
     * @param string $file
     * @return array
     */
    public function deleteBackupFile(string $file): array
    {
        $res = $this->resolveBackupFilePath($file);
        if ($res['code'] !== 0)
        {
            return $res;
        }

        $path = (string) ($res['data']['path'] ?? '');
        if ($path === '' || !is_file($path))
        {
            return Result::fail('文件不存在');
        }

        if (!@unlink($path))
        {
            return Result::fail('删除失败');
        }

        return Result::success([], '删除成功');
    }

    private function getBackupDir(): string
    {
        return storage_path('app' . DIRECTORY_SEPARATOR . 'db_backup');
    }
    /**
     * 解析 mysqldump 二进制文件路径
     * @return array
     */
    private function resolveMysqldumpBinary(): array
    {
        $configured = trim((string) env('MYSQLDUMP_PATH', ''));
        if ($configured !== '')
        {
            if (is_file($configured))
            {
                return Result::success(['path' => $configured]);
            }

            return Result::fail('MYSQLDUMP_PATH 文件不存在：' . $configured);
        }

        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if (class_exists(\Symfony\Component\Process\Process::class))
        {
            $finder    = $isWindows ? ['where', 'mysqldump'] : ['command', '-v', 'mysqldump'];
            $process   = new \Symfony\Component\Process\Process($finder);
            $process->setTimeout(5);
            $process->run();

            if ($process->isSuccessful())
            {
                $out = trim((string) $process->getOutput());
                $out = $this->toUtf8($out);
                $lines = preg_split("/\r\n|\n|\r/", $out) ?: [];
                foreach ($lines as $line)
                {
                    $line = trim((string) $line);
                    if ($line !== '' && is_file($line))
                    {
                        return Result::success(['path' => $line]);
                    }
                }
            }
        }

        $candidates = [];
        if ($isWindows)
        {
            $candidates = array_merge($candidates, glob('D:\\phpstudy_pro\\Extensions\\MySQL*\\bin\\mysqldump.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\phpstudy_pro\\Extensions\\MySQL*\\bin\\mysqldump.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\xampp\\mysql\\bin\\mysqldump.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysqldump.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\Program Files\\MySQL\\MySQL Server*\\bin\\mysqldump.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\Program Files (x86)\\MySQL\\MySQL Server*\\bin\\mysqldump.exe') ?: []);
        }
        else
        {
            $candidates[] = '/usr/bin/mysqldump';
            $candidates[] = '/usr/local/bin/mysqldump';
            $candidates[] = '/bin/mysqldump';
        }

        foreach ($candidates as $path)
        {
            $path = (string) $path;
            if ($path !== '' && is_file($path))
            {
                return Result::success(['path' => $path]);
            }
        }

        return Result::fail("未找到 mysqldump，请安装 MySQL 客户端或配置环境变量 MYSQLDUMP_PATH（例如：D:\\\\phpstudy_pro\\\\Extensions\\\\MySQL\\\\bin\\\\mysqldump.exe）");
    }

    private function resolveMysqlBinary(): array
    {
        $configured = trim((string) env('MYSQL_PATH', ''));
        if ($configured !== '')
        {
            if (is_file($configured))
            {
                return Result::success(['path' => $configured]);
            }

            return Result::fail('MYSQL_PATH 文件不存在：' . $configured);
        }

        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if (class_exists(\Symfony\Component\Process\Process::class))
        {
            $finder    = $isWindows ? ['where', 'mysql'] : ['command', '-v', 'mysql'];
            $process   = new \Symfony\Component\Process\Process($finder);
            $process->setTimeout(5);
            $process->run();

            if ($process->isSuccessful())
            {
                $out = trim((string) $process->getOutput());
                $out = $this->toUtf8($out);
                $lines = preg_split("/\r\n|\n|\r/", $out) ?: [];
                foreach ($lines as $line)
                {
                    $line = trim((string) $line);
                    if ($line !== '' && is_file($line))
                    {
                        return Result::success(['path' => $line]);
                    }
                }
            }
        }

        $candidates = [];
        if ($isWindows)
        {
            $candidates = array_merge($candidates, glob('D:\\phpstudy_pro\\Extensions\\MySQL*\\bin\\mysql.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\phpstudy_pro\\Extensions\\MySQL*\\bin\\mysql.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\xampp\\mysql\\bin\\mysql.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysql.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\Program Files\\MySQL\\MySQL Server*\\bin\\mysql.exe') ?: []);
            $candidates = array_merge($candidates, glob('C:\\Program Files (x86)\\MySQL\\MySQL Server*\\bin\\mysql.exe') ?: []);
        }
        else
        {
            $candidates[] = '/usr/bin/mysql';
            $candidates[] = '/usr/local/bin/mysql';
            $candidates[] = '/bin/mysql';
        }

        foreach ($candidates as $path)
        {
            $path = (string) $path;
            if ($path !== '' && is_file($path))
            {
                return Result::success(['path' => $path]);
            }
        }

        return Result::fail("未找到 mysql，请安装 MySQL 客户端或配置环境变量 MYSQL_PATH（例如：D:\\\\phpstudy_pro\\\\Extensions\\\\MySQL\\\\bin\\\\mysql.exe）");
    }

    private function resolveMysqlSslArgs(string $mysql, string $mode): array
    {
        $mode = strtoupper(trim($mode));
        $mode = $mode === '' ? 'AUTO' : $mode;

        $supported = $this->detectMysqlSupportedOptions($mysql);
        $hasSslMode = (bool) ($supported['ssl_mode'] ?? false);
        $hasSkipSsl = (bool) ($supported['skip_ssl'] ?? false);

        if ($mode === 'AUTO')
        {
            return Result::success(['args' => []]);
        }

        if ($mode === 'DISABLED')
        {
            if ($hasSslMode)
            {
                return Result::success(['args' => ['--ssl-mode=DISABLED']]);
            }
            if ($hasSkipSsl)
            {
                return Result::success(['args' => ['--skip-ssl']]);
            }
            return Result::success(['args' => []]);
        }

        if (in_array($mode, ['PREFERRED', 'REQUIRED', 'VERIFY_CA', 'VERIFY_IDENTITY'], true))
        {
            if ($hasSslMode)
            {
                return Result::success(['args' => ['--ssl-mode=' . $mode]]);
            }

            return Result::fail('当前 mysql 不支持 --ssl-mode，请升级 MySQL 客户端或改用 MYSQL_SSL_MODE=DISABLED');
        }

        return Result::fail('MYSQL_SSL_MODE 值非法：' . $mode);
    }

    private function detectMysqlSupportedOptions(string $mysql): array
    {
        static $cache = [];
        if (isset($cache[$mysql]))
        {
            return $cache[$mysql];
        }

        $res = [
            'ssl_mode' => false,
            'skip_ssl' => false,
        ];

        if (class_exists(\Symfony\Component\Process\Process::class))
        {
            $process = new \Symfony\Component\Process\Process([$mysql, '--help']);
            $process->setTimeout(5);
            $process->run();

            $out = (string) $process->getOutput();
            if ($out === '')
            {
                $out = (string) $process->getErrorOutput();
            }
            $out = $this->toUtf8($out);

            if ($out !== '')
            {
                $res['ssl_mode'] = stripos($out, '--ssl-mode') !== false;
                $res['skip_ssl'] = stripos($out, '--skip-ssl') !== false;
            }
        }

        $cache[$mysql] = $res;
        return $res;
    }

    private function runMysqlImportCommand(array $commandBase, array $sslArgs, string $inputSqlPath): array
    {
        $command = array_values(array_merge($commandBase, $sslArgs));

        $inner = '';
        foreach ($command as $arg)
        {
            $inner .= ($inner === '' ? '' : ' ') . escapeshellarg((string) $arg);
        }

        $inner .= ' < ' . escapeshellarg($inputSqlPath);

        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $shellCmd  = $isWindows ? ('cmd /C ' . escapeshellarg($inner)) : ('sh -c ' . escapeshellarg($inner));

        if (class_exists(\Symfony\Component\Process\Process::class))
        {
            $process = \Symfony\Component\Process\Process::fromShellCommandline($shellCmd);
            $process->setTimeout(null);
            $process->run();

            if ($process->isSuccessful())
            {
                return Result::success([]);
            }

            $err = trim((string) $process->getErrorOutput());
            if ($err === '')
            {
                $err = trim((string) $process->getOutput());
            }
            $err = $this->toUtf8($err);
            return Result::fail($err !== '' ? $err : '恢复失败');
        }

        $output = @shell_exec($shellCmd . ' 2>&1');
        $err = is_string($output) ? $this->toUtf8(trim($output)) : '';
        if ($err === '')
        {
            return Result::success([]);
        }

        return Result::fail($err);
    }
    /**
     * 解析 mysqldump SSL 参数
     * @param string $mysqldump
     * @param string $mode
     * @return array
     */
    private function resolveMysqldumpSslArgs(string $mysqldump, string $mode): array
    {
        $mode = strtoupper(trim($mode));
        $mode = $mode === '' ? 'AUTO' : $mode;

        $supported = $this->detectMysqldumpSupportedOptions($mysqldump);
        $hasSslMode = (bool) ($supported['ssl_mode'] ?? false);
        $hasSkipSsl = (bool) ($supported['skip_ssl'] ?? false);

        if ($mode === 'AUTO')
        {
            return Result::success(['args' => []]);
        }

        if ($mode === 'DISABLED')
        {
            return Result::success(['args' => $this->getMysqldumpSslArgs($hasSslMode, $hasSkipSsl)]);
        }

        if (in_array($mode, ['PREFERRED', 'REQUIRED', 'VERIFY_CA', 'VERIFY_IDENTITY'], true))
        {
            if ($hasSslMode)
            {
                return Result::success(['args' => ['--ssl-mode=' . $mode]]);
            }

            return Result::fail('当前 mysqldump 不支持 --ssl-mode，请升级 MySQL 客户端或改用 MYSQLDUMP_SSL_MODE=DISABLED');
        }

        return Result::fail('MYSQLDUMP_SSL_MODE 值非法：' . $mode);
    }
    /**
     * 获取 mysqldump SSL 参数
     * @param bool $hasSslMode
     * @param bool $hasSkipSsl
     * @return array
     */
    private function getMysqldumpSslArgs(bool $hasSslMode, bool $hasSkipSsl): array
    {
        if ($hasSslMode)
        {
            return ['--ssl-mode=DISABLED'];
        }
        if ($hasSkipSsl)
        {
            return ['--skip-ssl'];
        }
        return [];
    }
    /**
     * 检测 mysqldump 支持的 SSL 参数
     * @param string $mysqldump
     * @return array
     */
    private function detectMysqldumpSupportedOptions(string $mysqldump): array
    {
        static $cache = [];
        if (isset($cache[$mysqldump]))
        {
            return $cache[$mysqldump];
        }

        $res = [
            'ssl_mode' => false,
            'skip_ssl' => false,
        ];

        if (class_exists(\Symfony\Component\Process\Process::class))
        {
            $process = new \Symfony\Component\Process\Process([$mysqldump, '--help']);
            $process->setTimeout(5);
            $process->run();

            $out = (string) $process->getOutput();
            if ($out === '')
            {
                $out = (string) $process->getErrorOutput();
            }
            $out = $this->toUtf8($out);

            if ($out !== '')
            {
                $res['ssl_mode'] = stripos($out, '--ssl-mode') !== false;
                $res['skip_ssl'] = stripos($out, '--skip-ssl') !== false;
            }
        }

        $cache[$mysqldump] = $res;
        return $res;
    }
    /**
     * 检测 mysqldump SSL 错误
     * @param string $message
     * @return bool
     */
    private function isMysqldumpSslError(string $message): bool
    {
        $m = strtolower($message);
        return str_contains($m, 'ssl connection error') || str_contains($m, 'got error: 2026') || str_contains($m, 'error: 2026');
    }
    /**
     * 转换字符串为 UTF-8 编码
     * @param string $value
     * @return string
     */
    private function toUtf8(string $value): string
    {
        $value = trim($value);
        if ($value === '')
        {
            return '';
        }

        if (@preg_match('//u', $value))
        {
            return $value;
        }

        if (function_exists('iconv'))
        {
            foreach (['GBK', 'GB2312', 'BIG5', 'ISO-8859-1', 'Windows-1252'] as $enc)
            {
                $converted = @iconv($enc, 'UTF-8//IGNORE', $value);
                if (is_string($converted) && $converted !== '' && @preg_match('//u', $converted))
                {
                    return trim($converted);
                }
            }

            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if (is_string($converted) && $converted !== '' && @preg_match('//u', $converted))
            {
                return trim($converted);
            }
        }

        if (function_exists('mb_convert_encoding'))
        {
            $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5,ISO-8859-1,Windows-1252');
            if (is_string($converted) && $converted !== '' && @preg_match('//u', $converted))
            {
                return trim($converted);
            }
        }

        $cleaned = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $value);
        return is_string($cleaned) ? trim($cleaned) : '';
    }
}

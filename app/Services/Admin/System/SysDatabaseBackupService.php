<?php
namespace App\Services\Admin\System;

use App\Models\System\SysScheduleModel;
use App\Support\Utils\Result;
use Cron\CronExpression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 数据库备份服务
 */
class SysDatabaseBackupService
{
    public const COMMAND = 'video:db-backup';

    public function runBackup(int $keep = 0): array
    {
        $keep = max(0, min($keep, 30));
        $driver = $this->connectionDriver();
        if ($driver === 'sqlite') {
            $res = $this->runSqliteBackup();
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            $res = $this->runMysqlDump();
        } else {
            return Result::fail('只支持 SQLite 文件库和 MySQL/MariaDB');
        }
        if ((int) ($res['code'] ?? 1) !== 0) {
            return $res;
        }
        if ($keep > 0) {
            $this->pruneOldBackups($keep);
        }
        $name = (string) ($res['data']['name'] ?? '');
        $size = (int) ($res['data']['size'] ?? 0);

        return Result::success([
            'name' => $name,
            'size' => $size,
            'size_text' => $this->sizeText($size),
        ], '已备份到磁盘');
    }

    private function runMysqlDump(): array
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

        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $database) ?: 'mysql';
        $filename = $this->uniqueBackupName($safe, 'sql');
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
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function pageBoard(): array
    {
        $driver = $this->connectionDriver();
        $sqlitePath = $driver === 'sqlite' ? $this->sqliteDatabasePath() : '';
        $memory = $driver === 'sqlite' && $this->isMemorySqlite($sqlitePath);
        $can = $this->canBackup();
        $list = $this->listBackupFiles();
        $files = (array) ($list['data']['data'] ?? []);
        $last = $files[0] ?? null;
        $task = $this->findBackupScheduleRow();
        $keep = $this->currentKeep();
        $cron = (string) ($task['cron_expression'] ?? '0 3 * * *');
        if ($cron === '') {
            $cron = '0 3 * * *';
        }
        $on = $task !== null && (int) ($task['status'] ?? 0) === 1;
        $lastRun = (int) ($task['last_run_time'] ?? 0);
        $php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
        $cronLine = '* * * * * '.$php.' '.base_path('artisan').' schedule:run';
        $cronPresets = [
            '0 3 * * *' => admin_t('ui.cron_daily_3am'),
            '0 2 * * *' => admin_t('ui.cron_daily_2am'),
            '0 4 * * *' => admin_t('ui.cron_daily_4am'),
            '0 */6 * * *' => admin_t('ui.cron_every_6h'),
        ];
        if ($cron !== '' && ! isset($cronPresets[$cron])) {
            $cronPresets[$cron] = $cron;
        }
        $driverLabel = admin_t('ui.driver_unknown');
        $driverHint = '';
        if ($driver === 'sqlite' && $memory) {
            $driverLabel = admin_t('ui.driver_sqlite_mem');
            $driverHint = '测试或没配文件库时会这样。本机请把 DB_DATABASE 指到 database/database.sqlite。';
        } elseif ($driver === 'sqlite') {
            $driverLabel = admin_t('ui.driver_sqlite_file');
            $driverHint = '备份是复制库文件，不是导出 SQL 文本。';
        } elseif ($driver === 'mysql') {
            $driverLabel = 'MySQL';
            $driverHint = '用 mysqldump 写出 .sql。机器上要有这个命令。';
        } elseif ($driver === 'mariadb') {
            $driverLabel = 'MariaDB';
            $driverHint = '用 mysqldump 写出 .sql。机器上要有这个命令。';
        } else {
            $driverLabel = $driver !== '' ? $driver : admin_t('ui.driver_unknown');
            $driverHint = '现在这种库不能在这里备份。';
        }

        return [
            'driver' => $driver,
            'driver_label' => $driverLabel,
            'driver_hint' => $driverHint,
            'can_backup' => $can,
            'cannot_reason' => $can ? '' : $this->cannotBackupReason(),
            'dir_text' => 'storage/app/db_backup',
            'files_n' => count($files),
            'files' => $files,
            'last_text' => is_array($last) ? (string) ($last['time'] ?? '') : '',
            'keep' => $keep,
            'schedule' => [
                'id' => (int) ($task['id'] ?? 0),
                'on' => $on,
                'cron' => $cron,
                'cron_label' => $cronPresets[$cron] ?? $cron,
                'keep' => $keep,
                'last_text' => $on ? ($lastRun > 0 ? date('Y-m-d H:i', $lastRun) : admin_t('ui.never_ran')) : admin_t('ui.sched_off'),
                'idle' => $on && $lastRun < 1,
            ],
            'cron_line' => $cronLine,
            'cron_presets' => $cronPresets,
            'restore_unavailable' => $this->cannotRestoreReason(),
            'can_snapshot' => $this->canBackup(),
            'ui' => [
                'title' => '数据库备份',
                'lead' => '把当前库导出到这台服务器的磁盘。定时备份走计划任务。本机 artisan serve 不会自动跑。',
                'restore' => '恢复',
                'sql' => '执行 SQL',
                'schedule' => '计划任务',
                'replace' => '批量替换',
                'now' => '立刻备份',
                'now_hint' => '马上写一份到磁盘。打开着的后台不用关。',
                'timer' => '到点自动备份',
                'timer_hint' => '默认每天凌晨 3 点。只留最近几份，避免把磁盘写满。',
                'keep' => '留最近几份',
                'when' => '多久跑一次',
                'save_timer' => '保存定时',
                'on' => '到点自动备份',
                'install' => '服务器还要装这一行',
                'install_hint' => 'Linux / 宝塔放进 crontab。Windows 用任务计划每分钟跑同一条。没装的话，上面开了也不会自己备份。',
                'copy' => '复制',
                'copied' => '已复制',
                'files' => '已经备份的文件',
                'empty' => '还没有备份。',
                'empty_hint' => '先点「立刻备份」试一次，看这份能不能打开。',
                'download' => '下载',
                'delete' => '删除',
                'restore_one' => '恢复',
                'del_confirm' => '删除这份备份？',
                'busy' => '正在备份…',
                'ok' => '已备份到磁盘',
                'memory' => '当前是内存库，没法备份到文件。',
                'restore_title' => '数据库恢复',
                'restore_lead' => '把一份备份盖回当前库。盖上之后，现在的数据找不回来。',
                'restore_note' => '只能还原本页列出的备份。能备份时会先另存当前库。确定后请输入「盖回」。',
                'restore_now' => '当前库',
                'restore_files' => '可以盖回去的备份',
                'restore_blocked' => '种类对不上',
                'restore_confirm' => '会盖掉当前库，确定用这份？',
                'restore_confirm_save' => '会先把现在这份存到磁盘，再盖上选中的备份。确定后还要输入「盖回」。',
                'restore_confirm_nosave' => '当前库没法另存。盖上之后现在的数据找不回来。确定后还要输入「盖回」。',
                'restore_type' => '会盖掉当前库。要继续请输入：盖回',
                'restore_type_err' => '没输入对，没有盖',
                'restore_word' => '盖回',
                'restore_mismatch' => '这份备份和当前库不是同一种，不能直接还原。',
                'restore_empty' => '还没有可恢复的备份。',
                'restore_empty_hint' => '先到备份页导出一份。点「立刻备份」试一次。选好后点「盖回这份」，再输入「盖回」。',
                'restore_run' => '盖回这份',
                'restore_go' => '去备份',
                'restore_cache' => '缓存',
                'backup' => '去备份',
            ],
        ];
    }

    public function currentKeep(): int
    {
        $row = $this->findBackupScheduleRow();
        if ($row === null) {
            return 7;
        }

        return $this->parseKeep((string) ($row['params'] ?? ''));
    }

    public function parseKeep(string $params): int
    {
        if (preg_match('/--keep=(\d+)/', $params, $m) === 1) {
            return max(1, min(30, (int) $m[1]));
        }
        if (preg_match('/--keep\s+(\d+)/', $params, $m) === 1) {
            return max(1, min(30, (int) $m[1]));
        }

        return 7;
    }

    public function saveBackupSchedule(bool $on, string $cron, int $keep): array
    {
        $keep = max(1, min($keep, 30));
        $cron = trim($cron);
        if ($cron === '') {
            $cron = '0 3 * * *';
        }
        try {
            if (! CronExpression::isValidExpression($cron)) {
                return Result::fail('时间格式不对');
            }
        } catch (\Throwable) {
            return Result::fail('时间格式不对');
        }
        if ($cron === '* * * * *') {
            return Result::fail('备份别每分钟跑，磁盘会很快写满');
        }

        $row = $this->findBackupScheduleRow();
        $id = (int) ($row['id'] ?? 0);
        $prevOn = $id > 0 && (int) ($row['status'] ?? 0) === 1;
        $prevCron = (string) ($row['cron_expression'] ?? '');
        $prevKeep = $id > 0 ? $this->parseKeep((string) ($row['params'] ?? '')) : 7;
        if ($id < 1 && ! $on) {
            return Result::fail('没有改动，不用保存');
        }
        if ($id > 0 && $prevOn === $on && $prevCron === $cron && $prevKeep === $keep) {
            return Result::fail('没有改动，不用保存');
        }

        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $name = '每天备份库';
        }

        $schedule = app(SysScheduleService::class);
        if (! $on) {
            if ($prevCron !== $cron || $prevKeep !== $keep) {
                $saved = $schedule->saveSchedule(
                    $id, $name, '', 'artisan', self::COMMAND, '--keep='.$keep, $cron,
                    'Asia/Shanghai', 0, 1, 0, 0, 0, 1, '', 10
                );
                if ((int) ($saved['code'] ?? 1) !== 0) {
                    return $saved;
                }
            }

            return $schedule->updateScheduleStatus($id, 0);
        }

        if (! $this->canBackup()) {
            return Result::fail($this->cannotBackupReason());
        }

        return $schedule->saveSchedule(
            $id, $name, '', 'artisan', self::COMMAND, '--keep='.$keep, $cron,
            'Asia/Shanghai', 1, 1, 0, 0, 0, 1, '', 10
        );
    }

    public function restoreBackup(string $file, bool $snapshot = false): array
    {
        $fileRes = $this->resolveBackupFilePath($file);
        if (($fileRes['code'] ?? 1) !== 0)
        {
            return $fileRes;
        }

        $blocked = $this->cannotRestoreReason();
        if ($blocked !== '') {
            return Result::fail($blocked);
        }

        $path = (string) ($fileRes['data']['path'] ?? '');
        $kind = $this->fileKind($file);
        if (! $this->fileCanRestore($kind)) {
            return Result::fail($this->fileRestoreHint($kind));
        }

        $snapshotName = '';
        if ($snapshot) {
            if (! $this->canBackup()) {
                return Result::fail($this->cannotBackupReason());
            }
            $bak = $this->runBackup(0);
            if ((int) ($bak['code'] ?? 1) !== 0) {
                return Result::fail('没能先另存当前库：'.(string) ($bak['msg'] ?? '备份失败'));
            }
            $snapshotName = (string) ($bak['data']['name'] ?? '');
        }

        $driver = $this->connectionDriver();
        if ($driver === 'sqlite') {
            return $this->finishRestore($this->restoreSqliteBackup($path, $kind), $file, $snapshotName);
        }
        if ($kind === 'sqlite') {
            return Result::fail('这份是 SQLite 文件，当前库是 MySQL，不能直接还原');
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
                return $this->finishRestore(Result::success([], ''), $file, $snapshotName);
            }

            $err = (string) ($ok['msg'] ?? '');
            if ($sslMode === 'AUTO' && $this->isMysqldumpSslError($err) && $disabledSslArgs !== [])
            {
                $ok2 = $this->runMysqlImportCommand($commandBase, $disabledSslArgs, $sqlPath);
                if (($ok2['code'] ?? 1) === 0)
                {
                    return $this->finishRestore(Result::success([], ''), $file, $snapshotName);
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
        $data = $this->backupFileRows();

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

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\\.(sql|sqlite)$/', $file))
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
        $custom = trim((string) config('database.backup_path', ''));
        if ($custom !== '') {
            return $custom;
        }

        return storage_path('app' . DIRECTORY_SEPARATOR . 'db_backup');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function backupFileRows(): array
    {
        $dir = $this->getBackupDir();
        if (! is_dir($dir)) {
            return [];
        }
        $files = array_merge(
            glob($dir.DIRECTORY_SEPARATOR.'*.sql') ?: [],
            glob($dir.DIRECTORY_SEPARATOR.'*.sqlite') ?: []
        );
        $data = [];
        foreach ($files as $path) {
            $name = basename((string) $path);
            if ($name === '' || str_starts_with($name, '.')) {
                continue;
            }
            $mtime = (int) @filemtime($path);
            $size = (int) @filesize($path);
            $kind = $this->fileKind($name);
            $canRestore = $this->fileCanRestore($kind);
            $data[] = [
                'name' => $name,
                'size' => $size,
                'size_text' => $this->sizeText($size),
                'time' => $mtime > 0 ? date('Y-m-d H:i:s', $mtime) : '',
                'mtime' => $mtime,
                'kind' => $kind,
                'kind_label' => $kind === 'sqlite' ? 'SQLite 文件' : 'SQL 文本',
                'can_restore' => $canRestore,
                'restore_hint' => $canRestore ? '' : $this->fileRestoreHint($kind),
            ];
        }
        usort($data, function ($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });
        foreach ($data as &$item) {
            unset($item['mtime']);
        }
        unset($item);

        return $data;
    }

    private function pruneOldBackups(int $keep): void
    {
        if ($keep < 1) {
            return;
        }
        $dir = $this->getBackupDir();
        if (! is_dir($dir)) {
            return;
        }
        $files = array_merge(
            glob($dir.DIRECTORY_SEPARATOR.'*.sql') ?: [],
            glob($dir.DIRECTORY_SEPARATOR.'*.sqlite') ?: []
        );
        $rows = [];
        foreach ($files as $path) {
            $name = basename((string) $path);
            if ($name === '' || str_starts_with($name, '.')) {
                continue;
            }
            $rows[] = [
                'path' => $path,
                'mtime' => (int) @filemtime($path),
            ];
        }
        usort($rows, fn ($a, $b) => ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0));
        foreach (array_slice($rows, $keep) as $old) {
            $path = (string) ($old['path'] ?? '');
            if ($path !== '' && is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function runSqliteBackup(): array
    {
        $path = $this->sqliteDatabasePath();
        if ($this->isMemorySqlite($path)) {
            return Result::fail('当前是内存库，没法备份到文件。本地请用 database/database.sqlite');
        }
        if ($path === '' || ! is_file($path)) {
            return Result::fail('找不到 SQLite 文件');
        }
        $dir = $this->getBackupDir();
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return Result::fail('备份目录创建失败');
        }
        $filename = $this->uniqueBackupName('sqlite', 'sqlite');
        $dest = $dir.DIRECTORY_SEPARATOR.$filename;
        $ok = $this->vacuumSqliteInto($path, $dest);
        if (! $ok) {
            if (is_file($dest)) {
                @unlink($dest);
            }
            if (! @copy($path, $dest) || ! is_file($dest) || (int) filesize($dest) < 1) {
                return Result::fail('复制 SQLite 文件失败');
            }
        }
        $size = (int) filesize($dest);
        if ($size < 1) {
            @unlink($dest);
            return Result::fail('备份文件是空的');
        }

        return Result::success([
            'name' => $filename,
            'size' => $size,
        ]);
    }

    private function restoreSqliteBackup(string $backupPath, string $kind): array
    {
        if ($kind !== 'sqlite') {
            return Result::fail('这份是 SQL 文本，当前是 SQLite 文件库，请选 .sqlite 备份');
        }
        $db = $this->sqliteDatabasePath();
        if ($this->isMemorySqlite($db)) {
            return Result::fail('当前是内存库，没法还原到文件');
        }
        if ($db === '') {
            return Result::fail('数据库配置不完整');
        }
        $realBackup = realpath($backupPath) ?: $backupPath;
        $realDb = is_file($db) ? (realpath($db) ?: $db) : $db;
        if ($realBackup !== '' && $realDb !== '' && $realBackup === $realDb) {
            return Result::fail('不能把当前库还原到自己');
        }
        $lockFile = storage_path('app'.DIRECTORY_SEPARATOR.'db_restore.lock');
        if (is_file($lockFile)) {
            return Result::fail('恢复任务正在执行中，请稍后再试');
        }
        if (@file_put_contents($lockFile, (string) time()) === false) {
            return Result::fail('恢复锁创建失败');
        }
        $live = $this->sqlitePdoIsThisFile($db);
        try {
            if ($live) {
                DB::disconnect();
                DB::purge();
            }
            $dir = dirname($db);
            if ($dir !== '' && $dir !== '.' && ! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                return Result::fail('库目录不存在且创建失败');
            }
            $tmp = $db.'.restore-'.str_replace('.', '', uniqid('', true));
            if (! @copy($backupPath, $tmp)) {
                return Result::fail('还原失败，写临时文件失败');
            }
            if (is_file($db)) {
                $old = $db.'.old-'.str_replace('.', '', uniqid('', true));
                if (! @rename($db, $old) && ! @unlink($db)) {
                    @unlink($tmp);
                    return Result::fail('还原失败，库文件可能正被占用');
                }
                @unlink($old);
            }
            if (! @rename($tmp, $db)) {
                if (! @copy($tmp, $db)) {
                    @unlink($tmp);
                    return Result::fail('还原失败，盖不回去');
                }
                @unlink($tmp);
            }
        } finally {
            @unlink($lockFile);
            if ($live) {
                try {
                    DB::reconnect();
                } catch (\Throwable) {
                }
            }
        }

        return Result::success([], '已盖回这份备份。刷新后台确认数据对不对');
    }

    private function uniqueBackupName(string $prefix, string $ext): string
    {
        $prefix = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $prefix) ?: 'db';
        $ext = $ext === 'sqlite' ? 'sqlite' : 'sql';
        $base = $prefix.'_'.date('Ymd_His');
        $name = $base.'.'.$ext;
        $dir = $this->getBackupDir();
        $i = 2;
        while (is_file($dir.DIRECTORY_SEPARATOR.$name)) {
            $name = $base.'_'.$i.'.'.$ext;
            $i++;
        }

        return $name;
    }

    private function vacuumSqliteInto(string $src, string $dest): bool
    {
        if (is_file($dest)) {
            return false;
        }
        try {
            $pdo = new \PDO('sqlite:'.$src, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $quoted = $pdo->quote(str_replace('\\', '/', $dest));
            if (! is_string($quoted) || $quoted === '') {
                return false;
            }
            $pdo->exec('VACUUM INTO '.$quoted);
            $pdo = null;

            return is_file($dest) && (int) filesize($dest) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function connectionDriver(): string
    {
        $connection = (string) config('database.default', 'mysql');
        $cfg = (array) config('database.connections.'.$connection, []);

        return (string) ($cfg['driver'] ?? '');
    }

    private function sqliteDatabasePath(): string
    {
        $connection = (string) config('database.default', 'sqlite');
        $cfg = (array) config('database.connections.'.$connection, []);
        $db = (string) ($cfg['database'] ?? '');
        if ($db === '' || $this->isMemorySqlite($db)) {
            return $db;
        }
        if (is_file($db)) {
            return $db;
        }
        $abs = base_path($db);
        if (is_file($abs)) {
            return $abs;
        }
        $inDatabase = database_path(basename($db));
        if (is_file($inDatabase)) {
            return $inDatabase;
        }

        return $db;
    }

    private function sqlitePdoIsThisFile(string $db): bool
    {
        $db = trim($db);
        if ($db === '' || $this->isMemorySqlite($db)) {
            return false;
        }
        try {
            $row = DB::connection()->getPdo()->query('PRAGMA database_list')->fetch(\PDO::FETCH_ASSOC);
            $file = (string) ($row['file'] ?? '');
            if ($file === '' || $this->isMemorySqlite($file)) {
                return false;
            }
            $realFile = realpath($file) ?: $file;
            $realDb = realpath($db) ?: $db;

            return $realFile !== '' && $realFile === $realDb;
        } catch (\Throwable) {
            return false;
        }
    }

    private function isMemorySqlite(string $path): bool
    {
        $path = strtolower(trim($path));

        return $path === ':memory:' || str_starts_with($path, ':memory:');
    }

    private function canBackup(): bool
    {
        return $this->cannotBackupReason() === '';
    }

    private function cannotBackupReason(): string
    {
        $driver = $this->connectionDriver();
        if ($driver === 'sqlite') {
            $path = $this->sqliteDatabasePath();
            if ($this->isMemorySqlite($path)) {
                return '当前是内存库，没法备份到文件。本地请用 database/database.sqlite';
            }
            if ($path === '' || ! is_file($path)) {
                return '找不到 SQLite 文件';
            }

            return '';
        }
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return '';
        }

        return '只支持 SQLite 文件库和 MySQL/MariaDB';
    }

    private function cannotRestoreReason(): string
    {
        $driver = $this->connectionDriver();
        if ($driver === 'sqlite') {
            $path = $this->sqliteDatabasePath();
            if ($this->isMemorySqlite($path)) {
                return '当前是内存库，没法盖回去。本地请用 database/database.sqlite';
            }
            if ($path === '' || ! is_file($path)) {
                return '找不到 SQLite 文件';
            }

            return '';
        }
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return '';
        }

        return '现在这种库不能在这里恢复';
    }

    private function fileCanRestore(string $kind): bool
    {
        if ($this->cannotRestoreReason() !== '') {
            return false;
        }
        $driver = $this->connectionDriver();
        if ($driver === 'sqlite') {
            return $kind === 'sqlite';
        }
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return $kind === 'sql';
        }

        return false;
    }

    private function fileRestoreHint(string $kind): string
    {
        $blocked = $this->cannotRestoreReason();
        if ($blocked !== '') {
            return $blocked;
        }
        $driver = $this->connectionDriver();
        if ($driver === 'sqlite' && $kind !== 'sqlite') {
            return '这份是 SQL 文本，当前是 SQLite 文件库，不能直接还原';
        }
        if (($driver === 'mysql' || $driver === 'mariadb') && $kind !== 'sql') {
            return '这份是 SQLite 文件，当前库是 MySQL，不能直接还原';
        }

        return '这份备份和当前库不是同一种，不能直接还原';
    }

    /**
     * @param  array{code?:int,msg?:string,data?:mixed}  $res
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    private function finishRestore(array $res, string $file, string $snapshotName): array
    {
        if ((int) ($res['code'] ?? 1) !== 0) {
            return $res;
        }
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $data['file'] = $file;
        $data['snapshot'] = $snapshotName;

        return Result::success($data, $this->restoreDoneMsg($file, $snapshotName));
    }

    private function restoreDoneMsg(string $file, string $snapshotName): string
    {
        $msg = $snapshotName !== ''
            ? '已先另存当前库为 '.$snapshotName.'，再盖回 '.$file.'。'
            : '已盖回 '.$file.'。';

        return $msg.'后台账号以备份里的为准，可能要重新登录。';
    }

    private function fileKind(string $file): string
    {
        $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));

        return $ext === 'sqlite' ? 'sqlite' : 'sql';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findBackupScheduleRow(): ?array
    {
        try {
            if (! Schema::hasTable('sys_schedule')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }
        $row = SysScheduleModel::query()
            ->where('command', self::COMMAND)
            ->orderByDesc('id')
            ->first();

        return $row ? $row->toArray() : null;
    }

    private function sizeText(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1073741824, 1).' GB';
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

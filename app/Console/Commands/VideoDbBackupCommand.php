<?php

namespace App\Console\Commands;

use App\Services\Admin\System\SysDatabaseBackupService;
use Illuminate\Console\Command;

class VideoDbBackupCommand extends Command
{
    protected $signature = 'video:db-backup {--keep=7 : 只留最近几份，0 表示不删旧的}';

    protected $description = '备份数据库到 storage/app/db_backup';

    public function handle(SysDatabaseBackupService $backup): int
    {
        $keep = max(0, (int) $this->option('keep'));
        $result = $backup->runBackup($keep);
        $this->line((string) ($result['msg'] ?? ''));
        if ((int) ($result['code'] ?? 1) !== 0) {
            return self::FAILURE;
        }

        $name = (string) ($result['data']['name'] ?? '');
        if ($name !== '') {
            $this->line($name);
        }

        return self::SUCCESS;
    }
}

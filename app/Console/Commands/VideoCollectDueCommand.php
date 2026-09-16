<?php

namespace App\Console\Commands;

use App\Services\Video\SiteOpsService;
use Illuminate\Console\Command;

class VideoCollectDueCommand extends Command
{
    protected $signature = 'video:collect-due';

    protected $description = '按定时采集任务的 Cron 执行到期采集';

    public function handle(SiteOpsService $ops): int
    {
        $n = $ops->runDueCollectTasks();
        $this->info('已执行 '.$n.' 个任务');

        return self::SUCCESS;
    }
}

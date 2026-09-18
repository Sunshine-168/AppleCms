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
        $cj = 0;
        try {
            if (class_exists(\Plugins\CjRule\Services\CjRuleService::class)
                && app(\App\Support\Plugins\PluginManager::class)->isEnabled('cj_rule')) {
                $cj = (int) (app(\Plugins\CjRule\Services\CjRuleService::class)->runDue()['data']['ran'] ?? 0);
            }
        } catch (\Throwable) {
        }
        $this->info('已执行 '.$n.' 个任务'.($cj > 0 ? '，网站采集 '.$cj.' 个' : ''));

        return self::SUCCESS;
    }
}

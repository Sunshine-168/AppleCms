<?php

namespace App\Console\Commands;

use App\Services\Stats\StatService;
use Illuminate\Console\Command;

class StatPruneCommand extends Command
{
    protected $signature = 'stats:prune {--days=90 : 保留天数}';

    protected $description = '清理过期访问统计日志';

    public function handle(StatService $stats): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = $stats->prune($days);
        $this->info("已删除 {$deleted} 条早于 {$days} 天的访问记录");

        return self::SUCCESS;
    }
}

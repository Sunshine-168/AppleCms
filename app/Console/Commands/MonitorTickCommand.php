<?php

namespace App\Console\Commands;

use App\Services\Monitor\MonitorTick;
use Illuminate\Console\Command;

class MonitorTickCommand extends Command
{
    protected $signature = 'monitor:tick';

    protected $description = '采集运行指标并评估告警';

    public function handle(MonitorTick $tick): int
    {
        $res = $tick->run();
        $this->info((string) ($res['msg'] ?? 'ok'));

        return self::SUCCESS;
    }
}

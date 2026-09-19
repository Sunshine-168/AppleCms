<?php

namespace Plugins\Scout\Console;

use Illuminate\Console\Command;
use Plugins\Scout\Services\ScoutSearchService;

class ScoutSyncCommand extends Command
{
    protected $signature = 'scout:site-sync';

    protected $description = '重建站点影片/资讯 Scout 索引';

    /**
     * 执行重建。
     */
    public function handle(ScoutSearchService $scout): int
    {
        $res = $scout->syncAll();
        $this->info($res['msg'].' videos='.$res['videos'].' arts='.$res['arts']);

        return $res['ok'] ? self::SUCCESS : self::FAILURE;
    }
}

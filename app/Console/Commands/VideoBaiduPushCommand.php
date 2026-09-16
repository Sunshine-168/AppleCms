<?php

namespace App\Console\Commands;

use App\Services\Video\SiteOpsService;
use Illuminate\Console\Command;

class VideoBaiduPushCommand extends Command
{
    protected $signature = 'video:baidu-push {--limit=50}';

    protected $description = '向百度站长平台推送影片 URL';

    public function handle(SiteOpsService $ops): int
    {
        $result = $ops->baiduPush((int) $this->option('limit'));
        $this->line((string) ($result['msg'] ?? ''));

        return (($result['code'] ?? 1) === 0) ? self::SUCCESS : self::FAILURE;
    }
}

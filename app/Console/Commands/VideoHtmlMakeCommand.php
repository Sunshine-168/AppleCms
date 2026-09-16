<?php

namespace App\Console\Commands;

use App\Services\Video\SiteOpsService;
use Illuminate\Console\Command;

class VideoHtmlMakeCommand extends Command
{
    protected $signature = 'video:html-make';

    protected $description = '清理模板与全页 HTML 缓存';

    public function handle(SiteOpsService $ops): int
    {
        $result = $ops->makeHtml();
        $this->line((string) ($result['msg'] ?? ''));

        return self::SUCCESS;
    }
}

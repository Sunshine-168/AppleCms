<?php

namespace App\Console\Commands;

use App\Services\Video\SiteOpsService;
use Illuminate\Console\Command;

class VideoHtmlMakeCommand extends Command
{
    protected $signature = 'video:html-make {scope=all}';

    protected $description = '生成 public/html 静态页';

    public function handle(SiteOpsService $ops): int
    {
        $result = $ops->makeHtml((string) $this->argument('scope'));
        $this->line((string) ($result['msg'] ?? ''));

        return self::SUCCESS;
    }
}

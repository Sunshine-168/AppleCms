<?php

namespace App\Console\Commands;

use App\Models\Video\VideoModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class VideoPublishDueCommand extends Command
{
    protected $signature = 'video:publish-due';

    protected $description = '将到期的定时影片改为上架';

    public function handle(): int
    {
        if (! Schema::hasTable('videos') || ! Schema::hasColumn('videos', 'publish_at')) {
            $this->info('已发布 0 部');

            return self::SUCCESS;
        }
        $n = VideoModel::query()
            ->where('status', 4)
            ->where('publish_at', '>', 0)
            ->where('publish_at', '<=', time())
            ->update(['status' => 1, 'updated_at' => time()]);
        $this->info('已发布 '.$n.' 部');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Video\VideoOption;
use App\Services\Video\VideoSettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class VideoAdminIpClearCommand extends Command
{
    protected $signature = 'video:admin-ip-clear';

    protected $description = '清空后台 IP 白名单（把自己锁在外面时用）';

    public function handle(): int
    {
        if (! Schema::hasTable('video_options')) {
            $this->error('还没有 video_options 表');

            return self::FAILURE;
        }
        VideoOption::query()->updateOrCreate(
            ['k' => 'admin_ip_allow'],
            ['v' => '', 'updated_at' => time()]
        );
        Cache::forget(VideoSettingService::CACHE_KEY);
        $this->info('后台 IP 白名单已清空，现在不限制来源 IP。');

        return self::SUCCESS;
    }
}

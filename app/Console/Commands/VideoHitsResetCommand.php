<?php

namespace App\Console\Commands;

use App\Models\Video\VideoStatModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class VideoHitsResetCommand extends Command
{
    protected $signature = 'video:hits-reset {--week} {--month}';

    protected $description = '重置日/周/月人气';

    public function handle(): int
    {
        if (! Schema::hasTable('video_stats')) {
            return self::SUCCESS;
        }
        $payload = ['hits_day' => 0, 'updated_at' => time()];
        if ($this->option('week') || date('N') === '1') {
            $payload['hits_week'] = 0;
        }
        if ($this->option('month') || date('j') === '1') {
            $payload['hits_month'] = 0;
        }
        VideoStatModel::query()->update($payload);
        $this->info('人气计数已重置');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class VideoInitCommand extends Command
{
    protected $signature = 'video:init';

    protected $description = '迁移并写入默认分类/播放器';

    public function handle(): int
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        if (Schema::hasTable('video_types') && ! VideoTypeModel::query()->exists()) {
            $now = time();
            foreach ([
                ['电影', 'movie', 100],
                ['电视剧', 'tv', 90],
                ['综艺', 'show', 80],
                ['动漫', 'anime', 70],
            ] as [$name, $slug, $sort]) {
                VideoTypeModel::query()->create([
                    'parent_id' => 0,
                    'name' => $name,
                    'slug' => $slug,
                    'sort' => $sort,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $this->info('已写入默认分类');
        }

        if (Schema::hasTable('video_players') && VideoPlayerModel::query()->count() === 0) {
            $rows = VideoPlayerModel::presets();
            if (! Schema::hasColumn('video_players', 'engine')) {
                $rows = array_map(function ($row) {
                    unset($row['engine']);

                    return $row;
                }, $rows);
            }
            VideoPlayerModel::query()->insert($rows);
            $this->info('已写入默认播放器');
        }

        return self::SUCCESS;
    }
}

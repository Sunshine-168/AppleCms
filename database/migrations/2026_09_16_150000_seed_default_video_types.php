<?php

use App\Models\Video\VideoTypeModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_types')) {
            return;
        }
        if (VideoTypeModel::query()->exists()) {
            return;
        }
        $now = time();
        $rows = [
            ['name' => '电影', 'slug' => 'movie', 'sort' => 100],
            ['name' => '电视剧', 'slug' => 'tv', 'sort' => 90],
            ['name' => '综艺', 'slug' => 'show', 'sort' => 80],
            ['name' => '动漫', 'slug' => 'anime', 'sort' => 70],
        ];
        foreach ($rows as $row) {
            VideoTypeModel::query()->create([
                'parent_id' => 0,
                'name' => $row['name'],
                'slug' => $row['slug'],
                'sort' => $row['sort'],
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
    }
};

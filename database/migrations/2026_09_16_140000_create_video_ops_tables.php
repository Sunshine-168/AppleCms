<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_options')) {
            Schema::create('video_options', function (Blueprint $table) {
                $table->increments('id');
                $table->string('k', 80)->unique();
                $table->text('v')->nullable();
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_cards')) {
            Schema::create('video_cards', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 40)->unique();
                $table->unsignedInteger('points')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('used_by')->default(0);
                $table->unsignedInteger('used_at')->default(0);
                $table->unsignedInteger('created_at')->default(0);
            });
        }

        $now = time();
        if (Schema::hasTable('video_players') && DB::table('video_players')->count() === 0) {
            DB::table('video_players')->insert([
                ['code' => 'dplayer', 'name' => '直链播放', 'parse' => '', 'sort' => 10, 'status' => 1],
                ['code' => 'parse', 'name' => '解析接口', 'parse' => '', 'sort' => 0, 'status' => 1],
            ]);
        }

        if (Schema::hasTable('sys_perm')) {
            $parentId = (int) (DB::table('sys_perm')->where('code', 'vod_module')->value('id') ?: 0);
            if ($parentId > 0) {
                $items = [
                    ['站点设置', 'vod_setting', '/admin/video/settings', 5],
                    ['积分卡密', 'vod_card', '/admin/video/cards', 105],
                ];
                foreach ($items as [$name, $code, $api, $sort]) {
                    if (DB::table('sys_perm')->where('code', $code)->exists()) {
                        continue;
                    }
                    DB::table('sys_perm')->insert([
                        'name' => $name,
                        'code' => $code,
                        'api' => $api,
                        'method' => 'GET',
                        'pid' => $parentId,
                        'type' => 1,
                        'icon' => '',
                        'sort' => $sort,
                        'create_time' => $now,
                        'update_time' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_cards');
        Schema::dropIfExists('video_options');
    }
};

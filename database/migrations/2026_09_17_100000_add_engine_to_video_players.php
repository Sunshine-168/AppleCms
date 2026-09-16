<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_players')) {
            return;
        }
        if (! Schema::hasColumn('video_players', 'engine')) {
            Schema::table('video_players', function (Blueprint $table) {
                $table->string('engine', 20)->default('artplayer');
            });
        }
        $rows = DB::table('video_players')->get(['id', 'code', 'parse', 'engine']);
        foreach ($rows as $row) {
            $engine = strtolower(trim((string) ($row->engine ?? '')));
            if (in_array($engine, ['artplayer', 'dplayer', 'videojs', 'iframe'], true)) {
                continue;
            }
            $code = strtolower(trim((string) ($row->code ?? '')));
            $parse = trim((string) ($row->parse ?? ''));
            $next = match (true) {
                $parse !== '' && (str_contains($parse, '{url}') || str_starts_with($parse, 'http')) => 'iframe',
                in_array($code, ['dplayer', 'dp'], true) => 'dplayer',
                in_array($code, ['videojs', 'vjs', 'video.js'], true) => 'videojs',
                in_array($code, ['parse', 'jiexi', 'iframe'], true) => 'iframe',
                default => 'artplayer',
            };
            DB::table('video_players')->where('id', $row->id)->update(['engine' => $next]);
        }

        $presets = [
            ['code' => 'artplayer', 'name' => 'ArtPlayer 直链', 'parse' => '', 'sort' => 30, 'status' => 1, 'engine' => 'artplayer'],
            ['code' => 'dplayer', 'name' => 'DPlayer 直链', 'parse' => '', 'sort' => 20, 'status' => 1, 'engine' => 'dplayer'],
            ['code' => 'videojs', 'name' => 'Video.js 直链', 'parse' => '', 'sort' => 15, 'status' => 1, 'engine' => 'videojs'],
            ['code' => 'parse', 'name' => '解析接口', 'parse' => '', 'sort' => 0, 'status' => 1, 'engine' => 'iframe'],
        ];
        foreach ($presets as $row) {
            if (DB::table('video_players')->where('code', $row['code'])->exists()) {
                continue;
            }
            DB::table('video_players')->insert($row);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('video_players') && Schema::hasColumn('video_players', 'engine')) {
            Schema::table('video_players', function (Blueprint $table) {
                $table->dropColumn('engine');
            });
        }
    }
};

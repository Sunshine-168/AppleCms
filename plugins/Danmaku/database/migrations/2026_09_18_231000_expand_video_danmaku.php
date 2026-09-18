<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_danmaku') || Schema::hasColumn('video_danmaku', 'report')) {
            return;
        }
        Schema::table('video_danmaku', function (Blueprint $table) {
            $table->unsignedInteger('report')->default(0);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('video_danmaku') || ! Schema::hasColumn('video_danmaku', 'report')) {
            return;
        }
        Schema::table('video_danmaku', function (Blueprint $table) {
            $table->dropColumn('report');
        });
    }
};

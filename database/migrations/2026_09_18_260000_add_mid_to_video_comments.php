<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_comments') || Schema::hasColumn('video_comments', 'mid')) {
            return;
        }

        Schema::table('video_comments', function (Blueprint $table) {
            $table->unsignedTinyInteger('mid')->default(1)->after('video_id');
            $table->index(['mid', 'video_id', 'status'], 'video_comments_mid_rid_status_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('video_comments') || ! Schema::hasColumn('video_comments', 'mid')) {
            return;
        }

        Schema::table('video_comments', function (Blueprint $table) {
            $table->dropIndex('video_comments_mid_rid_status_index');
            $table->dropColumn('mid');
        });
    }
};

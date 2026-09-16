<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('video_comments') && ! Schema::hasColumn('video_comments', 'comment_report')) {
            Schema::table('video_comments', function (Blueprint $table) {
                $table->unsignedInteger('comment_report')->default(0);
            });
        }
        if (Schema::hasTable('video_roles')) {
            Schema::table('video_roles', function (Blueprint $table) {
                if (! Schema::hasColumn('video_roles', 'video_id')) {
                    $table->unsignedInteger('video_id')->default(0)->index();
                }
                if (! Schema::hasColumn('video_roles', 'actor_id')) {
                    $table->unsignedInteger('actor_id')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('video_comments') && Schema::hasColumn('video_comments', 'comment_report')) {
            Schema::table('video_comments', function (Blueprint $table) {
                $table->dropColumn('comment_report');
            });
        }
        if (Schema::hasTable('video_roles')) {
            Schema::table('video_roles', function (Blueprint $table) {
                if (Schema::hasColumn('video_roles', 'video_id')) {
                    $table->dropColumn('video_id');
                }
                if (Schema::hasColumn('video_roles', 'actor_id')) {
                    $table->dropColumn('actor_id');
                }
            });
        }
    }
};

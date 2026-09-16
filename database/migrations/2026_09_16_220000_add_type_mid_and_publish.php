<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('video_types') && ! Schema::hasColumn('video_types', 'mid')) {
            Schema::table('video_types', function (Blueprint $table) {
                $table->unsignedTinyInteger('mid')->default(1)->comment('1=vod 2=art 3=website');
            });
        }
        if (Schema::hasTable('video_websites') && ! Schema::hasColumn('video_websites', 'type_id')) {
            Schema::table('video_websites', function (Blueprint $table) {
                $table->unsignedInteger('type_id')->default(0);
            });
        }
        if (Schema::hasTable('videos')) {
            Schema::table('videos', function (Blueprint $table) {
                if (! Schema::hasColumn('videos', 'weekday')) {
                    $table->string('weekday', 20)->default('');
                }
                if (! Schema::hasColumn('videos', 'publish_at')) {
                    $table->unsignedInteger('publish_at')->default(0);
                }
            });
        }
        if (Schema::hasTable('video_comments') && ! Schema::hasColumn('video_comments', 'comment_up')) {
            Schema::table('video_comments', function (Blueprint $table) {
                $table->unsignedInteger('comment_up')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('video_types') && Schema::hasColumn('video_types', 'mid')) {
            Schema::table('video_types', function (Blueprint $table) {
                $table->dropColumn('mid');
            });
        }
        if (Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'type_id')) {
            Schema::table('video_websites', function (Blueprint $table) {
                $table->dropColumn('type_id');
            });
        }
        if (Schema::hasTable('videos')) {
            Schema::table('videos', function (Blueprint $table) {
                if (Schema::hasColumn('videos', 'weekday')) {
                    $table->dropColumn('weekday');
                }
                if (Schema::hasColumn('videos', 'publish_at')) {
                    $table->dropColumn('publish_at');
                }
            });
        }
        if (Schema::hasTable('video_comments') && Schema::hasColumn('video_comments', 'comment_up')) {
            Schema::table('video_comments', function (Blueprint $table) {
                $table->dropColumn('comment_up');
            });
        }
    }
};

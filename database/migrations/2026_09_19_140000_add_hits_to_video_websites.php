<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_websites')) {
            return;
        }
        if (! Schema::hasColumn('video_websites', 'hits')) {
            Schema::table('video_websites', function (Blueprint $table) {
                $table->unsignedInteger('hits')->default(0)->after('sort');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'hits')) {
            Schema::table('video_websites', function (Blueprint $table) {
                $table->dropColumn('hits');
            });
        }
    }
};

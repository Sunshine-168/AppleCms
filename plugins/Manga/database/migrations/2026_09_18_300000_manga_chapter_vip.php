<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plugin_manga_chapters') && ! Schema::hasColumn('plugin_manga_chapters', 'vip')) {
            Schema::table('plugin_manga_chapters', function (Blueprint $table) {
                $table->unsignedTinyInteger('vip')->default(0)->after('sort');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plugin_manga_chapters') && Schema::hasColumn('plugin_manga_chapters', 'vip')) {
            Schema::table('plugin_manga_chapters', function (Blueprint $table) {
                $table->dropColumn('vip');
            });
        }
    }
};

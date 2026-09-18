<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_mangas')) {
            return;
        }
        Schema::table('plugin_mangas', function (Blueprint $table) {
            if (! Schema::hasColumn('plugin_mangas', 'collect_source_id')) {
                $table->unsignedInteger('collect_source_id')->default(0);
            }
            if (! Schema::hasColumn('plugin_mangas', 'collect_id')) {
                $table->string('collect_id', 80)->default('');
            }
        });
        try {
            Schema::table('plugin_mangas', function (Blueprint $table) {
                $table->index(['collect_source_id', 'collect_id'], 'plugin_mangas_collect_idx');
            });
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plugin_mangas')) {
            return;
        }
        Schema::table('plugin_mangas', function (Blueprint $table) {
            foreach (['collect_source_id', 'collect_id'] as $col) {
                if (Schema::hasColumn('plugin_mangas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

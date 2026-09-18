<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_types')) {
            return;
        }
        Schema::table('video_types', function (Blueprint $table) {
            if (! Schema::hasColumn('video_types', 'kind')) {
                $table->string('kind', 16)->default('list');
            }
            if (! Schema::hasColumn('video_types', 'jump_url')) {
                $table->string('jump_url', 255)->default('');
            }
            if (! Schema::hasColumn('video_types', 'page_size')) {
                $table->unsignedInteger('page_size')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('video_types')) {
            return;
        }
        Schema::table('video_types', function (Blueprint $table) {
            foreach (['kind', 'jump_url', 'page_size'] as $col) {
                if (Schema::hasColumn('video_types', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

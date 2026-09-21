<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('videos')) {
            return;
        }
        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'seo_title')) {
                $table->string('seo_title', 255)->default('');
            }
            if (! Schema::hasColumn('videos', 'seo_keywords')) {
                $table->string('seo_keywords', 255)->default('');
            }
            if (! Schema::hasColumn('videos', 'seo_description')) {
                $table->string('seo_description', 500)->default('');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('videos')) {
            return;
        }
        Schema::table('videos', function (Blueprint $table) {
            foreach (['seo_title', 'seo_keywords', 'seo_description'] as $col) {
                if (Schema::hasColumn('videos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

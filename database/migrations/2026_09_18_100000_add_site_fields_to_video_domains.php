<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_domains')) {
            return;
        }
        Schema::table('video_domains', function (Blueprint $table) {
            if (! Schema::hasColumn('video_domains', 'site_name')) {
                $table->string('site_name', 120)->default('');
            }
            if (! Schema::hasColumn('video_domains', 'site_keyword')) {
                $table->string('site_keyword', 255)->default('');
            }
            if (! Schema::hasColumn('video_domains', 'site_description')) {
                $table->string('site_description', 500)->default('');
            }
        });
        try {
            Schema::table('video_domains', function (Blueprint $table) {
                $table->unique('host');
            });
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('video_domains')) {
            return;
        }
        Schema::table('video_domains', function (Blueprint $table) {
            foreach (['site_name', 'site_keyword', 'site_description'] as $col) {
                if (Schema::hasColumn('video_domains', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

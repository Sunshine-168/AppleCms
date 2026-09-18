<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return;
        }
        Schema::table('plugin_manga_types', function (Blueprint $table) {
            if (! Schema::hasColumn('plugin_manga_types', 'slug')) {
                $table->string('slug', 80)->default('')->after('name');
            }
            if (! Schema::hasColumn('plugin_manga_types', 'pic')) {
                $table->string('pic', 255)->default('')->after('slug');
            }
            if (! Schema::hasColumn('plugin_manga_types', 'page_size')) {
                $table->unsignedSmallInteger('page_size')->default(0)->after('sort');
            }
            if (! Schema::hasColumn('plugin_manga_types', 'seo_title')) {
                $table->string('seo_title', 120)->default('')->after('status');
            }
            if (! Schema::hasColumn('plugin_manga_types', 'seo_keywords')) {
                $table->string('seo_keywords', 255)->default('')->after('seo_title');
            }
            if (! Schema::hasColumn('plugin_manga_types', 'seo_description')) {
                $table->string('seo_description', 500)->default('')->after('seo_keywords');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return;
        }
        Schema::table('plugin_manga_types', function (Blueprint $table) {
            foreach (['slug', 'pic', 'page_size', 'seo_title', 'seo_keywords', 'seo_description'] as $col) {
                if (Schema::hasColumn('plugin_manga_types', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

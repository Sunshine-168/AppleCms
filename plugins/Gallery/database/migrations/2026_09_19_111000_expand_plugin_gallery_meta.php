<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plugin_galleries')) {
            Schema::table('plugin_galleries', function (Blueprint $t) {
                if (! Schema::hasColumn('plugin_galleries', 'author')) {
                    $t->string('author', 120)->default('')->after('cover');
                }
                if (! Schema::hasColumn('plugin_galleries', 'tags')) {
                    $t->string('tags', 255)->default('')->after('author');
                }
            });
        }
        if (! Schema::hasTable('plugin_gallery_favors')) {
            Schema::create('plugin_gallery_favors', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('member_id');
                $t->unsignedInteger('gallery_id');
                $t->unsignedInteger('created_at')->default(0);
                $t->unique(['member_id', 'gallery_id']);
                $t->index('gallery_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_gallery_favors');
        if (Schema::hasTable('plugin_galleries')) {
            Schema::table('plugin_galleries', function (Blueprint $t) {
                if (Schema::hasColumn('plugin_galleries', 'tags')) {
                    $t->dropColumn('tags');
                }
                if (Schema::hasColumn('plugin_galleries', 'author')) {
                    $t->dropColumn('author');
                }
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_art_tags')) {
            Schema::create('video_art_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 60)->unique();
                $table->string('slug', 80)->unique();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('video_art_tag_rel')) {
            Schema::create('video_art_tag_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('art_id');
                $table->unsignedInteger('tag_id');
                $table->unique(['art_id', 'tag_id']);
                $table->index('tag_id');
            });
        }
        if (Schema::hasTable('video_arts') && ! Schema::hasColumn('video_arts', 'deleted_at')) {
            Schema::table('video_arts', function (Blueprint $table) {
                $table->unsignedInteger('deleted_at')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_art_tag_rel');
        Schema::dropIfExists('video_art_tags');
        if (Schema::hasTable('video_arts') && Schema::hasColumn('video_arts', 'deleted_at')) {
            Schema::table('video_arts', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};

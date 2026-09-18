<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_manga_tags')) {
            Schema::create('plugin_manga_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 60)->unique();
                $table->string('slug', 80)->unique();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('plugin_manga_tag_rel')) {
            Schema::create('plugin_manga_tag_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('manga_id');
                $table->unsignedInteger('tag_id');
                $table->unique(['manga_id', 'tag_id']);
                $table->index('tag_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_manga_tag_rel');
        Schema::dropIfExists('plugin_manga_tags');
    }
};

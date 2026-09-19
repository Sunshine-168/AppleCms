<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_manga_authors')) {
            Schema::create('plugin_manga_authors', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80)->unique();
                $table->string('slug', 80)->unique();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('plugin_manga_author_rel')) {
            Schema::create('plugin_manga_author_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('manga_id');
                $table->unsignedInteger('author_id');
                $table->unique(['manga_id', 'author_id']);
                $table->index('author_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_manga_author_rel');
        Schema::dropIfExists('plugin_manga_authors');
    }
};

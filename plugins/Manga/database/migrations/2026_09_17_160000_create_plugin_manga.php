<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_mangas')) {
            Schema::create('plugin_mangas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 200);
                $table->string('cover', 500)->default('');
                $table->string('author', 80)->default('');
                $table->string('remarks', 80)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('hits')->default(0);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['status', 'sort']);
            });
        }
        if (! Schema::hasTable('plugin_manga_chapters')) {
            Schema::create('plugin_manga_chapters', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('manga_id');
                $table->string('name', 120)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->mediumText('pics')->nullable();
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['manga_id', 'sort']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_manga_chapters');
        Schema::dropIfExists('plugin_mangas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_novel_tags')) {
            Schema::create('plugin_novel_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 60)->unique();
                $table->string('slug', 80)->unique();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('plugin_novel_tag_rel')) {
            Schema::create('plugin_novel_tag_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('novel_id');
                $table->unsignedInteger('tag_id');
                $table->unique(['novel_id', 'tag_id']);
                $table->index('tag_id');
            });
        }
        if (! Schema::hasTable('plugin_novel_authors')) {
            Schema::create('plugin_novel_authors', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80)->unique();
                $table->string('slug', 80)->unique();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('plugin_novel_author_rel')) {
            Schema::create('plugin_novel_author_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('novel_id');
                $table->unsignedInteger('author_id');
                $table->unique(['novel_id', 'author_id']);
                $table->index('author_id');
            });
        }
        if (! Schema::hasTable('plugin_novel_comments')) {
            Schema::create('plugin_novel_comments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('novel_id');
                $table->unsignedInteger('member_id')->default(0);
                $table->string('author_name', 80)->default('');
                $table->string('content', 2000);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['novel_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_novel_comments');
        Schema::dropIfExists('plugin_novel_author_rel');
        Schema::dropIfExists('plugin_novel_authors');
        Schema::dropIfExists('plugin_novel_tag_rel');
        Schema::dropIfExists('plugin_novel_tags');
    }
};

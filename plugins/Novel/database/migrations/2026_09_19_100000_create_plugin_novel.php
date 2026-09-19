<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_novels')) {
            Schema::create('plugin_novels', function (Blueprint $t) {
                $t->increments('id'); $t->string('title', 200); $t->string('cover', 500)->default('');
                $t->string('author', 120)->default(''); $t->string('remarks', 255)->default(''); $t->text('content')->nullable();
                $t->unsignedInteger('type_id')->default(0); $t->unsignedTinyInteger('serialize')->default(0);
                $t->unsignedTinyInteger('yid')->default(0); $t->unsignedTinyInteger('recommend')->default(0);
                $t->unsignedInteger('hits')->default(0); $t->unsignedInteger('sort')->default(0);
                $t->unsignedTinyInteger('status')->default(1); $t->unsignedInteger('created_at')->default(0);
                $t->unsignedInteger('updated_at')->default(0); $t->index(['status', 'yid', 'sort']);
            });
        }
        if (! Schema::hasTable('plugin_novel_chapters')) {
            Schema::create('plugin_novel_chapters', function (Blueprint $t) {
                $t->increments('id'); $t->unsignedInteger('novel_id'); $t->string('name', 160);
                $t->mediumText('content')->nullable(); $t->unsignedInteger('sort')->default(0);
                $t->unsignedTinyInteger('vip')->default(0); $t->unsignedInteger('created_at')->default(0);
                $t->index(['novel_id', 'sort']);
            });
        }
        if (! Schema::hasTable('plugin_novel_types')) {
            Schema::create('plugin_novel_types', function (Blueprint $t) {
                $t->increments('id'); $t->string('name', 80); $t->string('slug', 80)->default('');
                $t->unsignedInteger('parent_id')->default(0); $t->unsignedInteger('sort')->default(0);
                $t->unsignedTinyInteger('status')->default(1); $t->unsignedInteger('created_at')->default(0);
            });
        }
        if (! Schema::hasTable('plugin_novel_favors')) {
            Schema::create('plugin_novel_favors', function (Blueprint $t) {
                $t->increments('id'); $t->unsignedInteger('member_id'); $t->unsignedInteger('novel_id');
                $t->unsignedInteger('created_at')->default(0); $t->unique(['member_id', 'novel_id']);
            });
        }
        if (! Schema::hasTable('plugin_novel_histories')) {
            Schema::create('plugin_novel_histories', function (Blueprint $t) {
                $t->increments('id'); $t->unsignedInteger('member_id'); $t->unsignedInteger('novel_id');
                $t->unsignedInteger('chapter_id')->default(0); $t->unsignedInteger('progress')->default(0);
                $t->unsignedInteger('created_at')->default(0); $t->unsignedInteger('updated_at')->default(0);
                $t->unique(['member_id', 'novel_id']);
            });
        }
    }

    public function down(): void
    {
        foreach (['plugin_novel_histories','plugin_novel_favors','plugin_novel_chapters','plugin_novel_types','plugin_novels'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

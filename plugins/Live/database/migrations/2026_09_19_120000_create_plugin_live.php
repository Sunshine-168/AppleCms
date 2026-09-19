<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_live_categories')) {
            Schema::create('plugin_live_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('slug', 80)->default('');
                $table->string('pic', 500)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['status', 'sort']);
            });
        }
        if (! Schema::hasTable('plugin_live_channels')) {
            Schema::create('plugin_live_channels', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cate_id')->default(0);
                $table->string('title', 200);
                $table->string('sub', 255)->default('');
                $table->string('slug', 100)->default('');
                $table->string('cover', 500)->default('');
                $table->text('urls');
                $table->string('play_from', 40)->default('hls');
                $table->unsignedInteger('hits')->default(0);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('remarks', 255)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['status', 'cate_id', 'sort']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_live_channels');
        Schema::dropIfExists('plugin_live_categories');
    }
};

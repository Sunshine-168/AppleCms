<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_manga_histories')) {
            Schema::create('plugin_manga_histories', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('manga_id')->default(0);
                $table->unsignedInteger('chapter_id')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->unique(['member_id', 'manga_id'], 'manga_his_member_manga');
                $table->index(['member_id', 'updated_at'], 'manga_his_member_time');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_manga_histories');
    }
};

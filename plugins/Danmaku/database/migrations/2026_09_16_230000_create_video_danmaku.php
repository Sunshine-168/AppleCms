<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('video_danmaku')) {
            return;
        }
        Schema::create('video_danmaku', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('video_id');
            $table->unsignedInteger('episode_id')->default(0);
            $table->unsignedInteger('member_id')->default(0);
            $table->string('text', 120);
            $table->string('color', 16)->default('#ffffff');
            $table->unsignedTinyInteger('mode')->default(0);
            $table->decimal('time', 10, 2)->default(0);
            $table->string('ip', 45)->default('');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('created_at')->default(0);
            $table->index(['video_id', 'episode_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_danmaku');
    }
};

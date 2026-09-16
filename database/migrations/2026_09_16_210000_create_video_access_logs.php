<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('video_access_logs')) {
            return;
        }
        Schema::create('video_access_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip', 64)->default('');
            $table->string('url', 500)->default('');
            $table->string('ua', 500)->default('');
            $table->unsignedTinyInteger('is_bot')->default(0);
            $table->unsignedInteger('created_at')->default(0);
            $table->index('created_at');
            $table->index('is_bot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_access_logs');
    }
};

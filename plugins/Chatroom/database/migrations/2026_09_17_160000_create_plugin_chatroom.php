<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plugin_chat_messages')) {
            return;
        }
        Schema::create('plugin_chat_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('video_id');
            $table->unsignedInteger('member_id')->default(0);
            $table->string('name', 50)->default('');
            $table->string('text', 500);
            $table->string('ip', 45)->default('');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('created_at')->default(0);
            $table->index(['video_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_chat_messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stat_hits', function (Blueprint $table) {
            $table->id();
            $table->string('path', 500);
            $table->string('query', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('visitor_hash', 64)->index();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('locale', 16)->nullable();
            $table->boolean('is_spider')->default(false)->index();
            $table->string('spider_name', 64)->nullable()->index();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stat_hits');
    }
};

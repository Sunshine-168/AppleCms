<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('mac_vod_search', function (Blueprint $table) {
            $table->char('search_key', 32)->primary();
            $table->string('search_word', 128);
            $table->string('search_field', 64);
            $table->unsignedBigInteger('search_hit_count')->default(0);
            $table->unsignedInteger('search_last_hit_time')->default(0);
            $table->unsignedInteger('search_update_time')->default(0);
            $table->unsignedInteger('search_result_count')->default(0);
            $table->mediumText('search_result_ids');
            $table->index('search_field', 'search_field');
            $table->index('search_update_time', 'search_update_time');
            $table->index('search_hit_count', 'search_hit_count');
            $table->index('search_last_hit_time', 'search_last_hit_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_vod_search');
    }
};

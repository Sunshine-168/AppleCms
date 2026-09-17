<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sys_schedule') && ! Schema::hasColumn('sys_schedule', 'last_duration_ms')) {
            Schema::table('sys_schedule', function (Blueprint $table) {
                $table->unsignedInteger('last_duration_ms')->default(0);
            });
        }

        if (! Schema::hasTable('sys_schedule_log')) {
            Schema::create('sys_schedule_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('schedule_id')->default(0);
                $table->unsignedTinyInteger('status')->default(0);
                $table->unsignedInteger('duration_ms')->default(0);
                $table->string('output', 500)->default('');
                $table->string('error', 255)->default('');
                $table->unsignedInteger('create_time')->default(0);
                $table->index(['schedule_id', 'id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_schedule_log');
        if (Schema::hasTable('sys_schedule') && Schema::hasColumn('sys_schedule', 'last_duration_ms')) {
            Schema::table('sys_schedule', function (Blueprint $table) {
                $table->dropColumn('last_duration_ms');
            });
        }
    }
};

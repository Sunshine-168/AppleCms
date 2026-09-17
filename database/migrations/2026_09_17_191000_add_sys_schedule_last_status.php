<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_schedule')) {
            return;
        }
        Schema::table('sys_schedule', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_schedule', 'last_status')) {
                $table->unsignedTinyInteger('last_status')->default(0);
            }
            if (! Schema::hasColumn('sys_schedule', 'last_error')) {
                $table->string('last_error', 255)->default('');
            }
        });
    }

    public function down(): void
    {
    }
};

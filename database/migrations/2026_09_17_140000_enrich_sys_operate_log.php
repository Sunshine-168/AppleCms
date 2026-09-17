<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_operate_log')) {
            return;
        }
        if (! Schema::hasColumn('sys_operate_log', 'action')) {
            Schema::table('sys_operate_log', function (Blueprint $table) {
                $table->string('action', 50)->default('')->after('title');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sys_operate_log')) {
            return;
        }
        if (Schema::hasColumn('sys_operate_log', 'action')) {
            Schema::table('sys_operate_log', function (Blueprint $table) {
                $table->dropColumn('action');
            });
        }
    }
};

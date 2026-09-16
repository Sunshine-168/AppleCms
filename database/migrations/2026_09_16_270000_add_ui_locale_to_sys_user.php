<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sys_user') && ! Schema::hasColumn('sys_user', 'ui_locale')) {
            Schema::table('sys_user', function (Blueprint $table) {
                $table->string('ui_locale', 16)->default('zh_cn');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_user') && Schema::hasColumn('sys_user', 'ui_locale')) {
            Schema::table('sys_user', function (Blueprint $table) {
                $table->dropColumn('ui_locale');
            });
        }
    }
};

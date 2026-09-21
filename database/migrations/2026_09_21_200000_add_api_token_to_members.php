<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members') && ! Schema::hasColumn('members', 'api_token')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('api_token', 80)->default('')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'api_token')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('api_token');
            });
        }
    }
};

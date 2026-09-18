<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_chat_messages') || Schema::hasColumn('plugin_chat_messages', 'report')) {
            return;
        }
        Schema::table('plugin_chat_messages', function (Blueprint $table) {
            $table->unsignedInteger('report')->default(0);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plugin_chat_messages') || ! Schema::hasColumn('plugin_chat_messages', 'report')) {
            return;
        }
        Schema::table('plugin_chat_messages', function (Blueprint $table) {
            $table->dropColumn('report');
        });
    }
};

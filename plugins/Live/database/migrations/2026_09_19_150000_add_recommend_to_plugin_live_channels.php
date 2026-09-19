<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_live_channels')) {
            return;
        }
        if (! Schema::hasColumn('plugin_live_channels', 'recommend')) {
            Schema::table('plugin_live_channels', function (Blueprint $table) {
                $table->unsignedTinyInteger('recommend')->default(0)->after('hits');
                $table->index(['status', 'recommend', 'sort'], 'plugin_live_channels_status_rec_sort');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plugin_live_channels') || ! Schema::hasColumn('plugin_live_channels', 'recommend')) {
            return;
        }
        Schema::table('plugin_live_channels', function (Blueprint $table) {
            $table->dropIndex('plugin_live_channels_status_rec_sort');
            $table->dropColumn('recommend');
        });
    }
};

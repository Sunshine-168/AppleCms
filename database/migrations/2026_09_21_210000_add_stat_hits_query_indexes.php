<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stat_hits')) {
            return;
        }
        Schema::table('stat_hits', function (Blueprint $table) {
            if (! Schema::hasIndex('stat_hits', 'stat_hits_created_spider_index')) {
                $table->index(['created_at', 'is_spider'], 'stat_hits_created_spider_index');
            }
            if (! Schema::hasIndex('stat_hits', 'stat_hits_spider_lookup_index')) {
                $table->index(['is_spider', 'spider_name', 'created_at'], 'stat_hits_spider_lookup_index');
            }
            if (! Schema::hasIndex('stat_hits', 'stat_hits_status_created_index')) {
                $table->index(['status_code', 'created_at'], 'stat_hits_status_created_index');
            }
            if (! Schema::hasIndex('stat_hits', 'stat_hits_path_index')) {
                $table->index('path', 'stat_hits_path_index');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stat_hits')) {
            return;
        }
        Schema::table('stat_hits', function (Blueprint $table) {
            foreach ([
                'stat_hits_created_spider_index',
                'stat_hits_spider_lookup_index',
                'stat_hits_status_created_index',
                'stat_hits_path_index',
            ] as $name) {
                if (Schema::hasIndex('stat_hits', $name)) {
                    $table->dropIndex($name);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('video_cj_rules')) {
            Schema::table('video_cj_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('video_cj_rules', 'type')) {
                    $table->string('type', 16)->default('html');
                }
                if (! Schema::hasColumn('video_cj_rules', 'options')) {
                    $table->mediumText('options')->nullable();
                }
                if (! Schema::hasColumn('video_cj_rules', 'interval_minutes')) {
                    $table->unsignedInteger('interval_minutes')->default(60);
                }
                if (! Schema::hasColumn('video_cj_rules', 'limit_items')) {
                    $table->unsignedInteger('limit_items')->default(10);
                }
                if (! Schema::hasColumn('video_cj_rules', 'publish_immediately')) {
                    $table->unsignedTinyInteger('publish_immediately')->default(0);
                }
                if (! Schema::hasColumn('video_cj_rules', 'last_run_at')) {
                    $table->unsignedInteger('last_run_at')->default(0);
                }
                if (! Schema::hasColumn('video_cj_rules', 'last_status')) {
                    $table->string('last_status', 32)->default('');
                }
                if (! Schema::hasColumn('video_cj_rules', 'last_message')) {
                    $table->string('last_message', 500)->default('');
                }
            });
        }
        if (! Schema::hasTable('video_cj_rule_logs')) {
            Schema::create('video_cj_rule_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id')->default(0);
                $table->string('status', 16)->default('');
                $table->unsignedInteger('fetched')->default(0);
                $table->unsignedInteger('created')->default(0);
                $table->unsignedInteger('skipped')->default(0);
                $table->string('message', 1000)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('rule_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_cj_rule_logs');
        if (Schema::hasTable('video_cj_rules')) {
            Schema::table('video_cj_rules', function (Blueprint $table) {
                foreach (['type', 'options', 'interval_minutes', 'limit_items', 'publish_immediately', 'last_run_at', 'last_status', 'last_message'] as $col) {
                    if (Schema::hasColumn('video_cj_rules', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

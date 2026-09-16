<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('collect_sources')) {
            Schema::table('collect_sources', function (Blueprint $table) {
                if (! Schema::hasColumn('collect_sources', 'last_page')) {
                    $table->unsignedInteger('last_page')->default(0);
                }
                if (! Schema::hasColumn('collect_sources', 'last_created')) {
                    $table->unsignedInteger('last_created')->default(0);
                }
                if (! Schema::hasColumn('collect_sources', 'last_updated')) {
                    $table->unsignedInteger('last_updated')->default(0);
                }
                if (! Schema::hasColumn('collect_sources', 'last_error')) {
                    $table->string('last_error', 255)->default('');
                }
            });
        }

        if (! Schema::hasTable('video_collect_logs')) {
            Schema::create('video_collect_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('collect_source_id')->default(0);
                $table->unsignedInteger('page')->default(0);
                $table->unsignedInteger('created_n')->default(0);
                $table->unsignedInteger('updated_n')->default(0);
                $table->unsignedInteger('skipped_n')->default(0);
                $table->unsignedTinyInteger('ok')->default(1);
                $table->string('msg', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('collect_source_id');
            });
        }

        if (! Schema::hasTable('member_point_logs')) {
            Schema::create('member_point_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->integer('points')->default(0);
                $table->integer('balance')->default(0);
                $table->string('type', 20)->default('sys');
                $table->string('remark', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('member_id');
            });
        }

        if (! Schema::hasTable('video_visit_items')) {
            Schema::create('video_visit_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('day')->default(0);
                $table->unsignedInteger('video_id')->default(0);
                $table->unsignedInteger('type_id')->default(0);
                $table->unsignedInteger('pv')->default(0);
                $table->index(['day', 'video_id']);
                $table->index(['day', 'type_id']);
            });
        }

        if (Schema::hasTable('video_ads')) {
            Schema::table('video_ads', function (Blueprint $table) {
                if (! Schema::hasColumn('video_ads', 'type_id')) {
                    $table->unsignedInteger('type_id')->default(0);
                }
                if (! Schema::hasColumn('video_ads', 'expire_at')) {
                    $table->unsignedInteger('expire_at')->default(0);
                }
            });
        }

        if (Schema::hasTable('video_audit_rules') && ! Schema::hasColumn('video_audit_rules', 'is_regex')) {
            Schema::table('video_audit_rules', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_regex')->default(0);
            });
        }

        if (Schema::hasTable('video_sources') && ! Schema::hasColumn('video_sources', 'status')) {
            Schema::table('video_sources', function (Blueprint $table) {
                $table->unsignedTinyInteger('status')->default(1);
            });
        }

        if (Schema::hasTable('member_groups')) {
            Schema::table('member_groups', function (Blueprint $table) {
                if (! Schema::hasColumn('member_groups', 'trysee')) {
                    $table->unsignedInteger('trysee')->default(0);
                }
                if (! Schema::hasColumn('member_groups', 'day_free')) {
                    $table->unsignedInteger('day_free')->default(0);
                }
                if (! Schema::hasColumn('member_groups', 'need_login')) {
                    $table->unsignedTinyInteger('need_login')->default(0);
                }
            });
        }

        if (Schema::hasTable('member_orders') && ! Schema::hasColumn('member_orders', 'channel')) {
            Schema::table('member_orders', function (Blueprint $table) {
                $table->string('channel', 20)->default('manual');
                $table->string('trade_no', 64)->default('');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_collect_logs');
        Schema::dropIfExists('member_point_logs');
        Schema::dropIfExists('video_visit_items');
    }
};

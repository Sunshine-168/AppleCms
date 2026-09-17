<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (! Schema::hasColumn('members', 'phone')) {
                    $table->string('phone', 20)->default('');
                }
                if (! Schema::hasColumn('members', 'qq_openid')) {
                    $table->string('qq_openid', 64)->default('');
                }
                if (! Schema::hasColumn('members', 'wechat_openid')) {
                    $table->string('wechat_openid', 64)->default('');
                }
            });
        }

        if (Schema::hasTable('video_sources')) {
            Schema::table('video_sources', function (Blueprint $table) {
                if (! Schema::hasColumn('video_sources', 'downer')) {
                    $table->string('downer', 40)->default('');
                }
                if (! Schema::hasColumn('video_sources', 'server_id')) {
                    $table->unsignedInteger('server_id')->default(0);
                }
            });
        }

        if (Schema::hasTable('video_cj_rules')) {
            Schema::table('video_cj_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('video_cj_rules', 'content_rule')) {
                    $table->string('content_rule', 255)->default('');
                }
                if (! Schema::hasColumn('video_cj_rules', 'type_id')) {
                    $table->unsignedInteger('type_id')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                foreach (['phone', 'qq_openid', 'wechat_openid'] as $col) {
                    if (Schema::hasColumn('members', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('video_sources')) {
            Schema::table('video_sources', function (Blueprint $table) {
                foreach (['downer', 'server_id'] as $col) {
                    if (Schema::hasColumn('video_sources', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('video_cj_rules')) {
            Schema::table('video_cj_rules', function (Blueprint $table) {
                foreach (['content_rule', 'type_id'] as $col) {
                    if (Schema::hasColumn('video_cj_rules', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

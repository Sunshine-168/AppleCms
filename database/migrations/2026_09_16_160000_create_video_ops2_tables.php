<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_downloaders')) {
            Schema::create('video_downloaders', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 40)->default('');
                $table->string('name', 80);
                $table->text('parse')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('video_servers')) {
            Schema::create('video_servers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('url', 255)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('video_play_fails')) {
            Schema::create('video_play_fails', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id')->default(0);
                $table->unsignedInteger('source_id')->default(0);
                $table->unsignedInteger('episode_id')->default(0);
                $table->string('url', 500)->default('');
                $table->string('content', 500)->default('');
                $table->string('ip', 45)->default('');
                $table->unsignedTinyInteger('status')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index('video_id');
            });
        }
        if (! Schema::hasTable('video_audit_rules')) {
            Schema::create('video_audit_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('scope', 20)->default('title');
                $table->text('words')->nullable();
                $table->string('action', 20)->default('skip');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
            });
        }
        if (! Schema::hasTable('video_collect_tasks')) {
            Schema::create('video_collect_tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->unsignedInteger('collect_source_id')->default(0);
                $table->string('cron_expression', 40)->default('0 * * * *');
                $table->unsignedInteger('pages')->default(1);
                $table->unsignedInteger('hours')->default(24);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('last_run_at')->default(0);
                $table->string('last_msg', 255)->default('');
            });
        }
        if (! Schema::hasTable('video_ads')) {
            Schema::create('video_ads', function (Blueprint $table) {
                $table->increments('id');
                $table->string('slot', 40);
                $table->string('name', 80)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('video_guestbooks')) {
            Schema::create('video_guestbooks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->string('author_name', 80)->default('');
                $table->string('content', 2000);
                $table->string('reply', 2000)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
            });
        }
        if (! Schema::hasTable('member_groups')) {
            Schema::create('member_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->unsignedInteger('points_min')->default(0);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('member_orders')) {
            Schema::create('member_orders', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->string('order_no', 40)->default('');
                $table->unsignedInteger('amount')->default(0);
                $table->unsignedInteger('points')->default(0);
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('remark', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('member_withdraws')) {
            Schema::create('member_withdraws', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('amount')->default(0);
                $table->string('account', 120)->default('');
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('remark', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('member_pms')) {
            Schema::create('member_pms', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('from_id')->default(0);
                $table->unsignedInteger('to_id')->default(0);
                $table->string('title', 120)->default('');
                $table->text('content')->nullable();
                $table->unsignedTinyInteger('is_read')->default(0);
                $table->unsignedInteger('created_at')->default(0);
            });
        }
        if (! Schema::hasTable('video_visit_days')) {
            Schema::create('video_visit_days', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('day')->unique();
                $table->unsignedInteger('pv')->default(0);
                $table->unsignedInteger('uv')->default(0);
            });
        }
        if (Schema::hasTable('members') && ! Schema::hasColumn('members', 'group_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->unsignedInteger('group_id')->default(0);
            });
        }

        if (Schema::hasTable('member_groups') && DB::table('member_groups')->count() === 0) {
            DB::table('member_groups')->insert([
                ['name' => '普通会员', 'points_min' => 0, 'sort' => 0, 'status' => 1],
                ['name' => 'VIP', 'points_min' => 1000, 'sort' => 10, 'status' => 1],
            ]);
        }

        if (Schema::hasTable('sys_perm')) {
            $now = time();
            $parentId = (int) (DB::table('sys_perm')->where('code', 'vod_module')->value('id') ?: 0);
            if ($parentId > 0) {
                $items = [
                    ['下载器', 'vod_downer', '/admin/video/downloaders', 72],
                    ['服务器组', 'vod_server', '/admin/video/servers', 74],
                    ['播放失败', 'vod_playfail', '/admin/video/playfails', 92],
                    ['入库审核', 'vod_audit', '/admin/video/audits', 34],
                    ['定时采集', 'vod_collect_task', '/admin/video/collect_tasks', 32],
                    ['广告位', 'vod_ad', '/admin/video/ads', 112],
                    ['留言', 'vod_gbook', '/admin/video/guestbooks', 82],
                    ['会员组', 'vod_mgroup', '/admin/video/groups', 101],
                    ['会员订单', 'vod_order', '/admin/video/orders', 102],
                    ['提现', 'vod_withdraw', '/admin/video/withdraws', 103],
                    ['站内信', 'vod_pm', '/admin/video/pms', 104],
                    ['访问统计', 'vod_visit', '/admin/video/visits', 6],
                    ['模板编辑', 'vod_tpl', '/admin/video/templates', 7],
                    ['百度推送', 'vod_push', '/admin/video/push', 8],
                    ['静态生成', 'vod_make', '/admin/video/make', 9],
                ];
                foreach ($items as [$name, $code, $api, $sort]) {
                    if (DB::table('sys_perm')->where('code', $code)->exists()) {
                        continue;
                    }
                    DB::table('sys_perm')->insert([
                        'name' => $name,
                        'code' => $code,
                        'api' => $api,
                        'method' => 'GET',
                        'pid' => $parentId,
                        'type' => 1,
                        'icon' => '',
                        'sort' => $sort,
                        'create_time' => $now,
                        'update_time' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_visit_days');
        Schema::dropIfExists('member_pms');
        Schema::dropIfExists('member_withdraws');
        Schema::dropIfExists('member_orders');
        Schema::dropIfExists('member_groups');
        Schema::dropIfExists('video_guestbooks');
        Schema::dropIfExists('video_ads');
        Schema::dropIfExists('video_collect_tasks');
        Schema::dropIfExists('video_audit_rules');
        Schema::dropIfExists('video_play_fails');
        Schema::dropIfExists('video_servers');
        Schema::dropIfExists('video_downloaders');
    }
};

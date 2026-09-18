<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('member_tasks')) {
            Schema::create('member_tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 40)->default('');
                $table->unsignedTinyInteger('type')->default(1);
                $table->string('action', 40)->default('');
                $table->string('hint', 255)->default('');
                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('target')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unique('action');
            });
        } else {
            Schema::table('member_tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('member_tasks', 'name')) {
                    $table->string('name', 40)->default('');
                }
                if (! Schema::hasColumn('member_tasks', 'type')) {
                    $table->unsignedTinyInteger('type')->default(1);
                }
                if (! Schema::hasColumn('member_tasks', 'action')) {
                    $table->string('action', 40)->default('');
                }
                if (! Schema::hasColumn('member_tasks', 'hint')) {
                    $table->string('hint', 255)->default('');
                }
                if (! Schema::hasColumn('member_tasks', 'points')) {
                    $table->unsignedInteger('points')->default(0);
                }
                if (! Schema::hasColumn('member_tasks', 'target')) {
                    $table->unsignedInteger('target')->default(1);
                }
                if (! Schema::hasColumn('member_tasks', 'sort')) {
                    $table->unsignedInteger('sort')->default(0);
                }
                if (! Schema::hasColumn('member_tasks', 'status')) {
                    $table->unsignedTinyInteger('status')->default(1);
                }
                if (! Schema::hasColumn('member_tasks', 'created_at')) {
                    $table->unsignedInteger('created_at')->default(0);
                }
            });
        }

        if (! Schema::hasTable('member_task_logs')) {
            Schema::create('member_task_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('task_id')->default(0);
                $table->string('action', 40)->default('');
                $table->unsignedInteger('progress')->default(0);
                $table->unsignedTinyInteger('status')->default(0);
                $table->unsignedInteger('points')->default(0);
                $table->string('day_key', 10)->default('');
                $table->unsignedInteger('claimed_at')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unique(['member_id', 'task_id', 'day_key'], 'member_task_logs_member_task_day');
                $table->index('member_id');
            });
        }

        if (! Schema::hasTable('member_sign_milestones')) {
            Schema::create('member_sign_milestones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 40)->default('');
                $table->unsignedInteger('days')->default(0);
                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unique('days');
            });
        }

        if (! Schema::hasTable('member_sign_milestone_logs')) {
            Schema::create('member_sign_milestone_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('milestone_id')->default(0);
                $table->unsignedInteger('days')->default(0);
                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unique(['member_id', 'milestone_id'], 'member_sign_milestone_logs_member_mile');
            });
        }

        $this->ensureMemberSignsUnique();
        $this->seedIfEmpty();
    }

    public function down(): void
    {
        Schema::dropIfExists('member_sign_milestone_logs');
        Schema::dropIfExists('member_sign_milestones');
        Schema::dropIfExists('member_task_logs');
        Schema::dropIfExists('member_tasks');
    }

    private function ensureMemberSignsUnique(): void
    {
        if (! Schema::hasTable('member_signs')) {
            return;
        }
        try {
            foreach (Schema::getIndexes('member_signs') as $idx) {
                if (! empty($idx['unique']) && ($idx['columns'] ?? []) === ['member_id', 'day_key']) {
                    return;
                }
            }
        } catch (\Throwable) {
        }
        try {
            Schema::table('member_signs', function (Blueprint $table) {
                $table->unique(['member_id', 'day_key'], 'member_signs_member_day_unique');
            });
        } catch (\Throwable) {
        }
    }

    private function seedIfEmpty(): void
    {
        $now = time();
        if (Schema::hasTable('member_tasks') && (int) DB::table('member_tasks')->count() === 0) {
            DB::table('member_tasks')->insert([
                ['name' => '每日签到', 'type' => 1, 'action' => 'daily_sign', 'hint' => '每天签到获得积分奖励', 'points' => 5, 'target' => 1, 'sort' => 1, 'status' => 1, 'created_at' => $now],
                ['name' => '观看影片', 'type' => 1, 'action' => 'watch_vod', 'hint' => '每日观看3部影片', 'points' => 3, 'target' => 3, 'sort' => 2, 'status' => 1, 'created_at' => $now],
                ['name' => '分享影片', 'type' => 1, 'action' => 'share_vod', 'hint' => '每日复制一次影片链接，不是微信分享', 'points' => 2, 'target' => 1, 'sort' => 3, 'status' => 1, 'created_at' => $now],
                ['name' => '发表评论', 'type' => 1, 'action' => 'post_comment', 'hint' => '每日发表1条评论', 'points' => 2, 'target' => 1, 'sort' => 4, 'status' => 1, 'created_at' => $now],
                ['name' => '绑定手机', 'type' => 2, 'action' => 'bind_phone', 'hint' => '账号里有手机号才发一次', 'points' => 20, 'target' => 1, 'sort' => 1, 'status' => 1, 'created_at' => $now],
                ['name' => '绑定邮箱', 'type' => 2, 'action' => 'bind_email', 'hint' => '注册就要邮箱，不当新手任务', 'points' => 20, 'target' => 1, 'sort' => 2, 'status' => 0, 'created_at' => $now],
            ]);
        }
        if (Schema::hasTable('member_sign_milestones') && (int) DB::table('member_sign_milestones')->count() === 0) {
            DB::table('member_sign_milestones')->insert([
                ['name' => '连续3天', 'days' => 3, 'points' => 5, 'sort' => 1, 'status' => 1, 'created_at' => $now],
                ['name' => '连续10天', 'days' => 10, 'points' => 10, 'sort' => 2, 'status' => 1, 'created_at' => $now],
                ['name' => '连续20天', 'days' => 20, 'points' => 20, 'sort' => 3, 'status' => 1, 'created_at' => $now],
                ['name' => '连续35天', 'days' => 35, 'points' => 30, 'sort' => 4, 'status' => 1, 'created_at' => $now],
                ['name' => '连续55天', 'days' => 55, 'points' => 50, 'sort' => 5, 'status' => 1, 'created_at' => $now],
                ['name' => '连续85天', 'days' => 85, 'points' => 100, 'sort' => 6, 'status' => 1, 'created_at' => $now],
            ]);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('email', 120)->unique();
                $table->string('password', 255);
                $table->string('avatar', 255)->default('');
                $table->unsignedInteger('points')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('last_login_at')->default(0);
                $table->rememberToken();
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (! Schema::hasTable('member_favorites')) {
            Schema::create('member_favorites', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('created_at')->default(0);
                $table->unique(['member_id', 'video_id']);
            });
        }

        if (! Schema::hasTable('member_histories')) {
            Schema::create('member_histories', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('source_id')->default(0);
                $table->unsignedInteger('episode_id')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->unique(['member_id', 'video_id']);
            });
        }

        if (! Schema::hasTable('video_comments')) {
            Schema::create('video_comments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('parent_id')->default(0);
                $table->string('author_name', 80)->default('');
                $table->string('content', 2000);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['video_id', 'status']);
            });
        }

        if (! Schema::hasTable('video_reports')) {
            Schema::create('video_reports', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id')->default(0);
                $table->unsignedInteger('member_id')->default(0);
                $table->string('content', 500);
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
            });
        }

        if (! Schema::hasTable('friend_links')) {
            Schema::create('friend_links', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('url', 255);
                $table->string('logo', 255)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (Schema::hasTable('sys_perm')) {
            $now = time();
            $parentId = (int) (DB::table('sys_perm')->where('code', 'vod_module')->value('id') ?: 0);
            if ($parentId < 1) {
                $parentId = (int) DB::table('sys_perm')->insertGetId([
                    'name' => '影视',
                    'code' => 'vod_module',
                    'api' => '',
                    'method' => '',
                    'pid' => 0,
                    'type' => 1,
                    'icon' => 'video',
                    'sort' => 5,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }
            $items = [
                ['影视列表', 'vod_list', '/admin/video', 10],
                ['分类管理', 'vod_type', '/admin/video/types', 20],
                ['采集资源', 'vod_collect', '/admin/video/collects', 30],
                ['标签管理', 'vod_tag', '/admin/video/tags', 40],
                ['演员管理', 'vod_actor', '/admin/video/actors', 50],
                ['专题管理', 'vod_topic', '/admin/video/topics', 60],
                ['播放器', 'vod_player', '/admin/video/players', 70],
                ['评论管理', 'vod_comment', '/admin/video/comments', 80],
                ['报错管理', 'vod_report', '/admin/video/reports', 90],
                ['会员管理', 'vod_member', '/admin/video/members', 100],
                ['友情链接', 'vod_link', '/admin/video/links', 110],
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

    public function down(): void
    {
        Schema::dropIfExists('friend_links');
        Schema::dropIfExists('video_reports');
        Schema::dropIfExists('video_comments');
        Schema::dropIfExists('member_histories');
        Schema::dropIfExists('member_favorites');
        Schema::dropIfExists('members');
    }
};

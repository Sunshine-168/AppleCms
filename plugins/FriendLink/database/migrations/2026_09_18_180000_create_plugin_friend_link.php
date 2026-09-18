<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_friend_link_cates')) {
            Schema::create('plugin_friend_link_cates', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('plugin_friend_links')) {
            Schema::create('plugin_friend_links', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cate_id')->default(0);
                $table->string('name', 120);
                $table->string('url', 500)->default('');
                $table->string('logo', 500)->default('');
                $table->string('email', 120)->default('');
                $table->string('remark', 255)->default('');
                $table->string('type', 16)->default('text');
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('edit_token', 32)->default('');
                $table->unsignedInteger('clicks')->default(0);
                $table->unsignedInteger('referer_total')->default(0);
                $table->unsignedInteger('referer_day')->default(0);
                $table->unsignedInteger('referer_month')->default(0);
                $table->unsignedInteger('referer_year')->default(0);
                $table->string('day_key', 8)->default('');
                $table->string('month_key', 6)->default('');
                $table->string('year_key', 4)->default('');
                $table->unsignedInteger('last_referer_at')->default(0);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['status', 'sort']);
                $table->index('cate_id');
                $table->index('edit_token');
            });
        }
        if (! Schema::hasTable('plugin_friend_link_clicks')) {
            Schema::create('plugin_friend_link_clicks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('link_id')->default(0);
                $table->string('ip', 45)->default('');
                $table->string('ua', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('link_id');
            });
        }
        if (! Schema::hasTable('plugin_friend_link_hits')) {
            Schema::create('plugin_friend_link_hits', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('link_id')->default(0);
                $table->string('ip', 45)->default('');
                $table->string('from_host', 120)->default('');
                $table->string('from_url', 500)->default('');
                $table->string('day_key', 8)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['link_id', 'ip', 'day_key']);
            });
        }
        if (! Schema::hasTable('plugin_friend_link_options')) {
            Schema::create('plugin_friend_link_options', function (Blueprint $table) {
                $table->increments('id');
                $table->string('k', 40)->unique();
                $table->text('v');
            });
        }

        if (Schema::hasTable('plugin_friend_link_cates') && DB::table('plugin_friend_link_cates')->count() === 0) {
            DB::table('plugin_friend_link_cates')->insert([
                'name' => '首页',
                'sort' => 1,
                'status' => 1,
            ]);
        }
        if (Schema::hasTable('plugin_friend_link_options')) {
            $defaults = [
                'mode' => 'normal',
                'min_referer' => '1',
                'allow_apply' => '1',
                'allow_edit' => '1',
            ];
            foreach ($defaults as $k => $v) {
                if (! DB::table('plugin_friend_link_options')->where('k', $k)->exists()) {
                    DB::table('plugin_friend_link_options')->insert(['k' => $k, 'v' => $v]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_friend_link_hits');
        Schema::dropIfExists('plugin_friend_link_clicks');
        Schema::dropIfExists('plugin_friend_links');
        Schema::dropIfExists('plugin_friend_link_cates');
        Schema::dropIfExists('plugin_friend_link_options');
    }
};

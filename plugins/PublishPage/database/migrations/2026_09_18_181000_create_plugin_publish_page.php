<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_publish_options')) {
            Schema::create('plugin_publish_options', function (Blueprint $table) {
                $table->increments('id');
                $table->string('k', 40)->unique();
                $table->text('v');
            });
        }
        if (Schema::hasTable('plugin_publish_options')) {
            $defaults = [
                'status' => '0',
                'title' => '地址发布页',
                'subtitle' => '请收藏本页。进入本站后，浏览器会记住一年。',
                'bookmark' => '建议把本页加入书签。清掉 Cookie 会再看到这一页。',
                'footer' => '',
                'permanent_text' => '',
                'permanent_url' => '',
                'groups' => '[]',
            ];
            foreach ($defaults as $k => $v) {
                if (! DB::table('plugin_publish_options')->where('k', $k)->exists()) {
                    DB::table('plugin_publish_options')->insert(['k' => $k, 'v' => $v]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_publish_options');
    }
};

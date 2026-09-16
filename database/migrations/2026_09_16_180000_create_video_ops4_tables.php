<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_roles')) {
            Schema::create('video_roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('slug', 80)->default('');
                $table->string('cover', 255)->default('');
                $table->string('blurb', 255)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('video_websites')) {
            Schema::create('video_websites', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 120);
                $table->string('url', 255);
                $table->string('logo', 255)->default('');
                $table->string('blurb', 255)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
            });
        }
        if (! Schema::hasTable('video_arts')) {
            Schema::create('video_arts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('type_id')->default(0);
                $table->string('title', 200);
                $table->string('cover', 255)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('hits')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index('type_id');
            });
        }
        if (! Schema::hasTable('video_domains')) {
            Schema::create('video_domains', function (Blueprint $table) {
                $table->increments('id');
                $table->string('host', 120);
                $table->string('theme', 40)->default('');
                $table->string('remark', 255)->default('');
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('video_unions')) {
            Schema::create('video_unions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('api_url', 500)->default('');
                $table->string('note', 255)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
            });
        }
        if (! Schema::hasTable('video_cj_rules')) {
            Schema::create('video_cj_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('url', 500)->default('');
                $table->string('list_rule', 255)->default('');
                $table->string('title_rule', 255)->default('');
                $table->string('url_rule', 255)->default('');
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('note', 255)->default('规则采集占位，请用接口采集入库');
            });
        }
        if (! Schema::hasTable('video_ulogs')) {
            Schema::create('video_ulogs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('video_id')->default(0);
                $table->string('type', 20)->default('play');
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['member_id', 'video_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_ulogs');
        Schema::dropIfExists('video_cj_rules');
        Schema::dropIfExists('video_unions');
        Schema::dropIfExists('video_domains');
        Schema::dropIfExists('video_arts');
        Schema::dropIfExists('video_websites');
        Schema::dropIfExists('video_roles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('videos') && ! Schema::hasColumn('videos', 'deleted_at')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->unsignedInteger('deleted_at')->default(0)->index();
            });
        }

        if (! Schema::hasTable('video_search_words')) {
            Schema::create('video_search_words', function (Blueprint $table) {
                $table->increments('id');
                $table->string('word', 80)->unique();
                $table->unsignedInteger('hits')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_slides')) {
            Schema::create('video_slides', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80)->default('');
                $table->string('pic', 1024)->default('');
                $table->string('url', 1024)->default('');
                $table->string('slot', 40)->default('home');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_coupons')) {
            Schema::create('video_coupons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 40)->unique();
                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('min_points')->default(0);
                $table->unsignedInteger('expire_at')->default(0);
                $table->unsignedInteger('used_by')->default(0);
                $table->unsignedInteger('used_at')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_notifies')) {
            Schema::create('video_notifies', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->string('title', 120)->default('');
                $table->text('content')->nullable();
                $table->unsignedTinyInteger('is_read')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index('member_id');
            });
        }

        if (! Schema::hasTable('member_follows')) {
            Schema::create('member_follows', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('target_id')->default(0);
                $table->string('target_type', 20)->default('actor');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['member_id', 'target_type']);
            });
        }

        if (! Schema::hasTable('member_dynamics')) {
            Schema::create('member_dynamics', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->string('type', 20)->default('text');
                $table->text('content')->nullable();
                $table->unsignedInteger('video_id')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index('member_id');
            });
        }

        if (! Schema::hasTable('member_shares')) {
            Schema::create('member_shares', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('video_id')->default(0);
                $table->string('channel', 20)->default('');
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('video_id');
            });
        }

        if (! Schema::hasTable('member_signs')) {
            Schema::create('member_signs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('days')->default(1);
                $table->unsignedInteger('points')->default(0);
                $table->string('day_key', 10)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['member_id', 'day_key']);
            });
        }

        if (! Schema::hasTable('video_collect_temps')) {
            Schema::create('video_collect_temps', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('collect_source_id')->default(0);
                $table->string('collect_id', 80)->default('');
                $table->string('title', 255)->default('');
                $table->string('cover', 1024)->default('');
                $table->unsignedInteger('type_id')->default(0);
                $table->mediumText('payload')->nullable();
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('msg', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['collect_source_id', 'collect_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_collect_temps');
        Schema::dropIfExists('member_signs');
        Schema::dropIfExists('member_shares');
        Schema::dropIfExists('member_dynamics');
        Schema::dropIfExists('member_follows');
        Schema::dropIfExists('video_notifies');
        Schema::dropIfExists('video_coupons');
        Schema::dropIfExists('video_slides');
        Schema::dropIfExists('video_search_words');
        if (Schema::hasTable('videos') && Schema::hasColumn('videos', 'deleted_at')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};

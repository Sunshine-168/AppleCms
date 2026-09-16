<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_plots')) {
            Schema::create('video_plots', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id')->default(0);
                $table->unsignedInteger('episode_num')->default(0);
                $table->string('title', 200)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['video_id', 'episode_num']);
            });
        }
        if (! Schema::hasTable('video_synonyms')) {
            Schema::create('video_synonyms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('from_word', 80);
                $table->string('to_word', 80)->default('');
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
        if (! Schema::hasTable('member_invites')) {
            Schema::create('member_invites', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 32)->unique();
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('used_by')->default(0);
                $table->unsignedInteger('points')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->index('member_id');
            });
        }
        if (! Schema::hasTable('video_classes')) {
            Schema::create('video_classes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_classes');
        Schema::dropIfExists('member_invites');
        Schema::dropIfExists('video_synonyms');
        Schema::dropIfExists('video_plots');
    }
};

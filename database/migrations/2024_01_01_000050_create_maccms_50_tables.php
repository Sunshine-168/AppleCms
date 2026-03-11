<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('mac_link', function (Blueprint $table) {
            $table->unsignedSmallInteger('link_id')->autoIncrement();
            $table->unsignedTinyInteger('link_type')->default(0);
            $table->string('link_name', 60);
            $table->smallInteger('link_sort')->default(0);
            $table->unsignedInteger('link_add_time')->default(0);
            $table->unsignedInteger('link_time')->default(0);
            $table->string('link_url', 255);
            $table->string('link_logo', 255);
            $table->index('link_sort', 'link_sort');
            $table->index('link_type', 'link_type');
            $table->index('link_add_time', 'link_add_time');
            $table->index('link_time', 'link_time');
        });

        Schema::create('mac_comment', function (Blueprint $table) {
            $table->unsignedInteger('comment_id')->autoIncrement();
            $table->unsignedTinyInteger('comment_mid')->default(1);
            $table->unsignedInteger('comment_rid')->default(0);
            $table->unsignedInteger('comment_pid')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedTinyInteger('comment_status')->default(1);
            $table->string('comment_name', 60);
            $table->unsignedInteger('comment_ip')->default(0);
            $table->unsignedInteger('comment_time')->default(0);
            $table->string('comment_content', 255);
            $table->unsignedMediumInteger('comment_up')->default(0);
            $table->unsignedMediumInteger('comment_down')->default(0);
            $table->unsignedMediumInteger('comment_reply')->default(0);
            $table->unsignedMediumInteger('comment_report')->default(0);
            $table->index('comment_mid', 'comment_mid');
            $table->index('comment_rid', 'comment_rid');
            $table->index('comment_time', 'comment_time');
            $table->index('comment_pid', 'comment_pid');
            $table->index('user_id', 'user_id');
            $table->index('comment_reply', 'comment_reply');
        });

        Schema::create('mac_gbook', function (Blueprint $table) {
            $table->unsignedInteger('gbook_id')->autoIncrement();
            $table->unsignedInteger('gbook_rid')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedTinyInteger('gbook_status')->default(1);
            $table->string('gbook_name', 60);
            $table->unsignedInteger('gbook_ip')->default(0);
            $table->unsignedInteger('gbook_time')->default(0);
            $table->unsignedInteger('gbook_reply_time')->default(0);
            $table->string('gbook_content', 255);
            $table->string('gbook_reply', 255);
            $table->index('gbook_rid', 'gbook_rid');
            $table->index('gbook_time', 'gbook_time');
            $table->index('gbook_reply_time', 'gbook_reply_time');
            $table->index('user_id', 'user_id');
            $table->index('gbook_reply', 'gbook_reply');
        });

        Schema::create('mac_visit', function (Blueprint $table) {
            $table->unsignedInteger('visit_id')->autoIncrement();
            $table->unsignedInteger('user_id')->nullable()->default(0);
            $table->unsignedInteger('visit_ip')->default(0);
            $table->string('visit_ly', 100);
            $table->unsignedInteger('visit_time')->default(0);
            $table->index('user_id', 'user_id');
            $table->index('visit_time', 'visit_time');
        });

        Schema::create('mac_annex', function (Blueprint $table) {
            $table->unsignedInteger('annex_id')->autoIncrement();
            $table->unsignedInteger('annex_time')->default(0);
            $table->string('annex_file', 255);
            $table->unsignedInteger('annex_size')->default(0);
            $table->string('annex_type', 8);
            $table->index('annex_time', 'annex_time');
            $table->index('annex_file', 'annex_file');
            $table->index('annex_type', 'annex_type');
        });

        Schema::create('mac_msg', function (Blueprint $table) {
            $table->unsignedInteger('msg_id')->autoIncrement();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedTinyInteger('msg_type')->default(0);
            $table->unsignedTinyInteger('msg_status')->default(0);
            $table->string('msg_to', 30);
            $table->string('msg_code', 10);
            $table->string('msg_content', 255);
            $table->unsignedInteger('msg_time')->default(0);
            $table->index('msg_code', 'msg_code');
            $table->index('msg_time', 'msg_time');
            $table->index('user_id', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_msg');
        Schema::dropIfExists('mac_annex');
        Schema::dropIfExists('mac_visit');
        Schema::dropIfExists('mac_gbook');
        Schema::dropIfExists('mac_comment');
        Schema::dropIfExists('mac_link');
    }
};

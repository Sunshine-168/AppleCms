<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('mac_user', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->autoIncrement();
            $table->string('group_id', 255)->default(0);
            $table->string('user_name', 30);
            $table->string('user_pwd', 32);
            $table->string('user_nick_name', 30);
            $table->string('user_qq', 16);
            $table->string('user_email', 30);
            $table->string('user_phone', 16);
            $table->unsignedTinyInteger('user_status')->default(0);
            $table->string('user_portrait', 100);
            $table->string('user_portrait_thumb', 100);
            $table->string('user_openid_qq', 40);
            $table->string('user_openid_weixin', 40);
            $table->string('user_question', 255);
            $table->string('user_answer', 255);
            $table->unsignedInteger('user_points')->default(0);
            $table->unsignedInteger('user_points_froze')->default(0);
            $table->unsignedInteger('user_reg_time')->default(0);
            $table->unsignedInteger('user_reg_ip')->default(0);
            $table->unsignedInteger('user_login_time')->default(0);
            $table->unsignedInteger('user_login_ip')->default(0);
            $table->unsignedInteger('user_last_login_time')->default(0);
            $table->unsignedInteger('user_last_login_ip')->default(0);
            $table->unsignedSmallInteger('user_login_num')->default(0);
            $table->unsignedSmallInteger('user_extend')->default(0);
            $table->string('user_random', 32);
            $table->unsignedInteger('user_end_time')->default(0);
            $table->unsignedInteger('user_pid')->default(0);
            $table->unsignedInteger('user_pid_2')->default(0);
            $table->unsignedInteger('user_pid_3')->default(0);
            $table->index('group_id', 'type_id');
            $table->index('user_name', 'user_name');
            $table->index('user_reg_time', 'user_reg_time');
        });

        Schema::create('mac_ulog', function (Blueprint $table) {
            $table->unsignedInteger('ulog_id')->autoIncrement();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedTinyInteger('ulog_mid')->default(0);
            $table->unsignedTinyInteger('ulog_type')->default(1);
            $table->unsignedInteger('ulog_rid')->default(0);
            $table->unsignedTinyInteger('ulog_sid')->default(0);
            $table->unsignedSmallInteger('ulog_nid')->default(0);
            $table->unsignedSmallInteger('ulog_points')->default(0);
            $table->unsignedInteger('ulog_time')->default(0);
            $table->index('user_id', 'user_id');
            $table->index('ulog_mid', 'ulog_mid');
            $table->index('ulog_type', 'ulog_type');
            $table->index('ulog_rid', 'ulog_rid');
        });

        Schema::create('mac_plog', function (Blueprint $table) {
            $table->unsignedInteger('plog_id')->autoIncrement();
            $table->unsignedInteger('user_id')->default(0);
            $table->integer('user_id_1')->default(0);
            $table->unsignedTinyInteger('plog_type')->default(1);
            $table->unsignedSmallInteger('plog_points')->default(0);
            $table->unsignedInteger('plog_time')->default(0);
            $table->string('plog_remarks', 100);
            $table->index('user_id', 'user_id');
            $table->index('plog_type', 'plog_type');
        });

        Schema::create('mac_order', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->autoIncrement();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedTinyInteger('order_status')->default(0);
            $table->string('order_code', 30);
            $table->decimal('order_price', 12, 2)->unsigned()->default(0.00);
            $table->unsignedInteger('order_time')->default(0);
            $table->unsignedMediumInteger('order_points')->default(0);
            $table->string('order_pay_type', 10);
            $table->unsignedInteger('order_pay_time')->default(0);
            $table->string('order_remarks', 100);
            $table->index('order_code', 'order_code');
            $table->index('user_id', 'user_id');
            $table->index('order_time', 'order_time');
        });

        Schema::create('mac_card', function (Blueprint $table) {
            $table->unsignedInteger('card_id')->autoIncrement();
            $table->string('card_no', 16);
            $table->string('card_pwd', 8);
            $table->unsignedSmallInteger('card_money')->default(0);
            $table->unsignedSmallInteger('card_points')->default(0);
            $table->unsignedTinyInteger('card_use_status')->default(0);
            $table->unsignedTinyInteger('card_sale_status')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('card_add_time')->default(0);
            $table->unsignedInteger('card_use_time')->default(0);
            $table->index('user_id', 'user_id');
            $table->index('card_add_time', 'card_add_time');
            $table->index('card_use_time', 'card_use_time');
            $table->index('card_no', 'card_no');
            $table->index('card_pwd', 'card_pwd');
        });

        Schema::create('mac_cash', function (Blueprint $table) {
            $table->unsignedInteger('cash_id')->autoIncrement();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedTinyInteger('cash_status')->default(0);
            $table->unsignedSmallInteger('cash_points')->default(0);
            $table->decimal('cash_money', 12, 2)->unsigned()->default(0.00);
            $table->string('cash_bank_name', 60);
            $table->string('cash_bank_no', 30);
            $table->string('cash_payee_name', 30);
            $table->unsignedInteger('cash_time')->default(0);
            $table->unsignedInteger('cash_time_audit')->default(0);
            $table->index('user_id', 'user_id');
            $table->index('cash_status', 'cash_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_cash');
        Schema::dropIfExists('mac_card');
        Schema::dropIfExists('mac_order');
        Schema::dropIfExists('mac_plog');
        Schema::dropIfExists('mac_ulog');
        Schema::dropIfExists('mac_user');
    }
};

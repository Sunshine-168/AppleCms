<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('mac_admin', function (Blueprint $table) {
            $table->unsignedSmallInteger('admin_id')->autoIncrement();
            $table->string('admin_name', 30);
            $table->char('admin_pwd', 32);
            $table->char('admin_random', 32);
            $table->unsignedTinyInteger('admin_status')->default(1);
            $table->text('admin_auth');
            $table->unsignedInteger('admin_login_time')->default(0);
            $table->unsignedInteger('admin_login_ip')->default(0);
            $table->unsignedInteger('admin_login_num')->default(0);
            $table->unsignedInteger('admin_last_login_time')->default(0);
            $table->unsignedInteger('admin_last_login_ip')->default(0);
            $table->index('admin_name', 'admin_name');
        });

        Schema::create('mac_type', function (Blueprint $table) {
            $table->unsignedSmallInteger('type_id')->autoIncrement();
            $table->string('type_name', 60);
            $table->string('type_en', 60);
            $table->unsignedSmallInteger('type_sort')->default(0);
            $table->unsignedSmallInteger('type_mid')->default(1);
            $table->unsignedSmallInteger('type_pid')->default(0);
            $table->unsignedTinyInteger('type_status')->default(1);
            $table->string('type_tpl', 30);
            $table->string('type_tpl_list', 30);
            $table->string('type_tpl_detail', 30);
            $table->string('type_tpl_play', 30);
            $table->string('type_tpl_down', 30);
            $table->string('type_key', 255);
            $table->string('type_des', 255);
            $table->string('type_title', 255);
            $table->string('type_union', 255);
            $table->text('type_extend');
            $table->string('type_logo', 255)->default('');
            $table->string('type_pic', 1024)->default('');
            $table->string('type_jumpurl', 150)->default('');
            $table->index('type_sort', 'type_sort');
            $table->index('type_pid', 'type_pid');
            $table->index('type_name', 'type_name');
            $table->index('type_en', 'type_en');
            $table->index('type_mid', 'type_mid');
        });

        Schema::create('mac_group', function (Blueprint $table) {
            $table->smallInteger('group_id')->autoIncrement();
            $table->string('group_name', 30);
            $table->unsignedTinyInteger('group_status')->default(1);
            $table->text('group_type');
            $table->text('group_popedom');
            $table->unsignedSmallInteger('group_points_day')->default(0);
            $table->smallInteger('group_points_week')->default(0);
            $table->unsignedSmallInteger('group_points_month')->default(0);
            $table->unsignedSmallInteger('group_points_year')->default(0);
            $table->unsignedTinyInteger('group_points_free')->default(0);
            $table->index('group_status', 'group_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_group');
        Schema::dropIfExists('mac_type');
        Schema::dropIfExists('mac_admin');
    }
};

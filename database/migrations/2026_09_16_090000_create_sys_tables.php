<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_user')) {
            Schema::create('sys_user', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 50)->unique();
                $table->string('password', 255);
                $table->string('email', 120)->default('');
                $table->string('remark', 255)->default('');
                $table->unsignedTinyInteger('role')->default(1);
                $table->unsignedInteger('role_id')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('token', 64)->default('');
                $table->unsignedInteger('login_time')->default(0);
                $table->string('login_ip', 45)->default('');
                $table->string('ip_address', 255)->default('');
                $table->string('login_agent', 255)->default('');
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
            });
        }

        if (! Schema::hasTable('sys_user_log')) {
            Schema::create('sys_user_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('uid')->default(0);
                $table->string('username', 50)->default('');
                $table->string('login_ip', 45)->default('');
                $table->string('login_agent', 255)->default('');
                $table->string('ip_address', 255)->default('');
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->index('uid');
            });
        }

        if (! Schema::hasTable('sys_user_role')) {
            Schema::create('sys_user_role', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->index(['user_id', 'role_id']);
            });
        }

        if (! Schema::hasTable('sys_role')) {
            Schema::create('sys_role', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('code', 50)->unique();
                $table->string('remark', 255)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
            });
        }

        if (! Schema::hasTable('sys_perm')) {
            Schema::create('sys_perm', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('code', 80)->default('');
                $table->string('api', 255)->default('');
                $table->string('method', 20)->default('');
                $table->unsignedInteger('pid')->default(0);
                $table->unsignedTinyInteger('type')->default(1);
                $table->string('icon', 40)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->index('pid');
                $table->index('code');
            });
        }

        if (! Schema::hasTable('sys_role_perm')) {
            Schema::create('sys_role_perm', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('perm_id');
                $table->index(['role_id', 'perm_id']);
            });
        }

        if (! Schema::hasTable('sys_schedule')) {
            Schema::create('sys_schedule', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('code', 80)->default('');
                $table->string('type', 30)->default('command');
                $table->string('command', 255)->default('');
                $table->text('params')->nullable();
                $table->string('cron_expression', 64)->default('* * * * *');
                $table->string('timezone', 64)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedTinyInteger('without_overlapping')->default(0);
                $table->unsignedTinyInteger('on_one_server')->default(0);
                $table->unsignedTinyInteger('run_in_maintenance')->default(0);
                $table->unsignedInteger('timeout')->default(0);
                $table->unsignedInteger('max_attempts')->default(1);
                $table->unsignedInteger('last_run_time')->default(0);
                $table->unsignedInteger('next_run_time')->default(0);
                $table->string('remark', 255)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->string('create_at', 32)->default('');
                $table->string('update_at', 32)->default('');
            });
        }

        if (! Schema::hasTable('sys_dict')) {
            Schema::create('sys_dict', function (Blueprint $table) {
                $table->increments('id');
                $table->string('dict_type', 50);
                $table->string('dict_key', 80);
                $table->unsignedTinyInteger('value_type')->default(0);
                $table->string('value_string', 500)->nullable();
                $table->integer('value_int')->nullable();
                $table->decimal('value_float', 14, 4)->nullable();
                $table->text('value_json')->nullable();
                $table->text('value_text')->nullable();
                $table->text('enum_limit')->nullable();
                $table->string('label', 80)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('remark', 255)->default('');
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->unique(['dict_type', 'dict_key']);
            });
        }

        if (! Schema::hasTable('sys_file')) {
            Schema::create('sys_file', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->default('');
                $table->string('path', 500)->default('');
                $table->string('url', 500)->default('');
                $table->unsignedBigInteger('size')->default(0);
                $table->string('md5', 32)->default('');
                $table->string('type', 30)->default('');
                $table->string('mime', 120)->default('');
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
            });
        }

        if (! Schema::hasTable('sys_operate_log')) {
            Schema::create('sys_operate_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('uid')->default(0);
                $table->string('username', 50)->default('');
                $table->string('title', 100)->default('');
                $table->string('action', 50)->default('');
                $table->string('permission', 100)->default('');
                $table->string('module', 50)->default('');
                $table->string('method', 10)->default('');
                $table->string('url', 255)->default('');
                $table->string('route', 150)->default('');
                $table->mediumText('request_data')->nullable();
                $table->integer('response_code')->default(0);
                $table->string('response_msg', 255)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('duration_ms')->default(0);
                $table->string('login_ip', 45)->default('');
                $table->string('ip_address', 255)->default('');
                $table->string('user_agent', 255)->default('');
                $table->string('referer', 255)->default('');
                $table->string('target_type', 50)->default('');
                $table->unsignedInteger('target_id')->default(0);
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->index('uid');
            });
        }

        if (! Schema::hasTable('sys_system_log')) {
            Schema::create('sys_system_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('level', 20)->default('info');
                $table->string('channel', 40)->default('');
                $table->string('module', 40)->default('');
                $table->text('message')->nullable();
                $table->mediumText('context')->nullable();
                $table->mediumText('extra')->nullable();
                $table->string('exception_class', 255)->default('');
                $table->text('exception_message')->nullable();
                $table->string('file', 255)->default('');
                $table->unsignedInteger('line')->default(0);
                $table->mediumText('trace')->nullable();
                $table->string('request_id', 64)->default('');
                $table->string('method', 10)->default('');
                $table->string('url', 500)->default('');
                $table->string('ip', 45)->default('');
                $table->string('user_agent', 255)->default('');
                $table->unsignedInteger('uid')->default(0);
                $table->string('username', 50)->default('');
                $table->unsignedInteger('create_time')->default(0);
                $table->unsignedInteger('update_time')->default(0);
                $table->index('level');
                $table->index('create_time');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_system_log');
        Schema::dropIfExists('sys_operate_log');
        Schema::dropIfExists('sys_file');
        Schema::dropIfExists('sys_dict');
        Schema::dropIfExists('sys_schedule');
        Schema::dropIfExists('sys_role_perm');
        Schema::dropIfExists('sys_perm');
        Schema::dropIfExists('sys_role');
        Schema::dropIfExists('sys_user_role');
        Schema::dropIfExists('sys_user_log');
        Schema::dropIfExists('sys_user');
    }
};

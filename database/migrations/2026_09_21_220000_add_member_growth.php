<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members') && ! Schema::hasColumn('members', 'group_expire_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->unsignedInteger('group_expire_at')->default(0);
            });
        }
        if (Schema::hasTable('members') && ! Schema::hasColumn('members', 'invite_code')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('invite_code', 32)->nullable()->unique();
            });
        }
        if (! Schema::hasTable('member_invite_logs')) {
            Schema::create('member_invite_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('inviter_id')->default(0);
                $table->unsignedInteger('invitee_id')->default(0);
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('days')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['inviter_id', 'created_at']);
                $table->index(['inviter_id', 'ip']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_invite_logs');
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'invite_code')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique(['invite_code']);
                $table->dropColumn('invite_code');
            });
        }
    }
};

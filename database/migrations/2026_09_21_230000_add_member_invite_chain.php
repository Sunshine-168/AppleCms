<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members') && ! Schema::hasColumn('members', 'inviter_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->unsignedInteger('inviter_id')->default(0);
                $table->index('inviter_id');
            });
        }
        if (Schema::hasTable('member_invite_logs') && ! Schema::hasColumn('member_invite_logs', 'level')) {
            Schema::table('member_invite_logs', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1);
                $table->index(['level', 'inviter_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'inviter_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropIndex(['inviter_id']);
                $table->dropColumn('inviter_id');
            });
        }
        if (Schema::hasTable('member_invite_logs') && Schema::hasColumn('member_invite_logs', 'level')) {
            Schema::table('member_invite_logs', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('member_orders') && ! Schema::hasColumn('member_orders', 'paid_at')) {
            Schema::table('member_orders', function (Blueprint $table) {
                $table->unsignedInteger('paid_at')->default(0);
            });
        }

        if (Schema::hasTable('member_tasks')) {
            $exists = DB::table('member_tasks')->where('action', 'recharge')->exists();
            if (! $exists) {
                DB::table('member_tasks')->insert([
                    'name' => '在线充值',
                    'type' => 1,
                    'action' => 'recharge',
                    'hint' => '每日成功充值 1 次（支付回调或后台确认已付）',
                    'points' => 5,
                    'target' => 1,
                    'sort' => 5,
                    'status' => 1,
                    'created_at' => time(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('member_orders') && Schema::hasColumn('member_orders', 'paid_at')) {
            Schema::table('member_orders', function (Blueprint $table) {
                $table->dropColumn('paid_at');
            });
        }
        if (Schema::hasTable('member_tasks')) {
            DB::table('member_tasks')->where('action', 'recharge')->delete();
        }
    }
};

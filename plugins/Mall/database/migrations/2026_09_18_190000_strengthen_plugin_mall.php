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

        if (Schema::hasTable('plugin_mall_goods')) {
            if (! Schema::hasColumn('plugin_mall_goods', 'is_hot')) {
                Schema::table('plugin_mall_goods', function (Blueprint $table) {
                    $table->unsignedTinyInteger('is_hot')->default(0);
                });
            }
            if (! Schema::hasColumn('plugin_mall_goods', 'sales')) {
                Schema::table('plugin_mall_goods', function (Blueprint $table) {
                    $table->unsignedInteger('sales')->default(0);
                });
            }
        }

        if (Schema::hasTable('plugin_mall_orders')) {
            if (! Schema::hasColumn('plugin_mall_orders', 'contact')) {
                Schema::table('plugin_mall_orders', function (Blueprint $table) {
                    $table->string('contact', 80)->default('');
                });
            }
            if (! Schema::hasColumn('plugin_mall_orders', 'address')) {
                Schema::table('plugin_mall_orders', function (Blueprint $table) {
                    $table->string('address', 250)->default('');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'group_expire_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('group_expire_at');
            });
        }
        if (Schema::hasTable('plugin_mall_goods')) {
            Schema::table('plugin_mall_goods', function (Blueprint $table) {
                foreach (['is_hot', 'sales'] as $col) {
                    if (Schema::hasColumn('plugin_mall_goods', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('plugin_mall_orders')) {
            Schema::table('plugin_mall_orders', function (Blueprint $table) {
                foreach (['contact', 'address'] as $col) {
                    if (Schema::hasColumn('plugin_mall_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

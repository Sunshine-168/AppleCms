<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plugin_mall_goods')) {
            if (! Schema::hasColumn('plugin_mall_goods', 'type')) {
                Schema::table('plugin_mall_goods', function (Blueprint $table) {
                    $table->string('type', 20)->default('goods');
                });
            }
            if (! Schema::hasColumn('plugin_mall_goods', 'ext')) {
                Schema::table('plugin_mall_goods', function (Blueprint $table) {
                    $table->text('ext')->nullable();
                });
            }
        }

        if (Schema::hasTable('plugin_mall_orders')) {
            if (! Schema::hasColumn('plugin_mall_orders', 'goods_type')) {
                Schema::table('plugin_mall_orders', function (Blueprint $table) {
                    $table->string('goods_type', 20)->default('goods');
                });
            }
            if (! Schema::hasColumn('plugin_mall_orders', 'delivery')) {
                Schema::table('plugin_mall_orders', function (Blueprint $table) {
                    $table->text('delivery')->nullable();
                });
            }
            if (! Schema::hasColumn('plugin_mall_orders', 'complete_at')) {
                Schema::table('plugin_mall_orders', function (Blueprint $table) {
                    $table->unsignedInteger('complete_at')->default(0);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plugin_mall_goods')) {
            Schema::table('plugin_mall_goods', function (Blueprint $table) {
                foreach (['type', 'ext'] as $col) {
                    if (Schema::hasColumn('plugin_mall_goods', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('plugin_mall_orders')) {
            Schema::table('plugin_mall_orders', function (Blueprint $table) {
                foreach (['goods_type', 'delivery', 'complete_at'] as $col) {
                    if (Schema::hasColumn('plugin_mall_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

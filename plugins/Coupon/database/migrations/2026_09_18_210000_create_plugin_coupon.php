<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_coupons')) {
            Schema::create('plugin_coupons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->default('');
                $table->string('type', 16)->default('amount');
                $table->decimal('value', 10, 2)->default(0);
                $table->decimal('min_price', 10, 2)->default(0);
                $table->string('scene', 16)->default('all');
                $table->unsignedInteger('total')->default(1);
                $table->unsignedInteger('received')->default(0);
                $table->unsignedInteger('used')->default(0);
                $table->unsignedTinyInteger('per_user')->default(1);
                $table->text('target')->nullable();
                $table->unsignedInteger('start_at')->default(0);
                $table->unsignedInteger('end_at')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }
        if (! Schema::hasTable('plugin_coupon_users')) {
            Schema::create('plugin_coupon_users', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('coupon_id')->default(0);
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedTinyInteger('status')->default(0);
                $table->unsignedInteger('received_at')->default(0);
                $table->unsignedInteger('used_at')->default(0);
                $table->unsignedInteger('order_id')->default(0);
                $table->string('order_no', 40)->default('');
                $table->unique(['coupon_id', 'member_id']);
                $table->index('member_id');
            });
        }
        if (Schema::hasTable('member_orders')) {
            if (! Schema::hasColumn('member_orders', 'original_amount')) {
                Schema::table('member_orders', function (Blueprint $table) {
                    $table->unsignedInteger('original_amount')->default(0);
                });
            }
            if (! Schema::hasColumn('member_orders', 'coupon_user_id')) {
                Schema::table('member_orders', function (Blueprint $table) {
                    $table->unsignedInteger('coupon_user_id')->default(0);
                });
            }
            if (! Schema::hasColumn('member_orders', 'coupon_discount')) {
                Schema::table('member_orders', function (Blueprint $table) {
                    $table->unsignedInteger('coupon_discount')->default(0);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_coupon_users');
        Schema::dropIfExists('plugin_coupons');
    }
};

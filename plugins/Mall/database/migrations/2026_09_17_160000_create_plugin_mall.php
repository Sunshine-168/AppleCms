<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_mall_goods')) {
            Schema::create('plugin_mall_goods', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 120);
                $table->string('cover', 500)->default('');
                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('stock')->default(0);
                $table->string('hint', 500)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['status', 'sort']);
            });
        }
        if (! Schema::hasTable('plugin_mall_orders')) {
            Schema::create('plugin_mall_orders', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->default(0);
                $table->unsignedInteger('goods_id')->default(0);
                $table->string('goods_name', 120)->default('');
                $table->unsignedInteger('points')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('remark', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('member_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_mall_orders');
        Schema::dropIfExists('plugin_mall_goods');
    }
};

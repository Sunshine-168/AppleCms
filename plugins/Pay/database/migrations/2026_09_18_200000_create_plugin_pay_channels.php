<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_pay_channels')) {
            Schema::create('plugin_pay_channels', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 80)->default('');
                $table->string('driver', 20)->default('epay'); // epay | dfpay
                $table->string('code', 40)->default(''); // 产品码：alipay/wxpay 或三方 payType
                $table->string('api_url', 255)->default('');
                $table->string('mch_id', 80)->default('');
                $table->string('app_key', 120)->default('');
                $table->unsignedInteger('min_fen')->default(0);
                $table->unsignedInteger('max_fen')->default(0);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('hint', 250)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (Schema::hasTable('member_orders') && ! Schema::hasColumn('member_orders', 'pay_channel_id')) {
            Schema::table('member_orders', function (Blueprint $table) {
                $table->unsignedInteger('pay_channel_id')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_pay_channels');
        if (Schema::hasTable('member_orders') && Schema::hasColumn('member_orders', 'pay_channel_id')) {
            Schema::table('member_orders', function (Blueprint $table) {
                $table->dropColumn('pay_channel_id');
            });
        }
    }
};

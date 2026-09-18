<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_ads')) {
            Schema::create('plugin_ads', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 120);
                $table->string('type', 16)->default('text');
                $table->string('slot', 16)->default('content');
                $table->string('title', 200)->default('');
                $table->string('url', 500)->default('');
                $table->string('image', 500)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('start_at')->default(0);
                $table->unsignedInteger('expire_at')->default(0);
                $table->unsignedInteger('impressions')->default(0);
                $table->unsignedInteger('clicks')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['slot', 'status', 'sort']);
            });
        }
        if (! Schema::hasTable('plugin_ad_clicks')) {
            Schema::create('plugin_ad_clicks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ad_id')->default(0);
                $table->string('ip', 45)->default('');
                $table->string('ua', 255)->default('');
                $table->string('page', 255)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index('ad_id');
            });
        }
        if (! Schema::hasTable('plugin_ad_days')) {
            Schema::create('plugin_ad_days', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ad_id')->default(0);
                $table->string('day_key', 8)->default('');
                $table->unsignedInteger('impressions')->default(0);
                $table->unsignedInteger('clicks')->default(0);
                $table->unique(['ad_id', 'day_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_ad_days');
        Schema::dropIfExists('plugin_ad_clicks');
        Schema::dropIfExists('plugin_ads');
    }
};

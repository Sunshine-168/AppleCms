<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_sms_codes')) {
            Schema::create('plugin_sms_codes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('phone', 20);
                $table->string('code', 12);
                $table->string('scene', 20)->default('register');
                $table->unsignedInteger('expire_at')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['phone', 'scene']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_sms_codes');
    }
};

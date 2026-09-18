<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_manga_comments')) {
            Schema::create('plugin_manga_comments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('manga_id');
                $table->unsignedInteger('member_id')->default(0);
                $table->string('author_name', 80)->default('');
                $table->string('content', 2000);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('ip', 45)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['manga_id', 'status']);
            });
        }
        if (! Schema::hasTable('plugin_manga_favors')) {
            Schema::create('plugin_manga_favors', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id');
                $table->unsignedInteger('manga_id');
                $table->unsignedInteger('created_at')->default(0);
                $table->unique(['member_id', 'manga_id']);
                $table->index('manga_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_manga_favors');
        Schema::dropIfExists('plugin_manga_comments');
    }
};

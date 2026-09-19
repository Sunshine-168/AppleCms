<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_galleries')) Schema::create('plugin_galleries', function (Blueprint $t) {
            $t->increments('id'); $t->string('title',200); $t->string('cover',500)->default('');
            $t->string('remarks',255)->default(''); $t->text('content')->nullable(); $t->unsignedInteger('type_id')->default(0);
            $t->unsignedTinyInteger('yid')->default(0); $t->unsignedInteger('hits')->default(0); $t->unsignedInteger('sort')->default(0);
            $t->unsignedTinyInteger('status')->default(1); $t->unsignedInteger('created_at')->default(0); $t->unsignedInteger('updated_at')->default(0);
            $t->index(['status','yid','sort']);
        });
        if (! Schema::hasTable('plugin_gallery_pics')) Schema::create('plugin_gallery_pics', function (Blueprint $t) {
            $t->increments('id'); $t->unsignedInteger('gallery_id'); $t->string('url',1000); $t->string('title',200)->default('');
            $t->unsignedInteger('sort')->default(0); $t->unsignedInteger('created_at')->default(0); $t->index(['gallery_id','sort']);
        });
        if (! Schema::hasTable('plugin_gallery_types')) Schema::create('plugin_gallery_types', function (Blueprint $t) {
            $t->increments('id'); $t->string('name',80); $t->string('slug',80)->default(''); $t->unsignedInteger('sort')->default(0);
            $t->unsignedTinyInteger('status')->default(1); $t->unsignedInteger('created_at')->default(0);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('plugin_gallery_pics'); Schema::dropIfExists('plugin_gallery_types'); Schema::dropIfExists('plugin_galleries');
    }
};

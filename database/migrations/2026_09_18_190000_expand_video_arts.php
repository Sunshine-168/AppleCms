<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_arts')) {
            return;
        }

        $this->addString('blurb', 500);
        $this->addString('source', 120);
        $this->addString('author', 80);
        $this->addString('tag', 255);
        $this->addString('flags', 80);
        if (! Schema::hasColumn('video_arts', 'sort')) {
            Schema::table('video_arts', function (Blueprint $table) {
                $table->unsignedInteger('sort')->default(0);
            });
        }
        $this->addString('seo_title', 255);
        $this->addString('seo_key', 255);
        $this->addString('seo_des', 500);
        if (! Schema::hasColumn('video_arts', 'published_at')) {
            Schema::table('video_arts', function (Blueprint $table) {
                $table->unsignedInteger('published_at')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('video_arts')) {
            return;
        }
        foreach (['blurb', 'source', 'author', 'tag', 'flags', 'sort', 'seo_title', 'seo_key', 'seo_des', 'published_at'] as $col) {
            if (Schema::hasColumn('video_arts', $col)) {
                Schema::table('video_arts', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }

    private function addString(string $column, int $length): void
    {
        if (Schema::hasColumn('video_arts', $column)) {
            return;
        }
        Schema::table('video_arts', function (Blueprint $table) use ($column, $length) {
            $table->string($column, $length)->default('');
        });
    }
};

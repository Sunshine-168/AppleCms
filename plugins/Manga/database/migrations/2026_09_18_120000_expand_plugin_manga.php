<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            Schema::create('plugin_manga_types', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('parent_id')->default(0);
                $table->string('name', 80);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->index('parent_id');
            });
        }

        if (Schema::hasTable('plugin_mangas')) {
            if (! Schema::hasColumn('plugin_mangas', 'type_id')) {
                Schema::table('plugin_mangas', function (Blueprint $table) {
                    $table->unsignedInteger('type_id')->default(0);
                    $table->index('type_id');
                });
            }
            if (! Schema::hasColumn('plugin_mangas', 'serialize')) {
                Schema::table('plugin_mangas', function (Blueprint $table) {
                    $table->unsignedTinyInteger('serialize')->default(0);
                });
            }
            if (! Schema::hasColumn('plugin_mangas', 'tags')) {
                Schema::table('plugin_mangas', function (Blueprint $table) {
                    $table->string('tags', 255)->default('');
                });
            }
            if (! Schema::hasColumn('plugin_mangas', 'recommend')) {
                Schema::table('plugin_mangas', function (Blueprint $table) {
                    $table->unsignedTinyInteger('recommend')->default(0);
                });
            }
            if (! Schema::hasColumn('plugin_mangas', 'yid')) {
                Schema::table('plugin_mangas', function (Blueprint $table) {
                    $table->unsignedTinyInteger('yid')->default(0);
                    $table->index('yid');
                });
            }
        }

        if (! Schema::hasTable('plugin_manga_pics')) {
            Schema::create('plugin_manga_pics', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('manga_id')->default(0);
                $table->unsignedInteger('chapter_id')->default(0);
                $table->string('url', 500)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->index(['chapter_id', 'sort']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_manga_pics');
        if (Schema::hasTable('plugin_mangas')) {
            Schema::table('plugin_mangas', function (Blueprint $table) {
                foreach (['type_id', 'serialize', 'tags', 'recommend', 'yid'] as $col) {
                    if (Schema::hasColumn('plugin_mangas', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        Schema::dropIfExists('plugin_manga_types');
    }
};

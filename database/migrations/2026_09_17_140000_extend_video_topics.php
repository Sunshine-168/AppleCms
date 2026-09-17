<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @return array<string, callable(Blueprint): void> */
    private function columnBuilders(): array
    {
        return [
            'sub' => fn (Blueprint $table) => $table->string('sub', 120)->default(''),
            'letter' => fn (Blueprint $table) => $table->string('letter', 8)->default(''),
            'color' => fn (Blueprint $table) => $table->string('color', 16)->default(''),
            'tpl' => fn (Blueprint $table) => $table->string('tpl', 64)->default(''),
            'type' => fn (Blueprint $table) => $table->string('type', 120)->default(''),
            'tag' => fn (Blueprint $table) => $table->string('tag', 120)->default(''),
            'cover_thumb' => fn (Blueprint $table) => $table->string('cover_thumb', 1024)->default(''),
            'cover_slide' => fn (Blueprint $table) => $table->string('cover_slide', 1024)->default(''),
            'seo_title' => fn (Blueprint $table) => $table->string('seo_title', 200)->default(''),
            'seo_key' => fn (Blueprint $table) => $table->string('seo_key', 255)->default(''),
            'seo_des' => fn (Blueprint $table) => $table->string('seo_des', 255)->default(''),
            'remarks' => fn (Blueprint $table) => $table->string('remarks', 255)->default(''),
            'level' => fn (Blueprint $table) => $table->unsignedTinyInteger('level')->default(0),
            'hits' => fn (Blueprint $table) => $table->unsignedInteger('hits')->default(0),
            'hits_day' => fn (Blueprint $table) => $table->unsignedInteger('hits_day')->default(0),
            'hits_week' => fn (Blueprint $table) => $table->unsignedInteger('hits_week')->default(0),
            'hits_month' => fn (Blueprint $table) => $table->unsignedInteger('hits_month')->default(0),
            'up' => fn (Blueprint $table) => $table->unsignedInteger('up')->default(0),
            'down' => fn (Blueprint $table) => $table->unsignedInteger('down')->default(0),
            'score' => fn (Blueprint $table) => $table->decimal('score', 3, 1)->default(0),
            'score_all' => fn (Blueprint $table) => $table->unsignedInteger('score_all')->default(0),
            'score_num' => fn (Blueprint $table) => $table->unsignedInteger('score_num')->default(0),
            'time_hits' => fn (Blueprint $table) => $table->unsignedInteger('time_hits')->default(0),
            'extend' => fn (Blueprint $table) => $table->mediumText('extend')->nullable(),
        ];
    }

    public function up(): void
    {
        if (Schema::hasTable('video_topics')) {
            $builders = $this->columnBuilders();
            $missing = [];
            foreach (array_keys($builders) as $col) {
                if (! Schema::hasColumn('video_topics', $col)) {
                    $missing[] = $col;
                }
            }
            if ($missing !== []) {
                Schema::table('video_topics', function (Blueprint $table) use ($builders, $missing) {
                    foreach ($missing as $col) {
                        $builders[$col]($table);
                    }
                });
            }
        }

        if (! Schema::hasTable('video_topic_art_rel')) {
            Schema::create('video_topic_art_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('topic_id');
                $table->unsignedInteger('art_id');
                $table->unsignedInteger('sort')->default(0);
                $table->index(['topic_id', 'art_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_topic_art_rel');

        if (! Schema::hasTable('video_topics')) {
            return;
        }
        $drop = [];
        foreach (array_keys($this->columnBuilders()) as $col) {
            if (Schema::hasColumn('video_topics', $col)) {
                $drop[] = $col;
            }
        }
        if ($drop === []) {
            return;
        }
        Schema::table('video_topics', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_types')) {
            Schema::create('video_types', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('parent_id')->default(0);
                $table->string('name', 60);
                $table->string('slug', 80)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('pic', 255)->default('');
                $table->string('seo_title', 255)->default('');
                $table->string('seo_keywords', 255)->default('');
                $table->string('seo_description', 255)->default('');
                $table->string('tpl_list', 40)->default('');
                $table->string('tpl_detail', 40)->default('');
                $table->string('tpl_play', 40)->default('');
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index('parent_id');
                $table->index('slug');
                $table->index('status');
            });
        } else {
            $this->addColumns('video_types', function (Blueprint $table) {
                if (! Schema::hasColumn('video_types', 'slug')) {
                    $table->string('slug', 80)->default('')->after('name');
                }
                if (! Schema::hasColumn('video_types', 'pic')) {
                    $table->string('pic', 255)->default('');
                }
                if (! Schema::hasColumn('video_types', 'seo_title')) {
                    $table->string('seo_title', 255)->default('');
                }
                if (! Schema::hasColumn('video_types', 'seo_keywords')) {
                    $table->string('seo_keywords', 255)->default('');
                }
                if (! Schema::hasColumn('video_types', 'seo_description')) {
                    $table->string('seo_description', 255)->default('');
                }
                if (! Schema::hasColumn('video_types', 'tpl_list')) {
                    $table->string('tpl_list', 40)->default('');
                }
                if (! Schema::hasColumn('video_types', 'tpl_detail')) {
                    $table->string('tpl_detail', 40)->default('');
                }
                if (! Schema::hasColumn('video_types', 'tpl_play')) {
                    $table->string('tpl_play', 40)->default('');
                }
            });
        }

        if (! Schema::hasTable('videos')) {
            Schema::create('videos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('type_id')->nullable();
                $table->unsignedInteger('type_pid')->default(0);
                $table->string('title', 255);
                $table->string('subtitle', 255)->default('');
                $table->string('slug', 255)->default('');
                $table->char('letter', 1)->default('');
                $table->string('cover', 1024)->default('');
                $table->string('banner', 1024)->default('');
                $table->string('class', 255)->default('');
                $table->string('area', 40)->default('');
                $table->string('lang', 20)->default('');
                $table->string('year', 10)->default('');
                $table->string('director', 255)->default('');
                $table->string('writer', 255)->default('');
                $table->string('remarks', 100)->default('');
                $table->string('serial', 20)->default('');
                $table->unsignedInteger('total')->default(0);
                $table->unsignedTinyInteger('isend')->default(0);
                $table->unsignedTinyInteger('lock')->default(0);
                $table->unsignedTinyInteger('level')->default(0);
                $table->unsignedInteger('points')->default(0);
                $table->string('duration', 20)->default('');
                $table->unsignedInteger('douban_id')->default(0);
                $table->decimal('douban_score', 3, 1)->default(0);
                $table->decimal('score', 3, 1)->default(0);
                $table->mediumText('description')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedTinyInteger('is_recommend')->default(0);
                $table->unsignedTinyInteger('is_hot')->default(0);
                $table->string('collect_id', 64)->default('');
                $table->unsignedInteger('collect_source_id')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index('type_id');
                $table->index('type_pid');
                $table->index('status');
                $table->index('year');
                $table->index('area');
                $table->index('lang');
                $table->index('letter');
                $table->index('is_recommend');
                $table->index('is_hot');
                $table->index('title');
            });
        } else {
            $this->addColumns('videos', function (Blueprint $table) {
                foreach ([
                    'type_pid' => fn (Blueprint $t) => $t->unsignedInteger('type_pid')->default(0),
                    'slug' => fn (Blueprint $t) => $t->string('slug', 255)->default(''),
                    'letter' => fn (Blueprint $t) => $t->char('letter', 1)->default(''),
                    'class' => fn (Blueprint $t) => $t->string('class', 255)->default(''),
                    'writer' => fn (Blueprint $t) => $t->string('writer', 255)->default(''),
                    'remarks' => fn (Blueprint $t) => $t->string('remarks', 100)->default(''),
                    'serial' => fn (Blueprint $t) => $t->string('serial', 20)->default(''),
                    'total' => fn (Blueprint $t) => $t->unsignedInteger('total')->default(0),
                    'isend' => fn (Blueprint $t) => $t->unsignedTinyInteger('isend')->default(0),
                    'lock' => fn (Blueprint $t) => $t->unsignedTinyInteger('lock')->default(0),
                    'level' => fn (Blueprint $t) => $t->unsignedTinyInteger('level')->default(0),
                    'points' => fn (Blueprint $t) => $t->unsignedInteger('points')->default(0),
                    'duration' => fn (Blueprint $t) => $t->string('duration', 20)->default(''),
                    'douban_id' => fn (Blueprint $t) => $t->unsignedInteger('douban_id')->default(0),
                    'douban_score' => fn (Blueprint $t) => $t->decimal('douban_score', 3, 1)->default(0),
                ] as $col => $def) {
                    if (! Schema::hasColumn('videos', $col)) {
                        $def($table);
                    }
                }
            });
        }

        if (! Schema::hasTable('video_stats')) {
            Schema::create('video_stats', function (Blueprint $table) {
                $table->unsignedInteger('video_id')->primary();
                $table->unsignedInteger('hits')->default(0);
                $table->unsignedInteger('hits_day')->default(0);
                $table->unsignedInteger('hits_week')->default(0);
                $table->unsignedInteger('hits_month')->default(0);
                $table->unsignedInteger('up')->default(0);
                $table->unsignedInteger('down')->default(0);
                $table->decimal('score', 3, 1)->default(0);
                $table->unsignedInteger('score_all')->default(0);
                $table->unsignedInteger('score_num')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_sources')) {
            Schema::create('video_sources', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id');
                $table->string('name', 60);
                $table->string('type', 30)->default('m3u8');
                $table->string('player', 40)->default('');
                $table->string('note', 255)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index('video_id');
            });
        } else {
            $this->addColumns('video_sources', function (Blueprint $table) {
                if (! Schema::hasColumn('video_sources', 'player')) {
                    $table->string('player', 40)->default('');
                }
                if (! Schema::hasColumn('video_sources', 'note')) {
                    $table->string('note', 255)->default('');
                }
                if (! Schema::hasColumn('video_sources', 'status')) {
                    $table->unsignedTinyInteger('status')->default(1);
                }
            });
        }

        if (! Schema::hasTable('video_episodes')) {
            Schema::create('video_episodes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('source_id');
                $table->string('episode_name', 80)->default('');
                $table->unsignedInteger('episode_num')->default(1);
                $table->text('url');
                $table->unsignedInteger('duration')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index(['video_id', 'source_id']);
                $table->index('episode_num');
            });
        }

        if (! Schema::hasTable('video_tags')) {
            Schema::create('video_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 60);
                $table->string('slug', 80)->default('');
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->index('slug');
            });
        } else {
            $this->addColumns('video_tags', function (Blueprint $table) {
                if (! Schema::hasColumn('video_tags', 'slug')) {
                    $table->string('slug', 80)->default('');
                }
            });
        }

        if (! Schema::hasTable('video_tag_rel')) {
            Schema::create('video_tag_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('tag_id');
                $table->index('video_id');
                $table->index('tag_id');
            });
        }

        if (! Schema::hasTable('actors')) {
            Schema::create('actors', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 80);
                $table->string('slug', 80)->default('');
                $table->string('avatar', 255)->default('');
                $table->char('sex', 1)->default('');
                $table->string('area', 40)->default('');
                $table->string('birthday', 20)->default('');
                $table->text('content')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        } else {
            $this->addColumns('actors', function (Blueprint $table) {
                foreach (['slug', 'sex', 'area', 'birthday'] as $col) {
                    if (! Schema::hasColumn('actors', $col)) {
                        if ($col === 'sex') {
                            $table->char('sex', 1)->default('');
                        } elseif ($col === 'slug') {
                            $table->string('slug', 80)->default('');
                        } else {
                            $table->string($col, 40)->default('');
                        }
                    }
                }
                if (! Schema::hasColumn('actors', 'content')) {
                    $table->text('content')->nullable();
                }
            });
        }

        if (! Schema::hasTable('video_actor_rel')) {
            Schema::create('video_actor_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('actor_id');
                $table->unsignedTinyInteger('role_type')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->index('video_id');
                $table->index('actor_id');
            });
        }

        if (! Schema::hasTable('collect_sources')) {
            Schema::create('collect_sources', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 60);
                $table->string('api_url', 255)->default('');
                $table->string('api_type', 20)->default('json');
                $table->unsignedTinyInteger('mid')->default(1);
                $table->string('param', 255)->default('');
                $table->text('bind_json')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('last_collect_at')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        } else {
            $this->addColumns('collect_sources', function (Blueprint $table) {
                if (! Schema::hasColumn('collect_sources', 'api_url')) {
                    $table->string('api_url', 255)->default('');
                }
                if (! Schema::hasColumn('collect_sources', 'api_type')) {
                    $table->string('api_type', 20)->default('json');
                }
                if (! Schema::hasColumn('collect_sources', 'mid')) {
                    $table->unsignedTinyInteger('mid')->default(1);
                }
                if (! Schema::hasColumn('collect_sources', 'param')) {
                    $table->string('param', 255)->default('');
                }
                if (! Schema::hasColumn('collect_sources', 'bind_json')) {
                    $table->text('bind_json')->nullable();
                }
                if (! Schema::hasColumn('collect_sources', 'last_collect_at')) {
                    $table->unsignedInteger('last_collect_at')->default(0);
                }
            });
        }

        if (! Schema::hasTable('video_topics')) {
            Schema::create('video_topics', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 120);
                $table->string('slug', 80)->default('');
                $table->string('cover', 1024)->default('');
                $table->string('blurb', 255)->default('');
                $table->mediumText('content')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unsignedInteger('created_at')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_topic_rel')) {
            Schema::create('video_topic_rel', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('topic_id');
                $table->unsignedInteger('video_id');
                $table->unsignedInteger('sort')->default(0);
                $table->index('topic_id');
                $table->index('video_id');
            });
        }

        if (! Schema::hasTable('video_players')) {
            Schema::create('video_players', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 40);
                $table->string('name', 60);
                $table->text('parse')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('sort')->default(0);
                $table->unique('code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_players');
        Schema::dropIfExists('video_topic_rel');
        Schema::dropIfExists('video_topics');
        Schema::dropIfExists('video_actor_rel');
        Schema::dropIfExists('video_tag_rel');
        Schema::dropIfExists('video_episodes');
        Schema::dropIfExists('video_sources');
        Schema::dropIfExists('video_stats');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('video_tags');
        Schema::dropIfExists('video_types');
        Schema::dropIfExists('actors');
        Schema::dropIfExists('collect_sources');
    }

    private function addColumns(string $table, callable $callback): void
    {
        Schema::table($table, $callback);
    }
};

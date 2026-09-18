<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_monitor_min')) {
            Schema::create('video_monitor_min', function (Blueprint $table) {
                $table->string('metric_key', 64);
                $table->unsignedInteger('stat_min');
                $table->unsignedTinyInteger('metric_type')->default(1);
                $table->decimal('metric_value', 18, 4)->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->unique(['metric_key', 'stat_min']);
                $table->index('stat_min');
            });
        }

        if (! Schema::hasTable('video_monitor_hour')) {
            Schema::create('video_monitor_hour', function (Blueprint $table) {
                $table->string('metric_key', 64);
                $table->unsignedInteger('stat_hour');
                $table->decimal('val_avg', 18, 4)->default(0);
                $table->decimal('val_max', 18, 4)->default(0);
                $table->decimal('val_min', 18, 4)->default(0);
                $table->decimal('val_sum', 20, 4)->default(0);
                $table->unsignedInteger('sample_cnt')->default(0);
                $table->unsignedInteger('updated_at')->default(0);
                $table->unique(['metric_key', 'stat_hour']);
                $table->index('stat_hour');
            });
        }

        if (! Schema::hasTable('video_monitor_state')) {
            Schema::create('video_monitor_state', function (Blueprint $table) {
                $table->string('state_key', 64)->primary();
                $table->bigInteger('state_num')->default(0);
                $table->text('state_val');
                $table->unsignedInteger('updated_at')->default(0);
            });
        }

        if (! Schema::hasTable('video_monitor_rules')) {
            Schema::create('video_monitor_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->default('');
                $table->string('metric_key', 64)->default('');
                $table->string('agg', 8)->default('avg');
                $table->unsignedInteger('window_min')->default(5);
                $table->string('op', 4)->default('gt');
                $table->float('threshold')->default(0);
                $table->unsignedInteger('for_min')->default(0);
                $table->unsignedInteger('silence_min')->default(30);
                $table->unsignedTinyInteger('status')->default(0);
                $table->string('hint', 255)->default('');
            });
        }

        if (! Schema::hasTable('video_monitor_events')) {
            Schema::create('video_monitor_events', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id')->default(0);
                $table->string('fingerprint', 32)->default('');
                $table->unsignedTinyInteger('status')->default(1);
                $table->string('message', 500)->default('');
                $table->float('value')->default(0);
                $table->unsignedInteger('opened_at')->default(0);
                $table->unsignedInteger('closed_at')->default(0);
                $table->unsignedInteger('acked_at')->default(0);
                $table->index(['rule_id', 'status']);
                $table->index('fingerprint');
            });
        }

        $this->seedOptions();
        $this->seedRules();
    }

    public function down(): void
    {
        Schema::dropIfExists('video_monitor_events');
        Schema::dropIfExists('video_monitor_rules');
        Schema::dropIfExists('video_monitor_state');
        Schema::dropIfExists('video_monitor_hour');
        Schema::dropIfExists('video_monitor_min');
    }

    private function seedOptions(): void
    {
        if (! Schema::hasTable('video_options')) {
            return;
        }
        $now = time();
        $rows = [
            'monitor_enabled' => '1',
            'monitor_slow_ms' => '1000',
            'monitor_retain_min_days' => '3',
            'monitor_retain_hour_days' => '90',
            'monitor_access_cc' => '120',
        ];
        foreach ($rows as $k => $v) {
            $exists = DB::table('video_options')->where('k', $k)->exists();
            if ($exists) {
                continue;
            }
            DB::table('video_options')->insert([
                'k' => $k,
                'v' => $v,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedRules(): void
    {
        if (DB::table('video_monitor_rules')->count() > 0) {
            return;
        }
        DB::table('video_monitor_rules')->insert([
            [
                'name' => 'CPU 持续偏高',
                'metric_key' => 'sys.cpu.pct',
                'agg' => 'avg',
                'window_min' => 5,
                'op' => 'gt',
                'threshold' => 85,
                'for_min' => 5,
                'silence_min' => 30,
                'status' => 0,
                'hint' => 'Windows 上常常采不到 CPU，请先看性能页有没有这条曲线再开。',
            ],
            [
                'name' => '内存占用偏高',
                'metric_key' => 'sys.mem.used_pct',
                'agg' => 'avg',
                'window_min' => 5,
                'op' => 'gt',
                'threshold' => 90,
                'for_min' => 5,
                'silence_min' => 30,
                'status' => 0,
                'hint' => '读不到内存时不会误报。默认停用。',
            ],
            [
                'name' => '磁盘占用偏高',
                'metric_key' => 'sys.disk.used_pct',
                'agg' => 'last',
                'window_min' => 1,
                'op' => 'gt',
                'threshold' => 90,
                'for_min' => 1,
                'silence_min' => 60,
                'status' => 0,
                'hint' => '按站点目录所在盘。默认停用。',
            ],
            [
                'name' => '5 分钟 5xx 偏多',
                'metric_key' => 'http.5xx',
                'agg' => 'sum',
                'window_min' => 5,
                'op' => 'gt',
                'threshold' => 20,
                'for_min' => 2,
                'silence_min' => 15,
                'status' => 0,
                'hint' => '计划任务跑起来后才有请求计数。默认停用。',
            ],
            [
                'name' => '5 分钟慢请求偏多',
                'metric_key' => 'http.slow',
                'agg' => 'sum',
                'window_min' => 5,
                'op' => 'gt',
                'threshold' => 30,
                'for_min' => 2,
                'silence_min' => 15,
                'status' => 0,
                'hint' => '慢请求按设置里的毫秒门槛。默认停用。',
            ],
        ]);
    }
};

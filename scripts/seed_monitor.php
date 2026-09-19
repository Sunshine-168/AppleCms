<?php

/**
 * One-shot: seed ~1h of monitor samples and refresh heartbeat.
 * Run: php artisan tinker --execute="require 'scripts/seed_monitor.php';"
 * Or: php scripts/seed_monitor.php (bootstraps app)
 */

use App\Services\Monitor\MonitorStore;
use App\Services\Monitor\MonitorTick;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tick = app(MonitorTick::class);
$now = time();
$seeded = 0;

for ($i = 59; $i >= 0; $i--) {
    $ts = $now - ($i * 60);
    $statMin = (int) (floor($ts / 60) * 60);
    $tick->run($ts, true);

    // Windows skips load/CPU; bump request volume so that chart has a curve.
    $req = 3 + (($i * 7) % 37) + (int) round(8 * sin($i / 5));
    MonitorStore::upsertMany($statMin, [
        ['k' => 'http.req', 't' => MonitorStore::TYPE_COUNTER, 'v' => max(1, $req)],
        ['k' => 'http.2xx', 't' => MonitorStore::TYPE_COUNTER, 'v' => max(1, $req - 1)],
    ]);
    $seeded++;
}

$live = $tick->run($now, true);
echo json_encode([
    'seeded_minutes' => $seeded,
    'last_tick' => $live,
    'heartbeat' => App\Services\Monitor\MonitorState::getNum('cron_heartbeat', 0),
    'min_rows' => Illuminate\Support\Facades\DB::table('video_monitor_min')->count(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;

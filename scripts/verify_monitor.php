<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = app(App\Services\Monitor\MonitorService::class);
$p = $svc->seriesPayload('1h');
$hb = App\Services\Monitor\MonitorState::getNum('cron_heartbeat', 0);

foreach (($p['groups'] ?? []) as $g) {
    $n = 0;
    foreach (($g['series'] ?? []) as $s) {
        $n += count($s['points'] ?? []);
    }
    echo ($g['id'] ?? '?')."\t".($g['label'] ?? '')."\tpoints=".$n."\n";
}

echo 'heartbeat='.$hb.' age='.($hb > 0 ? (time() - $hb) : -1)."s\n";
echo 'min_rows='.Illuminate\Support\Facades\DB::table('video_monitor_min')->count()."\n";

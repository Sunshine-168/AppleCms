<?php
$j = json_decode((string) file_get_contents(__DIR__.'/_rest_aligned.json'), true);
foreach ($j as $r) {
    if (($r['zh'] ?? '') !== '') {
        continue;
    }
    $cur = preg_replace('/\s+/', ' ', (string) ($r['cur'] ?? ''));
    echo $r['key']."\t".$r['file'].':'.$r['line']."\t".$cur."\n";
}

<?php
$j = json_decode((string) file_get_contents(__DIR__.'/_rest_aligned.json'), true);
foreach ($j as $r) {
    $zh = (string) ($r['zh'] ?? '');
    $bad = $zh === '' || str_contains($zh, '$') || str_contains($zh, '{{') || str_contains($zh, 'href=') || str_contains($zh, '@if');
    echo ($bad ? 'BAD' : 'OK ').' '.$r['key']."\t".$zh."\n";
}

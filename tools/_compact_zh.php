<?php
$j = json_decode((string) file_get_contents(__DIR__.'/_rest_aligned.json'), true);
$out = [];
foreach ($j as $r) {
    $out[$r['key']] = [
        'zh' => $r['zh'],
        'src' => $r['src'],
        'file' => $r['file'],
        'cur' => $r['cur'],
        'tsv' => $r['tsv'],
    ];
}
file_put_contents(__DIR__.'/_rest_zh_compact.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo count($out).PHP_EOL;

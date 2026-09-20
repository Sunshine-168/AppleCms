<?php

$root = dirname(__DIR__);
$en = include $root.'/resources/lang/en/admin.php';
$es = include $root.'/resources/lang/es/admin.php';

function flatten(array $a, string $p = ''): array
{
    $o = [];
    foreach ($a as $k => $v) {
        $kk = $p === '' ? (string) $k : $p.'.'.$k;
        if (is_array($v)) {
            $o += flatten($v, $kk);
        } else {
            $o[$kk] = (string) $v;
        }
    }

    return $o;
}

$fe = flatten($en);
$fs = flatten($es);
$need = [];
foreach ($fe as $k => $v) {
    if (! isset($fs[$k]) || $fs[$k] === $v) {
        $need[$v] = $k;
    }
}
$phrases = array_keys($need);
sort($phrases);
file_put_contents($root.'/tools/locale-remain-en.json', json_encode($phrases, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo 'remain unique='.count($phrases).PHP_EOL;

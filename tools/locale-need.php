<?php

$root = dirname(__DIR__);

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

$en = flatten(include $root.'/resources/lang/en/admin.php');
$es = flatten(include $root.'/resources/lang/es/admin.php');
$need = [];
foreach ($en as $k => $v) {
    if (! isset($es[$k]) || $es[$k] === $v) {
        $need[$v] = true;
    }
}
$phrases = array_keys($need);
sort($phrases);
file_put_contents($root.'/tools/locale-need-en.json', json_encode($phrases, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo 'unique EN to translate='.count($phrases).PHP_EOL;

$f = fopen('php://stderr', 'w');
foreach (['intl', 'mbstring'] as $ext) {
    echo $ext.'='.(extension_loaded($ext) ? '1' : '0').PHP_EOL;
}
$t = function_exists('transliterator_create') ? transliterator_create('Han-Traditional') : null;
echo 'han='.($t ? $t->transliterate('后台分类采集默认信息') : 'no').PHP_EOL;

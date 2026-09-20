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

$phrases = [];
$missingKeys = [];
foreach ($en as $k => $v) {
    if (! isset($es[$k])) {
        $missingKeys[$k] = $v;
        continue;
    }
    if ($es[$k] === $v) {
        $phrases[$v] = ($phrases[$v] ?? 0) + 1;
    }
}
arsort($phrases);
echo 'unique leftover phrases='.count($phrases).' missingKeys='.count($missingKeys).PHP_EOL;
echo PHP_EOL.'--- top 40 leftover phrases ---'.PHP_EOL;
$i = 0;
foreach ($phrases as $p => $n) {
    echo $n."\t".$p.PHP_EOL;
    if (++$i >= 40) {
        break;
    }
}
echo PHP_EOL.'--- missing keys ---'.PHP_EOL;
foreach ($missingKeys as $k => $v) {
    echo $k.' => '.$v.PHP_EOL;
}

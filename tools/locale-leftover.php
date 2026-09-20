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
$keep = ['Admin', 'OK', 'URL', 'UA', 'IP', 'PHP', 'Laravel', 'WeChat', 'ArtPlayer', 'DPlayer', 'Video.js', 'HLS', 'FLV', 'MP4', 'MIT', 'Epay', 'DfPay', 'm3u8', 'JSON', 'RSS', 'HTML', 'API', 'CDN', 'SEO', 'VIP', '#ff6600', '.', ':n', '1×'];

foreach (['de', 'es', 'fr', 'ko', 'pt'] as $code) {
    $m = flatten(include $root.'/resources/lang/'.$code.'/admin.php');
    echo "==== $code leftover ====\n";
    $n = 0;
    foreach ($en as $k => $v) {
        if (($m[$k] ?? null) !== $v) {
            continue;
        }
        $brand = in_array($v, $keep, true) || preg_match('/^(API|PHP|URL|OK)/', $v);
        if ($brand) {
            continue;
        }
        echo $k.' => '.$v.PHP_EOL;
        if (++$n >= 40) {
            echo "...\n";
            break;
        }
    }
    echo "shown=$n\n\n";
}

$zh = flatten(include $root.'/resources/lang/zh_cn/admin.php');
echo 'nav.sites zh='.($zh['nav.sites'] ?? 'ABSENT').' en='.($en['nav.sites'] ?? 'ABSENT').PHP_EOL;

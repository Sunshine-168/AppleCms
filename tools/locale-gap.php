<?php

$root = dirname(__DIR__);
$codes = ['de', 'en', 'es', 'fr', 'ja', 'ko', 'pt', 'zh_cn', 'zh_tw'];

function flatten(array $a, string $p = ''): array
{
    $o = [];
    foreach ($a as $k => $v) {
        $kk = $p === '' ? (string) $k : $p.'.'.$k;
        if (is_array($v)) {
            $o += flatten($v, $kk);
        } else {
            $o[$kk] = is_scalar($v) || $v === null ? (string) $v : json_encode($v);
        }
    }

    return $o;
}

$maps = [];
foreach ($codes as $code) {
    $maps[$code] = flatten(include $root.'/resources/lang/'.$code.'/admin.php');
}
$en = $maps['en'];
$zh = $maps['zh_cn'];

echo "en=".count($en)." zh_cn=".count($zh).PHP_EOL;
foreach ($codes as $code) {
    if ($code === 'en') {
        continue;
    }
    $m = $maps[$code];
    $missing = 0;
    $sameEn = 0;
    $uiSame = 0;
    $uiMissing = 0;
    $groups = [];
    foreach ($en as $k => $v) {
        $g = explode('.', $k, 2)[0];
        if (! isset($m[$k])) {
            $missing++;
            if (str_starts_with($k, 'ui.')) {
                $uiMissing++;
            }
            $groups[$g] = ($groups[$g] ?? 0) + 1;
            continue;
        }
        if ($m[$k] === $v) {
            $sameEn++;
            if (str_starts_with($k, 'ui.')) {
                $uiSame++;
            }
            $groups[$g] = ($groups[$g] ?? 0) + 1;
        }
    }
    arsort($groups);
    $top = [];
    $i = 0;
    foreach ($groups as $g => $n) {
        $top[] = $g.':'.$n;
        if (++$i >= 8) {
            break;
        }
    }
    echo sprintf(
        "%s keys=%d missing=%d sameEN=%d uiMissing=%d uiSameEN=%d leftover=%s\n",
        str_pad($code, 6),
        count($m),
        $missing,
        $sameEn,
        $uiMissing,
        $uiSame,
        implode(', ', $top)
    );
}

$sample = [
    'ui.add_video', 'ui.recycle', 'ui.ph_title', 'ui.types', 'ui.status',
    'ui.search', 'ui.reset', 'ui.more_filters', 'ui.all', 'ui.off',
    'ui.no_url', 'ui.no_cover', 'ui.duplicate', 'ui.fill_tools',
    'ui.videos', 'ui.col_points', 'ui.col_updated', 'ui.actions',
    'ui.empty_videos', 'ui.empty_videos_hint', 'ui.go_collect',
    'page.videos', 'nav.videos', 'nav.sites',
];
echo PHP_EOL.'--- es samples ---'.PHP_EOL;
foreach ($sample as $k) {
    echo $k.' => '.($maps['es'][$k] ?? 'ABSENT').PHP_EOL;
}

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
$skipExact = [
    'https://', 'http', 'artplayer', '#ff6600', '.', ':n', '1×', 'OK', 'URL', 'UA', 'IP',
    'PHP', 'Laravel', 'WeChat', 'ArtPlayer', 'DPlayer', 'Video.js', 'HLS', 'FLV', 'MP4',
    'MIT', 'Epay', 'DfPay', 'm3u8', 'JSON', 'RSS', 'HTML', 'API', 'CDN', 'SEO', 'VIP',
    'Admin', 'Manga', 'Danmaku', 'Alipay', 'EPay', 'ID :id', 'UV :n', ':label :pct%',
    ':used / :total', ':have / :total eps', ':n CNY', ':n fen', 'https://jx.example.com/?url={url}',
    'https://play.example.com/hls', 'https://dl.example/get?u={url}',
];

foreach (['de', 'es', 'fr', 'ja', 'ko', 'pt', 'zh_tw'] as $code) {
    $m = flatten(include $root.'/resources/lang/'.$code.'/admin.php');
    $out = [];
    foreach ($en as $k => $v) {
        if (($m[$k] ?? null) !== $v) {
            continue;
        }
        if (in_array($v, $skipExact, true) || str_starts_with($v, 'https://') || str_starts_with($v, 'http')) {
            continue;
        }
        if (str_contains($v, 'example.com')) {
            continue;
        }
        $out[$k] = $v;
    }
    echo $code.' leftover='.count($out).PHP_EOL;
    file_put_contents($root.'/tools/overlays/_left_'.$code.'.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

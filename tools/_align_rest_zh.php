<?php

$root = dirname(__DIR__);
chdir($root);

$ctx = json_decode((string) file_get_contents($root.'/tools/_rest_ctx.json'), true);
if (! is_array($ctx)) {
    fwrite(STDERR, "no ctx\n");
    exit(1);
}

function head_path(string $root, string $rel): ?string
{
    $name = str_replace(['/', '\\'], '__', $rel);
    $p = $root.'/tools/_head_views/'.$name;
    return is_file($p) ? $p : null;
}

function cjk_bits(string $s): array
{
    $s = preg_replace('/\{\{[^}]+\}\}/', '', $s) ?? $s;
    $s = preg_replace('/@\w+[^\n]*/', '', $s) ?? $s;
    $out = [];
    if (preg_match_all('/>([^<]*[\x{4e00}-\x{9fff}][^<]*)</u', $s, $m)) {
        foreach ($m[1] as $t) {
            $t = trim(html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($t !== '') {
                $out[] = $t;
            }
        }
    }
    if (preg_match_all("/(?:placeholder|title|aria-label|data-[a-z-]+)=\"([^\"]*[\x{4e00}-\x{9fff}][^\"]*)\"/u", $s, $m2)) {
        foreach ($m2[1] as $t) {
            $t = trim($t);
            if ($t !== '') {
                $out[] = $t;
            }
        }
    }
    if (preg_match_all("/'(?:[^']*[\x{4e00}-\x{9fff}][^']*)'/u", $s, $m3)) {
        foreach ($m3[0] as $t) {
            $t = trim($t, "'");
            if ($t !== '' && ! str_contains($t, '$') && ! str_contains($t, '{{')) {
                $out[] = $t;
            }
        }
    }

    return array_values(array_unique($out));
}

function signatures(string $line): array
{
    $sigs = [];
    foreach (['id', 'for', 'name', 'href'] as $attr) {
        if (preg_match('/'.$attr.'="([^"]+)"/', $line, $m)) {
            $sigs[] = $attr.'="'.$m[1].'"';
        }
    }
    if (preg_match("/admin_t\('([^']+)'/", $line, $m)) {
        // keep surrounding tag name
    }
    if (preg_match('/<(label|h[1-6]|button|span|p|strong|a|option|th|td|legend)[\s>]/', $line, $m)) {
        $sigs[] = '<'.$m[1];
    }

    return $sigs;
}

$rows = [];
foreach ($ctx as $key => $info) {
    $tsv = is_string($info['tsv'] ?? null) ? $info['tsv'] : '';
    $tsvOk = $tsv !== '' && ! str_contains($tsv, '$') && ! str_contains($tsv, '{{') && ! str_contains($tsv, 'href=') && mb_strlen($tsv) < 200;
    $hit = $info['hits'][0] ?? null;
    $file = $hit['file'] ?? '';
    $lineText = $hit['line_text'] ?? '';
    $snip = $hit['snip'] ?? '';
    $zh = $tsvOk ? $tsv : '';
    $src = $tsvOk ? 'tsv' : '';
    $headMatch = '';

    $hp = $file !== '' ? head_path($root, $file) : null;
    if ($hp) {
        $hsrc = (string) file_get_contents($hp);
        $hsrc = preg_replace('/^fatal:.*\n/', '', $hsrc, 1) ?? $hsrc;
        $sigs = signatures($lineText);
        $best = '';
        $bestScore = 0;
        foreach (preg_split("/\r\n|\n|\r/", $hsrc) as $hl) {
            if (! preg_match('/[\x{4e00}-\x{9fff}]/u', $hl)) {
                continue;
            }
            $score = 0;
            foreach ($sigs as $sg) {
                if ($sg !== '' && str_contains($hl, $sg)) {
                    $score += 3;
                }
            }
            // id from key-ish
            $short = preg_replace('/^ui\./', '', $key) ?? $key;
            if (str_contains($hl, $short)) {
                $score += 1;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $hl;
            }
        }
        if ($best !== '') {
            $headMatch = trim($best);
            $bits = cjk_bits($best);
            if ($zh === '' && count($bits) === 1) {
                $zh = $bits[0];
                $src = 'head-cjk';
            } elseif ($zh === '' && $bits !== []) {
                $zh = $bits[0];
                $src = 'head-multi';
            }
        }
    }

    $rows[] = [
        'key' => $key,
        'zh' => $zh,
        'src' => $src,
        'file' => $file,
        'line' => $hit['line'] ?? 0,
        'cur' => trim($lineText),
        'head' => $headMatch,
        'snip' => $snip,
        'tsv' => $tsv,
    ];
}

file_put_contents($root.'/tools/_rest_aligned.json', json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
$empty = 0;
foreach ($rows as $r) {
    if ($r['zh'] === '') {
        $empty++;
    }
}
echo 'rows='.count($rows).' empty_zh='.$empty.PHP_EOL;

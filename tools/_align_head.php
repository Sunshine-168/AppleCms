<?php

/** Map admin_t keys to HEAD Chinese by walking matching HTML skeletons. */

$root = dirname(__DIR__);
$dir = $root.'/tools/_head_views';
$out = [];

function tokens(string $src, bool $keys): array
{
    $src = preg_replace('/@php.*?@endphp/s', '', $src) ?? $src;
    $src = preg_replace('/@push\(.*?\)(.*?)@endpush/s', '$1', $src) ?? $src;
    $src = preg_replace('/@section\(\'title\'.*?\)/', '', $src) ?? $src;
    $src = preg_replace('/\{\{\s*--.*?--\s*\}\}/s', '', $src) ?? $src;
    $src = preg_replace('/{{--.*?--}}/s', '', $src) ?? $src;
    $src = preg_replace('/<!--.*?-->/s', '', $src) ?? $src;
    $list = [];
    if ($keys) {
        if (preg_match_all("/admin_t\(\s*'([^']+)'(?:\s*,\s*\[[^\]]*\])?\s*\)/", $src, $m)) {
            foreach ($m[1] as $k) {
                if (! str_ends_with($k, '_') && ! str_ends_with($k, '.')) {
                    $list[] = $k;
                }
            }
        }

        return $list;
    }
    $src = preg_replace('/\{\{.*?\}\}/s', ' ', $src) ?? $src;
    $src = preg_replace('/\{!!.*?!!\}/s', ' ', $src) ?? $src;
    $src = preg_replace('/@\w+(\([^)]*\))?/', ' ', $src) ?? $src;
    if (preg_match_all('/>([^<]*[\x{4e00}-\x{9fff}][^<]*)</u', $src, $m)) {
        foreach ($m[1] as $t) {
            $t = trim(html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $t = preg_replace('/\s+/u', ' ', $t) ?? $t;
            if ($t !== '') {
                $list[] = $t;
            }
        }
    }
    if (preg_match_all('/(?:placeholder|title|aria-label|data-confirm|lay-tips)=["\']([^"\']*[\x{4e00}-\x{9fff}][^"\']*)["\']/u', $src, $m2)) {
        foreach ($m2[1] as $t) {
            $t = trim($t);
            if ($t !== '') {
                $list[] = $t;
            }
        }
    }

    return $list;
}

foreach (glob($dir.'/*.blade.php') ?: [] as $dump) {
    $name = basename($dump);
    $rel = str_replace('__', '/', preg_replace('/\.blade\.php$/', '', $name));
    $rel = str_replace('.blade', '.blade.php', $rel.'.blade.php');
    $current = $root.'/'.$rel;
    if (! is_file($current)) {
        continue;
    }
    $head = (string) file_get_contents($dump);
    $cur = (string) file_get_contents($current);
    $keys = tokens($cur, true);
    $zh = tokens($head, false);
    if ($keys === [] || $zh === []) {
        continue;
    }
    if (count($keys) === count($zh)) {
        foreach ($keys as $i => $k) {
            if (! isset($out[$k])) {
                $out[$k] = $zh[$i];
            }
        }
        continue;
    }
    $n = min(count($keys), count($zh));
    for ($i = 0; $i < $n; $i++) {
        if (! isset($out[$keys[$i]])) {
            $out[$keys[$i]] = $zh[$i];
        }
    }
}

file_put_contents($root.'/tools/_align_pairs.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo 'pairs='.count($out).PHP_EOL;

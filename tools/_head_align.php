<?php

$root = dirname(__DIR__);
chdir($root);

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

function extract_admin_t(string $src): array
{
    preg_match_all("/admin_t\(\s*'([^']+)'/", $src, $m);

    return $m[1] ?? [];
}

function collect_files(string $root): array
{
    $files = [];
    foreach ([$root.'/resources/views/admin', $root.'/app', $root.'/plugins'] as $dir) {
        if (! is_dir($dir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), '.php')) {
                $files[] = $f->getPathname();
            }
        }
    }

    return $files;
}

function rel_path(string $root, string $path): string
{
    $path = str_replace('\\', '/', $path);
    $root = str_replace('\\', '/', $root);

    return ltrim(str_replace($root, '', $path), '/');
}

function cjk_line(string $line): ?string
{
    $line = trim($line);
    if ($line === '' || ! preg_match('/[\x{4e00}-\x{9fff}]/u', $line)) {
        return null;
    }
    if (preg_match('/>([^<]*[\x{4e00}-\x{9fff}][^<]*)</u', $line, $m)) {
        $s = trim($m[1]);
        if ($s !== '') {
            return $s;
        }
    }
    if (preg_match("/['\"]([^'\"]*[\x{4e00}-\x{9fff}][^'\"]*)['\"]/u", $line, $m)) {
        $s = trim($m[1]);
        if ($s !== '' && ! str_contains($s, '$') && ! str_contains($s, '{{')) {
            return $s;
        }
    }
    $plain = trim(strip_tags($line));
    $plain = preg_replace('/\{\{.*?\}\}/u', '', $plain) ?? $plain;
    $plain = trim($plain);
    if ($plain !== '' && preg_match('/[\x{4e00}-\x{9fff}]/u', $plain) && ! str_contains($plain, '$')) {
        return $plain;
    }

    return null;
}

function ids_of(string $chunk): array
{
    $ids = [];
    if (preg_match_all('/\b(?:name|id|for|data-tab|data-pane|data-upload|data-desk|href)="([^"]+)"/', $chunk, $m)) {
        $ids = array_merge($ids, $m[1]);
    }
    if (preg_match_all("/'([a-z0-9_]+)'\s*=>\s*admin_t\(/", $chunk, $m)) {
        $ids = array_merge($ids, $m[1]);
    }

    return array_values(array_unique($ids));
}

$zh = include $root.'/resources/lang/zh_cn/admin.php';
$have = flatten($zh);
$usedByFile = [];
foreach (collect_files($root) as $path) {
    $src = (string) file_get_contents($path);
    foreach (extract_admin_t($src) as $key) {
        if (str_ends_with($key, '_') || str_ends_with($key, '.')) {
            continue;
        }
        $usedByFile[$path][$key] = true;
    }
}

$missing = [];
foreach ($usedByFile as $path => $keys) {
    foreach (array_keys($keys) as $key) {
        if (! isset($have[$key])) {
            $missing[$key] = $missing[$key] ?? [];
            $missing[$key][] = $path;
        }
    }
}

$recovered = [];
foreach ($usedByFile as $path => $keys) {
    $need = [];
    foreach (array_keys($keys) as $key) {
        if (! isset($have[$key]) && ! isset($recovered[$key])) {
            $need[$key] = true;
        }
    }
    if ($need === []) {
        continue;
    }
    $rel = rel_path($root, $path);
    $head = [];
    exec('git show HEAD:'.escapeshellarg($rel).' 2>NUL', $head);
    if ($head === []) {
        continue;
    }
    $headText = implode("\n", $head);
    $cur = (string) file_get_contents($path);
    $curLines = preg_split("/\r\n|\n|\r/", $cur) ?: [];
    foreach ($curLines as $i => $line) {
        if (! preg_match_all("/admin_t\(\s*'([^']+)'/", $line, $km)) {
            continue;
        }
        $ctx = implode("\n", array_slice($curLines, max(0, $i - 2), 5));
        $ids = ids_of($ctx."\n".$line);
        foreach ($km[1] as $key) {
            if (! isset($need[$key]) || isset($recovered[$key])) {
                continue;
            }
            $found = null;
            foreach ($ids as $id) {
                if ($id === '' || strlen($id) < 2) {
                    continue;
                }
                foreach ($head as $hLine) {
                    if (! str_contains($hLine, $id)) {
                        continue;
                    }
                    $bit = cjk_line($hLine);
                    if ($bit) {
                        $found = $bit;
                        break 2;
                    }
                }
            }
            if ($found === null && count($km[1]) === 1) {
                $bit = cjk_line($line);
                // current already i18n; look at same line number in HEAD
                if (isset($head[$i])) {
                    $found = cjk_line($head[$i]);
                }
            }
            if (is_string($found) && $found !== '') {
                $recovered[$key] = $found;
            }
        }
    }
    // leftover: scan HEAD for quoted JS keys matching last segment
    foreach (array_keys($need) as $key) {
        if (isset($recovered[$key])) {
            continue;
        }
        $last = (string) substr($key, (int) strrpos($key, '.') + 1);
        if ($last === '' || ! preg_match('/^[a-z0-9_]+$/', $last)) {
            continue;
        }
        if (preg_match("/['\"]".preg_quote($last, '/')."['\"]\s*=>\s*['\"]([^'\"]*[\x{4e00}-\x{9fff}][^'\"]*)['\"]/u", $headText, $m)
            || preg_match('/\b'.preg_quote($last, '/').'\s*:\s*[\'"]([^\'"]*[\x{4e00}-\x{9fff}][^\'"]*)[\'"]/u', $headText, $m)) {
            $recovered[$key] = trim($m[1]);
        }
    }
}

$tsv = is_file($root.'/tools/_keys_from_diff.tsv') ? file($root.'/tools/_keys_from_diff.tsv', FILE_IGNORE_NEW_LINES) : [];
foreach ($tsv as $line) {
    if ($line === '' || ! str_contains($line, "\t")) {
        continue;
    }
    [$k, $v] = array_pad(explode("\t", $line, 2), 2, '');
    $k = trim($k);
    $v = trim($v);
    if ($k !== '' && $v !== '' && ! isset($recovered[$k]) && ! isset($have[$k])) {
        $recovered[$k] = $v;
    }
}

ksort($recovered);
$still = array_values(array_diff(array_keys($missing), array_keys($recovered), array_keys($have)));
sort($still);

file_put_contents($root.'/tools/_missing_pairs.json', json_encode([
    'recovered' => $recovered,
    'still' => $still,
    'missingCount' => count($missing),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo 'missing='.count($missing).' recovered='.count($recovered).' still='.count($still).PHP_EOL;
echo implode("\n", array_slice($still, 0, 80)).PHP_EOL;

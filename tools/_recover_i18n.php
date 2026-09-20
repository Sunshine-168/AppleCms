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

function collect_files(): array
{
    global $root;
    $files = [];
    $dirs = [
        $root.'/resources/views/admin',
        $root.'/app',
        $root.'/plugins',
    ];
    foreach ($dirs as $dir) {
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

function extract_admin_t(string $src): array
{
    preg_match_all("/admin_t\(\s*'([^']+)'/", $src, $m);

    return $m[1] ?? [];
}

function extract_cjk_bits(string $text): array
{
    $cands = [];
    if (preg_match_all('/>([^<]*[\x{4e00}-\x{9fff}][^<]*)</u', $text, $tags)) {
        $cands = array_merge($cands, $tags[1]);
    }
    if (preg_match_all("/['\"]([^'\"]*[\x{4e00}-\x{9fff}][^'\"]*)['\"]/u", $text, $quotes)) {
        $cands = array_merge($cands, $quotes[1]);
    }
    if (preg_match_all('/\{\{\s*([^}]*[\x{4e00}-\x{9fff}][^}]*)\s*\}\}/u', $text, $must)) {
        foreach ($must[1] as $bit) {
            $bit = trim($bit);
            if (! str_contains($bit, 'admin_t') && ! str_contains($bit, '$')) {
                $cands[] = $bit;
            }
        }
    }
    $out = [];
    foreach ($cands as $s) {
        $s = trim(html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $s = trim($s, " \t\n\r\0\x0B\"'`");
        if ($s === '' || str_contains($s, '$') || str_contains($s, '{{') || str_contains($s, '=>')) {
            continue;
        }
        if (! preg_match('/[\x{4e00}-\x{9fff}]/u', $s)) {
            continue;
        }
        $out[] = $s;
    }

    return array_values(array_unique($out));
}

$zh = include $root.'/resources/lang/zh_cn/admin.php';
$have = flatten($zh);
$used = [];
foreach (collect_files() as $path) {
    $src = (string) file_get_contents($path);
    foreach (extract_admin_t($src) as $key) {
        $used[$key] = true;
    }
}
$missing = [];
foreach (array_keys($used) as $key) {
    if (! isset($have[$key])) {
        $missing[$key] = true;
    }
}
ksort($missing);
echo 'used='.count($used).' missing='.count($missing).PHP_EOL;

$diff = (string) shell_exec('git diff --unified=5 -- resources/views/admin plugins app/Services/Admin');
$map = [];
$pairs = preg_split("/^(?=diff --git )/m", $diff);
$flush = static function (array $minus, array $plus) use (&$map) {
    $mText = implode("\n", $minus);
    $pText = implode("\n", $plus);
    if ($pText === '' || $mText === '') {
        return;
    }
    if (! preg_match_all("/admin_t\(\s*'([^']+)'/", $pText, $km)) {
        return;
    }
    $keys = [];
    foreach ($km[1] as $k) {
        if (! in_array($k, $keys, true)) {
            $keys[] = $k;
        }
    }
    $cands = extract_cjk_bits($mText);
    if ($cands === []) {
        if (preg_match_all('/[\x{4e00}-\x{9fff}][^<\n{]{0,40}/u', $mText, $raw)) {
            foreach ($raw[0] as $bit) {
                $bit = trim($bit);
                if ($bit !== '') {
                    $cands[] = $bit;
                }
            }
            $cands = array_values(array_unique($cands));
        }
    }
    if (count($keys) === count($cands) && count($keys) > 0) {
        foreach ($keys as $i => $k) {
            if (! isset($map[$k])) {
                $map[$k] = $cands[$i];
            }
        }

        return;
    }
    if (count($keys) === 1 && count($cands) >= 1) {
        $k = $keys[0];
        if (! isset($map[$k])) {
            usort($cands, static fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
            $map[$k] = $cands[0];
        }
    }
};

foreach ($pairs as $chunk) {
    if ($chunk === '') {
        continue;
    }
    $lines = preg_split("/\r\n|\n|\r/", $chunk);
    $minus = [];
    $plus = [];
    foreach ($lines as $line) {
        if (str_starts_with($line, '@@')) {
            $flush($minus, $plus);
            $minus = [];
            $plus = [];
            continue;
        }
        if (str_starts_with($line, '+++') || str_starts_with($line, '---') || str_starts_with($line, 'diff ') || str_starts_with($line, 'index ')) {
            continue;
        }
        if (str_starts_with($line, '-')) {
            $minus[] = substr($line, 1);
            continue;
        }
        if (str_starts_with($line, '+')) {
            $plus[] = substr($line, 1);
            continue;
        }
        $flush($minus, $plus);
        $minus = [];
        $plus = [];
    }
    $flush($minus, $plus);
}

$new = 0;
$out = '';
foreach ($map as $k => $v) {
    if (isset($have[$k])) {
        continue;
    }
    $new++;
    $out .= $k."\t".$v."\n";
}
file_put_contents($root.'/tools/_keys_from_diff.tsv', $out);
echo 'mapped='.count($map).' newMissingMapped='.$new.PHP_EOL;

$still = [];
foreach (array_keys($missing) as $k) {
    if (! isset($map[$k]) && ! isset($have[$k])) {
        $still[] = $k;
    }
}
echo 'stillMissing='.count($still).PHP_EOL;
foreach (array_slice($still, 0, 120) as $k) {
    echo $k.PHP_EOL;
}

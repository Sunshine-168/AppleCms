<?php
$root = dirname(__DIR__);
chdir($root);
$diff = shell_exec('git diff --unified=3 -- resources/views/admin plugins/*/resources/views/admin');
$zh = include $root.'/resources/lang/zh_cn/admin.php';

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
$have = flatten($zh);
$map = [];
$pairs = preg_split("/^(?=diff --git )/m", (string) $diff);
foreach ($pairs as $chunk) {
    if ($chunk === '') {
        continue;
    }
    $lines = preg_split("/\r\n|\n|\r/", $chunk);
    $minus = [];
    $plus = [];
    $flush = static function () use (&$minus, &$plus, &$map) {
        $mText = implode("\n", $minus);
        $pText = implode("\n", $plus);
        $minus = [];
        $plus = [];
        if ($pText === '' || $mText === '') {
            return;
        }
        if (! preg_match_all("/admin_t\(\s*'([^']+)'/", $pText, $km)) {
            return;
        }
        $keys = array_values(array_unique($km[1]));
        preg_match_all('/>([^<]*[\x{4e00}-\x{9fff}][^<]*)</u', $mText, $tags);
        preg_match_all("/['\"]([^'\"]*[\x{4e00}-\x{9fff}][^'\"]*)['\"]/u", $mText, $quotes);
        $cands = array_values(array_unique(array_merge($tags[1] ?? [], $quotes[1] ?? [])));
        $cands = array_values(array_filter($cands, static fn ($s) => trim($s) !== ''));
        if (count($keys) === 1 && count($cands) === 1) {
            $map[$keys[0]] = trim($cands[0]);
        }
    };
    foreach ($lines as $line) {
        if (str_starts_with($line, '@@')) {
            $flush();
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
        $flush();
    }
    $flush();
}

$out = '';
$n = 0;
$skipHave = 0;
foreach ($map as $k => $v) {
    if (isset($have[$k])) {
        $skipHave++;
        continue;
    }
    $n++;
    $out .= $k."\t".$v."\n";
}
file_put_contents($root.'/tools/_keys_from_diff.tsv', $out);
echo "strictMapped=".count($map)." new=$n alreadyHave=$skipHave\n";

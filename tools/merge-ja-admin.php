<?php

$root = dirname(__DIR__);
$en = include $root.'/resources/lang/en/admin.php';
$ja = include $root.'/resources/lang/ja/admin.php';
$base = include $root.'/tools/ja_admin_overlay.php';
$modules = include $root.'/tools/ja_module_overlay.php';
$rest = include $root.'/tools/ja_rest_overlay.php';

$keys = ($base['keys'] ?? []) + $modules + $rest;
$phrases = $base['phrases'] ?? [];

function fill_missing(array $en, array $ja): array
{
    foreach ($en as $k => $v) {
        if (is_array($v)) {
            $cur = is_array($ja[$k] ?? null) ? $ja[$k] : [];
            $ja[$k] = fill_missing($v, $cur);
            continue;
        }
        if (! array_key_exists($k, $ja)) {
            $ja[$k] = $v;
        }
    }

    return $ja;
}

function apply_overlay(array $data, array $keys, array $phrases, string $prefix = ''): array
{
    foreach ($data as $k => $v) {
        $path = $prefix === '' ? (string) $k : $prefix.'.'.$k;
        if (is_array($v)) {
            $data[$k] = apply_overlay($v, $keys, $phrases, $path);
            continue;
        }
        if (! is_string($v)) {
            continue;
        }
        if (isset($keys[$path])) {
            $data[$k] = $keys[$path];
            continue;
        }
        if (isset($phrases[$v])) {
            $data[$k] = $phrases[$v];
        }
    }

    return $data;
}

function export_php(mixed $v, int $level = 0): string
{
    $pad = str_repeat('    ', $level);
    $inner = str_repeat('    ', $level + 1);
    if (is_array($v)) {
        if ($v === []) {
            return '[]';
        }
        $list = array_is_list($v);
        $out = "[\n";
        foreach ($v as $k => $item) {
            $key = $list ? '' : var_export((string) $k, true).' => ';
            $out .= $inner.$key.export_php($item, $level + 1).",\n";
        }

        return $out.$pad.']';
    }

    return var_export($v, true);
}

$merged = apply_overlay(fill_missing($en, $ja), $keys, $phrases);
$out = "<?php\n\nreturn ".export_php($merged).";\n";
file_put_contents($root.'/resources/lang/ja/admin.php', $out);

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

$fe = flatten($en);
$fj = flatten($merged);
$same = 0;
$missing = 0;
$uiSame = 0;
foreach ($fe as $k => $v) {
    if (! isset($fj[$k])) {
        $missing++;
        continue;
    }
    if ($fj[$k] === $v) {
        $same++;
        if (str_starts_with($k, 'ui.')) {
            $uiSame++;
        }
    }
}

$check = [
    'ui.column', 'ui.manage', 'ui.all', 'ui.loose_column', 'ui.articles',
    'ui.recycle', 'ui.write_art', 'ui.search', 'ui.reset', 'ui.draft',
    'ui.published', 'ui.arts_lead', 'ui.no_match_content', 'ui.title_label',
    'ui.time', 'ui.actions', 'ui.empty_columns', 'ui.add_column', 'ui.ph_art',
    'nav.novel', 'nav.art_types', 'manga.op_ok', 'live.title',
];
echo 'en='.count($fe).' ja='.count($fj).' missing='.$missing.' sameEN='.$same.' uiSameEN='.$uiSame.PHP_EOL;
foreach ($check as $k) {
    echo $k.' => '.($fj[$k] ?? 'ABSENT').PHP_EOL;
}

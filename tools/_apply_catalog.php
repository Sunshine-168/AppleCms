<?php

$root = dirname(__DIR__);
$cat = [];
foreach (glob($root.'/tools/_catalog_*.php') ?: [] as $file) {
    $chunk = include $file;
    if (is_array($chunk)) {
        $cat = $chunk + $cat;
    }
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

function set_path(array &$data, string $path, string $val): void
{
    $parts = explode('.', $path);
    $ref = &$data;
    foreach ($parts as $i => $part) {
        if ($i === count($parts) - 1) {
            $ref[$part] = $val;

            return;
        }
        if (! isset($ref[$part]) || ! is_array($ref[$part])) {
            $ref[$part] = [];
        }
        $ref = &$ref[$part];
    }
}

$zh = include $root.'/resources/lang/zh_cn/admin.php';
$en = include $root.'/resources/lang/en/admin.php';
$n = 0;
$overlay = [];
$t = static fn (string $de, string $es, string $fr, string $ko, string $pt, string $ja): array => [
    'de' => $de, 'es' => $es, 'fr' => $fr, 'ko' => $ko, 'pt' => $pt, 'ja' => $ja,
];
foreach ($cat as $path => $row) {
    if (! is_array($row) || ($row['zh'] ?? '') === '') {
        continue;
    }
    set_path($zh, $path, $row['zh']);
    set_path($en, $path, $row['en'] ?? $row['zh']);
    $overlay[$path] = $t(
        (string) ($row['de'] ?? $row['en']),
        (string) ($row['es'] ?? $row['en']),
        (string) ($row['fr'] ?? $row['en']),
        (string) ($row['ko'] ?? $row['en']),
        (string) ($row['pt'] ?? $row['en']),
        (string) ($row['ja'] ?? $row['en']),
    );
    $n++;
}

$forceEn = [
    'ui.theme_lead' => 'Change the logo, nav, and head code. Ad slots and the tag wizard stay separate.',
    'ui.theme_lottie_hint' => 'The default theme has one top bar, not a light/dark Lottie pair.',
];
foreach ($forceEn as $path => $val) {
    set_path($en, $path, $val);
}

file_put_contents($root.'/resources/lang/zh_cn/admin.php', "<?php\n\nreturn ".export_php($zh).";\n");
file_put_contents($root.'/resources/lang/en/admin.php', "<?php\n\nreturn ".export_php($en).";\n");

$out = "<?php\n\n\$t = static fn (string \$de, string \$es, string \$fr, string \$ko, string \$pt, string \$ja): array => [\n";
$out .= "    'de' => \$de, 'es' => \$es, 'fr' => \$fr, 'ko' => \$ko, 'pt' => \$pt, 'ja' => \$ja,\n];\n\nreturn [\n";
foreach ($overlay as $path => $map) {
    $out .= '    '.var_export($path, true).' => $t('
        .var_export($map['de'], true).', '
        .var_export($map['es'], true).', '
        .var_export($map['fr'], true).', '
        .var_export($map['ko'], true).', '
        .var_export($map['pt'], true).', '
        .var_export($map['ja'], true)."),\n";
}
$out .= "];\n";
file_put_contents($root.'/tools/overlays/rest_keys_missing.php', $out);

echo "applied=$n\n";

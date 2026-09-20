<?php

$root = dirname(__DIR__);

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

function set_path(array &$data, string $path, string $val): bool
{
    $parts = explode('.', $path);
    if ($parts === [] || $parts[0] === '') {
        return false;
    }
    $ref = &$data;
    foreach ($parts as $i => $part) {
        if ($i === count($parts) - 1) {
            if (! array_key_exists($part, $ref)) {
                $ref[$part] = $val;

                return true;
            }

            return false;
        }
        if (! isset($ref[$part]) || ! is_array($ref[$part])) {
            $ref[$part] = [];
        }
        $ref = &$ref[$part];
    }

    return false;
}

$tsv = $root.'/tools/_i18n_new_keys.tsv';
$zh = include $root.'/resources/lang/zh_cn/admin.php';
$en = include $root.'/resources/lang/en/admin.php';
$added = 0;
$skip = 0;
foreach (file($tsv, FILE_IGNORE_NEW_LINES) as $line) {
    if ($line === '' || ! str_contains($line, "\t")) {
        continue;
    }
    [$path, $zhVal, $enVal] = array_pad(explode("\t", $line, 3), 3, '');
    $path = trim($path);
    if ($path === '') {
        continue;
    }
    if (set_path($zh, $path, $zhVal)) {
        $added++;
    } else {
        $skip++;
    }
    set_path($en, $path, $enVal);
}
file_put_contents($root.'/resources/lang/zh_cn/admin.php', "<?php\n\nreturn ".export_php($zh).";\n");
file_put_contents($root.'/resources/lang/en/admin.php', "<?php\n\nreturn ".export_php($en).";\n");
echo "added=$added skippedExisting=$skip\n";

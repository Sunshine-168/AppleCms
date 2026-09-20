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

function set_path(array &$data, string $path, string $val, bool $overwrite = false): bool
{
    $parts = explode('.', $path);
    $ref = &$data;
    foreach ($parts as $i => $part) {
        if ($i === count($parts) - 1) {
            if (! $overwrite && array_key_exists($part, $ref)) {
                return false;
            }
            $ref[$part] = $val;

            return true;
        }
        if (! isset($ref[$part]) || ! is_array($ref[$part])) {
            $ref[$part] = [];
        }
        $ref = &$ref[$part];
    }

    return false;
}

function en_from_key(string $path): string
{
    $bits = explode('.', $path);
    $last = str_replace('_', ' ', (string) end($bits));

    return ucfirst($last);
}

$zh = include $root.'/resources/lang/zh_cn/admin.php';
$en = include $root.'/resources/lang/en/admin.php';

$critical = [
    'ui.theme_via_tpl' => ['模板 → 外观', 'Templates → look'],
    'ui.theme_via_files' => ['模板 → 文件', 'Templates → files'],
    'ui.settings_look_hint_a' => ['改 Logo 和主色，不用改页面文件。图标、导航、页头代码在「', 'Change the logo and primary color without editing page files. Icons, nav, and head code are in “'],
    'ui.settings_look_hint_b' => ['」。皮肤文件在「', '”. Skin files are in “'],
    'ui.settings_look_hint_c' => ['」。', '”.'],
    'ui.settings_to_more' => ['站点设置 → 更多', 'Site settings → More'],
    'ui.site_settings' => ['站点设置', 'Site settings'],
    'ui.find' => ['查找', 'Find'],
    'ui.find_label' => ['查找：', 'Find:'],
    'ui.op_fail' => ['操作失败', 'Action failed'],
    'ui.op_ok' => ['操作成功', 'Done'],
    'ui.wizard_lead_1' => ['生成主题里能跑的 Blade 标签。选一种，改条件，复制到', 'Build Blade tags the theme can run. Pick one, change conditions, copy into '],
    'ui.wizard_lead_2' => ['里贴。没有苹果的', ' and paste. There is no Apple '],
    'ui.wizard_lead_3' => ['。给片子打标签请去', '. To tag titles go to '],
    'ui.wizard_lead_4' => ['。', '.'],
];
$added = 0;
foreach ($critical as $path => [$z, $e]) {
    if (set_path($zh, $path, $z, true)) {
        $added++;
    }
    set_path($en, $path, $e, true);
}

$tsv = $root.'/tools/_keys_from_diff.tsv';
if (is_file($tsv)) {
    foreach (file($tsv, FILE_IGNORE_NEW_LINES) as $line) {
        if ($line === '' || ! str_contains($line, "\t")) {
            continue;
        }
        [$path, $zhVal] = array_pad(explode("\t", $line, 2), 2, '');
        $path = trim($path);
        $zhVal = trim($zhVal);
        if ($path === '' || $zhVal === '') {
            continue;
        }
        if (str_contains($zhVal, '$') || str_contains($zhVal, '{{') || str_contains($zhVal, '=>')) {
            continue;
        }
        if (isset($critical[$path])) {
            continue;
        }
        if (set_path($zh, $path, $zhVal)) {
            $added++;
            set_path($en, $path, en_from_key($path));
        }
    }
}

file_put_contents($root.'/resources/lang/zh_cn/admin.php', "<?php\n\nreturn ".export_php($zh).";\n");
file_put_contents($root.'/resources/lang/en/admin.php', "<?php\n\nreturn ".export_php($en).";\n");
echo "added=$added\n";

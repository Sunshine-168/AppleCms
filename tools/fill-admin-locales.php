<?php

$root = dirname(__DIR__);
$locale = $argv[1] ?? 'all';
$locales = $locale === 'all' ? ['de', 'es', 'fr', 'ja', 'ko', 'pt', 'zh_tw'] : [$locale];

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

function fill_missing(array $src, array $dst): array
{
    foreach ($src as $k => $v) {
        if (is_array($v)) {
            $cur = is_array($dst[$k] ?? null) ? $dst[$k] : [];
            $dst[$k] = fill_missing($v, $cur);
            continue;
        }
        if (! array_key_exists($k, $dst)) {
            $dst[$k] = $v;
        }
    }

    return $dst;
}

function apply_phrases(array $data, array $phrases): array
{
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $data[$k] = apply_phrases($v, $phrases);
            continue;
        }
        if (! is_string($v)) {
            continue;
        }
        if (isset($phrases[$v])) {
            $data[$k] = $phrases[$v];
        }
    }

    return $data;
}

function apply_keys(array $data, array $keys, string $prefix = ''): array
{
    foreach ($data as $k => $v) {
        $path = $prefix === '' ? (string) $k : $prefix.'.'.$k;
        if (is_array($v)) {
            $data[$k] = apply_keys($v, $keys, $path);
            continue;
        }
        if (isset($keys[$path])) {
            $data[$k] = $keys[$path];
        }
    }

    return $data;
}

function set_key_paths(array $data, array $keys): array
{
    foreach ($keys as $path => $val) {
        if (! is_string($path) || $path === '' || ! is_string($val) || $val === '') {
            continue;
        }
        $parts = explode('.', $path);
        $ref = &$data;
        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $ref[$part] = $val;
                break;
            }
            if (! isset($ref[$part]) || ! is_array($ref[$part])) {
                $ref[$part] = [];
            }
            $ref = &$ref[$part];
        }
        unset($ref);
    }

    return $data;
}

function convert_tw(mixed $v)
{
    static $tr = null;
    if ($tr === null) {
        $tr = transliterator_create('Hans-Hant');
        if (! $tr) {
            throw new RuntimeException('Hans-Hant transliterator missing');
        }
    }
    $swap = [
        '默認' => '預設',
        '信息' => '資訊',
        '數據庫' => '資料庫',
        '數據' => '資料',
        '軟件' => '軟體',
        '視頻' => '影片',
        '緩存' => '快取',
        '登錄' => '登入',
        '鏈接' => '連結',
        '屏幕' => '螢幕',
        '回收站' => '回收桶',
        '布爾' => '布林',
        '文件夾' => '資料夾',
        '內存' => '記憶體',
        '客戶端' => '用戶端',
        '裏' => '裡',
    ];
    if (is_array($v)) {
        foreach ($v as $k => $item) {
            $v[$k] = convert_tw($item);
        }

        return $v;
    }
    if (! is_string($v) || $v === '') {
        return $v;
    }
    $out = $tr->transliterate($v);
    if (! is_string($out)) {
        $out = $v;
    }

    return strtr($out, $swap);
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

function load_overlay(string $file): array
{
    if (! is_file($file)) {
        return ['keys' => [], 'phrases' => []];
    }
    $data = include $file;

    return [
        'keys' => is_array($data['keys'] ?? null) ? $data['keys'] : [],
        'phrases' => is_array($data['phrases'] ?? null) ? $data['phrases'] : (is_array($data) && ! isset($data['keys']) ? $data : []),
    ];
}

$en = include $root.'/resources/lang/en/admin.php';
$zh = include $root.'/resources/lang/zh_cn/admin.php';
$fe = flatten($en);
$common = is_file($root.'/tools/overlays/common_phrases.php') ? include $root.'/tools/overlays/common_phrases.php' : [];
$seedKeys = is_file($root.'/tools/overlays/seed_keys.php') ? include $root.'/tools/overlays/seed_keys.php' : [];
$restKeys = is_file($root.'/tools/overlays/rest_keys.php') ? include $root.'/tools/overlays/rest_keys.php' : [];
$boardKeys = is_file($root.'/tools/overlays/rest_keys_boards.php') ? include $root.'/tools/overlays/rest_keys_boards.php' : [];
$chromeKeys = is_file($root.'/tools/overlays/rest_keys_chrome.php') ? include $root.'/tools/overlays/rest_keys_chrome.php' : [];
$detailKeys = is_file($root.'/tools/overlays/rest_keys_details.php') ? include $root.'/tools/overlays/rest_keys_details.php' : [];
$leftoverKeys = is_file($root.'/tools/overlays/rest_keys_leftover.php') ? include $root.'/tools/overlays/rest_keys_leftover.php' : [];
$missingKeys = is_file($root.'/tools/overlays/rest_keys_missing.php') ? include $root.'/tools/overlays/rest_keys_missing.php' : [];
$displayKeys = is_file($root.'/tools/overlays/rest_keys_display.php') ? include $root.'/tools/overlays/rest_keys_display.php' : [];
$boardsUiKeys = is_file($root.'/tools/overlays/rest_keys_boards_ui.php') ? include $root.'/tools/overlays/rest_keys_boards_ui.php' : [];
$loginCacheKeys = is_file($root.'/tools/overlays/rest_keys_login_cache.php') ? include $root.'/tools/overlays/rest_keys_login_cache.php' : [];
$userSafetyKeys = is_file($root.'/tools/overlays/rest_keys_user_safety.php') ? include $root.'/tools/overlays/rest_keys_user_safety.php' : [];
$tplPushKeys = is_file($root.'/tools/overlays/rest_keys_tpl_push.php') ? include $root.'/tools/overlays/rest_keys_tpl_push.php' : [];
$makeTypesKeys = is_file($root.'/tools/overlays/rest_keys_make_types.php') ? include $root.'/tools/overlays/rest_keys_make_types.php' : [];
$extraCfgKeys = is_file($root.'/tools/overlays/rest_keys_extra_cfg.php') ? include $root.'/tools/overlays/rest_keys_extra_cfg.php' : [];
$displayKeys = (is_array($displayKeys) ? $displayKeys : [])
    + (is_array($boardsUiKeys) ? $boardsUiKeys : [])
    + (is_array($loginCacheKeys) ? $loginCacheKeys : [])
    + (is_array($userSafetyKeys) ? $userSafetyKeys : [])
    + (is_array($tplPushKeys) ? $tplPushKeys : [])
    + (is_array($makeTypesKeys) ? $makeTypesKeys : [])
    + (is_array($extraCfgKeys) ? $extraCfgKeys : []);
$seedKeys = $displayKeys + $chromeKeys + $boardKeys + $detailKeys + $leftoverKeys + $missingKeys + $restKeys + $seedKeys;

foreach ($locales as $code) {
    $path = $root.'/resources/lang/'.$code.'/admin.php';
    $cur = is_file($path) ? include $path : [];
    if ($code === 'zh_tw') {
        $merged = convert_tw(fill_missing($zh, is_array($cur) ? $cur : []));
        // If a value is still English (copied ui), replace from converted zh_cn
        $zhTw = flatten($merged);
        $zhHansTw = flatten(convert_tw($zh));
        foreach ($fe as $k => $enVal) {
            $parts = explode('.', $k);
            $ref = &$merged;
            $ok = true;
            foreach ($parts as $part) {
                if (! is_array($ref) || ! array_key_exists($part, $ref)) {
                    $ok = false;
                    break;
                }
                $ref = &$ref[$part];
            }
            if ($ok && is_string($ref) && $ref === $enVal && isset($zhHansTw[$k])) {
                $ref = $zhHansTw[$k];
            }
            unset($ref);
        }
    } else {
        $overlay = load_overlay($root.'/tools/overlays/'.$code.'.php');
        $phrases = [];
        foreach ($common as $enPhrase => $map) {
            if (isset($map[$code]) && is_string($map[$code]) && $map[$code] !== '') {
                $phrases[$enPhrase] = $map[$code];
            }
        }
        $phrases = $overlay['phrases'] + $phrases;
        $keys = [];
        foreach ($seedKeys as $seedPath => $map) {
            if (isset($map[$code]) && is_string($map[$code]) && $map[$code] !== '') {
                $keys[$seedPath] = $map[$code];
            }
        }
        $keys = $overlay['keys'] + $keys;
        $merged = set_key_paths(apply_keys(apply_phrases(fill_missing($en, is_array($cur) ? $cur : []), $phrases), $keys), $keys);
    }
    $ok = file_put_contents($path, "<?php\n\nreturn ".export_php($merged).";\n");
    echo $code.' write='.($ok === false ? 'FAIL' : $ok).' path='.$path.PHP_EOL;

    $fm = flatten($merged);
    $missing = 0;
    $same = 0;
    $uiSame = 0;
    foreach ($fe as $k => $v) {
        if (! isset($fm[$k])) {
            $missing++;
            continue;
        }
        if ($fm[$k] === $v) {
            $same++;
            if (str_starts_with($k, 'ui.')) {
                $uiSame++;
            }
        }
    }
    echo $code.' keys='.count($fm).' missing='.$missing.' sameEN='.$same.' uiSameEN='.$uiSame.PHP_EOL;
}

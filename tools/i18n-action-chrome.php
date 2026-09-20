<?php

$root = dirname(__DIR__);
$dirs = [
    $root.'/resources/views/admin',
    $root.'/plugins',
];
$htmlPairs = [
    '>添加</button>' => '>{{ admin_t(\'ui.add\') }}</button>',
    '>删除</button>' => '>{{ admin_t(\'ui.delete\') }}</button>',
    '>保存</button>' => '>{{ admin_t(\'ui.save\') }}</button>',
    '>搜索</button>' => '>{{ admin_t(\'ui.search\') }}</button>',
    '>查询</button>' => '>{{ admin_t(\'ui.search\') }}</button>',
    '>重置</button>' => '>{{ admin_t(\'ui.reset\') }}</button>',
    '>保存设置</button>' => '>{{ admin_t(\'page.save\') }}</button>',
];

function walk(string $dir, array &$files): void
{
    foreach (scandir($dir) ?: [] as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $path = $dir.DIRECTORY_SEPARATOR.$name;
        if (is_dir($path)) {
            if (in_array($name, ['vendor', 'node_modules', 'cankao'], true)) {
                continue;
            }
            walk($path, $files);
            continue;
        }
        if (str_ends_with($name, '.blade.php')) {
            $files[] = $path;
        }
    }
}

$files = [];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        walk($dir, $files);
    }
}

$changed = 0;
foreach ($files as $file) {
    $src = file_get_contents($file);
    $out = strtr($src, $htmlPairs);
    $out = preg_replace_callback('/<script\b[^>]*>.*?<\/script>/is', function ($m) {
        $js = $m[0];
        $js = preg_replace('/(js-edit">)编辑(<\/a>)/u', '$1\' + AdminUi.t(\'edit\') + \'$2', $js);
        $js = preg_replace('/(js-del">)删除(<\/a>)/u', '$1\' + AdminUi.t(\'delete\') + \'$2', $js);

        return $js;
    }, $out);
    if ($out !== $src) {
        file_put_contents($file, $out);
        $changed++;
        echo substr($file, strlen($root) + 1).PHP_EOL;
    }
}
echo "changed=$changed\n";

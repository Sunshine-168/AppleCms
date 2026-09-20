<?php
$root = dirname(__DIR__);
chdir($root);
$files = [
    'resources/views/admin/video/settings.blade.php',
    'resources/views/admin/video/rewrite.blade.php',
    'resources/views/admin/video/wizard.blade.php',
    'resources/views/admin/video/templates.blade.php',
    'resources/views/admin/video/theme.blade.php',
];
foreach ($files as $rel) {
    $src = shell_exec('git show HEAD:'.escapeshellarg($rel));
    echo "==== $rel ====\n";
    if (! is_string($src) || $src === '') {
        echo "(empty)\n";
        continue;
    }
    if (preg_match_all('/^.*[\x{4e00}-\x{9fff}].*$/mu', $src, $m)) {
        echo implode("\n", array_slice($m[0], 0, 25))."\n";
    }
}

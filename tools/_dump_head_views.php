<?php
$root = dirname(__DIR__);
chdir($root);
$files = [
    'resources/views/admin/video/settings.blade.php',
    'resources/views/admin/video/rewrite.blade.php',
    'resources/views/admin/video/wizard.blade.php',
    'resources/views/admin/video/theme.blade.php',
    'resources/views/admin/video/templates.blade.php',
    'resources/views/admin/system/monitor/operate_logs.blade.php',
    'resources/views/admin/partials/log-filters.blade.php',
    'resources/views/admin/video/invites.blade.php',
    'resources/views/admin/video/cards.blade.php',
    'resources/views/admin/video/config_api.blade.php',
    'resources/views/admin/video/config_ai.blade.php',
    'resources/views/admin/video/config_collect.blade.php',
    'resources/views/admin/video/config_ip.blade.php',
    'resources/views/admin/video/config_interface.blade.php',
    'resources/views/admin/system/tools/cache.blade.php',
    'resources/views/admin/video/push.blade.php',
    'resources/views/admin/video/hub.blade.php',
    'resources/views/admin/video/images.blade.php',
    'resources/views/admin/system/monitor/login_logs.blade.php',
    'resources/views/admin/system/monitor/system_logs.blade.php',
    'resources/views/admin/video/batch_players.blade.php',
];
$dir = $root.'/tools/_head_views';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}
foreach ($files as $rel) {
    $out = [];
    exec('git show HEAD:'.str_replace('/', DIRECTORY_SEPARATOR, $rel).' 2>&1', $out, $code);
    if ($code !== 0) {
        exec('git show HEAD:'.$rel.' 2>&1', $out, $code);
    }
    $name = str_replace(['/', '\\'], '__', $rel);
    file_put_contents($dir.'/'.$name, implode("\n", $out));
    echo $rel.' bytes='.strlen(implode("\n", $out)).' code='.$code.PHP_EOL;
}

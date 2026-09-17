<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Workerman\Worker;
use GatewayWorker\Register;

Worker::$logFile = __DIR__ . '/register_win.log';

$config_raw = include __DIR__ . '/../config.php';
$config = [];
foreach ($config_raw as $item) {
    $config[$item['name']] = $item['value'];
}

$register = new Register('text://' . $config['register_address']);

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
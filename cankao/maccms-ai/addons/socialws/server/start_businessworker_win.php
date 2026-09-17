<?php

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/Events.php';

use Workerman\Worker;
use GatewayWorker\BusinessWorker;

Worker::$logFile = __DIR__ . '/businessworker_win.log';

$config_raw = include __DIR__ . '/../config.php';
$config = [];
foreach ($config_raw as $item) {
    $config[$item['name']] = $item['value'];
}

$worker = new BusinessWorker();
$worker->name = 'SocialwsBusiness';
$worker->count = 1;
$worker->eventHandler = 'addons\\socialws\\server\\Events';
$worker->registerAddress = $config['register_address'];

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
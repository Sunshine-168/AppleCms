<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Workerman\Worker;
use GatewayWorker\Gateway;

Worker::$logFile = __DIR__ . '/gateway_win.log';

$config_raw = include __DIR__ . '/../config.php';
$config = [];
foreach ($config_raw as $item) {
    $config[$item['name']] = $item['value'];
}

$gateway = new Gateway('websocket://0.0.0.0:' . (int)$config['port']);
$gateway->name = 'SocialwsGateway';
$gateway->count = 1;
$gateway->lanIp = explode(':', $config['register_address'])[0];
$gateway->startPort = 2900;
$gateway->pingInterval = (int)$config['heartbeat'];
$gateway->pingData = json_encode(['type' => 'ping']);
$gateway->registerAddress = $config['register_address'];

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
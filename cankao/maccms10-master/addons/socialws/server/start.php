<?php

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/Events.php';

use Workerman\Worker;
use GatewayWorker\Register;
use GatewayWorker\Gateway;
use GatewayWorker\BusinessWorker;

$config_raw = include __DIR__ . '/../config.php';
$config = [];
foreach ($config_raw as $item) {
    $config[$item['name']] = $item['value'];
}

$register = new Register('text://' . $config['register_address']);

$gateway = new Gateway('websocket://0.0.0.0:' . (int)$config['port']);
$gateway->name = 'SocialwsGateway';
$gateway->count = 4;
$gateway->lanIp = explode(':', $config['register_address'])[0];
$gateway->startPort = 2900;
$gateway->pingInterval = (int)$config['heartbeat'];
$gateway->pingData = json_encode(['type' => 'ping']);
$gateway->registerAddress = $config['register_address'];

$worker = new BusinessWorker();
$worker->name = 'SocialwsBusiness';
$worker->count = 4;
$worker->eventHandler = 'addons\\socialws\\server\\Events';
$worker->registerAddress = $config['register_address'];

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}

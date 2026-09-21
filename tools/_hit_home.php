<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');
try {
    $response = $kernel->handle($request);
    echo 'status='.$response->getStatusCode().PHP_EOL;
    echo substr($response->getContent(), 0, 1500).PHP_EOL;
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage().PHP_EOL;
    echo $e->getFile().':'.$e->getLine().PHP_EOL;
}

<?php

$root = dirname(__DIR__);
$need = json_decode(file_get_contents($root.'/tools/locale-remain-en.json'), true);
foreach (['de', 'es', 'fr', 'ko', 'pt'] as $code) {
    $file = $root.'/tools/overlays/'.$code.'.php';
    if (! is_file($file)) {
        echo $code." MISSING FILE\n";
        continue;
    }
    $data = include $file;
    $phrases = $data['phrases'] ?? $data;
    $hit = 0;
    $miss = 0;
    $sample = [];
    foreach ($need as $en) {
        if (isset($phrases[$en]) && $phrases[$en] !== '' && $phrases[$en] !== $en) {
            $hit++;
        } else {
            $miss++;
            if (count($sample) < 8) {
                $sample[] = $en;
            }
        }
    }
    echo $code.' phrases='.count($phrases).' hit='.$hit.' stillEN='.$miss.PHP_EOL;
    if ($sample) {
        echo '  miss: '.json_encode($sample, JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}

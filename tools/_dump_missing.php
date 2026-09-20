<?php
$j = json_decode(file_get_contents(__DIR__.'/_missing_pairs.json'), true);
$keys = array_merge(array_keys($j['recovered'] ?? []), $j['still'] ?? []);
$keys = array_values(array_unique($keys));
sort($keys);
file_put_contents(__DIR__.'/_missing_keys.txt', implode("\n", $keys)."\n");
echo count($keys).PHP_EOL;

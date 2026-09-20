<?php
$j = json_decode((string) file_get_contents(__DIR__.'/_rest_aligned.json'), true);
foreach ($j as $r) {
    echo $r['key']."\n";
}

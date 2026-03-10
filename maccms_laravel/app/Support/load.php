<?php

$files = glob(__DIR__ . '/*.php');

foreach ($files as $file)
{
    if ($file === __FILE__)
    {
        continue;
    }

    require_once $file;
}

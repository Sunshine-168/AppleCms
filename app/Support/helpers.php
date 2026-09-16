<?php

foreach (glob(__DIR__.'/Helpers/*.php') as $file)
{
    require_once $file;
}

if (! function_exists('admin_t')) {
    function admin_t(string $key, array $replace = []): string
    {
        $line = trans('admin.'.$key, $replace);

        return is_string($line) ? $line : $key;
    }
}

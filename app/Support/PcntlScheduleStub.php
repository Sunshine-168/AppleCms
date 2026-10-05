<?php

namespace Illuminate\Console\Scheduling;

/**
 * 宝塔 PHP 常加载 pcntl，却把 pcntl_signal 写进 disable_functions。
 * Laravel 只判断 extension_loaded('pcntl')，schedule:run 会在这里直接 fatal。
 */
if (! \function_exists('pcntl_signal') && ! \function_exists(__NAMESPACE__.'\\pcntl_signal')) {
    function pcntl_signal(int $signal, callable|int $handler, bool $restart_syscalls = true): bool
    {
        return true;
    }
}

if (! \function_exists('pcntl_async_signals') && ! \function_exists(__NAMESPACE__.'\\pcntl_async_signals')) {
    function pcntl_async_signals(?bool $enable = null): bool
    {
        return (bool) $enable;
    }
}

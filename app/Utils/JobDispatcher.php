<?php

namespace App\Utils;

use App\Jobs\SequenceJob;
use Closure;
use Illuminate\Support\Facades\Queue;

class JobDispatcher
{
    /**
     * 投递单任务
     * @param mixed $handler 任务类
     * @param array $payload 任务数据
     */
    public static function dispatchSingle(string $handler, array $payload = [], ?string $lock = null, int $lockTime = 60, ?Closure $callback = null, string $source = 'business', ?string $queue = null): void
    {
        $data = [
            'lock'          => $lock,
            'lock_timeout'  => $lockTime,
            'payload'       => $payload,
            'source'        => $source,
            'callback'      => $callback,
        ];

        // Ensure handler is a valid class
        if (class_exists($handler)) {
            $job = new $handler($data);
            if ($queue) {
                $job->onQueue($queue);
            }
            dispatch($job);
        }
    }

    /**
     * 投递顺序任务
     * @param array $steps 顺序步骤类数组
     * @param array $payload 任务数据
     */
    public static function dispatchSequence(array $steps, array $payload = [], ?string $lock = null, int $lockTime = 60, ?Closure $callback = null, string $source = 'business', ?string $queue = null): void
    {

        $data = [
            'steps'         => $steps,
            'current'       => 0,
            'lock'          => $lock,
            'lock_timeout'  => $lockTime,
            'payload'       => $payload,
            'source'        => $source,
            'callback'      => $callback,
        ];

        $job = new SequenceJob($data);
        if ($queue) {
            $job->onQueue($queue);
        }
        dispatch($job);
    }
}
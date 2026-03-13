<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;


/**
 * 测试
 */
class Test extends Command
{
    protected $signature   = 'test';
    protected $description = '测试';

    public function handle(): int
    {
        return 0;
    }


}

<?php

namespace App\Console;


use Illuminate\Console\Command;




/**
 * 秒级任务
 */
class Test extends Command
{
    protected $signature   = 'test';
    protected $description = '测试';

    public function handle(): int
    {

    }


}

<?php

namespace Tests\Unit;

use App\Services\Collect\CollectProgress;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CollectProgressTest extends TestCase
{
    public function test_ticks_created_updated_and_skipped(): void
    {
        Cache::flush();
        CollectProgress::start(9, '红牛资源');
        CollectProgress::tick(9, [
            'created' => 3,
            'updated' => 1,
            'skipped' => 2,
            'title' => '战狼',
            'action' => 'created',
            'page' => 1,
            'pages' => 4,
        ]);
        $row = CollectProgress::get(9);
        $this->assertSame('红牛资源', $row['name']);
        $this->assertSame(3, $row['created']);
        $this->assertSame(1, $row['updated']);
        $this->assertSame(2, $row['skipped']);
        $this->assertSame('战狼', $row['title']);
        $this->assertTrue($row['running']);
        CollectProgress::finish(9, ['msg' => '入库新建 3，更新 1，跳过 2']);
        $done = CollectProgress::get(9);
        $this->assertTrue($done['done']);
        $this->assertFalse($done['running']);
    }
}

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
        ], [
            'action' => 'created',
            'title' => '战狼',
            'msg' => 'ok',
            'page' => 1,
        ]);
        CollectProgress::tick(9, [
            'skipped' => 3,
            'title' => '未上架片',
            'action' => 'skipped',
        ], [
            'action' => 'skipped',
            'title' => '未上架片',
            'msg' => '未绑定分类',
            'page' => 1,
        ]);
        $row = CollectProgress::get(9);
        $this->assertSame('红牛资源', $row['name']);
        $this->assertSame(3, $row['created']);
        $this->assertSame(1, $row['updated']);
        $this->assertSame(3, $row['skipped']);
        $this->assertSame('未上架片', $row['title']);
        $this->assertTrue($row['running']);
        $this->assertCount(2, $row['lines']);
        $this->assertSame('created', $row['lines'][0]['action']);
        $this->assertSame('战狼', $row['lines'][0]['title']);
        $this->assertSame('skipped', $row['lines'][1]['action']);
        $this->assertSame('未绑定分类', $row['lines'][1]['msg']);
        CollectProgress::finish(9, ['msg' => '采集完成，共 1 页，新增 3，更新 1，跳过 3'], [
            'action' => 'done',
            'msg' => '采集完成，共 1 页，新增 3，更新 1，跳过 3',
        ]);
        $done = CollectProgress::get(9);
        $this->assertTrue($done['done']);
        $this->assertFalse($done['running']);
        $this->assertSame('done', $done['lines'][2]['action']);
    }
}

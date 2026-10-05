<?php

namespace Tests\Unit;

use App\Services\Monitor\MonitorCollector;
use Tests\TestCase;

class MonitorCpuTest extends TestCase
{
    public function test_parse_proc_stat_idle_and_total(): void
    {
        $raw = "cpu  100 20 30 800 50 1 2 0 0 0\ncpu0 50 10 15 400 25 0 1 0 0 0\n";
        $row = MonitorCollector::parseProcStat($raw);
        $this->assertNotNull($row);
        $this->assertSame(850, $row['idle']);
        $this->assertSame(1003, $row['total']);
    }

    public function test_cpu_percent_from_two_snapshots(): void
    {
        $prev = ['total' => 1000, 'idle' => 800, 'mode' => 'jiffies'];
        $cur = ['total' => 1200, 'idle' => 860, 'mode' => 'jiffies'];
        $this->assertSame(70.0, MonitorCollector::cpuPercent($prev, $cur));
    }

    public function test_cpu_percent_needs_jiffies_mode(): void
    {
        $this->assertNull(MonitorCollector::cpuPercent(null, ['total' => 10, 'idle' => 1, 'mode' => 'jiffies']));
        $this->assertNull(MonitorCollector::cpuPercent(
            ['total' => 10, 'idle' => 1],
            ['total' => 20, 'idle' => 2, 'mode' => 'jiffies']
        ));
    }
}

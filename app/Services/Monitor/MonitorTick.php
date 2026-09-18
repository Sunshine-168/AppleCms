<?php

namespace App\Services\Monitor;

class MonitorTick
{
    public const GATE_KEY = 'due.cron';

    public const GATE_SEC = 55;

    /**
     * @return array{ok:bool,skipped:bool,msg:string,stages:array<string, mixed>}
     */
    public function run(int $now = 0, bool $force = false): array
    {
        $now = $now > 0 ? $now : time();
        $stages = [];
        if (! MonitorService::enabled()) {
            return ['ok' => true, 'skipped' => true, 'msg' => '运行监控已关闭', 'stages' => $stages];
        }
        if (! $force && ! MonitorState::due(self::GATE_KEY, self::GATE_SEC, $now)) {
            return ['ok' => true, 'skipped' => true, 'msg' => '本分钟已跑过，跳过', 'stages' => ['locked' => 1]];
        }

        $stages['flush'] = $this->flush($now);
        $stages['collect'] = $this->collect($now);
        MonitorState::set('cron_heartbeat', $now, date('c', $now));
        $stages['heartbeat'] = 1;
        $stages['alert'] = AlertEngine::run($now);
        if ($force || MonitorState::due('due.rollup', 3600, $now)) {
            $hourStart = (int) (floor($now / 3600) * 3600) - 3600;
            $stages['rollup'] = MonitorStore::rollupHour($hourStart);
        }
        if ($force || MonitorState::due('due.purge', 86400, $now)) {
            $stages['purge'] = MonitorStore::purge(
                (int) MonitorService::option('monitor_retain_min_days', 3),
                (int) MonitorService::option('monitor_retain_hour_days', 90)
            );
            MonitorBucket::gc(3600);
        }

        return ['ok' => true, 'skipped' => false, 'msg' => '已采集', 'stages' => $stages];
    }

    private function flush(int $now): int
    {
        $written = 0;
        $buckets = MonitorBucket::drainClosed($now, 30) + MonitorBucket::peekOpen($now);
        foreach ($buckets as $statMin => $fields) {
            $rows = [];
            foreach ($fields as $k => $v) {
                $rows[] = ['k' => (string) $k, 't' => MonitorStore::TYPE_COUNTER, 'v' => (float) $v];
            }
            $written += MonitorStore::upsertMany((int) $statMin, $rows);
        }

        return $written;
    }

    private function collect(int $now): int
    {
        $res = MonitorCollector::collect();
        MonitorState::set('capabilities', $now, json_encode($res['skipped'], JSON_UNESCAPED_UNICODE) ?: '{}');
        $statMin = (int) (floor($now / 60) * 60);

        return MonitorStore::upsertMany($statMin, $res['metrics']);
    }
}

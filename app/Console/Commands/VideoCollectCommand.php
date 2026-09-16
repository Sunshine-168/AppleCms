<?php

namespace App\Console\Commands;

use App\Models\Video\CollectSourceModel;
use App\Services\Collect\CollectIngestService;
use Illuminate\Console\Command;

class VideoCollectCommand extends Command
{
    protected $signature = 'video:collect {id? : 采集源ID} {--page=1} {--pages=1} {--hours=0} {--all : 采集所有启用源}';

    protected $description = '按苹果 CMS 资源接口采集入库';

    public function handle(CollectIngestService $ingest): int
    {
        if ($this->argument('id')) {
            $ids = [(int) $this->argument('id')];
        } elseif ($this->option('all')) {
            $ids = CollectSourceModel::query()->where('status', 1)->pluck('id')->all();
        } else {
            $this->error('请传入采集源 ID，或加 --all');

            return self::FAILURE;
        }
        if ($ids === []) {
            $this->warn('没有可用采集源');

            return self::SUCCESS;
        }

        foreach ($ids as $id) {
            $this->info("采集源 #{$id}");
            $result = $ingest->run((int) $id, [
                'page' => (int) $this->option('page'),
                'pages' => (int) $this->option('pages'),
                'hours' => (int) $this->option('hours'),
            ]);
            $this->line(($result['msg'] ?? '').' '.json_encode($result['data']['page'] ?? [], JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }
}

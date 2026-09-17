<?php

namespace App\Console\Commands;

use App\Support\Plugins\PluginHost;
use App\Support\Plugins\PluginScheduleHandler;
use Illuminate\Console\Command;

class PluginRunCommand extends Command
{
    protected $signature = 'plugin:run {plugin : 插件 id} {job : plugin.json 里的任务 id}';

    protected $description = '跑插件在 plugin.json 里声明的定时任务';

    public function handle(PluginHost $host): int
    {
        $plugin = strtolower(trim((string) $this->argument('plugin')));
        $job = strtolower(trim((string) $this->argument('job')));
        foreach ($host->scheduleJobs() as $row) {
            if (($row['plugin'] ?? '') !== $plugin || ($row['id'] ?? '') !== $job) {
                continue;
            }
            $handler = (string) ($row['handler'] ?? '');
            if ($handler === '' || ! class_exists($handler) || ! is_a($handler, PluginScheduleHandler::class, true)) {
                $this->error('插件任务处理器无效');

                return self::FAILURE;
            }
            $out = trim((string) app($handler)->handle());
            if ($out !== '') {
                $this->line($out);
            }

            return self::SUCCESS;
        }
        $this->error('没有这条插件任务，或插件没启用');

        return self::FAILURE;
    }
}

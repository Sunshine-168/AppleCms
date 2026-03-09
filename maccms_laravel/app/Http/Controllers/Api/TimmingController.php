<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TimmingController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->query('name');
        $enforce = $request->query('enforce') == '1';
        $list = config('timming', []);
        $result = [];

        foreach ($list as $key => $task) {
            if (!empty($name) && ($task['name'] ?? null) !== $name) {
                continue;
            }

            $shouldRun = $this->shouldRun($task, $enforce);
            if ($shouldRun) {
                $list[$key]['runtime'] = time();
                $result[] = [
                    'name' => $task['name'] ?? $key,
                    'status' => 'executed',
                    'message' => $this->executeTask($task['file'] ?? '', $task['param'] ?? ''),
                ];
            } else {
                $result[] = [
                    'name' => $task['name'] ?? $key,
                    'status' => 'skipped',
                    'message' => '未到执行时间',
                ];
            }
        }

        $this->persistTasks($list);

        return response()->json([
            'code' => 1,
            'msg' => '执行完成',
            'data' => $result,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    protected function shouldRun(array $task, bool $enforce = false): bool
    {
        if (($task['status'] ?? '0') != '1') {
            return false;
        }

        if ($enforce) {
            return true;
        }

        $runtime = intval($task['runtime'] ?? 0);
        $oldWeek = $runtime ? date('w', $runtime) : null;
        $oldHour = $runtime ? date('H', $runtime) : null;
        $curWeek = date('w');
        $curHour = date('H');
        $weeks = explode(',', (string) ($task['weeks'] ?? ''));
        $hours = explode(',', (string) ($task['hours'] ?? ''));

        if ($runtime === 0) {
            return in_array((string) $curWeek, $weeks, true) && in_array($curHour, $hours, true);
        }

        return ($oldWeek . '-' . $oldHour) !== ($curWeek . '-' . $curHour)
            && in_array((string) $curWeek, $weeks, true)
            && in_array($curHour, $hours, true);
    }

    protected function executeTask(string $file, string $param): string
    {
        parse_str($param, $output);

        return match ($file) {
            'cache' => $this->clearCache(),
            'make' => '静态生成任务已触发（当前为兼容占位实现）',
            'collect' => '采集任务已触发（当前为兼容占位实现）',
            'cj' => '自定义采集任务已触发（当前为兼容占位实现）',
            'urlsend' => 'URL 推送任务已触发（当前为兼容占位实现）',
            default => '未知任务类型：' . $file,
        };
    }

    protected function clearCache(): string
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        return '缓存已清理';
    }

    protected function persistTasks(array $tasks): void
    {
        $content = "<?php\nreturn " . var_export($tasks, true) . ";\n";
        File::put(config_path('timming.php'), $content);
    }
}

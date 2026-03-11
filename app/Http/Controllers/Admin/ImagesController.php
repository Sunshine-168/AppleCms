<?php

namespace App\Http\Controllers\Admin;

use App\Services\ImageSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImagesController extends BaseController
{
    public function __construct(protected ImageSyncService $imageSyncService)
    {
        parent::__construct();
    }

    public function opt(Request $request)
    {
        $tab = $request->input('tab', 'vod');
        return view('admin.images.opt', compact('tab'));
    }

    public function sync(Request $request)
    {
        $param = $request->all();
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(10, intval($param['limit'] ?? 10));
        $flag = '#err' . date('Y-m-d');

        $tab = $param['tab'] ?? 'vod';
        $col = $param['col'] ?? 1; // 1=封面图, 2=内容中的图片

        $tableMap = [
            'vod' => ['table' => 'mac_vod', 'id' => 'vod_id', 'name' => 'vod_name', 'pic' => 'vod_pic', 'content' => 'vod_content', 'time' => 'vod_time'],
            'art' => ['table' => 'mac_art', 'id' => 'art_id', 'name' => 'art_name', 'pic' => 'art_pic', 'content' => 'art_content', 'time' => 'art_time'],
            'topic' => ['table' => 'mac_topic', 'id' => 'topic_id', 'name' => 'topic_name', 'pic' => 'topic_pic', 'content' => 'topic_content', 'time' => 'topic_time'],
            'actor' => ['table' => 'mac_actor', 'id' => 'actor_id', 'name' => 'actor_name', 'pic' => 'actor_pic', 'content' => 'actor_content', 'time' => 'actor_time'],
            'role' => ['table' => 'mac_role', 'id' => 'role_id', 'name' => 'role_name', 'pic' => 'role_pic', 'content' => 'role_content', 'time' => 'role_time'],
            'website' => ['table' => 'mac_website', 'id' => 'website_id', 'name' => 'website_name', 'pic' => 'website_pic', 'content' => 'website_content', 'time' => 'website_time'],
        ];
        
        if (!isset($tableMap[$tab])) {
            return $this->error('参数错误');
        }

        $tableInfo = $tableMap[$tab];
        $colPic = ($col == 2) ? $tableInfo['content'] : $tableInfo['pic'];
        $query = DB::table($tableInfo['table']);
        $query = $this->applySyncFilters($query, $tableInfo['time'], $colPic, $col, $param, $flag);
        $total = $query->count();

        $list = $query->orderBy($tableInfo['id'], 'asc')
                     ->skip(($param['page'] - 1) * $param['limit'])
                     ->take($param['limit'])
                     ->get();
        $results = [];
        $syncCount = 0;
        $failedCount = 0;

        foreach ($list as $item) {
            if ($col == 2) {
                $sync = $this->imageSyncService->syncContentImages((string) $item->{$colPic}, $tab);
                DB::table($tableInfo['table'])
                    ->where($tableInfo['id'], $item->{$tableInfo['id']})
                    ->update([$colPic => $sync['content']]);

                $syncCount += $sync['success'];
                $failedCount += $sync['failed'];
                $results[] = [
                    'name' => $item->{$tableInfo['name']},
                    'id' => $item->{$tableInfo['id']},
                    'processed' => $sync['processed'],
                    'success' => $sync['success'],
                    'failed' => $sync['failed'],
                ];
            } else {
                $picValue = (string) $item->{$colPic};
                $picValue = str_replace('#err', '', $picValue);
                $sync = $this->imageSyncService->syncImage($picValue, $tab);
                DB::table($tableInfo['table'])
                    ->where($tableInfo['id'], $item->{$tableInfo['id']})
                    ->update([$colPic => $sync['path']]);

                $sync['success'] ? $syncCount++ : $failedCount++;
                $results[] = [
                    'name' => $item->{$tableInfo['name']},
                    'id' => $item->{$tableInfo['id']},
                    'processed' => 1,
                    'success' => $sync['success'] ? 1 : 0,
                    'failed' => $sync['success'] ? 0 : 1,
                    'message' => $sync['message'],
                ];
            }
        }

        return view('admin.images.result', [
            'tab' => $tab,
            'col' => $col,
            'total' => $total,
            'page' => $param['page'],
            'limit' => $param['limit'],
            'successCount' => $syncCount,
            'failedCount' => $failedCount,
            'results' => $results,
        ]);
    }

    public function del(Request $request)
    {
        $fnames = $request->input('ids', []);
        if (empty($fnames)) {
            return $this->error('请选择要删除的文件');
        }
        
        $deleted = 0;
        foreach ($fnames as $fname) {
            $fname = str_replace('\\', '/', $fname);
            
            // 安全检查：只能删除 upload 目录下的文件
            if (strpos($fname, './upload') !== 0 || substr_count($fname, './') > 1) {
                continue;
            }
            
            $filePath = base_path($fname);
            if (File::exists($filePath)) {
                File::delete($filePath);
                $deleted++;
            }
        }
        
        return $this->success("已删除 {$deleted} 个文件");
    }

    protected function applySyncFilters($query, string $timeColumn, string $picColumn, int $col, array $param, string $flag)
    {
        if (($param['range'] ?? '1') === '2' && !empty($param['date'])) {
            $dayStart = strtotime(str_replace('|', '-', (string) $param['date']));
            if ($dayStart !== false) {
                $query->whereBetween($timeColumn, [$dayStart, $dayStart + 86400]);
            }
        }

        if ($col === 2) {
            $query->where($picColumn, 'like', '%<img%src="http%');
            return $query;
        }

        $opt = (string) ($param['opt'] ?? '0');
        if ($opt === '1') {
            $query->where($picColumn, 'not like', '%#err%');
        } elseif ($opt === '2') {
            $query->where($picColumn, 'not like', '%' . $flag . '%');
        } elseif ($opt === '3') {
            $query->where($picColumn, 'like', '%#err%');
        }

        return $query->where($picColumn, 'like', 'http%');
    }
}

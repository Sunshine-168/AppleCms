<?php

namespace App\Http\Controllers\Admin;

use App\Models\Annex;
use App\Services\ImageSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AnnexController extends BaseController
{
    public function __construct(protected ImageSyncService $imageSyncService)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $query = Annex::query();

        if ($request->has('wd') && $request->wd) {
            $query->where('annex_file', 'like', '%' . $request->wd . '%');
        }

        if ($request->has('type') && $request->type) {
            $query->where('annex_type', $request->type);
        }
        
        $query->orderBy('annex_time', 'desc');

        $limit = $request->input('limit', 20);
        $list = $query->paginate($limit);

        return view('admin.annex.index', compact('list'));
    }

    public function file(Request $request)
    {
        $path = $request->input('path', 'upload');
        // Sanitize path
        $path = str_replace(['\\', '..'], ['/', ''], $path);
        
        // Ensure path starts with upload/
        if (!str_starts_with($path, 'upload')) {
            $path = 'upload';
        }

        $fullPath = public_path($path);

        if (!is_dir($fullPath)) {
             $path = 'upload';
             $fullPath = public_path($path);
        }

        $files = [];
        $items = scandir($fullPath);
        
        $num_path = 0;
        $num_file = 0;
        $sum_size = 0;

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $itemPath = $fullPath . '/' . $item;
            $relPath = $path . '/' . $item;
            
            if (is_dir($itemPath)) {
                $num_path++;
                $files[] = [
                    'isfile' => 0,
                    'name' => $item,
                    'path' => $relPath,
                    'time' => filemtime($itemPath)
                ];
            } else {
                $num_file++;
                $size = filesize($itemPath);
                $sum_size += $size;
                $files[] = [
                    'isfile' => 1,
                    'name' => $item,
                    'path' => $relPath,
                    'size' => $this->formatSize($size),
                    'time' => filemtime($itemPath)
                ];
            }
        }
        
        // Parent path logic
        $upPath = 'upload';
        if ($path != 'upload') {
            $parts = explode('/', $path);
            array_pop($parts);
            $upPath = implode('/', $parts);
        }

        return view('admin.annex.file', compact('files', 'path', 'upPath', 'num_path', 'num_file', 'sum_size'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return response()->json(['code' => 1001, 'msg' => 'Param error']);

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        
        Annex::destroy($ids);
        
        return response()->json(['code' => 1, 'msg' => 'Deleted successfully']);
    }

    public function check(Request $request)
    {
        $missingFiles = [];
        $deleted = 0;

        Annex::query()->orderBy('annex_time', 'desc')->chunk(500, function ($items) use (&$missingFiles, &$deleted) {
            foreach ($items as $item) {
                $file = ltrim((string) $item->annex_file, '/');
                if ($file === '' || File::exists(public_path($file))) {
                    continue;
                }

                $missingFiles[] = $file;
                $item->delete();
                $deleted++;
            }
        });

        return view('admin.annex.result', [
            'title' => '附件校验结果',
            'summary' => "共删除 {$deleted} 条失效附件记录",
            'items' => $missingFiles,
        ]);
    }

    public function init(Request $request)
    {
        if (!$request->boolean('ck')) {
            return view('admin.annex.init');
        }

        $tables = ['actor', 'art', 'topic', 'type', 'vod', 'website', 'role'];
        $inserted = 0;
        $items = [];

        foreach ($tables as $table) {
            $tableName = 'mac_' . $table;
            $idColumn = $table . '_id';
            $nameColumn = $table . '_name';
            $columns = [$table . '_pic', $table . '_pic_thumb', $table . '_pic_slide', $table . '_content'];

            \Illuminate\Support\Facades\DB::table($tableName)->orderBy($idColumn)->chunk(200, function ($rows) use ($columns, $idColumn, $nameColumn, &$inserted, &$items) {
                foreach ($rows as $row) {
                    foreach ($columns as $column) {
                        if (!isset($row->{$column}) || empty($row->{$column})) {
                            continue;
                        }

                        $paths = str_ends_with($column, '_content')
                            ? $this->extractUploadImages((string) $row->{$column})
                            : [(string) $row->{$column}];

                        foreach ($paths as $path) {
                            $normalized = ltrim(str_replace('\\', '/', $path), '/');
                            if (!str_starts_with($normalized, 'upload/') || !File::exists(public_path($normalized))) {
                                continue;
                            }

                            $exists = Annex::query()->where('annex_file', $normalized)->exists();
                            if ($exists) {
                                continue;
                            }

                            $this->imageSyncService->recordAnnex($normalized, 'image', filesize(public_path($normalized)));
                            $inserted++;
                            $items[] = $row->{$nameColumn} . ' -> ' . $normalized;
                        }
                    }
                }
            });
        }

        return view('admin.annex.result', [
            'title' => '附件初始化结果',
            'summary' => "共补录 {$inserted} 条附件记录",
            'items' => $items,
        ]);
    }

    protected function extractUploadImages(string $content): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        $paths = [];
        foreach (($matches[1] ?? []) as $path) {
            $path = ltrim((string) $path, '/');
            if (str_starts_with($path, 'upload/')) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }

    private function formatSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
}

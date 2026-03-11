<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class SafetyController extends BaseController
{
    protected $files = [];

    public function index()
    {
        return redirect()->route('admin.safety.file');
    }

    public function file(Request $request)
    {
        if ($request->input('ck')) {
            $ft = (array) $request->input('ft', ['1', '2']);
            $version = config('version.code', '');
            $url = base64_decode('aHR0cDovL3VwZGF0ZS5tYWNjbXMubGEv') . 'v10/mac_files_' . $version . '.html';

            $html = Http::timeout(30)->withOptions(['verify' => false])->get($url)->body();
            $json = json_decode($html, true);
            if (!is_array($json)) {
                return $this->renderSafetyOutput([__('admin/safety/file_msg1')]);
            }

            $this->files = [];
            $this->listDir(base_path());
            if (empty($this->files)) {
                return $this->renderSafetyOutput([__('admin/safety/file_msg2')]);
            }

            $lines = [];
            foreach ($this->files as $path => $meta) {
                $color = '';
                $msg = 'ok';
                if (empty($json[$path]) && in_array('1', $ft, true)) {
                    $color = 'BlueViolet';
                    $msg = __('admin/safety/file_msg3');
                } elseif (!empty($json[$path]) && ($meta['md5'] ?? '') !== ($json[$path]['md5'] ?? '')) {
                    if (in_array('2', $ft, true)) {
                        $color = 'red';
                        $msg = __('admin/safety/file_msg4');
                    }
                }

                if ($color !== '') {
                    $lines[] = $path . '---<span style="color:' . $color . ';">' . e($msg) . '</span>';
                }
            }

            if (empty($lines)) {
                $lines[] = 'ok';
            }

            return $this->renderSafetyOutput($lines);
        }

        return view('admin.safety.file');
    }

    public function data(Request $request)
    {
        if ($request->input('ck')) {
            $pre = config('database.connections.mysql.prefix', 'mac_');
            $tables = ['actor', 'art', 'gbook', 'link', 'topic', 'type', 'vod'];
            $checkArr = ['<script', '<iframe', '{php}', '{:'];
            $replaceRules = [
                ["/<script[\s\S]*?<\/(.*)>/is", "/<script[\s\S]*?>/is"],
                ["/<iframe[\s\S]*?<\/(.*)>/is", "/<iframe[\s\S]*?>/is"],
                ["/{php}[\s\S]*?{\/php}/is"],
                ["/{:[\s\S]*?}/is"],
            ];

            $lines = [];
            foreach ($tables as $table) {
                $fullTable = $pre . $table;
                if (!Schema::hasTable($fullTable)) {
                    continue;
                }

                $columns = $this->getTableColumns($fullTable);
                if (empty($columns)) {
                    continue;
                }

                $lines[] = sprintf(__('admin/safety/data_check_tip1'), $fullTable);
                $textColumns = array_values(array_filter($columns, static function (array $column) {
                    return strpos((string) $column['type'], 'int') === false;
                }));

                if (empty($textColumns)) {
                    continue;
                }

                $idColumn = $table . '_id';
                $nameColumn = $table . '_name';
                $selectColumns = array_unique(array_merge(
                    array_column($textColumns, 'name'),
                    [$idColumn],
                    Schema::hasColumn($fullTable, $nameColumn) ? [$nameColumn] : []
                ));

                $query = DB::table($fullTable)->select($selectColumns);
                $query->where(function ($builder) use ($textColumns, $checkArr) {
                    foreach ($textColumns as $column) {
                        foreach ($checkArr as $keyword) {
                            $builder->orWhere($column['name'], 'like', '%' . $keyword . '%');
                        }
                    }
                });

                $list = $query->get();
                $lines[] = sprintf(__('admin/safety/data_check_tip2'), $list->count());

                foreach ($list as $row) {
                    $rowArray = (array) $row;
                    $update = [];
                    foreach ($textColumns as $column) {
                        $columnName = $column['name'];
                        $value = $rowArray[$columnName] ?? null;
                        if (!is_string($value) || $value === '') {
                            continue;
                        }

                        $cleaned = $value;
                        foreach ($replaceRules as $patterns) {
                            foreach ($patterns as $pattern) {
                                $cleaned = preg_replace($pattern, '', $cleaned) ?? $cleaned;
                            }
                        }

                        if ($cleaned !== $value) {
                            $update[$columnName] = $cleaned;
                        }
                    }

                    if (!empty($update) && isset($rowArray[$idColumn])) {
                        DB::table($fullTable)->where($idColumn, $rowArray[$idColumn])->update($update);
                        $valName = strip_tags((string) ($rowArray[$nameColumn] ?? $rowArray[$idColumn]));
                        $lines[] = $rowArray[$idColumn] . '、' . $valName . ' ok';
                    }
                }
            }

            $lines[] = __('admin/safety/data_clear_ok');
            return $this->renderSafetyOutput($lines);
        }

        $tables = DB::select("SHOW TABLE STATUS");
        return view('admin.safety.data', compact('tables'));
    }

    protected function listDir($dir)
    {
        if (!File::isDirectory($dir)) {
            return;
        }

        $skipDirectories = [
            'vendor',
            'node_modules',
            'storage',
            '.git',
        ];

        foreach (File::directories($dir) as $subDirectory) {
            $name = basename($subDirectory);
            $relative = ltrim(str_replace('\\', '/', str_replace(base_path(), '', $subDirectory)), '/');
            if (in_array($name, $skipDirectories, true) || $relative === 'bootstrap/cache') {
                continue;
            }
            $this->listDir($subDirectory);
        }

        foreach (File::files($dir) as $file) {
            $path = $file->getPathname();
            $relative = './' . ltrim(str_replace('\\', '/', str_replace(base_path(), '', $path)), '/');
            $this->files[$relative] = [
                'md5' => md5_file($path),
            ];
        }
    }

    protected function getTableColumns(string $fullTable): array
    {
        $database = config('database.connections.mysql.database');
        $rows = DB::select(
            'select COLUMN_NAME as name, DATA_TYPE as type from information_schema.columns where table_schema = ? and table_name = ?',
            [$database, $fullTable]
        );

        return array_map(static fn ($row) => (array) $row, $rows);
    }

    protected function renderSafetyOutput(array $lines)
    {
        $content = '<style type="text/css">body{font-size:12px;color:#333;line-height:21px;}span{font-weight:bold;color:#FF0000}</style>';
        $content .= implode('<br>', $lines);

        return response($content);
    }
}

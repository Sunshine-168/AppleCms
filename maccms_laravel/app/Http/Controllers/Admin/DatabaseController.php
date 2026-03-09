<?php

namespace App\Http\Controllers\Admin;

use App\Utils\Database as DatabaseUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseController extends BaseController
{
    public function index(Request $request)
    {
        $group = $request->input('group', 'export');
        
        if ($group == 'import') {
            // 列出备份文件列表
            $backupPath = config('maccms.db.backup_path', storage_path('app/backup/database'));
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            $list = [];
            $files = File::files($backupPath);
            
            foreach ($files as $file) {
                $name = $file->getFilename();
                if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                    $parts = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                    $date = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
                    $time = "{$parts[3]}:{$parts[4]}:{$parts[5]}";
                    $part = $parts[6];
                    
                    $key = "{$date} {$time}";
                    if (isset($list[$key])) {
                        $list[$key]['part'] = max($list[$key]['part'], $part);
                        $list[$key]['size'] += $file->getSize();
                    } else {
                        $extension = strtoupper($file->getExtension());
                        $list[$key] = [
                            'part' => $part,
                            'size' => $file->getSize(),
                            'compress' => ($extension === 'SQL') ? '无' : $extension,
                            'time' => strtotime("{$date} {$time}"),
                        ];
                    }
                }
            }
        } else {
            $group = 'export';
            $list = DB::select("SHOW TABLE STATUS");
        }
        
        return view('admin.database.' . $group, compact('list'));
    }

    public function export(Request $request)
    {
        if (!$request->isMethod('post')) {
            return $this->error('请求方式错误');
        }
        
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要导出的数据表');
        }
        
        $tables = is_array($ids) ? $ids : [$ids];
        
        // 确保管理员表在最后
        $adminTable = null;
        foreach ($tables as $k => $v) {
            if (strpos($v, '_admin') !== false) {
                $adminTable = $v;
                unset($tables[$k]);
            }
        }
        if ($adminTable) {
            $tables[] = $adminTable;
        }
        
        $backupPath = config('maccms.db.backup_path', storage_path('app/backup/database'));
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }
        
        $lockFile = $backupPath . '/backup.lock';
        if (File::exists($lockFile)) {
            return $this->error('已有备份任务正在执行，请稍后再试');
        }
        
        File::put($lockFile, time());
        
        try {
            $config = [
                'path' => rtrim($backupPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
                'part' => (int) config('maccms.db.part_size', 20971520),
                'compress' => (int) config('maccms.db.compress', 1),
                'level' => (int) config('maccms.db.compress_level', 4),
            ];
            $file = [
                'name' => date('Ymd-His'),
                'part' => 1,
            ];

            $database = new DatabaseUtil($file, $config);
            if ($database->create() === false) {
                throw new \RuntimeException('创建备份文件失败');
            }

            foreach ($tables as $table) {
                $start = 0;
                do {
                    $start = $database->backup($table, is_array($start) ? (int) $start[0] : (int) $start);
                    if ($start === false) {
                        throw new \RuntimeException("备份数据表失败: {$table}");
                    }
                } while ($start !== 0);
            }

            File::delete($lockFile);
            return $this->success('备份成功');
        } catch (\Throwable $e) {
            File::delete($lockFile);
            return $this->error('备份失败：' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return $this->error('请选择要导入的备份文件');
        }

        $name = date('Ymd-His', (int) $id) . '-*.sql*';
        $files = File::glob(rtrim(config('maccms.db.backup_path', storage_path('app/backup/database')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name);
        $list = [];
        foreach ($files as $file) {
            $basename = basename($file);
            $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz = preg_match('/^\d{8}-\d{6}-\d+\.sql\.gz$/', $basename) === 1;
            if (!$match) {
                continue;
            }
            $list[(int) $match[6]] = [(int) $match[6], $file, $gz];
        }

        ksort($list);
        $last = end($list);
        if (!$last || count($list) !== (int) $last[0]) {
            return $this->error('备份文件可能已损坏');
        }

        try {
            foreach ($list as $item) {
                $database = new DatabaseUtil($item, [
                    'path' => rtrim(config('maccms.db.backup_path', storage_path('app/backup/database')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
                    'compress' => (bool) $item[2],
                ]);
                $start = $database->import(0);
                while ($start !== 0) {
                    if ($start === false) {
                        throw new \RuntimeException('导入过程中执行 SQL 失败');
                    }
                    $start = $database->import((int) $start[0]);
                }
            }
        } catch (\Throwable $e) {
            return $this->error('导入失败：' . $e->getMessage());
        }

        return $this->success('导入成功');
    }

    public function optimize(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要优化的数据表');
        }
        
        $tables = is_array($ids) ? $ids : [$ids];
        $tableList = '`' . implode('`,`', $tables) . '`';
        
        try {
            DB::statement("OPTIMIZE TABLE {$tableList}");
            return $this->success('优化成功');
        } catch (\Exception $e) {
            return $this->error('优化失败：' . $e->getMessage());
        }
    }

    public function repair(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error('请选择要修复的数据表');
        }
        
        $tables = is_array($ids) ? $ids : [$ids];
        $tableList = '`' . implode('`,`', $tables) . '`';
        
        try {
            DB::statement("REPAIR TABLE {$tableList}");
            return $this->success('修复成功');
        } catch (\Exception $e) {
            return $this->error('修复失败：' . $e->getMessage());
        }
    }

    public function del(Request $request)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return $this->error('请选择要删除的备份文件');
        }
        
        $backupPath = config('maccms.db.backup_path', storage_path('app/backup/database'));
        $pattern = date('Ymd-His', $id) . '-*.sql*';
        $files = File::glob($backupPath . '/' . $pattern);
        
        foreach ($files as $file) {
            File::delete($file);
        }
        
        return $this->success('删除成功');
    }

    public function sql(Request $request)
    {
        if ($request->isMethod('post')) {
            $sql = trim($request->input('sql', ''));
            
            if (empty($sql)) {
                return $this->error('SQL语句不能为空');
            }
            
            // 安全检查：禁止危险操作
            $forbiddenKeywords = ['into dumpfile', 'into outfile', 'char(', 'load_file'];
            foreach ($forbiddenKeywords as $keyword) {
                if (stripos($sql, $keyword) !== false) {
                    return $this->error('SQL语句包含危险关键字');
                }
            }
            
            // 替换表前缀
            $prefix = config('database.connections.mysql.prefix', 'mac_');
            $sql = str_replace('{pre}', $prefix, $sql);
            
            try {
                if (stripos($sql, 'select') === 0) {
                    $results = DB::select($sql);
                    return $this->success('执行成功', $results);
                } else {
                    DB::statement($sql);
                    return $this->success('执行成功');
                }
            } catch (\Exception $e) {
                return $this->error('执行失败：' . $e->getMessage());
            }
        }
        
        return view('admin.database.sql');
    }

    public function rep(Request $request)
    {
        if ($request->isMethod('post')) {
            $table = $request->input('table');
            $field = $request->input('field');
            $findstr = $request->input('findstr');
            $tostr = $request->input('tostr');
            $where = $request->input('where', '');
            
            if (empty($table) || empty($field) || empty($findstr) || empty($tostr)) {
                return $this->error('参数不完整');
            }
            
            // 验证表名
            if (!$this->isValidTable($table)) {
                return $this->error('数据表无效');
            }
            
            try {
                $sql = "UPDATE `{$table}` SET `{$field}` = REPLACE(`{$field}`, '{$findstr}', '{$tostr}') WHERE 1=1 {$where}";
                DB::statement($sql);
                return $this->success('替换成功');
            } catch (\Exception $e) {
                return $this->error('替换失败：' . $e->getMessage());
            }
        }
        
        $list = DB::select("SHOW TABLE STATUS");
        return view('admin.database.rep', compact('list'));
    }

    protected function isValidTable($table)
    {
        $tables = DB::select("SHOW TABLE STATUS");
        foreach ($tables as $tableInfo) {
            if ($tableInfo->Name == $table) {
                return true;
            }
        }
        return false;
    }
}

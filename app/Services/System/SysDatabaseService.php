<?php
namespace App\Services\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;

/**
 * 数据库字典服务
 */
class SysDatabaseService
{
    /**
     * 获取数据库表列表
     * @return array
     */
    public function getDatabaseTables(): array
    {
        $rows = DB::select('SHOW TABLE STATUS');

        $data = [];
        foreach ($rows as $row)
        {
            $name = (string) ($row->Name ?? '');
            if ($name === '')
            {
                continue;
            }

            $data[] = [
                'name'        => $name,
                'engine'      => (string) ($row->Engine ?? ''),
                'rows'        => (int) ($row->Rows ?? 0),
                'collation'   => (string) ($row->Collation ?? ''),
                'data_length' => (int) ($row->Data_length ?? 0),
                'comment'     => (string) ($row->Comment ?? ''),
                'create_time' => (string) ($row->Create_time ?? ''),
                'update_time' => (string) ($row->Update_time ?? ''),
            ];
        }

        return Result::success([
            'total' => count($data),
            'data'  => $data,
        ]);
    }

    /**
     * 获取数据库表字段列表
     * @param string $table 表名
     * @return array
     */
    public function getDatabaseColumns(string $table): array
    {
        $table = trim($table);
        if ($table === '')
        {
            return Result::fail('请选择表');
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table))
        {
            return Result::fail('非法表名');
        }

        $exists = DB::select(
            'SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
            [$table]
        );
        if (empty($exists))
        {
            return Result::fail('表不存在');
        }

        $rows = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');

        $data = [];
        foreach ($rows as $row)
        {
            $data[] = [
                'field'      => (string) ($row->Field ?? ''),
                'type'       => (string) ($row->Type ?? ''),
                'collation'  => (string) ($row->Collation ?? ''),
                'null'       => (string) ($row->Null ?? ''),
                'key'        => (string) ($row->Key ?? ''),
                'default'    => ($row->Default ?? null),
                'extra'      => (string) ($row->Extra ?? ''),
                'privileges' => (string) ($row->Privileges ?? ''),
                'comment'    => (string) ($row->Comment ?? ''),
            ];
        }

        return Result::success([
            'table' => $table,
            'total' => count($data),
            'data'  => $data,
        ]);
    }
}

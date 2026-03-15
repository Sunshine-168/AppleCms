<?php

namespace App\Services\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;

/**
 * 数据库数据批量替换服务
 */
class SysDatabaseReplaceService
{   
    /**
     * 执行数据批量替换
     * @param string $table 表名
     * @param array $fields 字段名数组
     * @param string $from 被替换内容
     * @param string $to 替换内容
     * @param string $whereRaw WHERE 子句（原始 SQL）
     * @return array
     */
    public function run(string $table, array $fields, string $from, string $to, string $whereRaw): array
    {
        $connection = (string) config('database.default', 'mysql');
        $cfg        = (array) config('database.connections.' . $connection, []);
        $driver     = (string) ($cfg['driver'] ?? '');
        if ($driver !== 'mysql' && $driver !== 'mariadb')
        {
            return Result::fail('仅支持 MySQL/MariaDB');
        }

        $table = trim($table);
        if ($table === '')
        {
            return Result::fail('请选择表');
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table))
        {
            return Result::fail('非法表名');
        }

        $from = (string) $from;
        $to   = (string) $to;

        if ($from === '')
        {
            return Result::fail('请输入被替换内容');
        }

        $fields = array_values(array_filter(array_map(function ($v) {
            return trim((string) $v);
        }, $fields), function ($v) {
            return $v !== '';
        }));

        if (empty($fields))
        {
            return Result::fail('请选择字段');
        }

        foreach ($fields as $f)
        {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $f))
            {
                return Result::fail('非法字段名');
            }
        }

        $exists = DB::select(
            'SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
            [$table]
        );
        if (empty($exists))
        {
            return Result::fail('表不存在');
        }

        $columnRows = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $allowedColumns = [];
        foreach ($columnRows as $row)
        {
            $name = (string) ($row->Field ?? '');
            if ($name !== '')
            {
                $allowedColumns[$name] = true;
            }
        }

        $validFields = [];
        foreach ($fields as $f)
        {
            if (!isset($allowedColumns[$f]))
            {
                return Result::fail('字段不存在：' . $f);
            }
            $validFields[] = $f;
        }

        $setParts = [];
        $bindings = [];
        foreach ($validFields as $col)
        {
            $setParts[] = '`' . $col . '` = REPLACE(`' . $col . '`, ?, ?)';
            $bindings[] = $from;
            $bindings[] = $to;
        }

        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $setParts);

        $whereRaw = trim($whereRaw);
        if ($whereRaw !== '')
        {
            $sql .= ' WHERE ' . $whereRaw;
        }

        try {
            $affected = DB::affectingStatement($sql, $bindings);
            return Result::success([
                'affected' => (int) $affected,
            ], '替换成功');
        } catch (\Throwable $e) {
            return Result::fail('替换失败: ' . $e->getMessage());
        }
    }
}

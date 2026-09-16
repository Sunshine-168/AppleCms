<?php

namespace App\Services\Admin\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;

/**
 * 数据库数据批量替换服务（安全版）
 */
class SysDatabaseReplaceService
{
    /**
     * 执行数据批量替换
     * @param string $table 表名
     * @param array $fields 字段名数组
     * @param string $from 被替换内容
     * @param string $to 替换内容
     * @param string $whereRaw WHERE 子句（支持 ? 占位符）
     * @param array $bindings WHERE 条件绑定参数
     * @return array
     */
    public function run(string $table, array $fields, string $from, string $to, string $whereRaw = '', array $bindings = []): array
    {
        $connection = config('database.default', 'mysql');
        $cfg = config('database.connections.' . $connection, []);
        $driver = $cfg['driver'] ?? '';

        if (!in_array($driver, ['mysql', 'mariadb'], true))
        {
            return Result::fail('仅支持 MySQL/MariaDB');
        }

        $table = trim($table);

        if ($table === '' || !preg_match('/^[A-Za-z0-9_]+$/', $table))
        {
            return Result::fail('非法表名');
        }

        $from = (string)$from;
        $to   = (string)$to;

        if ($from === '')
        {
            return Result::fail('请输入被替换内容');
        }

        $fields = array_values(array_filter(array_map('trim', $fields)));
        if (empty($fields))
        {
            return Result::fail('请选择字段');
        }

        foreach ($fields as $f)
        {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $f))
            {
                return Result::fail('非法字段名: ' . $f);
            }
        }

        // 检查表是否存在
        $exists = DB::select(
            'SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
            [$table]
        );
        if (empty($exists))
        {
            return Result::fail('表不存在');
        }

        // 检查字段是否存在
        $columns = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $allowedColumns = array_column($columns, null, 'Field');
        foreach ($fields as $f)
        {
            if (!isset($allowedColumns[$f]))
            {
                return Result::fail('字段不存在：' . $f);
            }
        }

        // 构建 SET 语句
        $setParts = [];
        $setBindings = [];
        foreach ($fields as $col)
        {
            $setParts[] = "`$col` = REPLACE(`$col`, ?, ?)";
            $setBindings[] = $from;
            $setBindings[] = $to;
        }

        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $setParts);

        // 拼接 WHERE 条件
        $whereRaw = trim($whereRaw);
        if ($whereRaw !== '')
        {
            $sql .= ' WHERE ' . $whereRaw;
        }

        $allBindings = array_merge($setBindings, $bindings);

        try {
            // 使用事务保护
            $affected = DB::transaction(function() use ($sql, $allBindings) {
                return DB::affectingStatement($sql, $allBindings);
            });

            return Result::success([
                'affected' => (int)$affected,
            ], "替换成功，影响 {$affected} 条数据");

        } catch (\Throwable $e)
        {
            return Result::fail('替换失败: ' . $e->getMessage());
        }
    }
}

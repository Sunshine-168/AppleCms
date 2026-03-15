<?php

namespace App\Services\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;

/**
 * 系统数据库SQL执行服务
 */
class SysDatabaseSqlService
{
    /**
     * 执行 SQL
     * @param string $sql
     * @return array
     */
    public function run(string $sql): array
    {
        $sql = trim($sql);
        if ($sql === '') 
        {
            return Result::fail('请输入SQL');
        }

        // 按分号拆分 SQL，忽略分号在引号内的情况
        $parts = preg_split('/;(?=(?:[^\'"]|\'[^\']*\'|"[^"]*")*$)/', $sql) ?: [];
        $statements = [];
        foreach ($parts as $p) 
        {
            $p = trim($p);
            if ($p !== '') {
                $statements[] = $p;
            }
        }

        if (count($statements) !== 1) 
        {
            return Result::fail('仅支持执行一条SQL语句');
        }

        $statement = $statements[0];
        $keyword = strtolower((string) strtok(ltrim($statement), " \t\r\n"));

        // 安全检查：禁止危险 SQL
        $forbidden = ['drop', 'truncate', 'alter', 'create', 'rename'];
        if (in_array($keyword, $forbidden, true)) 
        {
            return Result::fail('禁止执行此类 SQL');
        }

        try {
            // 查询类 SQL
            if (in_array($keyword, ['select', 'show', 'describe', 'desc', 'explain'], true)) 
            {
                $rows = DB::select($statement);
                $list = [];
                foreach ($rows as $row) {
                    $list[] = (array) $row;
                }

                $columns = [];
                if (isset($list[0]) && is_array($list[0])) 
                {
                    $columns = array_keys($list[0]);
                }

                return Result::success([
                    'type'    => 'query',
                    'columns' => $columns,
                    'rows'    => $list,
                    'count'   => count($list),
                ], '执行成功');
            }

            // 数据修改类 SQL（事务保护）
            if (in_array($keyword, ['insert', 'update', 'delete', 'replace'], true)) {
                $affected = DB::transaction(function() use ($statement) 
                {
                    return DB::affectingStatement($statement);
                });

                return Result::success([
                    'type'     => 'affecting',
                    'affected' => (int) $affected,
                ], '执行成功');
            }

            // 其他语句（例如 SET、USE 等）
            $ok = DB::statement($statement);
            return Result::success([
                'type'   => 'statement',
                'result' => (bool) $ok,
            ], '执行成功');

        } catch (\Throwable $e) 
        {
            return Result::fail('执行失败: ' . $e->getMessage());
        }
    }
}
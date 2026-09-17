<?php

namespace App\Services\Admin\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;

/**
 * 数据库字典服务
 */
class SysDatabaseService
{
    /**
     * 获取数据库表列表
     */
    public function getDatabaseTables(): array
    {
        try {
            $tables = Schema::getTables();
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '读不了表列表');
        }

        $data = [];
        foreach ($tables as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '' || str_starts_with($name, 'sqlite_')) {
                continue;
            }

            $data[] = [
                'name' => $name,
                'engine' => (string) ($row['engine'] ?? ''),
                'rows' => 0,
                'collation' => (string) ($row['collation'] ?? ''),
                'data_length' => (int) ($row['size'] ?? 0),
                'comment' => (string) ($row['comment'] ?? ''),
                'create_time' => '',
                'update_time' => '',
            ];
        }

        usort($data, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return Result::success([
            'total' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * 获取数据库表字段列表
     */
    public function getDatabaseColumns(string $table): array
    {
        $table = trim($table);
        if ($table === '') {
            return Result::fail('请选择表');
        }

        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return Result::fail('非法表名');
        }

        if (! Schema::hasTable($table)) {
            return Result::fail('表不存在');
        }

        try {
            $rows = Schema::getColumns($table);
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '读不了字段');
        }

        $data = [];
        foreach ($rows as $row) {
            $field = (string) ($row['name'] ?? '');
            if ($field === '') {
                continue;
            }
            $data[] = [
                'field' => $field,
                'type' => (string) ($row['type'] ?? $row['type_name'] ?? ''),
                'collation' => (string) ($row['collation'] ?? ''),
                'null' => ! empty($row['nullable']) ? 'YES' : 'NO',
                'key' => ! empty($row['auto_increment']) ? 'PRI' : '',
                'default' => $row['default'] ?? null,
                'extra' => ! empty($row['auto_increment']) ? 'auto_increment' : '',
                'privileges' => '',
                'comment' => (string) ($row['comment'] ?? ''),
            ];
        }

        return Result::success([
            'table' => $table,
            'total' => count($data),
            'data' => $data,
        ]);
    }
}

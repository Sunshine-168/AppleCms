<?php

namespace App\Services\Admin\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;

/**
 * 库表字段说明：看表和列干什么，不改结构。
 */
class SysDatabaseService
{
    public function __construct(
        protected SysDatabaseDictCatalog $catalog = new SysDatabaseDictCatalog()
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function pageBoard(string $table = ''): array
    {
        $list = [];
        try {
            $list = $this->tableRows();
        } catch (\Throwable) {
            $list = [];
        }
        $names = array_column($list, 'name');
        $table = trim($table);
        if ($table === '' || ! in_array($table, $names, true)) {
            $table = in_array('videos', $names, true) ? 'videos' : (string) ($names[0] ?? '');
        }
        $detail = $table === '' ? null : $this->presentTable($table);
        $columns = $table === '' ? [] : (($this->getDatabaseColumns($table)['data']['data'] ?? []) ?: []);
        $groups = [];
        foreach ($this->catalog->groups() as $id => $label) {
            $n = 0;
            foreach ($list as $row) {
                if (($row['group'] ?? '') === $id) {
                    $n++;
                }
            }
            if ($n > 0) {
                $groups[] = ['id' => $id, 'label' => $label, 'n' => $n];
            }
        }

        return [
            'groups' => $groups,
            'tables' => $list,
            'table' => $table,
            'detail' => $detail,
            'columns' => $columns,
            'ui' => [
                'title' => admin_t('page.db_dict'),
                'lead' => admin_t('ui.schema_lead'),
                'backup' => admin_t('ui.tab_backup'),
                'sql' => admin_t('page.db_sql'),
                'replace' => admin_t('page.db_replace'),
                'dicts' => admin_t('page.db_dict'),
                'find' => admin_t('ui.schema_find'),
                'search' => admin_t('ui.search'),
                'reset' => admin_t('ui.reset'),
                'all' => admin_t('ui.all'),
                'look' => admin_t('ui.schema_look'),
                'cols' => admin_t('ui.schema_cols'),
                'empty' => admin_t('ui.schema_empty'),
                'empty_hint' => admin_t('ui.schema_empty_hint'),
                'unknown' => admin_t('ui.schema_unknown'),
                'nullable' => admin_t('ui.schema_nullable'),
                'required' => admin_t('ui.schema_required'),
                'pk' => admin_t('ui.schema_pk'),
                'index' => admin_t('ui.schema_index'),
                'default' => admin_t('ui.schema_default'),
                'no_cols' => admin_t('ui.schema_no_cols'),
            ],
        ];
    }

    public function getDatabaseTables(): array
    {
        try {
            $data = $this->tableRows();
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '读不了表列表');
        }

        return Result::success([
            'total' => count($data),
            'data' => $data,
        ]);
    }

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

        $indexed = $this->indexedColumns($table);
        $data = [];
        foreach ($rows as $row) {
            $field = (string) ($row['name'] ?? '');
            if ($field === '') {
                continue;
            }
            $nullable = ! empty($row['nullable']);
            $pk = ! empty($row['auto_increment']) || ! empty($row['primary']);
            if (! $pk && isset($indexed[$field]) && ! empty($indexed[$field]['primary'])) {
                $pk = true;
            }
            $hasIndex = $pk || isset($indexed[$field]);
            $type = (string) ($row['type'] ?? $row['type_name'] ?? '');
            $comment = trim((string) ($row['comment'] ?? ''));
            $purpose = $this->catalog->fieldPurpose($table, $field);
            if ($comment !== '' && $purpose === $this->catalog->guessField($field)) {
                $purpose = $comment;
            }
            $data[] = [
                'field' => $field,
                'label' => $purpose,
                'purpose' => $purpose,
                'type' => $type,
                'type_text' => $this->typeText($type),
                'null' => $nullable ? 'YES' : 'NO',
                'null_text' => $nullable ? '可空' : '必填',
                'key' => $pk ? 'PRI' : ($hasIndex ? 'MUL' : ''),
                'key_text' => $pk ? '主键' : ($hasIndex ? '索引' : ''),
                'default' => $this->defaultText($row['default'] ?? null),
                'extra' => ! empty($row['auto_increment']) ? '自增' : '',
                'comment' => $comment,
            ];
        }

        $meta = $this->catalog->tableMeta($table);

        return Result::success([
            'table' => $table,
            'label' => $meta['label'],
            'hint' => $meta['hint'],
            'url' => $meta['url'],
            'group' => $meta['group'],
            'group_label' => $meta['group_label'],
            'known' => $meta['known'],
            'total' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tableRows(): array
    {
        $tables = Schema::getTables();
        $data = [];
        foreach ($tables as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '' || str_starts_with($name, 'sqlite_')) {
                continue;
            }
            $data[] = $this->presentTable($name, $row);
        }
        usort($data, function (array $a, array $b): int {
            $order = array_flip(array_keys($this->catalog->groups()));
            $ga = $order[$a['group'] ?? ''] ?? 99;
            $gb = $order[$b['group'] ?? ''] ?? 99;
            if ($ga !== $gb) {
                return $ga <=> $gb;
            }

            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return $data;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function presentTable(string $name, array $row = []): array
    {
        $meta = $this->catalog->tableMeta($name);

        return [
            'name' => $name,
            'label' => $meta['label'],
            'hint' => $meta['hint'],
            'group' => $meta['group'],
            'group_label' => $meta['group_label'],
            'url' => $meta['url'],
            'known' => $meta['known'],
            'engine' => (string) ($row['engine'] ?? ''),
            'collation' => (string) ($row['collation'] ?? ''),
            'comment' => (string) ($row['comment'] ?? ''),
            'search' => mb_strtolower($name.' '.$meta['label'].' '.$meta['hint'].' '.$this->fieldSearchBlob($name)),
        ];
    }

    private function fieldSearchBlob(string $table): string
    {
        $parts = [];
        foreach ($this->catalog->tableFields()[$table] ?? [] as $field => $purpose) {
            $parts[] = $field.' '.$purpose;
        }

        return implode(' ', $parts);
    }

    /**
     * @return array<string, array{primary?:bool,unique?:bool}>
     */
    private function indexedColumns(string $table): array
    {
        $out = [];
        try {
            foreach (Schema::getIndexes($table) as $index) {
                $primary = ! empty($index['primary']);
                $unique = ! empty($index['unique']);
                foreach ((array) ($index['columns'] ?? []) as $col) {
                    $col = (string) $col;
                    if ($col === '') {
                        continue;
                    }
                    $out[$col] = [
                        'primary' => $primary || ! empty($out[$col]['primary']),
                        'unique' => $unique || ! empty($out[$col]['unique']),
                    ];
                }
            }
        } catch (\Throwable) {
            return $out;
        }

        return $out;
    }

    private function typeText(string $type): string
    {
        $t = strtolower(trim($type));
        if ($t === '') {
            return '';
        }
        if (str_contains($t, 'int') || $t === 'integer' || $t === 'boolean') {
            return '整数';
        }
        if (str_contains($t, 'real') || str_contains($t, 'double') || str_contains($t, 'float') || str_contains($t, 'decimal') || str_contains($t, 'numeric')) {
            return '小数';
        }
        if (str_contains($t, 'text') || str_contains($t, 'blob') || str_contains($t, 'json')) {
            return '长文本';
        }
        if (str_contains($t, 'time') || str_contains($t, 'date')) {
            return '时间';
        }
        if (str_contains($t, 'char') || str_contains($t, 'string') || str_contains($t, 'varchar') || str_contains($t, 'clob')) {
            return '文本';
        }

        return $type;
    }

    private function defaultText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}

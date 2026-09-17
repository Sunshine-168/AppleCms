<?php
namespace App\Services\Admin\System;

use App\Models\System\SysDictModel;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Cache;

/**
 * 系统字典服务
 */
class SysDictService
{
    public const VALUE_TYPES = [
        0 => '文本',
        1 => '整数',
        2 => '小数',
        3 => 'JSON',
        4 => '数组',
        5 => '枚举',
        6 => '长文本',
    ];

    public SysDictModel $sysDictModel;

    public function __construct()
    {
        $this->sysDictModel = new SysDictModel();
    }

    /**
     * 字典工作台：分组与数量
     */
    public function pageBoard(): array
    {
        return [
            'types' => $this->typeRows(),
            'queues' => $this->queueCounts(),
        ];
    }

    /**
     * 获取系统字典列表
     */
    public function getSysLists(string $dictType, string $q, string $status, int $limit): array
    {
        if ($limit < 1)
        {
            $limit = 20;
        }

        $where = [];
        $dictType = trim($dictType);
        $q = trim($q);
        $status = trim($status);

        if ($dictType !== '')
        {
            $where[] = ['dict_type', '=', $dictType];
        }

        if ($status !== '')
        {
            $where[] = ['status', '=', (int) $status];
        }

        if ($q !== '')
        {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where['or'] = [
                ['dict_type', 'like', $like],
                ['dict_key', 'like', $like],
                ['label', 'like', $like],
                ['remark', 'like', $like],
            ];
        }

        $data = $this->sysDictModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);

        $rows = $data['data'] ?? [];
        if (!is_array($rows))
        {
            $rows = [];
        }

        foreach ($rows as &$item)
        {
            $item = $this->decorateRow(is_array($item) ? $item : []);
        }
        unset($item);

        $data['data'] = $rows;
        $data['types'] = $this->typeRows();
        $data['queues'] = $this->queueCounts();

        return Result::success($data);
    }

    /**
     * 添加系统字典
     */
    public function addSysSet(string $dictType, string $dictKey, int $valueType, mixed $dictValue, mixed $enumLimit, string $label, int $sort, int $status, string $remark): array
    {
        $dictType = trim($dictType);
        $dictKey = trim($dictKey);
        $label = trim($label);
        $remark = trim($remark);

        if ($dictType === '' || $dictKey === '')
        {
            return Result::fail('请填写分组和标识');
        }

        if ($this->sysDictModel->existsBy(['dict_type' => $dictType, 'dict_key' => $dictKey]))
        {
            return Result::fail('该分组下的标识已存在');
        }

        $time = time();
        $insert = array_merge(
            $this->buildValueFields($valueType, $dictValue, $enumLimit),
            [
                'dict_type' => $dictType,
                'dict_key' => $dictKey,
                'value_type' => (int) $valueType,
                'label' => $label,
                'sort' => (int) $sort,
                'status' => (int) $status,
                'remark' => $remark,
                'create_time' => $time,
                'update_time' => $time,
            ]
        );

        $res = $this->sysDictModel->inserts($insert);
        if (!$res)
        {
            return Result::fail('添加失败');
        }

        $this->forgetDictCache($dictKey, $dictType);

        return Result::success();
    }

    /**
     * 更新系统字典
     */
    public function updateSysSet(int $id, string $dictType, string $dictKey, int $valueType, mixed $dictValue, mixed $enumLimit, string $label, int $sort, int $status, string $remark): array
    {
        if ($id < 1)
        {
            return Result::fail('缺少ID');
        }

        $dictType = trim($dictType);
        $dictKey = trim($dictKey);
        $label = trim($label);
        $remark = trim($remark);

        if ($dictType === '' || $dictKey === '')
        {
            return Result::fail('请填写分组和标识');
        }

        $old = $this->sysDictModel->findById($id);
        if (empty($old))
        {
            return Result::fail('记录不存在');
        }

        $exists = $this->sysDictModel->existsBy([
            ['id', '<>', $id],
            ['dict_type', '=', $dictType],
            ['dict_key', '=', $dictKey],
        ]);

        if ($exists)
        {
            return Result::fail('该分组下的标识已存在');
        }

        $update = array_merge(
            $this->buildValueFields($valueType, $dictValue, $enumLimit),
            [
                'dict_type' => $dictType,
                'dict_key' => $dictKey,
                'value_type' => (int) $valueType,
                'label' => $label,
                'sort' => (int) $sort,
                'status' => (int) $status,
                'remark' => $remark,
                'update_time' => time(),
            ]
        );

        $res = $this->sysDictModel->updateById($id, $update);
        if (!$res)
        {
            return Result::fail('修改失败');
        }

        $this->forgetDictCache((string) ($old['dict_key'] ?? ''), (string) ($old['dict_type'] ?? ''));
        $this->forgetDictCache($dictKey, $dictType);

        return Result::success();
    }

    /**
     * 删除系统字典
     */
    public function deleteSysSet(int $id): array
    {
        if ($id < 1)
        {
            return Result::fail('缺少ID');
        }

        $old = $this->sysDictModel->findById($id);
        if (empty($old))
        {
            return Result::fail('记录不存在');
        }

        $res = $this->sysDictModel->deleteById($id);
        if (!$res)
        {
            return Result::fail('删除失败');
        }

        $this->forgetDictCache((string) ($old['dict_key'] ?? ''), (string) ($old['dict_type'] ?? ''));

        return Result::success();
    }

    /**
     * 更新系统字典状态
     */
    public function updateState(int $id, int $status): array
    {
        if ($id < 1)
        {
            return Result::fail('缺少ID');
        }

        $old = $this->sysDictModel->findById($id);
        if (empty($old))
        {
            return Result::fail('记录不存在');
        }

        $res = $this->sysDictModel->updateById($id, ['status' => $status, 'update_time' => time()]);
        if (!$res)
        {
            return Result::fail('修改失败');
        }

        $this->forgetDictCache((string) ($old['dict_key'] ?? ''), (string) ($old['dict_type'] ?? ''));

        return Result::success();
    }

    /**
     * 构建系统字典值字段
     */
    private function buildValueFields(int $valueType, mixed $dictValue, mixed $enumLimit): array
    {
        $fields = [
            'value_string' => null,
            'value_int' => null,
            'value_float' => null,
            'value_json' => null,
            'value_text' => null,
            'enum_limit' => null,
        ];

        return match ((int) $valueType) {
            0 => array_merge($fields, ['value_string' => (string) $dictValue]),
            1 => array_merge($fields, ['value_int' => is_numeric($dictValue) ? (int) $dictValue : 0]),
            2 => array_merge($fields, ['value_float' => is_numeric($dictValue) ? (float) $dictValue : 0]),
            3, 4 => array_merge($fields, ['value_json' => $this->ensureJsonString($dictValue)]),
            5 => array_merge($fields, [
                'value_json' => $this->ensureJsonString($dictValue),
                'enum_limit' => $this->ensureJsonString($enumLimit),
            ]),
            6 => array_merge($fields, ['value_text' => (string) $dictValue]),
            default => array_merge($fields, ['value_string' => (string) $dictValue]),
        };
    }

    /**
     * 确保JSON字符串格式
     */
    private function ensureJsonString(mixed $value): ?string
    {
        if ($value === null || $value === '')
        {
            return null;
        }

        if (is_string($value))
        {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE)
            {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 列表行：显示名、值预览、是否启用
     */
    private function decorateRow(array $item): array
    {
        $item['dict_value'] = $this->formatDictValue($item);
        $item['value_preview'] = $this->previewText($item['dict_value']);
        $valueType = (int) ($item['value_type'] ?? 0);
        $item['value_type_label'] = self::VALUE_TYPES[$valueType] ?? self::VALUE_TYPES[0];
        $item['is_on'] = (int) ($item['status'] ?? 0) === 0;
        $label = trim((string) ($item['label'] ?? ''));
        $key = (string) ($item['dict_key'] ?? '');
        $item['title'] = $label !== '' ? $label : ($key !== '' ? $key : '未命名');
        $item['create_time'] = !empty($item['create_time']) ? date('Y-m-d H:i:s', (int) $item['create_time']) : '';
        $item['update_time'] = !empty($item['update_time']) ? date('Y-m-d H:i:s', (int) $item['update_time']) : '';

        return $item;
    }

    /**
     * 已有分组及条数
     */
    private function typeRows(): array
    {
        $rows = SysDictModel::query()
            ->select('dict_type')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('dict_type')
            ->orderBy('dict_type')
            ->get()
            ->toArray();

        $out = [];
        foreach ($rows as $row) {
            $type = trim((string) ($row['dict_type'] ?? ''));
            if ($type === '') {
                continue;
            }
            $out[] = [
                'dict_type' => $type,
                'cnt' => (int) ($row['cnt'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * 全部 / 已停用
     */
    private function queueCounts(): array
    {
        return [
            'all' => (int) SysDictModel::query()->count(),
            'off' => (int) SysDictModel::query()->where('status', 1)->count(),
        ];
    }

    /**
     * 格式化系统字典值
     */
    private function formatDictValue(array $row): string
    {
        $type = (int) ($row['value_type'] ?? 0);
        return match ($type) {
            0 => (string) ($row['value_string'] ?? ''),
            1 => (string) ((int) ($row['value_int'] ?? 0)),
            2 => (string) ((float) ($row['value_float'] ?? 0)),
            3, 4, 5 => $this->jsonPreview($row['value_json'] ?? null),
            6 => (string) ($row['value_text'] ?? ''),
            default => (string) ($row['value_string'] ?? ''),
        };
    }

    private function jsonPreview(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_array($value) || is_object($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $raw = (string) $value;
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE);
        }

        return $raw;
    }

    private function previewText(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        if ($value === '') {
            return '';
        }
        if (function_exists('mb_strlen') && mb_strlen($value) > 48) {
            return mb_substr($value, 0, 48) . '…';
        }
        if (strlen($value) > 48) {
            return substr($value, 0, 48) . '…';
        }

        return $value;
    }

    /**
     * 忘记系统字典缓存
     */
    private function forgetDictCache(string $key, string $type): void
    {
        $type = (string) $type;
        $key = (string) $key;

        Cache::forget("dict_label_{$type}_{$key}");
        Cache::forget("dict_value_{$type}_{$key}");
    }
}

<?php
namespace App\Services\Admin\System;

use App\Models\System\SysDictModel;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * 系统字典服务
 */
class SysDictService
{
    public const VALUE_TYPES = [
        0 => '字符串',
        1 => '数字',
        2 => '浮点',
        3 => 'JSON对象',
        4 => 'JSON数组',
        5 => '枚举',
        6 => '富文本',
    ];

    /** @return array<int, string> */
    public static function valueTypeLabels(): array
    {
        return [
            0 => admin_t('ui.vt_string'),
            1 => admin_t('ui.vt_int'),
            2 => admin_t('ui.vt_float'),
            3 => admin_t('ui.vt_json_obj'),
            4 => admin_t('ui.vt_json_arr'),
            5 => admin_t('ui.vt_enum'),
            6 => admin_t('ui.vt_html'),
        ];
    }

    public SysDictModel $sysDictModel;

    public function __construct(
        protected SysDictCatalog $catalog = new SysDictCatalog()
    ) {
        $this->sysDictModel = new SysDictModel();
    }

    /**
     * 字典工作台：目录分类 + 已有条数
     */
    public function pageBoard(): array
    {
        $empty = ! Schema::hasTable('sys_dict');
        $groups = $empty ? [] : $this->groupRows();

        return [
            'families' => $empty ? [] : $this->familyRows($groups),
            'groups' => $groups,
            'types' => $empty ? [] : $this->typeRows($groups),
            'queues' => $this->queueCounts(),
            'ui' => [
                'title' => admin_t('page.dict'),
                'lead' => admin_t('ui.dict_lead'),
                'compose' => admin_t('ui.add_action'),
                'add' => admin_t('ui.dict_add'),
                'edit' => admin_t('ui.dict_edit'),
                'find' => admin_t('ui.dict_find'),
                'value_types' => self::valueTypeLabels(),
            ],
        ];
    }

    /**
     * 前台筛选 / 后台填片用的选项。字典有启用项时优先。
     *
     * @return list<string>
     */
    public function filterChoices(string $name): array
    {
        $type = $this->catalog->filterTypeMap()[$name] ?? '';
        if ($type === '') {
            return [];
        }

        return $this->choiceValues($type);
    }

    /**
     * @return list<string>
     */
    public function choiceValues(string $dictType): array
    {
        $dictType = trim($dictType);
        if ($dictType === '' || $this->catalog->isAway($dictType)) {
            return [];
        }
        try {
            $rows = SysDictModel::query()
                ->where('dict_type', $dictType)
                ->where('status', 0)
                ->orderByDesc('sort')
                ->orderBy('id')
                ->get()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $item = is_array($row) ? $row : [];
            $val = trim($this->formatDictValue($item));
            if ($val === '') {
                $val = trim((string) ($item['dict_key'] ?? ''));
            }
            if ($val !== '' && ! in_array($val, $out, true)) {
                $out[] = $val;
            }
        }

        return $out;
    }

    /**
     * 把站点设置里的逗号串拆进当前分类。已有标识不动。
     */
    public function importFromSettings(string $dictType): array
    {
        $dictType = trim($dictType);
        $meta = $this->catalog->group($dictType);
        $key = is_array($meta) ? trim((string) ($meta['setting_key'] ?? '')) : '';
        if ($key === '') {
            return Result::fail(admin_t('ui.cannot_split_settings'));
        }
        if ($this->catalog->isAway($dictType)) {
            return Result::fail(admin_t('ui.not_in_dict'));
        }
        $raw = '';
        try {
            $raw = trim((string) app(\App\Services\Video\VideoSettingService::class)->get($key, ''));
        } catch (\Throwable) {
            $raw = '';
        }
        $parts = array_values(array_filter(array_map('trim', preg_split('/[,，]/u', $raw) ?: [])));
        if ($parts === []) {
            return Result::fail(admin_t('ui.settings_empty'));
        }
        $added = 0;
        $skip = 0;
        $sort = count($parts);
        foreach ($parts as $part) {
            if ($this->sysDictModel->existsBy(['dict_type' => $dictType, 'dict_key' => $part])) {
                $skip++;
                $sort--;
                continue;
            }
            $res = $this->addSysSet($dictType, $part, 0, $part, null, $part, $sort, 0, '从站点设置拆入');
            if ((int) ($res['code'] ?? 1) === 0) {
                $added++;
            }
            $sort--;
        }
        if ($added < 1) {
            return Result::fail($skip > 0 ? '这些项已经在字典里' : '没有拆进任何项');
        }

        return Result::success([
            'added' => $added,
            'skip' => $skip,
        ], '拆进了 '.$added.' 条'.($skip > 0 ? '，'.$skip.' 条已有没动' : ''));
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

        if ($this->catalog->isAway($dictType)) {
            $groups = $this->groupRows();

            return Result::success([
                'data' => [],
                'total' => 0,
                'types' => $this->typeRows($groups),
                'groups' => $groups,
                'families' => $this->familyRows($groups),
                'queues' => $this->queueCounts(),
                'away' => true,
            ]);
        }

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
        $groups = $this->groupRows();
        $data['types'] = $this->typeRows($groups);
        $data['groups'] = $groups;
        $data['families'] = $this->familyRows($groups);
        $data['queues'] = $this->queueCounts();
        $data['away'] = false;

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
            return Result::fail(admin_t('ui.fill_type_key'));
        }
        if ($this->catalog->isAway($dictType)) {
            return Result::fail(admin_t('ui.not_in_dict_go'));
        }

        if ($this->sysDictModel->existsBy(['dict_type' => $dictType, 'dict_key' => $dictKey]))
        {
            return Result::fail(admin_t('ui.dict_key_exists'));
        }

        $parsed = $this->normalizeRecord($valueType, $dictValue, $enumLimit, $label, $remark, $sort, $status, $dictKey);
        if ((int) ($parsed['code'] ?? 1) !== 0) {
            return $parsed;
        }
        $norm = is_array($parsed['data'] ?? null) ? $parsed['data'] : [];

        $time = time();
        $insert = array_merge(
            $norm['fields'],
            [
                'dict_type' => $dictType,
                'dict_key' => $dictKey,
                'value_type' => (int) $norm['value_type'],
                'label' => $norm['label'],
                'sort' => $norm['sort'],
                'status' => $norm['status'],
                'remark' => $norm['remark'],
                'create_time' => $time,
                'update_time' => $time,
            ]
        );

        $res = $this->sysDictModel->inserts($insert);
        if (!$res)
        {
            return Result::fail(admin_t('ui.add_fail'));
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
            return Result::fail(admin_t('ui.missing_id'));
        }

        $dictType = trim($dictType);
        $dictKey = trim($dictKey);
        $label = trim($label);
        $remark = trim($remark);

        if ($dictType === '' || $dictKey === '')
        {
            return Result::fail(admin_t('ui.fill_type_key'));
        }
        if ($this->catalog->isAway($dictType)) {
            return Result::fail(admin_t('ui.not_in_dict_go'));
        }

        $old = $this->sysDictModel->findById($id);
        if (empty($old))
        {
            return Result::fail(admin_t('ui.row_missing'));
        }

        $exists = $this->sysDictModel->existsBy([
            ['id', '<>', $id],
            ['dict_type', '=', $dictType],
            ['dict_key', '=', $dictKey],
        ]);

        if ($exists)
        {
            return Result::fail(admin_t('ui.dict_key_exists'));
        }

        $parsed = $this->normalizeRecord($valueType, $dictValue, $enumLimit, $label, $remark, $sort, $status, $dictKey);
        if ((int) ($parsed['code'] ?? 1) !== 0) {
            return $parsed;
        }
        $norm = is_array($parsed['data'] ?? null) ? $parsed['data'] : [];
        $update = array_merge(
            $norm['fields'],
            [
                'dict_type' => $dictType,
                'dict_key' => $dictKey,
                'value_type' => (int) $norm['value_type'],
                'label' => $norm['label'],
                'sort' => $norm['sort'],
                'status' => $norm['status'],
                'remark' => $norm['remark'],
                'update_time' => time(),
            ]
        );
        if ($this->fingerprint(is_array($old) ? $old : []) === $this->fingerprint(array_merge(is_array($old) ? $old : [], $update))) {
            return Result::fail(admin_t('ui.no_change'));
        }

        $res = $this->sysDictModel->updateById($id, $update);
        if (!$res)
        {
            return Result::fail(admin_t('ui.update_fail'));
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
            return Result::fail(admin_t('ui.missing_id'));
        }

        $old = $this->sysDictModel->findById($id);
        if (empty($old))
        {
            return Result::fail(admin_t('ui.row_missing'));
        }

        $res = $this->sysDictModel->deleteById($id);
        if (!$res)
        {
            return Result::fail(admin_t('ui.delete_fail'));
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
            return Result::fail(admin_t('ui.missing_id'));
        }

        $old = $this->sysDictModel->findById($id);
        if (empty($old))
        {
            return Result::fail(admin_t('ui.row_missing'));
        }

        $res = $this->sysDictModel->updateById($id, ['status' => $status, 'update_time' => time()]);
        if (!$res)
        {
            return Result::fail(admin_t('ui.update_fail'));
        }

        $this->forgetDictCache((string) ($old['dict_key'] ?? ''), (string) ($old['dict_type'] ?? ''));

        return Result::success();
    }

    /**
     * 按值类型校验并落到对应字段。对照 A13：0 字符串 1 数字 2 浮点 3 对象 4 数组 5 枚举 6 富文本。
     *
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    private function normalizeRecord(int $valueType, mixed $dictValue, mixed $enumLimit, string $label, string $remark, int $sort, int $status, string $dictKey): array
    {
        if (! array_key_exists($valueType, self::VALUE_TYPES)) {
            return Result::fail(admin_t('ui.bad_value_type'));
        }
        if (function_exists('mb_strlen') ? mb_strlen($remark) > 200 : strlen($remark) > 200) {
            return Result::fail(admin_t('ui.remark_max_200'));
        }
        $label = $label !== '' ? $label : $dictKey;
        $built = $this->buildValueFields($valueType, $dictValue, $enumLimit);
        if ((int) ($built['code'] ?? 1) !== 0) {
            return $built;
        }

        return Result::success([
            'value_type' => $valueType,
            'label' => $label,
            'remark' => $remark,
            'sort' => $sort,
            'status' => $status === 1 ? 1 : 0,
            'fields' => is_array($built['data'] ?? null) ? $built['data'] : [],
        ]);
    }

    /**
     * 构建系统字典值字段
     *
     * @return array{code:int,msg:string,data?:array<string,mixed>}
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
            0 => Result::success(array_merge($fields, ['value_string' => (string) $dictValue])),
            1 => $this->intFields($fields, $dictValue),
            2 => $this->floatFields($fields, $dictValue),
            3 => $this->jsonObjectFields($fields, $dictValue),
            4 => $this->jsonArrayFields($fields, $dictValue),
            5 => $this->enumFields($fields, $dictValue, $enumLimit),
            6 => Result::success(array_merge($fields, ['value_text' => (string) $dictValue])),
            default => Result::success(array_merge($fields, ['value_string' => (string) $dictValue])),
        };
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    private function intFields(array $fields, mixed $dictValue): array
    {
        $raw = is_string($dictValue) ? trim($dictValue) : $dictValue;
        if ($raw === '' || $raw === null) {
            return Result::fail(admin_t('ui.need_number'));
        }
        if (is_bool($raw) || ! is_numeric($raw) || ! preg_match('/^-?\d+$/', (string) $raw)) {
            return Result::fail(admin_t('ui.need_int'));
        }

        return Result::success(array_merge($fields, ['value_int' => (int) $raw]));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    private function floatFields(array $fields, mixed $dictValue): array
    {
        $raw = is_string($dictValue) ? trim($dictValue) : $dictValue;
        if ($raw === '' || $raw === null) {
            return Result::fail(admin_t('ui.need_float'));
        }
        if (is_bool($raw) || ! is_numeric($raw)) {
            return Result::fail(admin_t('ui.need_float_eg'));
        }

        return Result::success(array_merge($fields, ['value_float' => (float) $raw]));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    private function jsonObjectFields(array $fields, mixed $dictValue): array
    {
        if ($this->isBlankJson($dictValue)) {
            return Result::success(array_merge($fields, ['value_json' => '{}']));
        }
        $decoded = $this->decodeJsonRaw($dictValue);
        if ($decoded === '__invalid__') {
            return Result::fail(admin_t('ui.json_obj_bad'));
        }
        if (is_array($decoded) && ! array_is_list($decoded)) {
            return Result::success(array_merge($fields, ['value_json' => $this->ensureJsonString($decoded)]));
        }
        if (is_object($decoded) && ! $decoded instanceof \JsonSerializable) {
            return Result::success(array_merge($fields, ['value_json' => $this->ensureJsonString($decoded)]));
        }

        return Result::fail(admin_t('ui.json_obj_not_arr'));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    private function jsonArrayFields(array $fields, mixed $dictValue): array
    {
        if ($this->isBlankJson($dictValue)) {
            return Result::success(array_merge($fields, ['value_json' => '[]']));
        }
        $decoded = $this->decodeJsonRaw($dictValue);
        if ($decoded === '__invalid__') {
            return Result::fail(admin_t('ui.json_arr_bad'));
        }
        if (is_array($decoded) && array_is_list($decoded)) {
            return Result::success(array_merge($fields, ['value_json' => $this->ensureJsonString($decoded)]));
        }

        return Result::fail(admin_t('ui.json_arr_not_obj'));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    private function enumFields(array $fields, mixed $dictValue, mixed $enumLimit): array
    {
        $limit = $this->decodeJson($enumLimit);
        if ($limit === '__invalid__') {
            return Result::fail(admin_t('ui.enum_limit_json'));
        }
        if (! is_array($limit) || ! array_is_list($limit)) {
            return Result::fail(admin_t('ui.enum_limit_first'));
        }
        $options = [];
        foreach ($limit as $item) {
            if (! is_scalar($item) && $item !== null) {
                return Result::fail(admin_t('ui.enum_limit_scalar'));
            }
            $options[] = (string) $item;
        }
        if ($options === []) {
            return Result::fail(admin_t('ui.enum_limit_first'));
        }
        $picked = $this->scalarText($dictValue);
        if ($picked === '') {
            return Result::fail(admin_t('ui.pick_enum'));
        }
        if (! in_array($picked, $options, true)) {
            return Result::fail(admin_t('ui.enum_not_in'));
        }

        return Result::success(array_merge($fields, [
            'value_json' => $this->ensureJsonString($picked),
            'enum_limit' => $this->ensureJsonString($options),
        ]));
    }

    private function decodeJson(mixed $value): mixed
    {
        $raw = $this->decodeJsonRaw($value);
        if ($raw === '__invalid__' || $raw === null) {
            return $raw;
        }
        if (is_object($raw)) {
            return json_decode((string) json_encode($raw), true);
        }

        return $raw;
    }

    private function decodeJsonRaw(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            return $value;
        }
        if (! is_string($value)) {
            return is_scalar($value) ? $value : '__invalid__';
        }
        $decoded = json_decode(trim($value));
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '__invalid__';
        }

        return $decoded;
    }

    private function isBlankJson(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }

        return false;
    }

    private function scalarText(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_scalar($value)) {
            return trim((string) $value);
        }
        $decoded = $this->decodeJson($value);
        if (is_scalar($decoded) || $decoded === null) {
            return trim((string) $decoded);
        }

        return '';
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
        $item['value_type_label'] = self::valueTypeLabels()[$valueType] ?? admin_t('ui.vt_string');
        $item['enum_preview'] = $valueType === 5 ? $this->previewText($this->jsonPreview($item['enum_limit'] ?? null)) : '';
        $item['is_on'] = (int) ($item['status'] ?? 0) === 0;
        $label = trim((string) ($item['label'] ?? ''));
        $key = (string) ($item['dict_key'] ?? '');
        $item['title'] = $label !== '' ? $label : ($key !== '' ? $key : admin_t('ui.unnamed'));
        $item['create_time'] = ! empty($item['create_time']) ? date('Y-m-d H:i:s', (int) $item['create_time']) : '';
        $item['update_time'] = ! empty($item['update_time']) ? date('Y-m-d H:i:s', (int) $item['update_time']) : '';
        if ($valueType === 6) {
            $plain = trim(html_entity_decode(strip_tags((string) ($item['value_text'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $item['value_preview'] = $plain !== '' ? $this->previewText($plain) : admin_t('ui.vt_html');
        }

        return $item;
    }

    /**
     * 已有分组及条数（含目录里还没写过的分类）
     *
     * @param  list<array<string, mixed>>|null  $groups
     * @return list<array{dict_type:string,cnt:int,label:string}>
     */
    private function typeRows(?array $groups = null): array
    {
        $groups = $groups ?? $this->groupRows();
        $out = [];
        foreach ($groups as $row) {
            if (($row['kind'] ?? '') === 'away') {
                continue;
            }
            $out[] = [
                'dict_type' => (string) ($row['dict_type'] ?? ''),
                'cnt' => (int) ($row['cnt'] ?? 0),
                'label' => (string) ($row['label'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function groupRows(): array
    {
        $counts = [];
        try {
            $rows = SysDictModel::query()
                ->select('dict_type')
                ->selectRaw('COUNT(*) as cnt')
                ->groupBy('dict_type')
                ->get()
                ->toArray();
            foreach ($rows as $row) {
                $type = trim((string) ($row['dict_type'] ?? ''));
                if ($type !== '') {
                    $counts[$type] = (int) ($row['cnt'] ?? 0);
                }
            }
        } catch (\Throwable) {
            $counts = [];
        }

        $out = [];
        foreach ($this->catalog->groups() as $id => $meta) {
            $out[] = $this->presentGroup($id, $meta, (int) ($counts[$id] ?? 0));
            unset($counts[$id]);
        }
        foreach ($counts as $id => $cnt) {
            $out[] = $this->presentGroup((string) $id, [
                'family' => 'other',
                'label' => (string) $id,
                'hint' => admin_t('ui.custom_group_hint'),
                'url' => '',
                'kind' => 'options',
                'setting_key' => '',
            ], (int) $cnt);
        }

        return $out;
    }

    /**
     * @param  array{family?:string,label?:string,hint?:string,url?:string,kind?:string,setting_key?:string}  $meta
     * @return array<string, mixed>
     */
    private function presentGroup(string $id, array $meta, int $cnt): array
    {
        $family = (string) ($meta['family'] ?? 'other');
        $settingKey = trim((string) ($meta['setting_key'] ?? ''));

        return [
            'id' => $id,
            'dict_type' => $id,
            'label' => $this->groupLabel($id, (string) ($meta['label'] ?? $id)),
            'family' => $family,
            'family_label' => $family === 'filter' ? admin_t('ui.dict_family_filter') : admin_t('ui.dict_family_other'),
            'hint' => $this->groupHint($id, (string) ($meta['hint'] ?? '')),
            'url' => (string) ($meta['url'] ?? ''),
            'kind' => (string) ($meta['kind'] ?? 'options'),
            'setting_key' => $settingKey,
            'can_import' => $settingKey !== '',
            'cnt' => $cnt,
        ];
    }

    private function groupLabel(string $id, string $fallback): string
    {
        return match ($id) {
            'filter_area' => admin_t('ui.area'),
            'filter_lang' => admin_t('ui.lang_label'),
            'filter_year' => admin_t('ui.year'),
            'filter_weekday' => admin_t('ui.cycle'),
            'filter_serial' => admin_t('ui.serial'),
            default => $fallback,
        };
    }

    private function groupHint(string $id, string $fallback): string
    {
        return match ($id) {
            'filter_area' => admin_t('ui.filter_area_hint'),
            'filter_lang' => admin_t('ui.filter_lang_hint'),
            'filter_year' => admin_t('ui.filter_year_hint'),
            'filter_weekday' => admin_t('ui.filter_weekday_hint'),
            'filter_serial' => admin_t('ui.filter_serial_hint'),
            default => $fallback !== '' ? $fallback : admin_t('ui.custom_group_hint'),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return list<array{id:string,label:string,n:int}>
     */
    private function familyRows(array $groups): array
    {
        $n = [];
        foreach ($groups as $row) {
            $id = (string) ($row['family'] ?? 'other');
            $n[$id] = ($n[$id] ?? 0) + 1;
        }
        $out = [];
        foreach ($this->catalog->families() as $id => $label) {
            if ($id === 'other' && ($n[$id] ?? 0) < 1) {
                continue;
            }
            $out[] = [
                'id' => $id,
                'label' => $id === 'filter' ? admin_t('ui.dict_family_filter') : admin_t('ui.dict_family_other'),
                'n' => (int) ($n[$id] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * 全部 / 已停用
     */
    private function queueCounts(): array
    {
        if (! Schema::hasTable('sys_dict')) {
            return ['all' => 0, 'off' => 0];
        }

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
            3, 4 => $this->jsonPreview($row['value_json'] ?? null),
            5 => $this->scalarText($row['value_json'] ?? null),
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

    /** @param  array<string, mixed>  $row */
    private function fingerprint(array $row): string
    {
        return json_encode([
            't' => (string) ($row['dict_type'] ?? ''),
            'k' => (string) ($row['dict_key'] ?? ''),
            'vt' => (int) ($row['value_type'] ?? 0),
            'v' => $this->formatDictValue($row),
            'e' => $this->jsonPreview($row['enum_limit'] ?? null),
            'l' => trim((string) ($row['label'] ?? '')),
            's' => (int) ($row['sort'] ?? 0),
            'st' => (int) ($row['status'] ?? 0),
            'r' => trim((string) ($row['remark'] ?? '')),
        ], JSON_UNESCAPED_UNICODE);
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

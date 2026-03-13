<?php
namespace App\Models\Sys;

use App\Traits\QueryCacheTrait;
use App\Traits\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class SysDictModel extends Model
{
    use QueryTrait, QueryCacheTrait;

    protected $table = 'sys_dict';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'value_type' => 'integer',
        'value_float' => 'float',
        'value_int' => 'integer',
        'value_json' => 'array',
        'enum_limit' => 'array',
    ];

    public function getDictValueAttribute(): mixed
    {
        $type = (int) ($this->attributes['value_type'] ?? 0);

        return match ($type) {
            0 => $this->attributes['value_string'] ?? '',
            1 => (int) ($this->attributes['value_int'] ?? 0),
            2 => (float) ($this->attributes['value_float'] ?? 0),
            3, 4, 5 => $this->getAttribute('value_json'),
            6 => $this->attributes['value_text'] ?? '',
            default => $this->attributes['value_string'] ?? '',
        };
    }

    public function setDictValueAttribute(mixed $value): void
    {
        $type = (int) ($this->attributes['value_type'] ?? 0);

        switch ($type) {
            case 0:
                $this->attributes['value_string'] = (string) $value;
                break;
            case 1:
                $this->attributes['value_int'] = (int) $value;
                break;
            case 2:
                $this->attributes['value_float'] = (float) $value;
                break;
            case 3:
            case 4:
            case 5:
                $this->attributes['value_json'] = json_encode($value, JSON_UNESCAPED_UNICODE);
                break;
            case 6:
                $this->attributes['value_text'] = (string) $value;
                break;
        }
    }

    public function getEnumLimitAttribute(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 获取字典 label
     * @param string $key
     * @param string $type
     * @return string
     */
    public function getLabel(string $key, string $type = ''): string
    {
        $where = [['dict_key', '=', $key]];
        if ($type) $where[] = ['dict_type', '=', $type];
        return $this->where($where)->value('label') ?? '';
    }

    /**
     * 根据 key 获取 dict_value
     * @param string $key
     * @param string $type
     * @return mixed
     */
    public function getValue(string $key, string $type = '')
    {
        $where = [['dict_key', '=', $key]];
        if ($type) $where[] = ['dict_type', '=', $type];
        $row = $this->where($where)->first();
        return $row ? $row->dict_value : null;
    }
}

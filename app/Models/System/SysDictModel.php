<?php

namespace App\Models\System;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

/**
 * 系统字典
 */
class SysDictModel extends Model
{
    protected $table = 'sys_dict';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'value_type'  => 'integer',
        'value_float' => 'float',
        'value_int'   => 'integer',
        'value_json'  => 'array',
        'enum_limit'  => 'array',
    ];

    use QueryTrait, QueryCacheTrait;

    /*
    |--------------------------------------------------------------------------
    | Dict Value Accessor
    |--------------------------------------------------------------------------
    */

    protected function dictValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                $type = (int) ($this->attributes['value_type'] ?? 0);

                return match ($type) {
                    0 => $this->attributes['value_string'] ?? '',
                    1 => (int) ($this->attributes['value_int'] ?? 0),
                    2 => (float) ($this->attributes['value_float'] ?? 0),
                    3,4,5 => $this->value_json,
                    6 => $this->attributes['value_text'] ?? '',
                    default => $this->attributes['value_string'] ?? '',
                };
            },

            set: function ($value) {
                $type = (int) ($this->attributes['value_type'] ?? 0);

                return match ($type) {
                    0 => ['value_string' => (string)$value],
                    1 => ['value_int' => (int)$value],
                    2 => ['value_float' => (float)$value],
                    3,4,5 => ['value_json' => json_encode($value, JSON_UNESCAPED_UNICODE)],
                    6 => ['value_text' => (string)$value],
                    default => ['value_string' => (string)$value],
                };
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 获取字典 Label
    |--------------------------------------------------------------------------
    */

    public static function label(string $key, string $type = ''): string
    {
        return Cache::remember(
            "dict_label_{$type}_{$key}",
            3600,
            fn () => static::query()
                ->when($type, fn($q) => $q->where('dict_type', $type))
                ->where('dict_key', $key)
                ->value('label') ?? ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 获取字典值
    |--------------------------------------------------------------------------
    */

    public static function value(string $key, string $type = '')
    {
        return Cache::remember(
            "dict_value_{$type}_{$key}",
            3600,
            function () use ($key, $type) {

                $row = static::query()
                    ->when($type, fn($q) => $q->where('dict_type', $type))
                    ->where('dict_key', $key)
                    ->first();

                return $row?->dict_value;
            }
        );
    }
}

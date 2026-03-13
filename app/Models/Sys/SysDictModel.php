<?php
namespace app\common\model;

use app\common\enum\biz\SysDictTypeEnum;
use app\common\logic\traits\QueryCacheTrait;
use app\common\logic\traits\QueryTrait;
use think\Model;
use think\model\type\Json;

/**
 * 系统字典模型
 */
class SysDictModel extends Model
{
    use QueryTrait, QueryCacheTrait;

    // 表名
    protected  $name = 'sys_dict';

    // 主键
    protected  $pk   = 'id';

    // 自动类型转换
    protected array $type = [
        'value_json'    => 'json',
        'enum_limit'    => 'json',
        'value_type'    => 'integer',
        'value_float'   => 'float',
        'value_int'     => 'integer',
    ];

    /**
     * 获取 dict_value 属性，自动根据 value_type 映射字段
     * @param mixed $value
     * @param array $data
     * @return mixed
     */
    public function getDictValueAttr($value, array $data): mixed
    {
        switch ((int)($data['value_type'] ?? 0)) {
            case SysDictTypeEnum::value("STRING"): // string
                return $data['value_string'] ?? '';
            case SysDictTypeEnum::value("INT"): // int
                return $data['value_int'] ?? 0;
            case SysDictTypeEnum::value("FLOAT"): // float
                return $data['value_float'] ?? 0.0;
            case SysDictTypeEnum::value("JSON"): // json
            case SysDictTypeEnum::value("ARRAY"): // array
            case SysDictTypeEnum::value("ENUM"): // enum
                $val = $data['value_json'] ?? null;
                return $val instanceof Json ? json_decode($val->__toString(), true) : $val ?? [];
            case SysDictTypeEnum::value("TEXT"): // text
                return $data['value_text'] ?? '';
            default:
                return $value;
        }
    }

    /**
     * 获取 enum_limit 属性，保证返回数组
     * @param mixed $value
     * @return array
     */
    public function getEnumLimitAttr($value): array
    {
        $val = $value instanceof Json ? json_decode($value->__toString(), true) : $value;
        return is_array($val) ? $val : [];
    }

    /**
     * 设置 dict_value，根据 value_type 自动写入对应字段
     * @param mixed $value
     * @param array $data
     * @return void
     */
    public function setDictValueAttr($value, array $data): void
    {
        switch ((int)($data['value_type'] ?? 0))
        {
            case SysDictTypeEnum::value("STRING"): $this->setAttr('value_string', (string)$value); break;
            case SysDictTypeEnum::value("INT"): $this->setAttr('value_int', (int)$value); break;
            case SysDictTypeEnum::value("FLOAT"): $this->setAttr('value_float', (float)$value); break;
            case SysDictTypeEnum::value("JSON"):
            case SysDictTypeEnum::value("ARRAY"):
            case SysDictTypeEnum::value("ENUM"):
                $this->setAttr('value_json', json_encode($value, JSON_UNESCAPED_UNICODE));
                break;
            case SysDictTypeEnum::value("TEXT"): $this->setAttr('value_text', (string)$value); break;
        }
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
        $row = $this->where($where)->find();
        return $row ? $row->dict_value : null;
    }
}

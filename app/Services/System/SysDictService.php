<?php
namespace App\Services\System;

use app\common\enum\biz\PaymentClassTypeEnum;
use app\common\enum\biz\PaymentTypeEnum;
use app\common\enum\biz\SysDictTypeEnum;
use app\common\model\PaymentAccountModel;
use app\common\model\PaymentChannelModel;
use app\common\model\PaymentClassModel;
use app\common\utils\Result;
use App\Models\Sys\SysDictModel;
use function app\admin\service\v1\pageSize;

class SysDictService
{
    public SysDictModel $sysDictModel;
    public PaymentClassModel $paymentClassModel;
    public PaymentChannelModel $paymentChannelModel;
    public PaymentAccountModel $paymentAccountModel;

    public function __construct()
    {
        $this->sysDictModel        = new SysDictModel();
        $this->paymentClassModel   = new PaymentClassModel();
        $this->paymentChannelModel = new PaymentChannelModel();
        $this->paymentAccountModel = new PaymentAccountModel();
    }

    /**
     * 获取列表（支持模糊查询）
     * @param array $params
     * @return array
     */
    public function getSysLists(array $params): array
    {
        $where = array_filter([
            !empty($params['dict_type']) ? ['dict_type', 'like', '%' . $params['dict_type'] . '%'] : null,
            !empty($params['dict_key']) ? ['dict_key', 'like', '%' . $params['dict_key'] . '%'] : null,
            !empty($params['label']) ? ['label', 'like', '%' . $params['label'] . '%'] : null,
        ]);

        $data = $this->sysDictModel->paginates($where, '*', pageSize());

        foreach ($data['data'] as &$item)
        {
            if ($item['value_type'] == SysDictTypeEnum::value("TEXT"))
            {
                $item['value_text']  = html_entity_decode(html_entity_decode($item['value_text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return Result::success($data);
    }

    /**
     * 构造数据
     * @param array $params
     * @return null[]
     */
    private function buildValueFields(array $params): array
    {
        $type   = intval($params['value_type'] ?? 0);

        $fields = [
            'value_string' => null,
            'value_int'    => null,
            'value_float'  => null,
            'value_json'   => null,
            'value_text'   => null,
            'enum_limit'   => null,
        ];

        $value      = $params['dict_value'] ?? null;
        $enumLimit  = $params['enum_limit'] ?? [];

        return match ($type) {
            SysDictTypeEnum::value("STRING")    => array_merge($fields, ['value_string' => (string)$value]),
            SysDictTypeEnum::value("INT")       => array_merge($fields, ['value_int' => (int)$value]),
            SysDictTypeEnum::value("FLOAT")     => array_merge($fields, ['value_float' => (float)$value]),
            SysDictTypeEnum::value("JSON"), SysDictTypeEnum::value("ARRAY") => array_merge($fields, ['value_json' => json_encode($value, JSON_UNESCAPED_UNICODE)]),
            SysDictTypeEnum::value("ENUM")      => array_merge($fields, [
                'value_json' => json_encode($value, JSON_UNESCAPED_UNICODE),
                'enum_limit' => json_encode($enumLimit, JSON_UNESCAPED_UNICODE)
            ]),
            SysDictTypeEnum::value("TEXT") => array_merge($fields, ['value_text' => (string)$value]),
            default => $fields,
        };
    }

    /**
     * 获取基础字段
     * @param array $params
     * @return array
     */
    private function getBaseFields(array $params): array
    {
        $time = time();
        return [
            'dict_type'   => $params['dict_type'] ?? '',
            'dict_key'    => $params['dict_key'] ?? '',
            'value_type'  => $params['value_type'] ?? 0,
            'sort'        => $params['sort'] ?? 0,
            'status'      => $params['status'] ?? 0,
            'remark'      => $params['remark'] ?? '',
            'label'       => $params['label'] ?? '',
            'update_time' => $time,
            'create_time' => $params['create_time'] ?? $time,
        ];
    }


    /**
     * usdt汇率
     * @param $rate
     * @return void
     */
    private function syncUsdtRate($rate): void
    {
        $time = time();

        $this->paymentClassModel->updateByCondition(
            [['type', '=', PaymentClassTypeEnum::value("USDT")]],
            ['rate' => $rate, 'update_time' => $time]
        );

        $this->paymentChannelModel->updateByCondition(
            [['class_type', '=', PaymentTypeEnum::value("USDT")]],
            ['class_rate' => $rate, 'update_time' => $time]
        );

        $this->paymentAccountModel->updateByCondition(
            [['type', '=', PaymentTypeEnum::value("USDT")]],
            ['rate' => $rate, 'update_time' => $time]
        );
    }

    /**
     * 更新状态
     * @param array $params
     * @return array
     */
    public function updateSysSet(array $params): array
    {

        if (empty($params['id'])) return Result::fail("缺少ID");

        $update = array_merge($this->buildValueFields($params), $this->getBaseFields($params));

        $res    = $this->sysDictModel->updateById($params['id'], $update);

        if (!$res) return Result::fail('修改失败');

        if ($params['dict_key'] === 'usdt_rate' && is_numeric($params['dict_value']))
        {
            $this->syncUsdtRate($params['dict_value']);
        }

        return Result::success();
    }


    /**
     * 添加设置
     * @param array $params
     * @return array
     */
    public function addSysSet(array $params): array
    {
        if (empty($params['dict_type']) || empty($params['dict_key']))
        {
            return Result::fail("类型和 KEY 不能为空");
        }

        $insert = array_merge($this->buildValueFields($params), $this->getBaseFields($params));
        $res    = $this->sysDictModel->inserts($insert);

        return $res ? Result::success() : Result::fail('添加失败');
    }

    /**
     * 删掉设置
     * @param int $id
     * @return array
     */
    public function deleteSysSet(int $id): array
    {
        if (empty($id)) return Result::fail("缺少ID");
        return $this->sysDictModel->deleteById($id) ? Result::success() : Result::fail('删除失败');
    }


    /**
     * 更新状态
     * @param array $params
     * @return array
     */
    public function updateState(array $params): array
    {
        if (empty($params['id'])) return Result::fail("缺少ID");

        $update = ['status' => $params['status'], 'update_time' => time()];
        return $this->sysDictModel->updateById($params['id'], $update) ? Result::success() : Result::fail('修改失败');
    }
}

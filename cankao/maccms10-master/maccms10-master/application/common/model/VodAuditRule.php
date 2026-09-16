<?php

namespace app\common\model;

class VodAuditRule extends Base
{
    protected $name = 'vod_audit_rule';
    protected $createTime = '';
    protected $updateTime = '';
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    public function listData($where, $order, $page = 1, $limit = 20, $start = 0)
    {
        $page = $page > 0 ? (int)$page : 1;
        $limit = $limit ? (int)$limit : 20;
        $start = $start ? (int)$start : 0;
        if (!is_array($where)) {
            $where = json_decode($where, true);
        }
        $limit_str = ($limit * ($page - 1) + $start) . ',' . $limit;
        $total = $this->where($where)->count();
        $list = $this->where($where)->order($order)->limit($limit_str)->select();

        return [
            'code' => 1,
            'msg' => lang('data_list'),
            'page' => $page,
            'pagecount' => ceil($total / $limit),
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ];
    }

    public function infoData($where, $field = '*')
    {
        if (empty($where) || !is_array($where)) {
            return ['code' => 1001, 'msg' => lang('param_err')];
        }
        $info = $this->field($field)->where($where)->find();
        if (empty($info)) {
            return ['code' => 1002, 'msg' => lang('obtain_err')];
        }
        return ['code' => 1, 'msg' => lang('obtain_ok'), 'info' => $info->toArray()];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('VodAuditRule');
        if (!$validate->check($data)) {
            return ['code' => 1001, 'msg' => lang('param_err') . '：' . $validate->getError()];
        }
        if (($data['rule_type'] ?? '') === 'title_keyword' && trim((string)($data['rule_pattern'] ?? '')) === '') {
            return ['code' => 1001, 'msg' => lang('param_err') . '：' . lang('admin/vod_audit/rule_pattern')];
        }

        $data['rule_time'] = time();
        if (!empty($data['rule_id'])) {
            $where = ['rule_id' => ['eq', $data['rule_id']]];
            $res = $this->allowField(true)->where($where)->update($data);
        } else {
            $data['rule_time_add'] = time();
            $res = $this->allowField(true)->insert($data);
        }
        if ($res === false) {
            return ['code' => 1002, 'msg' => lang('save_err') . '：' . $this->getError()];
        }
        $this->clearRuleCache();
        return ['code' => 1, 'msg' => lang('save_ok')];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if ($res === false) {
            return ['code' => 1001, 'msg' => lang('del_err') . '：' . $this->getError()];
        }
        $this->clearRuleCache();
        return ['code' => 1, 'msg' => lang('del_ok')];
    }

    public function fieldData($where, $col, $val)
    {
        if (!isset($col) || !isset($val)) {
            return ['code' => 1001, 'msg' => lang('param_err')];
        }
        $data = [$col => $val];
        $res = $this->allowField(true)->where($where)->update($data);
        if ($res === false) {
            return ['code' => 1001, 'msg' => lang('set_err') . '：' . $this->getError()];
        }
        return ['code' => 1, 'msg' => lang('set_ok')];
    }

    /**
     * 按 sort 升序返回启用规则（缓存 60 秒）。
     */
    public function getEnabledRules()
    {
        $cacheKey = $GLOBALS['config']['app']['cache_flag'] . '_vod_audit_rules';
        $rules = \think\Cache::get($cacheKey);
        if ($rules !== false && is_array($rules)) {
            return $rules;
        }
        $rules = $this->where(['rule_status' => 1])->order('rule_sort asc,rule_id asc')->select();
        $list = [];
        if ($rules) {
            foreach ($rules as $row) {
                $list[] = is_array($row) ? $row : $row->toArray();
            }
        }
        \think\Cache::set($cacheKey, $list, 60);
        return $list;
    }

    public function clearRuleCache()
    {
        $cacheKey = $GLOBALS['config']['app']['cache_flag'] . '_vod_audit_rules';
        \think\Cache::rm($cacheKey);
    }
}

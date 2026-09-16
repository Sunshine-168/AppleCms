<?php
namespace app\admin\controller;
use think\Db;

class Seckill extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->view->config('view_path', APP_PATH . 'admin/view_new/');
    }

    public function index()
    {
        $param = input();
        $param['page'] = intval($param['page']) < 1 ? 1 : intval($param['page']);
        $param['limit'] = intval($param['limit']) < 1 ? $this->_pagesize : intval($param['limit']);

        $where = [];
        if (in_array($param['status'] ?? '', ['0', '1'], true)) {
            $where['seckill_status'] = ['eq', $param['status']];
        }
        if (!empty($param['wd'])) {
            $param['wd'] = htmlspecialchars(urldecode($param['wd']));
            $where['seckill_name'] = ['like', '%' . $param['wd'] . '%'];
        }

        $order = 'seckill_id desc';
        $res = model('Seckill')->listData($where, $order, $param['page'], $param['limit']);

        $this->assign('list', $res['list']);
        $this->assign('total', $res['total']);
        $this->assign('page', $res['page']);
        $this->assign('limit', $res['limit']);

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param', $param);

        $this->assign('title', lang('admin/seckill/title'));
        return $this->fetch('seckill/index');
    }

    public function info()
    {
        $param = input();
        $info = [];
        if (!empty($param['id'])) {
            $where = [];
            $where['seckill_id'] = ['eq', intval($param['id'])];
            $res = model('Seckill')->infoData($where);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            $info = $res['info'];
        }
        $info['seckill_start_time_input'] = !empty($info['seckill_start_time']) ? date('Y-m-d\TH:i:s', intval($info['seckill_start_time'])) : '';
        $info['seckill_end_time_input'] = !empty($info['seckill_end_time']) ? date('Y-m-d\TH:i:s', intval($info['seckill_end_time'])) : '';

        $this->assign('info', $info);
        $this->assign('title', lang('admin/seckill/info_title'));
        return $this->fetch('seckill/info');
    }

    public function save()
    {
        $param = input('post.');
        $param['seckill_name'] = htmlspecialchars(urldecode(trim($param['seckill_name'] ?? '')));
        if (!empty($param['seckill_start_time_input'])) {
            $param['seckill_start_time'] = strtotime($param['seckill_start_time_input']);
            unset($param['seckill_start_time_input']);
        }
        if (!empty($param['seckill_end_time_input'])) {
            $param['seckill_end_time'] = strtotime($param['seckill_end_time_input']);
            unset($param['seckill_end_time_input']);
        }
        $param['seckill_per_user'] = 1;
        // 开通时长白名单校验
        if (!in_array($param['seckill_target_long'] ?? '', ['day', 'week', 'month', 'year'], true)) {
            $param['seckill_target_long'] = 'month';
        }

        $res = model('Seckill')->saveData($param);
        if ($res['code'] > 1) {
            return $this->error($res['msg']);
        }
        return $this->success($res['msg'], url('seckill/index'));
    }

    public function del()
    {
        $param = input();
        $ids = isset($param['ids']) ? $param['ids'] : '';
        if (!empty($ids)) {
            $where = [];
            $where['seckill_id'] = ['in', $ids];
            $res = model('Seckill')->delData($where);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }

    public function field()
    {
        $param = input();
        if (empty($param['ids']) || !isset($param['col']) || !isset($param['val'])) {
            return $this->error(lang('param_err'));
        }
        if (!in_array($param['col'], ['seckill_status'], true) || !in_array((string)$param['val'], ['0', '1'], true)) {
            return $this->error(lang('param_err'));
        }
        $where = [];
        $where['seckill_id'] = ['in', $param['ids']];
        $res = model('Seckill')->fieldData($where, $param['col'], $param['val']);
        if ($res['code'] > 1) {
            return $this->error($res['msg']);
        }
        return $this->success($res['msg']);
    }
}
<?php
namespace app\admin\controller;
use think\Db;

class Group extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->view->config('view_path', APP_PATH . 'admin/view_new/');
    }

    public function index()
    {
        $param = input();
        $where=[];

        if(in_array($param['status'],['0','1'],true)){
            $where['group_status'] = ['eq',$param['status']];
        }
        if(!empty($param['wd'])){
            $param['wd'] = htmlspecialchars(urldecode($param['wd']));
            $where['group_name'] = ['like','%'.$param['wd'].'%'];
        }

        $order='group_id asc';
        $res = model('Group')->listData($where,$order);

        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);

        $this->assign('param',$param);
        $this->assign('title',lang('admin/group/title'));
        return $this->fetch('group/index');
    }

    public function info()
    {
        if (Request()->isPost()) {
            $param = input('post.');

            // VIP 活动价时段：datetime-local 输入(_input) 转 unix 时间戳存库
            foreach (['day', 'week', 'month', 'year'] as $pl) {
                foreach (['start', 'end'] as $se) {
                    $col = 'group_activity_' . $se . '_time_' . $pl;
                    if (isset($param[$col . '_input'])) {
                        $param[$col] = !empty($param[$col . '_input']) ? strtotime($param[$col . '_input']) : 0;
                        unset($param[$col . '_input']);
                    }
                }
            }

            if($GLOBALS['config']['user']['reg_group'] == $param['group_id']){
                $param['group_status'] = 1;
            }
            $res = model('Group')->saveData($param);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }

        $id = input('id');
        $where=[];
        $where['group_id'] = ['eq',$id];
        $res = model('Group')->infoData($where);

        $info = $res['info'];
        // VIP 活动价时段：unix 时间戳转 datetime-local 输入串(_input) 供表单回显
        if (is_array($info)) {
            foreach (['day', 'week', 'month', 'year'] as $pl) {
                foreach (['start', 'end'] as $se) {
                    $col = 'group_activity_' . $se . '_time_' . $pl;
                    $info[$col . '_input'] = !empty($info[$col]) ? date('Y-m-d\TH:i:s', intval($info[$col])) : '';
                }
            }
        }
        $this->assign('info',$info);


        $type_tree = model('Type')->getCache('type_tree');
        $this->assign('type_tree',$type_tree);

        $this->assign('title',lang('admin/group/title'));
        return $this->fetch('group/info');
    }

    public function del()
    {
        $param = input();
        $ids = $param['ids'];

        if(!empty($ids)){

            if(strpos(','.$ids.',', ','.$GLOBALS['config']['user']['reg_group'].',')!==false){
                return $this->error(lang('admin/group/reg_group_del_err'));
            }

            $where=[];
            $where['group_id'] = ['in',$ids];
            $res = model('Group')->delData($where);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }

    public function field()
    {
        $param = input();
        $ids = $param['ids'];
        $col = $param['col'];
        $val = $param['val'];

        if(!empty($ids) && in_array($col,['group_status']) && in_array($val,['0','1'])){
            $where=[];
            $where['group_id'] = ['in',$ids];

            $res = model('Group')->fieldData($where,$col,$val);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }


}

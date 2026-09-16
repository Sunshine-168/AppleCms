<?php
namespace app\common\model;
use think\Db;

class Order extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'order';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];


    public function listData($where,$order,$page=1,$limit=20,$start=0)
    {
        $page = $page > 0 ? (int)$page : 1;
        $limit = $limit ? (int)$limit : 20;
        $start = $start ? (int)$start : 0;
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        $total = $this->alias('o')->where($where)->count();
        $list = Db::name('Order')->alias('o')
            ->field('o.*,u.user_name')
            ->join('__USER__ u','o.user_id = u.user_id','left')
            ->where($where)
            ->order($order)
            ->limit($limit_str)
            ->select();


        return ['code'=>1,'msg'=>lang('data_list'),'page'=>$page,'pagecount'=>ceil($total/$limit),'limit'=>$limit,'total'=>$total,'list'=>$list];
    }

    public function infoData($where,$field='*')
    {
        if(empty($where) || !is_array($where)){
            return ['code'=>1001,'msg'=>lang('param_err')];
        }
        $info = $this->field($field)->where($where)->find();

        if(empty($info)){
            return ['code'=>1002,'msg'=>lang('obtain_err')];
        }
        $info = $info->toArray();

        return ['code'=>1,'msg'=>lang('obtain_ok'),'info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Order');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>lang('param_err').'：'.$validate->getError() ];
        }

        $data['order_time'] = time();
        if(!empty($data['order_id'])){
            $where=[];
            $where['order_id'] = ['eq',$data['order_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $res = $this->allowField(true)->insert($data);
        }
        if(false === $res){
            return ['code'=>1002,'msg'=>lang('save_err').'：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>lang('save_ok')];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if($res===false){
            return ['code'=>1001,'msg'=>lang('del_err').'：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>lang('del_ok')];
    }

    public function fieldData($where,$col,$val)
    {
        if(!isset($col) || !isset($val)){
            return ['code'=>1001,'msg'=>lang('param_err')];
        }

        $data = [];
        $data[$col] = $val;
        $res = $this->allowField(true)->where($where)->update($data);
        if($res===false){
            return ['code'=>1001,'msg'=>lang('set_err').'：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>lang('set_ok')];
    }

    /*
     * 充值回调函数接口
     * 任何充值接口，回调接口里直接调用该接口更新订单状态、用户积分
     * pay_type预留值alipay,weixin,bank，可以继续自定义最长10个字符
     */
    public function notify($order_code,$pay_type)
    {
        if(empty($order_code) || empty($pay_type)){
            return ['code'=>1001,'msg'=>lang('param_err')];
        }

        $where = [];
        $where['order_code'] = $order_code;
        $order = model('Order')->infoData($where);
        if($order['code']>1){
            return $order;
        }
        if($order['info']['order_status'] == 1){
            return ['code'=>1,'msg'=>lang('model/order/pay_over')];
        }

        $where2=[];
        $where2['user_id'] = $order['info']['user_id'];
        $user = model('User')->infoData($where2);
        if($user['code']>1){
            return $user;
        }

        Db::startTrans();
        try{
            $update = [];
            $update['order_status'] = 1;
            $update['order_pay_time'] = time();
            $update['order_pay_type'] = $pay_type;
            // 状态翻转即幂等闩：仅当订单仍未支付(0)时翻转，堵住并发重复回调
            // （异步 notify + 同步 return、网关重试）重复发积分 / 重复升级 VIP
            $res = $this->where($where)->where('order_status', 0)->update($update);
            if($res===false){
                Db::rollback();
                return ['code'=>2002,'msg'=>lang('model/order/update_status_err')];
            }
            if(intval($res) < 1){
                // 已被另一并发回调抢先置为已支付，本次视为已完成，不重复发放
                Db::rollback();
                return ['code'=>1,'msg'=>lang('model/order/pay_over')];
            }

            // 优惠券核销：order_remarks 快照含 coupon.coupon_user_id
            // 支付回调是「钱已经收到」的终态。券的账目异常（券被删除、并发被其他订单核销等）
            // 绝不能回滚订单：回滚会让订单停在未支付、积分不发，而网关的钱已经收了，用户白付。
            // 因此这里一律降级为无券订单继续发放积分，异常只记日志供站长事后对账（最多少收一张券的折扣）。
            $remarks = json_decode($order['info']['order_remarks'], true);
            $writeOff = $this->writeOffFromOrder($order['info'], $order_code);
            if ($writeOff['code'] > 1) {
                \think\Log::error('COUPON ANOMALY order_code=' . $order_code
                    . ' coupon_user_id=' . intval($writeOff['info']['coupon_user_id'])
                    . ' code=' . $writeOff['code'] . ' msg=' . $writeOff['msg']);
            }

            $where2 = [];
            $where2['user_id'] = $user['info']['user_id'];
            $res = model('User')->where($where2)->setInc('user_points',$order['info']['order_points']);
            if($res===false){
                Db::rollback();
                return ['code'=>2003,'msg'=>lang('model/order/update_user_points_err')];
            }

            //积分日志
            $data = [];
            $data['user_id'] = $user['info']['user_id'];
            $data['plog_type'] = 1;
            $data['plog_points'] = $order['info']['order_points'];
            $data['plog_remarks'] = (string)$order_code;
            model('Plog')->saveData($data);

            if(!empty($remarks) && is_array($remarks) && ($remarks['biz'] ?? '') === 'member_upgrade'){
                $user_latest = model('User')->infoData(['user_id' => $user['info']['user_id']]);
                if($user_latest['code'] > 1){
                    Db::rollback();
                    return $user_latest;
                }
                $upgrade_res = model('User')->upgradeByPaidOrder($order['info'], $user_latest['info']);
                if($upgrade_res['code'] > 1){
                    Db::rollback();
                    return $upgrade_res;
                }
            }

            Db::commit();

            try {
                model('Notify')->send($user['info']['user_id'], 'order', lang('notify/order_pay_ok_title'), lang('notify/order_pay_ok_content', [$order['info']['order_code']]), '/user/orders');
            } catch (\Exception $e) {
                \think\Log::error('Order pay notify uid=' . $user['info']['user_id'] . ' err=' . $e->getMessage());
            }

            return ['code'=>1,'msg'=>lang('model/order/pay_ok')];
        }catch (\Exception $e){
            Db::rollback();
            // 异常细节（SQL/表名/字段/堆栈）只进日志，不回传给调用方/前端
            \think\Log::error('Order notify failed order_code=' . $order_code . ' err=' . $e->getMessage());
            return ['code'=>2004,'msg'=>lang('save_err')];
        }

    }

    /**
     * 支付成功后核销订单关联的优惠券
     * - 无券订单：无核销动作，成功
     * - 已核销且绑定到本订单：幂等成功
     * - 已核销但绑定到其他订单：拒绝
     * - 未核销：原子 0->1 并绑定本订单
     */
    public function writeOffFromOrder($order, $order_code = '')
    {
        if (empty($order) || !is_array($order)) {
            return ['code' => 1001, 'msg' => lang('param_err'), 'info' => ['coupon_user_id' => 0]];
        }
        $remarks = json_decode((string)$order['order_remarks'], true);
        $cuId = 0;
        if (is_array($remarks)) {
            // 支持两种快照格式：扁平 coupon_user_id（充值）与嵌套 coupon.coupon_user_id（VIP）
            if (!empty($remarks['coupon']['coupon_user_id'])) {
                $cuId = intval($remarks['coupon']['coupon_user_id']);
            } elseif (!empty($remarks['coupon_user_id'])) {
                $cuId = intval($remarks['coupon_user_id']);
            }
        }
        if ($cuId < 1) {
            return ['code' => 1, 'msg' => lang('save_ok'), 'info' => ['coupon_user_id' => 0]];
        }
        $cu = model('CouponUser')->where('coupon_user_id', $cuId)->find();
        if (empty($cu)) {
            return ['code' => 2010, 'msg' => lang('coupon/not_yours'), 'info' => ['coupon_user_id' => $cuId]];
        }
        $cu = $cu->toArray();
        if (intval($cu['coupon_user_status']) === 1) {
            if ((string)$cu['order_code'] !== '' && (string)$order_code !== '' && (string)$cu['order_code'] !== (string)$order_code) {
                // 已被其他订单核销：欺诈/重复使用，必须阻断支付完成
                return ['code' => 2011, 'msg' => lang('coupon/used'), 'info' => ['coupon_user_id' => $cuId]];
            }
            return ['code' => 1, 'msg' => lang('save_ok'), 'info' => ['coupon_user_id' => $cuId]];
        }
        $now = time();
        $aff = model('CouponUser')->where('coupon_user_id', $cuId)
            ->where('coupon_user_status', 0)
            ->update([
                'coupon_user_status' => 1,
                'coupon_user_use_time' => $now,
                'order_code' => (string)$order_code,
                'order_id' => intval($order['order_id']),
            ]);
        if ($aff === false || $aff < 1) {
            // 并发竞争：可能已被另一笔并发订单核销，视为已使用
            return ['code' => 2010, 'msg' => lang('coupon/used'), 'info' => ['coupon_user_id' => $cuId]];
        }
        Db::name('coupon')->where('coupon_id', $cu['coupon_id'])->setInc('coupon_used', 1);
        return ['code' => 1, 'msg' => lang('save_ok'), 'info' => ['coupon_user_id' => $cuId]];
    }

}
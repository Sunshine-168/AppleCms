<?php

namespace app\api\controller;

use app\common\util\CsrfGuard;
use think\Db;
use think\Request;

/**
 * 优惠券 API
 *
 * 提供可用优惠券列表查询与领取。所有接口需用户登录。
 */
class Coupon extends Base
{
    use PublicApi;
    use CsrfGuard;

    public function __construct()
    {
        parent::__construct();
        $this->check_config();
    }

    /**
     * 辅助：检查登录
     */
    private function _checkLogin()
    {
        $check = model('User')->checkLogin();
        if ($check['code'] > 1) {
            return ['ok' => false, 'user_id' => 0, 'user' => null,
                    'response' => json(['code' => 1401, 'msg' => lang('model/user/not_login')])];
        }
        $uid  = intval($check['info']['user_id']);
        $user = Db::name('User')->where('user_id', $uid)->find();
        if (!$user) {
            return ['ok' => false, 'user_id' => 0, 'user' => null,
                    'response' => json(['code' => 1002, 'msg' => lang('model/user/not_found')])];
        }
        return ['ok' => true, 'user_id' => $uid, 'user' => $user, 'response' => null];
    }

    /**
     * 获取可用优惠券列表
     * GET /api.php/coupon/get_list?scene=recharge|vip&group_id=&long=&price=
     *
     * 返回当前用户可领取、在有效期、有库存、匹配场景与目标、且未达每人限领的优惠券。
     * coupon_end_time = 0 视为永不过期。
     */
    public function get_list(Request $request)
    {
        $auth = $this->_checkLogin();
        if (!$auth['ok']) return $auth['response'];

        $param = $request->param();
        $scene = trim((string)($param['scene'] ?? 'all'));
        if (!in_array($scene, ['all', 'recharge', 'vip'], true)) {
            $scene = 'all';
        }
        $price = floatval($param['price'] ?? 0);
        $groupId = intval($param['group_id'] ?? 0);
        $long = trim((string)($param['long'] ?? ''));
        $now = time();

        $where = [];
        $where['coupon_status'] = 1;
        $where['coupon_start_time'] = [['eq', 0], ['elt', $now], 'or'];
        $where['coupon_end_time'] = [['eq', 0], ['egt', $now], 'or'];

        $list = Db::name('coupon')->where($where)->order('coupon_time desc')->select();

        $available = [];
        $mine = [];
        // 已领取记录按 coupon_id 索引：mine 需要回传 coupon_user_id，下单时用它抵扣
        $myReceivedMap = [];
        $myRows = Db::name('coupon_user')->where('user_id', $auth['user_id'])->select();
        foreach ($myRows as $cu) {
            $myReceivedMap[intval($cu['coupon_id'])] = $cu;
        }

        foreach ($list as $row) {
            // 场景匹配
            if ($row['coupon_scene'] !== 'all' && $row['coupon_scene'] !== $scene) {
                continue;
            }
            // 已领取：先于库存判断，券被领完后持有人仍要能看到自己的券
            if (isset($myReceivedMap[intval($row['coupon_id'])])) {
                $cu = $myReceivedMap[intval($row['coupon_id'])];
                $row['coupon_user_id'] = intval($cu['coupon_user_id']);
                $row['coupon_user_status'] = intval($cu['coupon_user_status']);
                $row['order_code'] = (string)$cu['order_code'];
                // usable：未核销且未被待支付订单预占，可直接用于下单
                $row['usable'] = (intval($cu['coupon_user_status']) === 0 && (string)$cu['order_code'] === '') ? 1 : 0;
                $row['coupon_target_arr'] = json_decode($row['coupon_target'], true);
                $mine[] = $row;
                continue;
            }
            // 库存：coupon_total 恒为发放上限（校验器强制 >=1），无「0=无限」语义，与 receive() 一致
            if (intval($row['coupon_received']) >= intval($row['coupon_total'])) {
                continue;
            }
            // 满减门槛：price=0 时不做门槛过滤（仅展示）
            if ($price > 0 && floatval($row['coupon_min_price']) > $price) {
                continue;
            }
            // VIP 场景目标匹配
            if ($scene === 'vip' && !empty($row['coupon_target'])) {
                $target = json_decode($row['coupon_target'], true);
                if (is_array($target)) {
                    if (!empty($target['groups']) && !in_array($groupId, $target['groups'], true)) {
                        continue;
                    }
                    if (!empty($target['longs']) && $long !== '' && !in_array($long, $target['longs'], true)) {
                        continue;
                    }
                }
            }
            $row['coupon_target_arr'] = json_decode($row['coupon_target'], true);
            $available[] = $row;
        }

        return json([
            'code' => 1,
            'msg'  => lang('obtain_ok'),
            'info' => [
                'available' => $available,
                'mine'       => $mine,
            ],
        ]);
    }

    /**
     * 领取优惠券
     * POST /api.php/coupon/receive  参数 coupon_id
     */
    public function receive(Request $request)
    {
        $auth = $this->_checkLogin();
        if (!$auth['ok']) return $auth['response'];

        if (!$request->isPost()) {
            return json(['code' => 1001, 'msg' => lang('param_err')]);
        }

        $csrfErr = $this->checkCsrf();
        if ($csrfErr !== null) return json($csrfErr);

        $limited = $this->apiRateLimit('coupon_receive', $auth['user_id'], 10, 60);
        if ($limited !== true) {
            return $limited;
        }

        $couponId = intval($request->param('coupon_id', 0));
        if ($couponId < 1) {
            return json(['code' => 1001, 'msg' => lang('param_err')]);
        }
        $res = model('Coupon')->receive($couponId, $auth['user_id']);
        return json($res);
    }
}

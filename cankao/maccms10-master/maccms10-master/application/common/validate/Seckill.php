<?php
namespace app\common\validate;

use think\Validate;

class Seckill extends Validate
{
    protected $rule = [
        'seckill_name'           => 'require|max:100',
        'seckill_target_type'    => 'require|in:vip_group',
        'seckill_target_id'      => 'require|integer|egt:3',
        'seckill_target_long'    => 'require|in:day,week,month,year',
        'seckill_origin_points'  => 'require|integer|egt:1',
        'seckill_price_points'   => 'require|integer|egt:1',
        'seckill_total'          => 'require|integer|egt:1',
        'seckill_per_user' => 'require|in:1',
        'seckill_status'         => 'require|in:0,1',
    ];

    protected $message = [
        'seckill_name.require'         => 'seckill/name_required',
        'seckill_target_type.require'  => 'seckill/target_type_required',
        'seckill_target_type.in'       => 'seckill/target_type_invalid',
        'seckill_target_long.require'  => 'seckill/target_long_required',
        'seckill_target_long.in'       => 'seckill/target_long_invalid',
        'seckill_target_id.require'    => 'seckill/target_id_required',
        'seckill_target_id.egt'        => 'seckill/target_id_invalid',
        'seckill_origin_points.require'=> 'seckill/origin_points_required',
        'seckill_price_points.require' => 'seckill/price_required',
        'seckill_price_points.egt'     => 'seckill/price_invalid',
        'seckill_total.require'        => 'seckill/total_required',
        'seckill_total.egt'            => 'seckill/total_invalid',
        'seckill_per_user.require'     => 'seckill/per_user_required',
        'seckill_per_user.in'           => 'seckill/per_user_invalid',
        'seckill_status.require'        => 'seckill/status_required',
        'seckill_status.in'             => 'seckill/status_invalid',
    ];

    protected $scene = [
        'add'  => ['seckill_name', 'seckill_target_type', 'seckill_target_id', 'seckill_target_long', 'seckill_origin_points', 'seckill_price_points', 'seckill_total', 'seckill_per_user', 'seckill_status'],
        'edit' => ['seckill_name', 'seckill_target_type', 'seckill_target_id', 'seckill_target_long', 'seckill_origin_points', 'seckill_price_points', 'seckill_total', 'seckill_per_user', 'seckill_status'],
        'buy'  => ['seckill_id'],
    ];
}
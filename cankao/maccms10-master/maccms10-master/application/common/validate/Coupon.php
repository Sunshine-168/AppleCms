<?php
namespace app\common\validate;

use think\Validate;

class Coupon extends Validate
{
    protected $rule = [
        'coupon_name'      => 'require|max:100',
        'coupon_type'      => 'require|in:amount,discount',
        'coupon_value'     => 'require|float|gt:0',
        'coupon_scene'     => 'require|in:all,recharge,vip',
        'coupon_total'     => 'require|integer|egt:1',
        'coupon_per_user' => 'require|in:1',
        'coupon_status'    => 'require|in:0,1',
    ];

    protected $message = [
        'coupon_name.require'     => 'coupon/name_required',
        'coupon_type.require'     => 'coupon/type_required',
        'coupon_type.in'          => 'coupon/type_invalid',
        'coupon_value.require'    => 'coupon/value_required',
        'coupon_value.gt'         => 'coupon/value_invalid',
        'coupon_scene.require'    => 'coupon/scene_required',
        'coupon_scene.in'         => 'coupon/scene_invalid',
        'coupon_total.require'    => 'coupon/total_required',
        'coupon_total.egt'        => 'coupon/total_invalid',
        'coupon_per_user.require' => 'coupon/per_user_required',
        'coupon_per_user.in'      => 'coupon/per_user_invalid',
        'coupon_status.require'   => 'coupon/status_required',
        'coupon_status.in'        => 'coupon/status_invalid',
    ];

    protected $scene = [
        'add'     => ['coupon_name', 'coupon_type', 'coupon_value', 'coupon_scene', 'coupon_total', 'coupon_per_user', 'coupon_status'],
        'edit'    => ['coupon_name', 'coupon_type', 'coupon_value', 'coupon_scene', 'coupon_total', 'coupon_per_user', 'coupon_status'],
        'receive' => ['coupon_id'],
    ];
}
<?php

namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'offset'     => 'number|between:0,' . PHP_INT_MAX,
        'limit'      => 'number|between:1,500',
        'id'         => 'require|number|between:1,' . PHP_INT_MAX,
        'user_id'    => 'number|between:1,' . PHP_INT_MAX,
        'page'       => 'number|between:1,' . PHP_INT_MAX,
        'name'       => 'max:50',
        'nickname'   => 'max:50',
        'time_start' => 'number|between:1,' . PHP_INT_MAX,
        'time_end'   => 'number|between:1,' . PHP_INT_MAX,
        // 安全（#1363）：公开列表仅按 reg_time 排序。login_time / points 会成为
        // 「近期活跃顺序 / 积分相对排序」的间接 oracle，一律不开放给未认证接口。
        'orderby'    => 'in:reg_time',
    ];


    protected $message = [

    ];

    protected $scene = [
        // 安全（#1363）：已下线 email/qq/phone 反查与 group_id 会员组过滤；
        // 这些参数已从 scene 与 $rule 移除，控制器会显式拒绝（返回 1001）。
        'get_list' => [
            'offset',
            'limit',
            'name',
            'nickname',
            'time_start',
            'time_end',
            'orderby',
        ],
        'get_detail' => [
            'id',
        ],
        'get_my_invite' => [],
        'get_invite_list' => [
            'user_id',
            'page',
            'limit',
        ],
        'get_favorites_status' => [],
        'get_notify_list' => [
            'page',
            'limit',
        ],
        'get_notify_unread' => [],
        'read_notify' => [],
        'del_notify' => [],
    ];
}

<?php

namespace app\common\validate;

use think\Validate;

class VodAuditRule extends Validate
{
    protected $rule = [
        'rule_name' => 'require|max:100',
        'rule_type' => 'require|in:title_keyword,pic_empty,pic_invalid',
        'rule_action' => 'require|in:0,1,2',
        'rule_status' => 'in:0,1',
    ];

    protected $message = [];

    protected $scene = [
        'add' => ['rule_name', 'rule_type', 'rule_action', 'rule_status'],
        'edit' => ['rule_name', 'rule_type', 'rule_action', 'rule_status'],
    ];
}

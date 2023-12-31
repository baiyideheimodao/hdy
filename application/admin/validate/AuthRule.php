<?php

namespace app\admin\validate;

use think\Validate;

class AuthRule extends Validate
{
    protected $rule = [
        'name'       => 'require',
        'sort_order' => 'number',
    ];

    protected $message = [
        'name.require'      => '名称',
        'sort_order.number' => '排序',
    ];
}

<?php

namespace app\admin\validate;

use think\Validate;

class Organizational extends Validate
{
    protected $rule = [
        'name'       => 'require',
    ];

    protected $message = [
        'name.require'      => '名称',
    ];
}

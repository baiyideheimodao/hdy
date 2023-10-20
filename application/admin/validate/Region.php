<?php

namespace app\admin\validate;

use think\Validate;

class Region extends Validate
{
    protected $rule = [
        'name'       => 'require',
    ];

    protected $message = [
        'name.require'      => '名称',
    ];
}

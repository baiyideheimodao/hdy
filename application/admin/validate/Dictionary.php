<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Dictionary.php
     *  Create Date : 2019/8/19 0019 15:15
     *  Version : 0.1
     *  Copyright : jingfu_api Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\validate;
    use think\Validate;

    class Dictionary extends Validate {
        protected $rule = [
            'name' => 'require',
            'dm' => 'require',
            'value'  => 'require',
        ];

        protected $message = [
            'name.require' => '数据字典名不能为空',
            'dm.require' => '代码不能为空',
            'value.require'  => '值不能为空',
        ];
    }
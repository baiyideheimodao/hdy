<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : NewsType.php
     *  Create Date : 2022/1/17 19:40
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\common\model;
    use think\Model;

    class NewsType extends Model {

        protected $name = 'news_type';

        static function getType(){
            return self::where("state = 1")->order("sort asc")->field('id,name')->select();
        }
    }
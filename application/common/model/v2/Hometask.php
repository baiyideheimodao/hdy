<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Hometask.php
     *  Create Date : 2022/5/22 13:39
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\common\model\v2;

    use think\Model;

    class Hometask extends Model  {

        protected $name = 'home_task';

        public function getStartDateAttr($value){
            return date('Y-m-d',$value);
        }

        public function getEndDateAttr($value){
            return date('Y-m-d',$value);
        }

        public function getCreateTimeAttr($value){
            return date('Y-m-d',$value);
        }
    }
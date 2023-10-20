<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : TypeTaskDoneRecord.php
     *  Create Date : 2022/5/22 18:16
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\common\model\v2;
    use think\Model;

    class TypeTaskDoneRecord extends Model {

        protected $name = 'type_task_done_record';

        public function getDoneTimeAttr($value){
            return date('Y-m-d',$value);
        }
    }
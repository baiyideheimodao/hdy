<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Subject.php
     *  Create Date : 2021/12/31 10:16
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\model;
    use think\Model;

    class Subject extends Model {
        protected $name = 'subject';
        public function getChildList(){
            $list = $this->where("state = 1")->select();
            foreach ($list as $key => $item){
                $list[$key]['child'] = db('subject_version')->where("subject_id = {$item['id']} and state = 1")->select();
            }
            return $list;
        }
    }
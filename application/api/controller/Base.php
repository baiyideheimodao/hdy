<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Base.php
     *  Create Date : 2021/12/3 8:11
     *  Version : 0.1
     *  Copyright : test-master Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\api\controller;
    use app\common\controller\ApiController;

    class Base extends ApiController {

        private $NO_LOGIN_LIST = [
            'api/customer/login',
        ];

        // 禁止登录用户类型
        public $FORBID_MEMBER_TYPE = [
            1, // 养生专家
        ];

        protected $uid;
        protected $utype;
        protected $currentUrlNode;
        protected $pageSize;
        protected $header;

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
            $this->uid = 0;
            $this->pageSize = 10;
            $this->header = request()->header();
            $currentUrlNode = strtolower(request()->module() . '/' . request()->controller() . '/' . request()->action());
            $this->currentUrlNode = $currentUrlNode;
            if (in_array($currentUrlNode, $this->NO_LOGIN_LIST, true) === false) {
                $this->checkLogin();
            }
        }


        /**
         * 登录状态判断
         * 1 获取header中的token
         * 2 验证此token是否合法
         * */
        public function checkLogin(){
            if(!isset($this->header['token']) || empty($this->header['token'])){
                $this->rs('未获取到用户token！',-1);
            }
            $token = str_replace(' ','+',$this->header['token']);
            $u_id = ltrim(strrchr(authcode($token,'DECODE',config('rx.code_key'),0),'|'),'|');
            $this->uid = $u_id;
            $this->utype = db('member')->where("id={$u_id}")->get('type');
        }

    }
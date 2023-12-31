<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Auth.php
     *  Create Date : 2022/5/14 23:27
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\index\controller;
    class Auth extends BaseController {
        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function login() {
            $data = $this->request->param();
            $user = db('admin')->where('username', $data['account'])->where('password', md5($data['password']))->find();
            if (!empty($user)) {
                unset($user['password']);
                $user['token'] = md5(md5($user['id']));
                return $this->returnJson($user, '获取成功', 200);
            }
            return $this->returnJson([], '账号或密码错误');
        }

        public function menus() {
            $user_id = $this->request->param('userId', 0);
            // 获取角色id
            $user   = db('admin')->where("id", $user_id)->field('group_id')->find();
            $access = db('auth_group')->where('id = ' . $user['group_id'])->find();
            $list   = db('auth_rule')
                ->where("id in ({$access['rules']})")
                ->field('id,true enabled,pid parentId,name title,null icon,null basePath,vue_path path,null target,if(type="nav",1,2) type')
                ->order('sort_order asc')
                ->select();
            return $this->returnJson($list, '获取成功', 200);
        }
    }
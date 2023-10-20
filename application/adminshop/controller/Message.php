<?php

namespace app\adminshop\controller;


use think\Controller;
use think\db;
use think\facade\Cookie;

class Message extends Controller
{

    //验证登录

    public function login()
    {
        return $this->success('请先登录', '/admin/login');
    }


    public function index($zt = '')
    {
        $arr = ($this->request->param());
        $res = db('message')->order('id desc')
            ->paginate(10, false, ['query' => request()->param()]);
        $location = [
            'title' => "留言管理",
            'name' => "message",
        ];
        $this->assign('location', $location);
        $this->assign('list', $res);
        $page = $res->render();
        $this->assign('page', $page);
        return $this->fetch();
    }




    public function upStatus()
    {
        $data = ($this->request->param());
        if ($data['status'] == 'true') {
            $status = 1;
        } else {
            $status = 0;
        }
        $res = db('message')->where('id', $data['id'])->data('status', $status)->update();
        if ($res) {
            return json(1);
        } else {
            return json(2);
        }
    }




    public function del($id)
    {
        db('message')->where('id', $id)->delete();
        return 1;
    }


}

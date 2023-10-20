<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Cookie;

class Index extends Controller
{
    public function index()
    {
        if(Cookie::has('name')){
            $this->redirect('/admin/');
            // $this->success('登录成功','/admin/');
        }else{
            $this->redirect('/admin/login/');
            // $this->success('请等登录','/admin/login/');
        }

    }

}

<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;
use think\Controller;
use think\facade\Log;

class News extends Controller
{
    public  function xinwen()
    {
        $data=($this->request->param());
        $res = Db::name('xinwen')->where('fenlei',$data['fenlei'])->order('px asc,time desc')->select();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    public  function view($id)
    {
        $res = Db::name('xinwen')->where('id',$id)->find();
        $res['time'] = date('Y-m-d H:m',$res['time']);
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }
}
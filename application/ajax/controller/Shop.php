<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;
use think\Controller;
use think\facade\Log;

class Shop extends Controller
{
    public function index()
    {
    }

    public  function proList($type)
    {
        if ($type == 'hot') {
            $res = Db::name('pro')->where('type', 1)->order('creatime desc')->select();
        } else {
            $res = Db::name('pro')->order('creatime desc')->limit(0, 8)->select();
        }

        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }


    public  function ad($type)
    {
        if ($type == '商城') {
            $limit = '0,5';
        } else {
            $limit = '0,1';
        }
        $res = Db::name('ad')->where('type', $type)->order('px asc')->limit($limit)->select();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    public  function profl($type = '')
    {
        if ($type == '首页') {
            $res = Db::name('profl')->where('pid', 0)->order('px asc')->limit(0, 5)->select();
            if ($res) {
                $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
            } else {
                $data = ['code' => 0, 'msg' => 'fail'];
            }
            return json($data);
        } else {
            $res = Db::name('profl')->field('id,pid,name,image as picture')->order('px asc')->select();

            foreach ($res as $k => $val) {
                if ($val['pid'] == 0) {
                    unset($res[$k]['pid']);
                    unset($res[$k]['picture']);
                }
            }
            return json($res);
        }
    }

    public function cpList($num, $id, $type, $paixv)
    {
        $start = $num * 6;
        if ($type == 1) {
            $order = 'sales desc,creatime desc';
        } else if ($type == 2 && $paixv == 1) {
            $order = 'price desc,creatime desc';
        } else if ($type == 2 && $paixv == 2) {
            $order = 'price asc,creatime desc';
        } else {
            $order = 'creatime desc';
        }
        $res = Db::name('pro')->where('typeid', $id)->limit($start, 6)->order($order)->select();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    public function proview($id)
    {
        $res = Db::name('pro')->where('id', $id)->find();
        $res['imgList'] = explode(',', $res['images']);
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    
}

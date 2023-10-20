<?php


namespace app\ajax\controller;

use think\Db;
use think\Controller;
use think\facade\Log;

class Product extends Controller
{
    //商品分类
    public function productSort()
    {
        $res = db('menu')->order('px asc')->select();
        foreach ($res as $k => $val){
            if($val['pid']===0){
                unset($res[$k]['pid']);
            }
        }
        if ($res) {
            $data = ['code' => 100, 'msg' => '获取成功', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => '获取失败'];
        }
        return json($data);
    }

    //焦点图
    public function banner()
    {
        $res = db('banner')->where(['status'=>1])->order('px asc')->select();
        if ($res) {
            $data = ['code' => 100, 'msg' => '获取成功', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => '获取失败'];
        }
        return json($data);
    }
}

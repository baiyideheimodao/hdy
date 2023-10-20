<?php


namespace app\adminshop\controller;


use think\Controller;
use think\db;
use think\facade\Cookie;

class System extends  Controller
{
    //验证登录
    public function login(){
        return $this->success('请先登录','/shop/public/admin/login');
    }

    public function index($action=1)
    {
        $data=($this->request->param());
        if(@($action=='update')){
            $arr = array_filter($data);
            unset($arr['action']);
            $result = Db::name('system')
                ->where('id', 1)
                ->data($arr)
                ->update();
            return json($result);
        }
        $result = Db::name('system')->where('id',1)->select();

        $this->assign('data',$result[0]);
        return $this->fetch();
    }

    public function delAll($biao=1){
        $data=($this->request->param());
        $result = Db::name($biao)->delete($data['arr']);
        return 1;
    }

    public function del($biao=1,$id){

        $result = Db::name($biao)->where('id',$id)->delete();
        return 1;
    }

}
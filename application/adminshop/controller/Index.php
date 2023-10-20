<?php

namespace app\adminshop\controller;

use think\facade\Session;
use think\Controller;
use think\db;
use think\facade\Cookie;



class Index extends Controller
{
    public function index()
    {
        if(!Cookie::has('name')){
            $this->redirect('/admin/login/');
        }
        $this->assign('admin_yhm', Session::get('admin_yhm'));
        $this->assign('admin_xm', Session::get('admin_xm'));
        $this->assign('admin_qx', Session::get('admin_qx'));
        return $this->fetch();
    }



    public function right()
    {
        $template['title'] = '欢迎使用网站管理系统'; // 标题
        $template['location'] = '/adminshop/index/right'; //主页地址
        $time1 = strtotime(date('Y-m-d 00:00:00',time()));
        $time2 = strtotime(date('Y-m-d 23:59:59',time()));
        $todayUser=DB::name('user')->whereBetween('create_time',[$time1,$time2])->count();
        $countUser=DB::name('user')->count();
        $countPro=DB::name('pro')->count();
        $countOrder=DB::name('orderlist')->count();
        $countyfOrder=DB::name('orderlist')->where('state',2)->count();
        $countdfhOrder=DB::name('orderlist')->where(['state'=>3])->count();
        $countshOrder=DB::name('orderlist')->where('state',5)->count();
        $countshOrder=DB::name('orderlist')->where('state',5)->count();
        $tixian=DB::name('recordscore')->where(['sfstatus'=>0,'status'=>1])->count();
        $data=['todayUser'=>$todayUser,'countUser'=>$countUser,'countPro'=>$countPro,'countOrder'=>$countOrder,'countyfOrder'=>$countyfOrder,'countdfhOrder'=>$countdfhOrder,'countshOrder'=>$countshOrder,'tixian'=>$tixian];
        $this->assign('location', $template);
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function exit()
    {
        //return '4444';
        Session::set('admin_yhm', NULL);
        echo "<script>parent.parent.location.href='/admin/login';</script>";
        exit();
    }
    public function tishi($ts)
    {
        $this->assign('ts', $ts);
        return $this->fetch();
    }


    public function upload()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        header('Access-Control-Allow-Headers: content-type,token');
        $data=$this->request->param();//获取传参
        $files = $this->request->file('file');//获取图片
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $files->move('./public/upload');//保存到longjue目录下
        if($info){
            $msg=['code'=>0,'msg'=>'上传成功','data'=>['src'=>xtsz(1).'/upload/'.str_replace('\\', '/', $info->getSaveName())]];
        }else{
            $msg=['code'=>1,'msg'=>$files->getError()];
        }
        return $msg;
    }




}
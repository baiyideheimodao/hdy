<?php

namespace app\ajax\controller;

use think\Db;
use think\facade\Session;
use think\Controller;
use think\facade\Log;

class Login extends Controller
{

    //获取微信openid
    public function code()
    {
        $list=($this->request->param());
        $code = $list['code'];
        $appid = 'wx32d11f1898c690d6';
        $secret = 'f0f5285f163324b11d3b2c0cb09ff8ac';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."";
        $res = $this->http_curl($url);
        // var_dump($res);
        $getOpenid = $res['openid'];
        //$getOpenid = 'oek-e5MLUFL4W_7OAzQ4hcI6YJFs';
        $res = $this->userInfo($getOpenid);
        if($res){
            $arr2 =[
                'code'=>100,
                'openid'=>$getOpenid,
                'data'=>$res,
                'msg'=>'提示'
            ];
        }else{
            $arr2 =[
                'code'=>10,
                'openid'=>$getOpenid
            ];
        }
        return json($arr2);
    }

    public function http_curl($url,$type='get',$res='json',$arr=''){
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if ($res == 'json') {
            return json_decode($output,true);
        }
    }
    //登录注册
    public function login()
    {
        $list = ($this->request->param());
        $getOpenid = $list['openid'];
        error_log(
            var_export([
            "api"=>__FILE__.__FUNCTION__,
            "content"=>$list
            ],true),
            3,
            'log.txt'
        );
        $res = Db::name('user')->where('openid',$getOpenid)->find();
        if($res){
            $phone = Db::name('user')->where('openid',$getOpenid)->value('phone');
            if($phone){
                $arr2 =[
                    'code'=>20,
                    'openid'=>$getOpenid,
                    'data'=>$res,
                    'msg'=>'提示'
                ];
            }else{
                $arr2 =[
                    'code'=>10,
                    'openid'=>$getOpenid,
                    'data'=>$res,
                    'msg'=>'提示'
                ];
            }
            return json($arr2);
        }else{
            $arr = [
                'nickname' => $list['nickName'],
                'avatar' => $list['avatarUrl'],
                'openid' => $list['openid'],
                'sex' => 0,
                'name' => '',
                'create_time' => time(),
            ];
            $res = Db::name('user')->insert($arr);
            if ($res) {
                $userId = Db::name('user')->getLastInsID();
                $arr1 = [
                    'id' => $userId,
                    'name' => '',
                    'phone' => '',
                    'nickname' => $list['nickName'],
                    'openid' => $list['openid'],
                    'avatar' => $list['avatarUrl'],
                    'area_id' => 0,
                    'grade_id' => '',
                    'sex' => 0
                ];
                $arr2 = [
                    'code' => 100,
                    'data' => $arr1,
                    'msg' => '注册成功'
                ];
            } else {
                $arr2 = [
                    'code' => 0,
                    'msg' => '注册失败'
                ];
            }
        }
        return json($arr2);
    }

    public function userInfo($openid){
        $res = db('user u')
            ->join('user_class_log lo','lo.u_id = u.id','left')
            ->join('class_group c','c.id = lo.class_group_id','left')
            ->join('area ar','ar.id = u.area_id','left')
            ->join('teacher t','t.openid=u.openid','left')
            ->field('u.base_id,t.type type,u.id,u.name,u.nickname,u.avatar,u.sex,u.phone,u.openid,u.area_id,u.p_id,u.points,u.grade_id,u.school_id,u.is_teacher
            ,u.work_bean,u.work_money,c.name class_name,ar.name area_name,c.id class_id,u.password')
            ->where('u.openid',$openid)
            ->find();
        if(!$res){
            return $res;
        }
        $res['password'] = !!$res['password'];
        return $res;
    }

    public function loginTest(){
        // $list = ($this->request->param());
        $list = [
            'grade_id'=>'6',
            'class_id'=>'1',
            'name'=>'小明',
            'sex'=>1,
            'phone'=>'13512345678'
        ];
        //获取注册登记者名单
        $login_arr = db('apply_for')->all();
        $sexMap = [
            1=>'男',
            2=>'女'
        ];
        // $class_group_id = ;
        // $grade_id = $list['grade_id'];
        $getInfo =  array_filter($login_arr,function($value)use($list,$sexMap){
           return ($value['年级'] === $list['grade_id'])
            && ($value['班级'] === $list['class_id'])
            && ($value['姓名'] === $list['name'])
            && ($value['性别'] === $sexMap[$list['sex']])
            && ($value['手机号'] === $list['phone']);
        });
        var_dump($login_arr,1,$getInfo);
        return json($login_arr);
    }

    //班级注册
    public function newLogin()
    {
        $list = ($this->request->param());
        //return json($list);
        error_log(
            var_export([
            "api"=>__FILE__.__FUNCTION__,
            "content"=>$list
            ],true),
            3,
            'log.txt'
        );
        $sexMap = [
            1=>'男',
            2=>'女'
        ];
        $grade_map = [
            '1'=>1,
            '2'=>2,
            '3'=>3,
            '4'=>5,
            '5'=>6,
            '6'=>7
        ];
        $class_map = [
            '81'=>'81',
            '87'=>'87',
            '88'=>'88',
            '89'=>'89',
            '90'=>'90',
            '91'=>'91',
            '92'=>'92',
            '93'=>'93',
            '94'=>'94',
            '95'=>'95',
            '96'=>'96'
        ];
        $getOpenid = $list['openid'];
        $arr = $list;
        $class_group_id = $arr['class_id'];
        // if(!in_array($list['name'],['肖鸣旭','王艺菲','周宥亦'])){
        //     if(in_array($class_group_id,[62,63])){
        //         return json([
        //             'code'=>400,
        //             'msg' => '本班级已经暂停注册'
        //         ],400);
        //     };
        // }
        //获取注册登记者名单
         
        if($list['type']==0){
            $arr_other = db('class_group')->field('area_id,school_id,grade_id')->where('id',$list['class_id'])->find();
            $arr = array_merge($arr,$arr_other);
            try {
                //code...
                $login_arr = db('apply_for')->all();
                $getInfo =  array_filter($login_arr,function($value)use($arr,$sexMap,$grade_map,$class_map){
                    if (!key_exists($value['年级'],$grade_map)){
                        return printLog([$value,$grade_map]);
                    }
                    if(!key_exists($value['班级'],$class_map)){
                        return printLog([$value,$class_map]);
                    }
                    $canLogin = ($grade_map[$value['年级']] === $arr['grade_id'])
                    && ($class_map[$value['班级']] === $arr['class_id'])
                    && ($value['姓名'] === $arr['name'])
                    && ($value['性别'] === $sexMap[$arr['sex']])
                    && ($value['手机号'] === $arr['phone']);
                    if(!$canLogin){
                        error_log(
                            var_export([
                            "api"=>__FILE__.__FUNCTION__,
                            "content"=>[
                                [$grade_map[$value['年级']],$arr['grade_id'],$value['年级']],
                                [$class_map[$value['班级']],$arr['class_id'],$value['班级']],
                                [$value['姓名'],$arr['name']],
                                [$value['性别'],$sexMap[$arr['sex']]],
                                [$value['手机号'],$arr['phone']]
                            ]
                            ],true),
                            3,
                            'log.txt'
                        );
                    }
                    return $canLogin;
                 });
                $arr_len = count($getInfo);
            } catch (\Throwable $th) {
                printLog($th->getMessage());
            } 
            if(!$arr_len){
                return json([
                    'code'=>400,
                    'msg'=>'未登记的用户无法注册'
                ],400);
            }
        }else{
            $arr['area_id']= 1;
            $arr['school_id']= 4;
            $arr['grade_id']= 6;
            $arr['is_default']=1;
        }
        unset($arr['type']);
        unset($arr['class_id']);
        $arr['create_time'] = time();
        //判断是否存在会员，不存在则注册
            $resId = db('user u')
            ->where('u.openid',$getOpenid)
            ->value('id');
        if(!in_array($list['type'],[0,1])){
            $baseInfo = db('base')->find($class_group_id);
            $arr['base_id'] = $baseInfo['base_id'];
        }
        if(empty($resId)){
            $resId = db('user')->insertGetId($arr);
        }else{
            try {
                if(!in_array($list['type'],[0,1])){
                    db('user')->where('id',$resId)->update(['base_id'=>$arr['base_id']]);
                }
            } catch (\Throwable $th) {
                printLog($th->getMessage());
                return json('失败',500);
            }
            
        }
        if($list['type']==0){
            db('user_class_log')->insert(['class_group_id'=>$class_group_id,'u_id'=>$resId,'create_time'=>$arr['create_time']]);
        }else if($list['type']==1){
            $arr_other = db('class_group')->field('area_id,school_id,grade_id')->where('id',$list['class_id'])->find();
            $base_id = 0;
            $school_id = $arr_other['school_id'];
            $tData = [
                'username'=>$arr['name'],
                'sex'=>$arr['sex'],
                'phone'=>$arr['phone'],
                'type'=>1,
                'status'=>1,
                'admin_id'=>1,
                'school_id'=>$school_id,
                'base_id'=>$base_id,
                'is_head'=>0,
                'openid'=>$getOpenid,
                'password'=>'e10adc3949ba59abbe56e057f20f883e',
                'avatar'=>$arr['avatar'],
                'nickname'=>$arr['nickname'],
                'create_time'=>$arr['create_time'],
                'class_id'=>$class_group_id,
            ];
            db('teacher')->insert($tData);
        }else{
            $base_id = $class_group_id;
            $school_id = 0;
            $class_group_id = 0;
            $tData = [
                'username'=>$arr['name'],
                'sex'=>$arr['sex'],
                'phone'=>$arr['phone'],
                'type'=>2,
                'status'=>1,
                'admin_id'=>1,
                'school_id'=>$school_id,
                'base_id'=>$base_id,
                'is_head'=>0,
                'openid'=>$getOpenid,
                'password'=>'e10adc3949ba59abbe56e057f20f883e',
                'avatar'=>$arr['avatar'],
                'nickname'=>$arr['nickname'],
                'create_time'=>$arr['create_time'],
                'class_id'=>$class_group_id,
            ];
            db('teacher')->insert($tData);
        }
        $resData = $this->userInfo($getOpenid);
        $arr2 = [
            'code' => 100,
            'data' => $resData,
            'msg' => '注册成功'
        ];
        return json($arr2);
    }
    
    
    public function upload_img()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        header('Access-Control-Allow-Headers: content-type,token');
        $data=$this->request->param();//获取传参
        $files = $this->request->file('file');//获取图片
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $files->move('./public/upload');//保存到xcx目录下
        $path = '../upload/image/'.date('Ymd',time()).'/'.$info->getFilename();//图片上传后的地址
        if($info){
            // 成功上传后 获取上传信息
            return $path;
        }else{
            // 上传失败获取错误信息
            return 0;
        }
    }

}

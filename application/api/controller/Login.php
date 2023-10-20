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
        $getOpenid = $res['openid'];
        $res = Db::name('user')->where('openid',$res['openid'])->find();
        if($res){
            $arr1 = [
                'id' => $res['id'],
                'phone'=>$res['phone'],
                'name' => $res['name'],
                'nickname'=>$res['nickname'],
                'avatar'=>$res['avatar']
            ];
            $arr2 =[
                'code'=>100,
                'openid'=>$getOpenid,
                'data'=>$arr1,
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
        $res = Db::name('user')->where('openid',$getOpenid)->find();
        if($res){
            $mobile = Db::name('user')->where('openid',$getOpenid)->value('mobile');
            $arr1 = [
                'id' => $res['id'],
                'mobile'=>$res['mobile'],
                'xm' => $res['xm'],
                'nickname'=>$res['nickname'],
                'portrait'=>$res['portrait']
            ];
            if($mobile){
                $arr2 =[
                    'code'=>20,
                    'openid'=>$getOpenid,
                    'data'=>$arr1,
                    'msg'=>'提示'
                ];
            }else{
                $arr2 =[
                    'code'=>10,
                    'openid'=>$getOpenid,
                    'data'=>$arr1,
                    'msg'=>'提示'
                ];
            }
            return json($arr2);
        }else{
            $arr = [
                'nickname' => $list['nickName'],
                'portrait' => $list['avatarUrl'],
                'openid' => $list['openid'],
                'xm' => '',
                'creatime' => time(),
            ];
            $res = Db::name('user')->insert($arr);
            if ($res) {
                $userId = Db::name('huiyuan')->getLastInsID();
                $arr1 = [
                    'id' => $userId,
                    'xm' => '',
                    'mobile' => '',
                    'nickname' => $list['nickName'],
                    'portrait' => $list['avatarUrl'],
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


}

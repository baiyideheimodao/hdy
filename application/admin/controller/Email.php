<?php
namespace app\admin\controller;


use think\Controller;
use think\Db;
use app\admin\model\System as SysData;
use think\facade\Cookie;
use think\facade\Request;
use think\Validate;

class Email extends Controller
{
    //验证登录
    public function login(){
        return $this->success('请先登录','/admin/login');
    }

    public function index()
    {
        if(!Cookie::has('name')) {
            $this->login();
        }else if(Cookie::has('qx')!=1){
            $this->error('你没有权限访问这个栏目');
        }
        $id = Cookie::get('id');
        $name = Cookie::get('name');
        $this->assign('adminid', $id);
        $this->assign('send_nickname', $name);
        $data = Db::name('email')->where('adminid',$id)->select();
        if(count($data)==0){
            $data = [
                'id'=>$id,
                'smtp_server'=>'',
                'cache'=>'',
                'send_email'=>'',
                'send_nickname'=>'',
                'send_username'=>'',
                'send_password'=>'',
                'action'=>'',
            ];
            $this->assign('data', $data);
        }else{
            $this->assign('data', $data[0]);
        }
        return $this->fetch();
    }

    //修改
    public function edit($id,$action=100)
    {
        $data=$this->request->param();
        if(@$action=='save'){
            $result = Db::name('email')
                ->where('id', $id)
                ->data($data)
                ->update();
        }
        else{
            unset($data['action'],$data['id']);
            $result = Db::name('email')
                ->insert($data);
        }
        return json($result);
    }

    //修改
    public function test($id)
    {
        $data = Db::name('email')->where('id',$id)->select();
        $this->assign('data', $data[0]);
        return $this->fetch('send');
    }

    //发送邮件
    public function contactMe()
    {
        if(Request::isGet()){
            $data = Request::param();
            $validate = new Validate();
            $validate->rule(['contact_name|联系名'=>'require|max:40','contact_way|联系方式'=>'require|max:80','content|内容'=>'require|max:800']);
            if(!$validate->check($data))
                return json(['code'=>400,'message'=>$validate->getError()]);
            $contact_name = $data['contact_name'];
            $contact_way = $data['contact_way'];
            $content = $data['content'];
            $data['adminid'] = Cookie::get('id');
            $data['send_nickname'] = Cookie::get('name');
            $body = "联系名：$contact_name<br/>联系方式：$contact_way<br/>内容：$content";
            $result = Db::name('email')->where('adminid',Cookie::get('id'))->select();
            $sendRes = sendMail($result[0],'249768879@qq.com','替换为收件人名称','替换为邮件标题',$body,null);
            $data['ip'] = Request::ip();
            $data['uid'] = 10;
            $data['create_time'] = time();
            try{
                $result = Db::name('email_list')->insert($data);
            }catch (Exception $e){
            }

            if($sendRes===true)
            {
                return json(['code'=>200,'message'=>'发送成功,收到了您的信息']);
            }
            else
                return json(['code'=>400,'message'=>'邮件接收失败,但数据已接收到']);
        }
        else{
            return '非法请求';
        }
    }



}

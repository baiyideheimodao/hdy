<?php
namespace app\adminshop\controller;


use think\Controller;
use think\Db;
use app\admin\model\System as SysData;

class User extends Controller
{
    //验证登录
    public function login(){

    }

    public function index()
    {
        $template['title'] = '后台管理员'; // 标题
        $template['location'] = '/admin/user'; //主页地址
        $template['name'] = 'user'; //主页地址
        $this->assign('location', $template);
        $list = Db::name('admin a')
            ->field('a.id,a.yhm,a.xm,g.name')
            ->join('admin_group g','a.qx=g.id')
            ->order('id', 'asc')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function add()
    {
        $template['title'] = '管理员添加'; // 标题
        $template['location'] = '/admin/user'; //主页地址
        $template['name'] = 'user'; //主页地址
        $this->assign('location', $template);
        $data=($this->request->post());
        if($data){
            $data['mm'] = md5($data['mm']);
            $result = Db::name('admin')->strict(false)->insert($data);
            return $result;
        }
        return $this->fetch();
    }

    public function edit($id)
    {
        $template['title'] = '管理员修改'; // 标题
        $template['location'] = '/admin/user'; //主页地址
        $template['name'] = 'user'; //主页地址
        $data=($this->request->post());
        if(@$data['qx']){
            $result = Db::name('admin')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            //return dump($sql);
            //$sql='22';
            return $result;
        }
        $this->assign('location', $template);
        $data=Db::name('admin')->where('id',$id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }

    //重置密码
    public function chongzhi($id){
        $dataa=[
            'password'=>'e10adc3949ba59abbe56e057f20f883e'
        ];
        $data=($this->request->param());
        $biao = $data['biao'];
        $result = Db::name($biao)
            ->where('id', $id)
            ->data($dataa)
            ->update();
        return json($result);
    }

    public function juese()
    {
        $template['title'] = '角色管理'; // 标题
        $template['location'] = '/admin/user'; //主页地址
        $template['name'] = 'user'; //主页地址
        $this->assign('location', $template);
        $list = Db::name('admin_group a')
            ->order('id', 'asc')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

}

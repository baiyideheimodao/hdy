<?php
namespace app\adminshop\controller;
use think\facade\Session;
use think\Controller;
use think\Db;
class Admin extends Controller
{
    public function index()
    {
        $template['title'] = '管理员管理'; // 标题
        $template['location'] = '/adminshop/admin'; //主页地址
        $template['name'] = 'admin'; //主页地址
        $this->assign('location', $template);
        $list = Db::name('admin')->where(1,1)->order('id', 'desc')->paginate(10);
        $this->assign('list', $list);
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function add()
    {
        $template['title'] = '管理员管理'; // 标题
        $template['location'] = '/adminshop/admin'; //主页地址
        $template['name'] = 'adminshop'; //主页地址
        $this->assign('location', $template);
        $data=($this->request->param());
        if($data){
            $data['mm']=md5($data['mm']);
            Db::name('admin')->strict(false)->insert($data);
            return 1;
        }
        return $this->fetch();
    }
    public function edit($id)
    {
        $template['title'] = '管理员管理'; // 标题
        $template['location'] = '/adminshop/admin'; //主页地址
        $template['name'] = 'adminshop'; //主页地址
        $data=Db::name('admin')->where('id',$id)->find();
        $this->assign('data', $data);
        $data=($this->request->param());
        if(@$data['action']){
            if($data['mm']==NULL){unset($data['mm']);}else{$data['mm']=md5($data['mm']);}
            Db::name('admin')->where('id', $data['id'])->data($data)->update();
            return 1;
        }
        $this->assign('location', $template);
        return $this->fetch();
    }
    public function editpass($action=null)
    {
        checkadmin();
        $template['title'] = '密码'; // 标题
        $template['location'] = '/adminshop/editpass'; //主页地址
        $template['name'] = 'adminshop'; //主页地址
        $data=($this->request->param());
        if($action=='save'){
            $data['mm']=md5($data['mm']);
            Db::name('admin')->where('id',Session::get('admin_id'))->data($data)->update();
            return 1;
        }
        $this->assign('location', $template);
        return $this->fetch();
    }
    public function del($id)
    {
        Db::name('admin')->where('id',$id)->delete();
        //$sql=Db::getLastSQL();
        //return dump($sql);
        return 1;
    }

    public function delall()
    {
        $data=$this->request->post();
        //return json($data['id']);
        Db::name('admin')->delete($data['id']);
        //$sql=Db::getLastSQL();
        //return $sql;
        return 1;
    }
}
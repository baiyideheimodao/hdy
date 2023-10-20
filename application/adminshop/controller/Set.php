<?php
namespace app\adminshop\controller;
use think\facade\Session;
use think\Controller;
use think\Db;
class Set extends Controller
{
    public function index()
    {
        $template['title'] = '参数设置'; // 标题
        $template['location'] = '/admin/set'; //主页地址
        $template['name'] = 'set'; //主页地址
        $this->assign('location', $template);
        $list = Db::name('set')->where(1,1)->order('id', 'asc')->paginate(10);
        $this->assign('list', $list);
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function edit($id)
    {
        $template['title'] = '参数设置'; // 标题
        $template['location'] = '/admin/set'; //主页地址
        $template['name'] = 'set'; //主页地址
        $data=($this->request->param());
        if(@$data['action']){
             Db::name('set')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            return 1;
        }
        $this->assign('location', $template);
        $data=Db::name('set')->where('id',$id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }
    public function del($id)
    {
        Db::name('news')->where('id',$id)->delete();
        //$sql=Db::getLastSQL();
        //return dump($sql);
        return 1;
    }
    public function delall()
    {
        $data=$this->request->post();
        //return json($data['id']);
        Db::name('news')->delete($data['id']);
        //$sql=Db::getLastSQL();
        //return $sql;
        return 1;
    }
}
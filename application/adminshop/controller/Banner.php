<?php
namespace app\adminshop\controller;
use think\facade\Session;
use think\Controller;
use think\Db;
class Banner extends Controller
{

    public function index($key=null)
    {
        checkadmin();
        $template['title'] = '轮播图'; // 标题
        $template['location'] = '/adminshop/banner'; //主页地址
        $template['name'] = 'banner'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $where="1=1";
        if($key!=null){
            $where.=" and bt like '%".$key."%'";
        }
        $list = Db::name('sc_banner')->where($where)->order('px asc,id desc')->paginate(20,false,['query' => request()->param()]);
        $this->assign('list', $list);
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function add($action=null)
    {
        checkadmin();
        $template['title'] = '轮播图'; // 标题
        $template['location'] = '/adminshop/banner'; //主页地址
        $template['name'] = 'banner'; //主页地址
        $this->assign('location', $template);
        $data=($this->request->param());
        if($action){
            Db::name('sc_banner')->strict(false)->insert($data);
            return 1;
        }
        return $this->fetch();
    }
    public function edit($id,$action=null,$type=null)
    {
        checkadmin();
        $template['title'] = '轮播图'; // 标题
        $template['location'] = '/adminshop/banner'; //主页地址
        $template['name'] = 'banner'; //主页地址
        $data=($this->request->param());
        if($action=='save'){
            unset($data['file']);
            unset($data['action']);
            Db::name('sc_banner')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            return 1;
        }
        $this->assign('location', $template);
        $data=Db::name('sc_banner')->where('id',$id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }
    public function del($id)
    {
        Db::name('sc_banner')->where('id',$id)->delete();
        return 1;
    }
    public function delall()
    {
        $data=$this->request->param();
        Db::name('sc_banner')->delete($data['id']);
        return 1;
    }
}
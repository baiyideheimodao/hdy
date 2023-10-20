<?php
namespace app\adminshop\controller;
use think\facade\Session;
use think\Controller;
use think\Db;
class Xtsz extends Controller
{
    public function index()
    {
        $template['title'] = '系统设置'; // 标题
        $template['location'] = '/adminshop/xtsz'; //主页地址
        $template['name'] = 'xtsz'; //主页地址
        $this->assign('location', $template);
        $list = Db::name('system')->where('source',1)->order('id','asc')->paginate(10);
        $this->assign('list', $list);
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function xianzhi($id,$type)
    {
        $template['title'] = '系统设置'; // 标题
        $template['location'] = '/adminshop/xtsz'; //主页地址
        $template['name'] = 'xtsz'; //主页地址
        $this->assign('location', $template);
        if($id){
            if($type=='false'){
                $type='关闭';
            }else{
                $type='开启';
            }
            Db::getlastsql(Db::name('xtsz')->where('id', $id)->data(['content'=>$type])->update());
            return 1;
        }
    }


    public function edit($id)
    {
        $template['title'] = '系统设置'; // 标题
        $template['location'] = '/adminshop/xtsz'; //主页地址
        $template['name'] = 'xtsz'; //主页地址
        $data=($this->request->param());
        if(@$data['action']){
            unset($data['action']);
            Db::name('system')->where('id', $data['id'])->data($data)->update();
            return 1;
        }
        $this->assign('location', $template);
        $data=Db::name('system')->where('id',$id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }



    public function banner($type = '')
    {
        if(@$type){
            $res = Db::name('sc_banner')->where('type',$type)->order('sort asc,id asc')->paginate(10, false, ['query' => request()->param()]);
        }else{
            $res = Db::name('sc_banner')->order('sort asc,id asc')->paginate(10, false, ['query' => request()->param()]);
        }
        $fenlei = Db::name('sc_banner')->select();
        $location = [
            'title' => "广告",
            'name' => "sc_banner",
        ];
        $this->assign('fenlei', $fenlei);
        $this->assign('location', $location);
        $this->assign('list', $res);
        $page = $res->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function jmbanner($type = '')
    {
        if(@$type){
            $res = Db::name('sc_banner')->where('type',$type)->order('sort asc,id asc')->paginate(10, false, ['query' => request()->param()]);
        }else{
            $res = Db::name('sc_banner')->order('sort asc,id asc')->paginate(10, false, ['query' => request()->param()]);
        }
        $fenlei = Db::name('sc_banner')->select();
        $location = [
            'title' => "广告",
            'name' => "sc_banner",
        ];
        $this->assign('fenlei', $fenlei);
        $this->assign('location', $location);
        $this->assign('list', $res);
        $page = $res->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function banneredit($id, $action = '')
    {
        $location = [
            'title' => "广告信息修改",
            'name' => "banner",
        ];
        $this->assign('location', $location);
        $data = ($this->request->param());
        if ($action == 'save') {
            $res = Db::name('sc_banner')->where('id', $data['id'])->data($data)->update();
            if ($res) {
                return json($res);
            }
        }
        $res = Db::name('sc_banner')->where('id', $data['id'])->find();
        $this->assign('data', $res);
        return $this->fetch();
    }

    public function banneradd($action=''){
        $location = [
            'title' => "广告添加",
            'name' => "xtsz",
        ];
        $this->assign('location', $location);
        $data = ($this->request->param());
        if ($action == 'save') {
            $res = Db::name('sc_banner')->data($data)->insert();
            if ($res) {
                return json($res);
            }
        }
        return $this->fetch();
    }
}
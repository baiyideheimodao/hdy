<?php

namespace app\adminshop\controller;

use think\Controller;
use think\facade\Session;
use think\Db;

class proorder extends Controller

{

    public function index($key = null, $state = null)
    {
        $template['title'] = '订单'; // 标题1
        $template['location'] = '/adminshop/proorder'; //主页地址
        $template['name'] = 'proorder'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $where = "1=1";
        if ($key != null) {
            $where .= " and a.name like '%" . $key . "%'";
        }

        if ($state != null||$state != 0) {
            $where .= " and b.state=" . $state;
        }

        $list = Db::name('orderlist')
            ->alias('b')
            ->field('b.id,b.ddh,a.name,a.phone,b.userid,b.total,b.state,b.cpnum,b.scoretotal,b.type,b.overtime,b.creatime,b.addressid,b.msg')
            ->join('user a', 'a.id=b.userid')
            ->where($where)->order('b.id desc')
            ->paginate(10, false, ['query' => request()->param()])->each(function ($item) {
                //  $item['content'] = unserialize($item['content']);
                return $item;
            });
        $this->assign('list', $list);

        $page = $list->render();

        $this->assign('page', $page);

        return $this->fetch();
    }



    public function del($id)
    {
        Db::name('orderlist')->where('id', $id)->delete();
        return 1;
    }


    public function delall()

    {
        $data = $this->request->param();
        Db::name('orderlist')->delete($data['id']);
        return 1;
    }

    public function edit($id,$action='')
    {
        $template['title'] = '订单信息修改'; // 标题
        $template['location'] = '/admin/proorder'; //主页地址
        $template['name'] = 'proorder'; //主页地址
        $data = ($this->request->param());
        if ($action == 'edit') {
            unset($data['action']);
            $data['creatime'] = strtotime($data['creatime']);
            if(@$data['updatetime']){
                $data['updatetime'] = strtotime($data['updatetime']);
            }
            $res = Db::name('orderlist')->where('id', $data['id'])->data($data)->update();
            return json(1);

        }
        $data = Db::name('orderlist')->where('id', $data['id'])->find();
        $this->assign('data', $data);
        $this->assign('location', $template);
        return $this->fetch();
    }

    public function chakan($id)
    {
        $template['title'] = '订单商品列表'; // 标题
        $template['location'] = '/admin/proorder'; //主页地址
        $template['name'] = 'proorder'; //主页地址
        $this->assign('location', $template);
        $data = ($this->request->param());
        $data = Db::name('orderlist')->where('id', $data['id'])->find();
        $this->assign('data', $data);
        $this->assign('proData', unserialize($data['proData']));
        return $this->fetch();
    }

    public function address($id)
    {
        $template['title'] = '订单收货地址'; // 标题
        $template['location'] = '/admin/proorder'; //主页地址
        $template['name'] = 'proorder'; //主页地址
        $this->assign('location', $template);
        $data = Db::name('hyaddress')->where('id', $id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }
}

<?php

namespace app\adminshop\controller;

use think\facade\Session;
use think\Controller;
use think\Db;

class Member extends Controller
{

    public function index($key = null, $status = '')
    {
        checkadmin();
        $template['title'] = '会员列表'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'member'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $where = "1=1";
        if ($key != null) {
            $where .= " and (name like '%" . $key . "%' or phone like '%" . $key . "%')";
        }
        $list = db('user')->where($where)->order('create_time desc,id desc')
            ->paginate(10, false, ['query' => request()->param()]);

        $page = $list->render();
        $list = $list->items();
        $this->assign('status', $status);
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function yzmlist($key='')
    {
        checkadmin();
        $template['title'] = '会员'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'member'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $where = "1=1";
        if ($key != null) {
            $where .= " and (phone like '%" . $key . "%')";
        }

        $list = Db::name('sms')->where($where)->order('creatime desc,id desc')
            ->paginate(20, false, ['query' => request()->param()]);
        $page = $list->render();
        $list = $list->items();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function zhitui($id='')
    {
        checkadmin();
        $template['title'] = '会员'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'member'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $list = Db::name('user')->where('reid',$id)->order('creatime desc,id desc')
            ->paginate(20, false, ['query' => request()->param()]);
        $page = $list->render();
        $list = $list->items();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }


    public function edit($id, $action = null, $type = null)
    {
        checkadmin();
        $template['title'] = '会员'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'member'; //主页地址
        $data = ($this->request->param());
        if ($action == 'save') {
            $data['updatetime']=time();
            Db::name('user')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            return 1;
        }
        $this->assign('location', $template);
        $data = Db::name('user')->where('id', $id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function add($id = '', $action = null, $type = null)
    {
        $template['title'] = '会员添加'; // 标题
        $template['location'] = '/admin/member/'; //主页地址
        $template['name'] = 'add'; //主页地址
        $data = ($this->request->param());
        if ($action == 'save') {
            $data['status'] = 1;
            $data['score'] = 0;
            $data['teamnum'] = 0;
            $data['hygroup'] = 1;
            $data['yhscore'] = 0;
            $data['teamgroup'] = 0;
            $data['creatime'] = time();
            $data['logintime'] = time();
            $data['pmcqnum'] = 0;
            $data['portrait'] = '/admin/static/images/touxiang.png';
            $data['zt'] = 1;
            Db::name('user')
                ->data($data)
                ->insert();
            return 1;
        }
        $this->assign('location', $template);
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function chakan($action = null)
    {
        $template['title'] = '会员认证信息'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'shiming'; //主页地址
        $data = ($this->request->param());
        if ($action == 'save') {
            unset($data['save']);
            $userid = $data['id'];
            unset($data['id']);
            $res = Db::name('hyrz')->where('id', $userid)->data($data)->update();
            $r =  Db::name('user')->where('sfz', $data['sfz'])->data(['status'=>$data['status']])->update();
            if ($res) {
                return 1;
            } else {
                return 2;
            }

        }
        $this->assign('location', $template);
        $data = Db::name('hyrz')->where('userid', $data['id'])->find();
        $this->assign('data', $data);
        return $this->fetch();
    }


    //$where .= " and (action = '%" . $key . "%' or tel like '%" . $key . "%')";
    public function jifen($action = null, $type = null,$key=null)
    {
        $template['title'] = '会员认证信息'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'chakan'; //主页地址
        $data = ($this->request->param());
        $this->assign('location', $template);
        $where = "1=1";
        if (@$data['userid'] != null) {
            $where .= " and a.userid ="."'".$data['userid']."'";
        }
        if (@$data['action'] != null) {
            $where .= " and a.type ="."'".$data['action']."'";
        }
        if (@$data['status'] != null) {
            $where .= " and a.status =".$data['status'];
        }
        if (@$data['sfstatus'] != null) {
            $where .= " and a.sfstatus =".$data['sfstatus'];
        }
        if (@$key != null) {
            $where .= " and (b.name like '%" . $key . "%' or b.phone like '%" . $key . "%')";
        }
        if (@$data['timefw'] != null) {
            $timefw = explode(' - ',$data['timefw']);
            $timefw[0]=strtotime($timefw[0]);
            $timefw[1]=strtotime($timefw[1]);
                $where .= " and a.creatime > ".$timefw[0]." and a.creatime<".$timefw[1];
        }
        if (@$data['timejt'] != null) {
            $timejt = explode(' - ',$data['timejt']);
            $timejt[0]=strtotime(date('Y-m-d')." ". $timejt[0]);
            $timejt[1]=strtotime(date('Y-m-d')." ". $timejt[1]);
            $where .= " and a.creatime > ".$timejt[0]." and a.creatime<".$timejt[1];
        }

        $list = db('accountrecords')
            ->alias('a')
            ->field('a.id,a.userid,a.creatime,a.type,a.money,a.msg,a.status,b.name')
            ->join('user b', 'a.userid=b.id')
            ->where($where)
            ->order('a.id desc')
            ->paginate(20, false, ['query' => request()->param()]);

        $page = $list->render();
        $list = $list->items();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function fhjifen($action = null, $type = null,$key=null)
    {
        $template['title'] = '会员认证信息'; // 标题
        $template['location'] = '/admin/member'; //主页地址
        $template['name'] = 'chakan'; //主页地址
        $data = ($this->request->param());
        if ($action == 'save') {
            Db::name('hyrz')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            return 1;
        }
        $this->assign('location', $template);
        $where = "1=1";
        if (@$data['userid'] != null) {
            $where .= " and a.userid ="."'".$data['userid']."'";
        }
        if (@$data['action'] != null) {
            $where .= " and a.type ="."'".$data['action']."'";
        }
        if (@$data['status'] != null) {
            $where .= " and a.status =".$data['status'];
        }

        if (@$key != null) {
            $where .= " and (b.xm like '%" . $key . "%' or b.phone like '%" . $key . "%')";
        }
        if (@$data['timefw'] != null) {
            $timefw = explode(' - ',$data['timefw']);
            $timefw[0]=strtotime($timefw[0]);
            $timefw[1]=strtotime($timefw[1]);
            $where .= " and a.creatime > ".$timefw[0]." and a.creatime<".$timefw[1];
        }
        if (@$data['timejt'] != null) {
            $timejt = explode(' - ',$data['timejt']);
            $timejt[0]=strtotime(date('Y-m-d')." ". $timejt[0]);
            $timejt[1]=strtotime(date('Y-m-d')." ". $timejt[1]);
            $where .= " and a.creatime > ".$timejt[0]." and a.creatime<".$timejt[1];
        }

        $list = Db::table('by_todayscore')
            ->alias('a')
            ->field('a.id,a.userid,a.creatime,a.type,a.score,a.msg,a.status,b.name,a.yue')
            ->join('by_user b', 'a.userid=b.id')
            ->where($where)
            ->order('a.id desc')
            ->paginate(20, false, ['query' => request()->param()]);

        $page = $list->render();
        $list = $list->items();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function zhanghu($action = null,$uid=0)
    {
        $template['title'] = '会员账户充值'; // 标题
        $template['location'] = '/admin/huiyuan/'; //主页地址
        $template['name'] = 'zhanghu'; //主页地址
        $data = ($this->request->param());
        if ($action == 'save') {
            if ($data['leixing'] == 0) {
                bktxliushui($data['userid'], '', 2,$data['score'], 'add', '管理员充值积分');
                Db::name('user')
                    ->where('id', $data['userid'])
                    ->setInc('syscore', $data['score']);
            } else {
                bktxliushui($data['userid'], '', 2,$data['score'], 'reduce', '管理员扣除积分');
                Db::name('user')
                    ->where('id', $data['userid'])
                    ->setDec('syscore', $data['score']);
            }
            return 1;
        }
        $this->assign('uid', $uid);
        $this->assign('location', $template);
        return $this->fetch();
    }



    public function disable($userid,$status){
        Db::name('user')->where('id',$userid)->data(['status'=>$status])->update();
        return 1;
    }
}
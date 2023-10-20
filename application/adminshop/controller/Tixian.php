<?php
namespace app\adminshop\controller;
use think\facade\Session;
use think\Controller;
use think\Db;
class Tixian extends Controller
{

    public function list($action = null, $type = null,$key=null)
    {
        checkadmin();
        $template['title'] = '提现'; // 标题
        $template['location'] = '/admin/tixian'; //主页地址
        $template['name'] = 'tixian'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $data = ($this->request->param());
        if ($action == 'save') {
            Db::name('hyrz')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            return 1;
        }
        $this->assign('location', $template);
        $where = "a.status=1";
        if (@$data['action'] != null) {
            $where .= " and a.type ="."'".$data['action']."'";
        }
        if (@$key != null) {
            $where .= " and (b.xm like '%" . $key . "%' or b.mobile like '%" . $key . "%')";
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
        $list = Db::table('by_recordscore')
            ->alias('a')
            ->field('a.id,a.userid,a.creatime,a.type,a.score,a.msg,a.status,a.sfstatus,b.xm')
            ->join('by_user b', 'a.userid=b.id')
            ->where($where)
            ->order('a.creatime desc,a.id desc')
            ->paginate(20, false, ['query' => request()->param()]);

        $page = $list->render();
        $list = $list->items();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }


    public function set()
    {
        checkadmin();
        $template['title'] = '系统设置'; // 标题
        $template['location'] = '/admin/xtsz'; //主页地址
        $template['name'] = 'xtsz'; //主页地址
        $this->assign('location', $template);
        $list = Db::name('xtsz')->where('status',2)->order('id','asc')->paginate(10);
        $this->assign('list', $list);
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function caozuo(){
        $data = $this->request->param();
        $res1 = Db::name('recordscore')->where('id', $data['id'])->find();
        if($data['type']==1){
            $yue = Db::name('user')->where('id',$data['userid'])->value('score');
            $res = db('recordscore')->where('id', $data['id'])->data(['updatetime'=>time(),'sfstatus'=>1,'yuescore'=>$yue])->update();
            //微信到账金额
            $dzMoney = $res1['score'];
            //添加消费流水记录
            db('accountrecords')->data(['userid' => $data['userid'],  'money' => $dzMoney, 'type' => 'reduce','status'=>2,'creatime' => time(),'msg'=>'会员申请提现到余额'])->insert();
            if($res){
                return json(1);
            }
        }else{
            $res2 = Db::name('user')->where('id',$res1['userid'])->setInc('score',$res1['score']);
            $yue = Db::name('user')->where('id',$data['userid'])->value('score');
            db('accountrecords')
                ->data(['userid'=>$res1['userid'],'overtime'=>time(),'creatime'=>time(),'money'=>$res1['score'],'msg'=>'管理员拒绝提现申请，返回余额','type'=>'add','status'=>2])
                ->insert();
            $res3 = Db::name('recordscore')->where('id', $data['id'])->data(['overtime'=>time(),'sfstatus'=>1])->update();
            if($res3){
                return json(0);
            }
        }
    }


    public function yinhang($userid){
        $res = db('user_yinhang')->where('userid', $userid)->find();
        $this->assign('data', $res);
        return $this->fetch('yinhang');
    }

}
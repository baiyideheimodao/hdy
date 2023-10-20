<?php
namespace app\adminshop\controller;
use think\facade\Session;
use think\Controller;
use think\Db;
class Liushui extends Controller
{

    public function index($choseTime=null,$status)
    {
        checkadmin();
        $template['title'] = '流水'; // 标题
        $template['location'] = '/admin/liushui'; //主页地址
        $template['name'] = 'liushui'; //主页地址
        $template['url'] = $_SERVER['HTTP_HOST']; //主页地址
        $this->assign('location', $template);
        $where="1=1";
        if($status==1){
            if($choseTime==null){
                $choseTime = date('Y-m-d',time());
            }
            $time1 = strtotime($choseTime.' 00:00:00');
            $time2 = strtotime($choseTime.' 23:59:59');
            $choseTimeinfo=$choseTime;
        }else if($status==2){
            if($choseTime==null){
                $choseTime = date('m');
                $choseTimeinfo=date('Y-m');
            }else{
                $choseTimeinfo=$choseTime;
                $choseTime = substr($choseTime,strripos($choseTime,"-")+1);
            }
            //本月时间戳
            $time1 = mktime(0,0,0,$choseTime,1,date('Y'));
            $time2 = mktime(23,59,59,$choseTime,date('t'),date('Y'));
        }else if($status==3){
            if($choseTime==null){
                $choseTime = date('Y');
                $choseTimeinfo=$choseTime;
            }else{
                $choseTimeinfo=$choseTime;
            }
            //本月时间戳
            $time1 = mktime(0,0,0,date('m'),1,$choseTime);
            $time2 = mktime(23,59,59,date('m'),date('t'),$choseTime);
        }
        $where.=" and (creatime > $time1 and creatime < $time2)";

        $list = Db::name('accountrecords')->where($where)->order('id desc')->paginate(10,false,['query' => request()->param()]);
        $moneyaddcount = db('accountrecords')->where($where)->where('type','add')->sum('money');
        $moneyreductcount = db('accountrecords')->where($where)->where('type','reduce')->sum('money');
        $Statistics =[
            'date'=>$choseTimeinfo,
            'count'=>($list->toArray())['total'],
            'moneycount' => ($moneyaddcount - $moneyreductcount),
            'moneyaddcount'=>$moneyaddcount,
            'moneyreducecount'=>$moneyreductcount
        ];
        $this->assign('Statistics', $Statistics);
        $this->assign('status', $status);
        $this->assign('list', $list);
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch();
    }


    public function add($action=null)
    {
        checkadmin();
        $template['title'] = '流水'; // 标题
        $template['location'] = '/admin/liushui'; //主页地址
        $template['name'] = 'liushui'; //主页地址
        $this->assign('location', $template);
        $data=($this->request->param());
        if($action){
            Db::name('accountrecords')->strict(false)->insert($data);
            return 1;
        }
        return $this->fetch();
    }
    public function edit($id,$action=null,$type=null)
    {
        checkadmin();
        $template['title'] = '流水'; // 标题
        $template['location'] = '/admin/liushui'; //主页地址
        $template['name'] = 'liushui'; //主页地址
        $data=($this->request->param());
        if($action=='save'){
            if($data['status']!=0){
                $data['overtime']=time();
            }
            Db::name('accountrecords')
                ->where('id', $data['id'])
                ->data($data)
                ->update();
            return 1;
        }
        $this->assign('location', $template);
        $data=Db::name('accountrecords')->where('id',$id)->find();
        $data['xtsz'] = 1;
        $this->assign('data', $data);
        return $this->fetch();
    }



}
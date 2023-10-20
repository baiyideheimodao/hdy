<?php


namespace app\admin\model;



use think\Controller;

class Khlist extends Controller
{
    public function find_createtime($day,$cid)
    {
        //查询当天数据
        $today = strtotime(date('Y-m-d 00:00:00'));
        $data['cid'] = $cid;
        $data['createtime'] = array('egt', $today);
        return $data;
    }
}
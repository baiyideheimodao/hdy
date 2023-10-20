<?php


namespace app\ajax\controller;


use think\Controller;
use think\Db;

class Automatic extends Controller
{
    //TODO....每天自动派送积分
    public function scoreAuto()
    {
        //时间戳
        $time = time();
        $where = [
            ['syscore','<>',0],
            ['updatetime','>',$time]
        ];
        
        //卖家发货
        $res = Db::name('user')->where($where)->select();
        //dump($res);
        foreach ($res as $k => $val) {
            $bili = (db('hygroup')->where('id',$val['hygroup'])->value('percentage'))/10000;
            
            //剩余多少天
            $every_day_score = number_format($val['syscore']*$bili,2);
            //dump($val['id']);
            db('user')->where('id',$val['id'])->setInc('score',$every_day_score);
            db('user')->where('id',$val['id'])->setDec('syscore',$every_day_score);
            bktxliushui($val['id'], '', 1,$every_day_score, 'reduce', '每日返利');
        }
    }
}
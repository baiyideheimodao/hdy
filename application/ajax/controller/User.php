<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;
use think\Controller;

class User extends Controller
{
    public function info($userid)
    {
        $res = Db::name('huiyuan')->where(['id' => $userid])->find();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    //获取付款信息
    public function fukuan_get($proid = null, $userid = null)
    {
        if (!$userid) {
            $sql="SELECT ykj_hyzfset.*,ykj_huiyuan.sj FROM ykj_hyzfset,ykj_pmpro,ykj_huiyuan where ykj_pmpro.id={$proid} and ykj_pmpro.userid=ykj_huiyuan.id and ykj_hyzfset.userid=ykj_pmpro.userid";
        }else{
            $sql="SELECT ykj_hyzfset.*,ykj_huiyuan.sj FROM ykj_hyzfset,ykj_huiyuan where ykj_huiyuan.id={$userid} and ykj_hyzfset.userid=ykj_huiyuan.id";
        }
        $shuju=DB::query($sql);
        //dump($sql);
        if($shuju==null){
            $data = ['code' => 0, 'msg' => 'fail'];
            return json($data);
        }
        $res=$shuju[0];
        //$res = Db::name('hyzfset')->where(['userid' => $userid])->find();
        $zfData = explode(',', $res['show']);

        if ($zfData[0] == '1') {
            $res['yhk'] = 1;
        } else {
            $res['yhk'] = 0;
        }
        if ($zfData[1] == '1') {
            $res['weixin'] = 1;
        } else {
            $res['weixin'] = 0;
        }
        if ($zfData[2] == '1') {
            $res['zhifubao'] = 1;
        } else {
            $res['zhifubao'] = 0;
        }
        //$userinfo = userinfo($userid);
        $res['maijia_xm'] = $res['xm'];
        $res['maijia_sj'] = $res['sj'];
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }

        return json($data);
    }

    //付款设置
    public function fukuan_set()
    {
        $list = ($this->request->param());
        $list= json_decode($list['zhi'], true);
        $userid = $this->reverse($list['userid']);
        $list['userid'] = $userid;
        $res = Db::name('hyzfset')->where(['userid' => $list['userid']])->find();
        if ($res) {
            $res1 = Db::name('hyzfset')->where(['userid' => $list['userid']])->data($list)->update();
            if ($res1) {
                $data = ['code' => 100, 'msg' => 'success'];
            } else {
                $data = ['code' => 0, 'msg' => 'fail'];
            }
        } else {
            $res2 = Db::name('hyzfset')->insert($list);
            if ($res2) {
                $data = ['code' => 100, 'msg' => 'success'];
            } else {
                $data = ['code' => 0, 'msg' => 'fail'];
            }
        }
        return json($data);
    }

    public function reverse($x) {

        $max = pow(2,31)-1;
        $min = pow(-2,31);
        $strlen = strlen($x);
        if($x >=0){
            $num = (int)strrev($x);
        }else{
            $num = '-'.(int)strrev(trim($x,'-'));
        }
        if($min  < $num && $num < $max){
            return $num;
        }else{
            return 0;
        }


    }

    //会员竞拍商品
    public function pmorder()
    {
        $list = ($this->request->param());
        $start = $list['num'] * 6;
        if (@$list['mdid']) {
            $where = ['a.mdid' => $list['mdid'], 'a.status' => $list['status'], 'p.userid' => $list['mdid'], 'p.overtime' => null];
            $dengji = userinfo($list['mdid'])['hygroup'];
        } else {
            $where = ['a.userid' => $list['userid'], 'a.status' => $list['status'], 'p.overtime' => null];
            $dengji = userinfo($list['userid'])['hygroup'];
        }
        if ($dengji == 1) {
            $sxfbl = 0.6666666;
        } else if ($dengji == 2) {
            $sxfbl = 0.5;
        } else if ($dengji == 3) {
            $sxfbl = 0.33333;
        }
        if ($list['status'] == 1) {
            $zhuangtai = '已付款';
        } else if ($list['status'] == 0) {
            $zhuangtai = '拍卖中';
        } else if ($list['status'] == 2) {
            $zhuangtai = '商品持有中';
        } else if ($list['status'] == 1) {
            $zhuangtai = '待放货';
        }
        //确认收款中
        if ($list['status'] == 1) {
            $res = Db::table('ykj_pmpro')
                ->alias('a')
                ->field('p.ddh,a.title,a.id,a.userid,a.typeid,a.ysprice,a.oldprice,a.price,a.image,a.msg,a.status,a.mdid,p.fktime')
                ->join('ykj_pmorder p', 'p.proid = a.id')
                ->where($where)
                ->limit($start, 6)
                ->select();
            //return Db::getlastsql($res);
            foreach ($res as $key => $v) {
                $res[$key]['fktime'] = date('Y-m-d', $res[$key]['fktime']);
                $res[$key]['maijia'] = $zhuangtai;
                $res[$key]['mrjg'] = $v['price'] - $v['oldprice'];
                $res[$key]['zsjg'] = ceil($v['price'] * 1.03 - $v['oldprice']);
                $res[$key]['ysjg'] = ceil($v['price'] * 0.03 * $sxfbl);
                if ($v['typeid'] == 1) {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 09:00-18:00';
                } else if ($v['typeid'] == 2) {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 10:00-11:00';
                } else if ($v['typeid'] == 3) {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 15:00-16:00';
                } else {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 17:00-20:00';
                }
            }
        } else if ($list['status'] == 100) {
            $res = Db::table('ykj_pmorder')
                ->alias('p')
                ->field('p.ddh,p.status as zt,p.fkstatus,a.title,a.zmnum,a.id,a.userid,a.typeid,a.ysprice,a.oldprice,a.price,a.image,a.msg,a.status,a.mdid,p.fktime')
                ->join('ykj_pmpro a', 'p.proid = a.id')
                ->where('p.userid', $list['userid'])
                ->limit($start, 6)
                ->select();
            foreach ($res as $key => $v) {
                $res[$key]['fktime'] = date('Y-m-d', $res[$key]['fktime']);
                if ($v['zt'] == 0 && $v['fkstatus'] == 1) {
                    $res[$key]['maijia'] = '待放货';
                } else if ($v['fkstatus'] == 0) {
                    $res[$key]['maijia'] = '待付款';
                } else if ($v['zt'] == 1) {
                    $res[$key]['maijia'] = '已完成';
                } else if ($v['zt'] == 2) {
                    $res[$key]['maijia'] = '已拒绝放货';
                }
                $res[$key]['mrjg'] = $v['price'] - $v['oldprice'];
                $res[$key]['zsjg'] = ceil($v['price'] * 1.03 - $v['oldprice']);
                $res[$key]['ysjg'] = ceil($v['price'] * 0.03 * $sxfbl);

                if ($v['typeid'] == 1) {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 09:00-18:00';
                } else if ($v['typeid'] == 2) {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 10:00-11:00';
                } else if ($v['typeid'] == 3) {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 15:00-16:00';
                } else {
                    $res[$key]['changci'] = $res[$key]['fktime'] . ' 17:00-20:00';
                }
            }
        } //其他
        else if ($list['status'] == 2) {
            unset($where['p.overtime']);
            $res = Db::table('ykj_pmpro')
                ->alias('a')
                ->where($where)
                ->limit($start, 6)
                ->select();
            foreach ($res as $key => $v) {
                $orderinfo = $this->orderxx($v['id']);
                $res[$key]['ddh'] = $orderinfo['ddh'];
                $res[$key]['pmtime'] = date('Y-m-d', $orderinfo['fktime']);
                $res[$key]['maijia'] = $zhuangtai;
                $res[$key]['mrjg'] = $v['price'] - $v['oldprice'];
                $res[$key]['zsjg'] = ceil($v['price'] * 1.03 - $v['oldprice']);
                $res[$key]['ysjg'] = ceil(($v['price'] * 1.03 - $v['price']) * $sxfbl);
                if ($v['typeid'] == 1) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 09:00-18:00';
                } else if ($v['typeid'] == 2) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 10:00-11:00';
                } else if ($v['typeid'] == 3) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 15:00-16:00';
                } else {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 17:00-20:00';
                }
            }
        } else if ($list['status'] == 0) {
            unset($where['p.overtime']);
            $res = Db::table('ykj_pmpro')
                ->alias('a')
                ->where($where)
                ->limit($start, 6)
                ->select();
            foreach ($res as $key => $v) {
                $orderinfo = $this->orderxx($v['id']);
                $res[$key]['ddh'] = $orderinfo['ddh'];
                $res[$key]['pmtime'] = date('Y-m-d', $orderinfo['fktime']);
                $res[$key]['maijia'] = $zhuangtai;
                $res[$key]['mrjg'] = $v['price'] - $v['oldprice'];
                $res[$key]['zsjg'] = ceil($v['price'] * 1.03 - $v['oldprice']);
                $res[$key]['ysjg'] = ceil(($v['price'] * 1.03 - $v['price']) * $sxfbl);
                if ($v['typeid'] == 1) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 09:00-18:00';
                } else if ($v['typeid'] == 2) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 10:00-11:00';
                } else if ($v['typeid'] == 3) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 15:00-16:00';
                } else {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 17:00-20:00';
                }
            }
        } else {
            unset($where['p.overtime']);
            $res = Db::table('ykj_pmpro')
                ->alias('a')
                ->where($where)
                ->limit($start, 6)
                ->select();
            foreach ($res as $key => $v) {
                $orderinfo = $this->orderxx($v['id']);
                $res[$key]['ddh'] = $orderinfo['ddh'];
                $res[$key]['pmtime'] = date('Y-m-d', $orderinfo['fktime']);
                $res[$key]['maijia'] = $zhuangtai;
                $res[$key]['mrjg'] = $v['price'] - $v['oldprice'];
                $res[$key]['zsjg'] = ceil($v['price'] * 1.03 - $v['oldprice']);
                $res[$key]['ysjg'] = ceil(($v['price'] * 1.03 - $v['price']) * $sxfbl);
                if ($v['typeid'] == 1) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 09:00-18:00';
                } else if ($v['typeid'] == 2) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 10:00-11:00';
                } else if ($v['typeid'] == 3) {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 15:00-16:00';
                } else {
                    $res[$key]['changci'] = $res[$key]['pmtime'] . ' 17:00-20:00';
                }
            }
        }
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    //获取订单信息
    public function orderxx($id)
    {
        return Db::name('pmorder')->field('ddh,fktime')->where('proid', $id)->order('id desc')->find();
    }

    //会员待付款订单
    public function pmorderdfk()
    {
        $list = ($this->request->param());
        $start = $list['num'] * 6;
        $where = ['p.userid' => $list['userid'], 'p.fkstatus' => 0, 'p.status' => 0, 'p.no' => '未超期'];
        $res = Db::table('ykj_pmorder')
            ->alias('p')
            ->field('p.ddh,p.creatime,p.proid,p.no,a.title,a.id,a.userid,a.price,a.ysprice,a.image,a.msg,a.status,a.mdid,p.fktime')
            ->join('ykj_pmpro a', 'p.proid = a.id')
            ->where($where)
            ->limit($start, 6)
            ->select();
        $time = time();
        foreach ($res as $key => $v) {
            $res[$key]['time'] = $res[$key]['creatime'];
            $res[$key]['creatime'] = date('Y-m-d H:i', $v['creatime']);
        }


        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    //获取商品付款凭证
    public function orderFukuan($ddh)
    {
        $res = Db::name('pmorder')->where(['ddh' => $ddh])->find();
        $data = [];
        $proinfo = unserialize($res['content']);
        if ($res['fktype'] == 0) {
            $data = [
                'type' => '银行卡转账',
                'fktype' => $res['fktype'],
                'img' => $proinfo['yhimg'],
                'yhname' => $proinfo['yh'],
                'kahao' => $proinfo['kahao'],
            ];
        } else if ($res['fktype'] == 1) {
            $data = [
                'fktype' => $res['fktype'],
                'img' => $proinfo['weixinimg'],
            ];
        } else {
            $data = [
                'fktype' => $res['fktype'],
                'img' => $proinfo['zhifubaoimg'],
            ];
        }
        if ($data['img']) {
            $data1 = ['code' => 100, 'msg' => 'success', 'data' => $data];
        } else {
            $data1 = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data1);
    }

    //卖家取消商品订单
    public function pmorderDel($proid, $ddh)
    {
        $redis = Predis::getInstance();
        Db::name('pmpro')->where(['id' => $proid])->data(['mdid' => null, 'status' => 0, 'lock' => null])->update();
        $redis->set($proid,1);
        $res = Db::name('pmorder')->where(['ddh' => $ddh])->data(['status' => 2, 'deltime' => time(), 'overtime' => time()])->update();
        $ddhinfo = ddhinfo($ddh);
        Db::name('dhliushui')
            ->data(['userid' => $ddhinfo['userid'], 'mjid' => $ddhinfo['mjid'], 'ddh' => $ddhinfo['ddh'], 'creatime' => time(), 'content' => $ddhinfo['content']
                , 'msg' => '卖家：' . xm($ddhinfo['mjid']) . '，对订单号:' . $ddh . '，' . '进行了拒绝放货操作', 'type' => '拒绝放货'])
            ->insert();
        if ($res) {
            $data1 = ['code' => 100, 'msg' => 'success'];
            //扣除200积分手续费
            Db::name('huiyuan')->where('id', $ddhinfo['userid'])->setDec('score', 200);
            $userid = $ddhinfo['userid'];
            jfliushui($userid, $ddh, 'reduce', '虚假支付扣除200积分返给卖家' . xm($ddhinfo['mjid']) . '，订单号：' . $ddh, 200, 0, 1, '');
            $ddhinfo = ddhinfo($ddh);
            Db::name('dhliushui')
                ->data(['userid' => $ddhinfo['userid'], 'mjid' => $ddhinfo['mjid'], 'ddh' => $ddhinfo['ddh'], 'creatime' => time(), 'content' => $ddhinfo['content']
                    , 'msg' => '买家：' . xm($ddhinfo['userid']) . '虚假支付扣除200积分返给卖家' . xm($ddhinfo['mjid']) . '，订单号：' . $ddh, 'type' => '超期未付款'])
                ->insert();

            //增加200积分
            Db::name('huiyuan')->where('id', $ddhinfo['mjid'])->setInc('score', 200);
            jfliushui($userid, $ddh, 'add', '买家' . xm($userid) . '虚假支付补偿200积分，订单号：' . $ddh, 200, 0, 1, '');
            Db::name('dhliushui')
                ->data(['userid' => $ddhinfo['userid'], 'mjid' => $ddhinfo['mjid'], 'ddh' => $ddhinfo['ddh'], 'creatime' => time(), 'content' => $ddhinfo['content']
                    , 'msg' => '买家：' . xm($ddhinfo['userid']) . '虚假支付补偿200积分' . '，订单号：' . $ddh, 'type' => '超期未付款'])
                ->insert();
        } else {
            $data1 = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data1);
    }

    //卖家放货
    public function orderFh($proid, $userid, $mdid)
    {
        $ddh = Db::name('pmorder')
            ->where(['proid' => $proid, 'userid' => $mdid, 'mdid' => $userid])
            ->order('id desc')
            ->value('ddh');
        $res = Db::name('pmpro')->where(['id' => $proid])->data(['userid' => $mdid, 'status' => 2, 'mdid' => null, 'lock' => null, 'msg' => null])->update();
        if ($res) {
            Db::name('pmorder')->where(['proid' => $proid, 'userid' => $mdid, 'mdid' => $userid])->data(['status' => 1, 'fhtime' => time(), 'overtime' => time()])->update();
            $ddhinfo = ddhinfo($ddh);
            Db::name('dhliushui')
                ->data(['userid' => $ddhinfo['userid'], 'mjid' => $ddhinfo['mjid'], 'ddh' => $ddh, 'creatime' => time(), 'content' => $ddhinfo['content']
                    , 'msg' => '卖家：' . xm($ddhinfo['mjid']) . '，对订单号:' . $ddh . '，' . '进行了放货操作，商品转给买家' . xm($ddhinfo['userid']), 'type' => '放货'])
                ->insert();
            $data1 = ['code' => 100, 'msg' => 'success'];
        } else {
            $data1 = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data1);
    }

    //会员申请商品回收
    public function shangpinhuishou()
    {
        $data = ($this->request->param());
        $userid = Db::name('pmpro')
            ->where(['id' => $data['id']])
            ->value('userid');
        $res = Db::name('hypmsq')
            ->insert(['proid' => $data['id'], 'userid' => $userid, 'status' => 0, 'creatime' => time()]);

        $ddhinfo = ddhinfo($data['ddh']);
        Db::name('dhliushui')
            ->data(['userid' => $ddhinfo['userid'], 'mjid' => $ddhinfo['mjid'], 'ddh' => $ddhinfo['ddh'], 'creatime' => time(), 'content' => $ddhinfo['content']
                , 'msg' => '会员：' . xm($ddhinfo['userid']) . '，订单号:' . $ddhinfo['ddh'] . '，' . '申请平台回收该商品！', 'type' => '申请平台回收'])
            ->insert();

        Db::name('pmpro')
            ->where(['id' => $data['id']])
            ->data(['msg' => '平台回收处理中', 'type' => 1, 'huishou' => 1])
            ->update();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    //会员申请商品转拍
    public function zhuanpai()
    {
        $da = ($this->request->param());

        $zmnum = (int)$da['zmnum'] + 1;
        //return json($da);
        $price = (int)$da['price'] * 1.03;
        //return json($price);
        if(userinfo($da['userid'])['score'] < $da['ysjg']){
            $data = ['code' => 0, 'msg' => 'fail'];
            return json($data);
        }
        $type = Db::name('pmpro')
            ->where('id', $da['id'])
            ->value('status');
        if($type!=2){
            $data = ['code' => 0, 'msg' => '该商品已经转拍，请刷新！'];
            return json($data);
        }
        $res = Db::name('pmpro')
            ->where('id', $da['id'])
            ->data(['oldprice' => $da['price'], 'price' => $price, 'creatime' => time(), 'pmtime' => (time() + 86400), 'zmnum' => $zmnum, 'status' => 0, 'now' => 1, 'mdid' => null, 'lock' => null, 'zdid' => null,'msg' => null])
            ->update();
        Db::name('huiyuan')
            ->where('id', $da['userid'])
            ->setDec('score', $da['ysjg']);
        $proname = cp_name($da['id']);
        //$creatime = time();
        jfliushui($da['userid'], $da['ddh'], 'reduce', '转拍手续费，转拍商品id:' . $da['id'] . '，商品名称:' . $proname, $da['ysjg'], 0, 1, '平台收回');
        $ddhinfo = ddhinfo($da['ddh']);
        $ddhinfo['content'] = unserialize($ddhinfo['content']);
        $ddhinfo['content']['sxf'] = $da['ysjg'];
        $ddhinfo['content']['yijiaprice'] = ceil($price);
        Db::name('dhliushui')
            ->data(['userid' => $ddhinfo['userid'], 'mjid' => $ddhinfo['mjid'], 'ddh' => $ddhinfo['ddh'], 'creatime' => time(), 'content' => serialize($ddhinfo['content'])
                , 'msg' => '买家：' . xm($ddhinfo['userid']) . '，对订单号:' . $da['ddh'] . '，' . '购买的商品进行了转拍操作', 'type' => '转拍'])
            ->insert();
        //更新会员状态为活跃会员
        Db::name('huiyuan')->where('id', $da['userid'])->data(['huotime' => time()])->update();
        //直推上级获利
        $userinfo = userinfo($da['userid']);
        $reid = $userinfo['reid'];
        if ($reid) {
            //获取今日开始时间戳和结束时间戳
            $time1 = strtotime(date('Y-m-d 00:00:00', time()));
            $time2 = strtotime(date('Y-m-d 23:59:59', time()));
            if ($userinfo['huotime'] > $time1 && $userinfo['huotime'] < $time2) {
                $fanjia = (int)($da['ysjg'] * 0.1);
                Db::name('huiyuan')
                    ->where('id', $reid)
                    ->setInc('score', $fanjia);
                $xm = $userinfo['xm'];
                jfliushui($reid, $da['ddh'], 'add', '下级' . $xm . '转拍商品:' . $proname . '，返利', $fanjia, 0, 1, '平台支出上级返利');
            }
            if ($userinfo['groupid']) {
                $this->teamjl($da['ysjg'], $userinfo['groupid'], $userinfo['id'], $xm, $proname);
            }
        }
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }


    //团队奖励分享
    public function teamjl($sxf, $groupid = '', $userid, $xm, $proname)
    {
        //$groupid = ",10000,10015,";
        $arr = explode(',', $groupid);
        unset($arr[0]);
        //删除最后一个数组
        array_pop($arr);
        $arr = array_reverse($arr);
        $fenData = Db::name('huiyuan')->field('id,teamgroup')->where([['id', 'in', $arr], ['teamgroup', '<>', 0]])->select();
        //$newarr = [];
        $qs = 0;
        $jibie = 1;
        foreach ($fenData as $k => $val) {
            if ($val['teamgroup'] > $qs) {
                if ($qs == 0) {
                    $jibie = $val['teamgroup'] - $qs;
                }
                $sxf1 = ceil(($sxf * ($val['teamgroup'] - $qs) * 0.03));
                Db::name('huiyuan')->where('id', $val['id'])->setInc('score', $sxf1);
                jfliushui($val['id'], '', 'add', '团队下级' . $xm . '转拍商品:' . $proname . '，返利', $sxf1, 0, 1, '平台支出上级返利');
                //array_push( $newarr,$val);
                $qs = $val['teamgroup'];
            } else if ($qs > 1) {
                $sxf2 = ceil($sxf * $jibie * 0.3 * 0.20);
                Db::name('huiyuan')->where('id', $val['id'])->setInc('score', $sxf2);
                jfliushui($val['id'], '', 'add', '团队下级' . $xm . '转拍商品:' . $proname . '，返利', $sxf2, 0, 1, '平台支出上级返利');
            }
        }
    }

    public function addressList($userid)
    {
        $res = Db::name('hyaddress')
            ->where(['userid' => $userid])
            ->order('default desc')
            ->select();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    public function addressUpdate()
    {
        $da = ($this->request->param());

        if ($da['default'] != '0') {
            $da['default'] = 1;
            Db::name('hyaddress')
                ->where('userid', $da['userid'])
                ->data(['default' => 0])
                ->update();
        }

        if ($da['type'] == 'edit') {
            unset($da['type']);
            $res = Db::name('hyaddress')
                ->where('id', $da['id'])
                ->data($da)
                ->update();
        } else {

            unset($da['type']);

            $res = Db::name('hyaddress')
                ->data($da)
                ->insert();

        }
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $da];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }


    //会员账户流水
    public function liushui($num, $userid, $status)
    {
        $start = $num * 6;
        if ($status == 0) {
            $where = ['userid' => $userid];
        } else {
            $where = ['userid' => $userid, 'zftype' => $status];
        };
        $res = Db::name('jfliushui')
            ->where($where)
            ->limit($start, 6)
            ->order('id desc')
            ->select();
        $len = ceil((Db::name('jfliushui')
                ->where($where)
                ->count()) / 6);
        foreach ($res as $k => $val) {
            if ($val['status'] == 0) {
                $res[$k]['status'] = '抢单竞拍';
            } else if ($val['status'] == 1) {
                $res[$k]['status'] = '商城购物';
            } else if ($val['status'] == 2) {
                $res[$k]['status'] = '积分转让';
            } else {
                $res[$k]['status'] = '平台充值';
            }
            if ($val['zftype'] == 1) {
                $res[$k]['zftype'] = '积分';
            } else {
                $res[$k]['zftype'] = '优惠卷';
            }
            $res[$k]['creatime'] = date('Y-m-d H:i', $val['creatime']);
            $res[$k]['type'] = false;
        }
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'len' => $len, 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }


    //我的会员
    public function team($userid, $num)
    {

        $start = $num * 6;
        if (userinfo('userid')['groupid']) {
            $groupid = userinfo('userid')['groupid'] . $userid . ',';
        } else {
            $groupid = ',' . $userid . ',';
        }
        $teamnum = Db::name('huiyuan')->whereLike('groupid', '%' . $groupid . '%')
            ->count();
        $res = Db::name('huiyuan')->where('reid', $userid)
            ->limit($start, 6)
            ->select();
        $len = Db::name('huiyuan')->where('reid', $userid)
            ->count();
        foreach ($res as $k => $val) {
            $res[$k]['xjData'] = Db::name('huiyuan')->where('reid', $val['id'])->select();
            $res[$k]['xjnum'] = count($res[$k]['xjData']);
            foreach ($res[$k]['xjData'] as $i => $val1) {
                $res[$k]['xjData'][$i]['creatime'] = date('Y-m-d', $val1['creatime']);
                $res[$k]['xjData'][$i]['num'] = Db::name('huiyuan')->where('reid', $val1['id'])->count();
            }
        }
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'len' => $len, 'team_num' => $teamnum, 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }


    //会员订单
    public function order()
    {
        $list = ($this->request->param());
        $start = $list['num'] * 6;
        $where = ['a.userid' => $list['userid'], 'a.status' => $list['status']];
        if ($list['status'] == 1) {
            $res = Db::table('ykj_proorder')
                ->alias('a')
                ->where($where)
                ->limit($start, 6)
                ->select();
        } else {
            $res = Db::table('ykj_proorder')
                ->alias('a')
                ->where($where)
                ->limit($start, 6)
                ->select();
        }
        foreach ($res as $key => $v) {
            $res[$key]['content'] = unserialize($res[$key]['content']);
            $res[$key]['creatime'] = date('Y-m-d H:i', $res[$key]['creatime']);
            $res[$key]['overtime'] = date('Y-m-d H:i', $res[$key]['overtime']);
        }
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }


    //买家取消商城商品订单
    public function orderDel($ddh)
    {

        $res = Db::name('proorder')->where('ddh', $ddh)->delete();
        if ($res) {
            $data1 = ['code' => 100, 'msg' => 'success'];
        } else {
            $data1 = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data1);
    }

    //申请退货
    //买家取消商城商品订单
    public function ordertui($ddh, $userid)
    {

        $res = Db::name('proorder')->where('ddh', $ddh)->data(['tui' => 1])->update();
        Db::name('proordertui')->data(['ddh' => $ddh, 'creatime' => time(), 'status' => 0, 'userid' => $userid])->insert();
        if ($res) {
            $data1 = ['code' => 100, 'msg' => 'success'];
        } else {
            $data1 = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data1);
    }

    //订单分类数量
    public function dataNum($userid)
    {
        //待付款
        $dfk = Db::name('pmorder')
            ->where(['fkstatus' => 0, 'status' => 0, 'userid' => $userid])
            ->where('no', '未超期')
            ->select();

        $dfk_num = count($dfk);
        //待收货
        $dsh_num = Db::name('pmorder')->where(['fkstatus' => 1, 'status' => 0, 'userid' => $userid, 'fhtime' => 0])->count();
        //待转派
        $dzp_num = Db::name('pmpro')->where(['status' => 2, 'userid' => $userid, 'huishou' => 0])->count();
        //待放货
        $dfh_num = Db::name('pmorder')->where(['fkstatus' => 1, 'status' => 0, 'mdid' => $userid, 'fhtime' => 0])->count();

        //return Db::getlastsql($dfh_num);

        $data = [
            'dfk_num' => $dfk_num,
            'dsh_num' => $dsh_num,
            'dzp_num' => $dzp_num,
            'dfh_num' => $dfh_num,
        ];
        $data['sessionid']=sessionid($userid);
        $data['zt']=Db::name('huiyuan')->where('id',$userid)->value('zt');
        return json($data);
    }

    public function zhuanrang($scorenum, $getid, $userid, $password)
    {
        $userinfo = userinfo($userid);
        if (userinfo($userid)['password'] != md5($password)) {
            $data = ['code' => 20, 'msg' => 'fail'];
            return json($data);
        }
        if (userinfo($userid)['sj'] == 18375527409) {
            $xm = xm($getid);
            Db::name('huiyuan')->where('id', $userid)->setDec('score', $scorenum);
            Db::name('huiyuan')->where('id', $getid)->setInc('score', $scorenum);
            jfliushui($userid, '', 'reduce', '总管理员积分转让给会员:' . $xm, $scorenum, 2, 1);
            jfliushui($getid, '', 'add', '积分接收来自总管理员:' . $userinfo['xm'] . '，转让', $scorenum, 2, 1);
            $data = ['code' => 100, 'msg' => 'success'];
            return json($data);
        }

        $groupid = userinfo($getid)['groupid'];

        if (!$groupid) {
            $data = ['code' => 0, 'msg' => 'fail'];
            return json($data);
        }
        $res = strstr($groupid, $userid);
        if (!$res) {
            $data = ['code' => 0, 'msg' => 'fail'];
            return json($data);
        } else {
            Db::name('huiyuan')->where('id', $userid)->setDec('score', $scorenum);
            Db::name('huiyuan')->where('id', $getid)->setInc('score', $scorenum);
            $xm = xm($getid);
            $sjxm = xm($userid);
            jfliushui($userid, '', 'reduce', '积分转让给下级会员:' . $xm, $scorenum, 2, 1);
            jfliushui($getid, '', 'add', '积分接收来自上级会员:' . $sjxm . '，转让', $scorenum, 2, 1);
            $data = ['code' => 100, 'msg' => 'success'];
            return json($data);
        }
    }

    public function zhuanrang1($scorenum, $getid, $userid, $password)
    {
        $userinfo = userinfo($userid);
        if(userinfo($userid)['password']!=md5($password)){
            $data = ['code' => 20, 'msg' => 'fail'];
            return json($data);
        }
        if (userinfo($userid)['sj'] == 18375527409){
            $xm = xm($getid);
            Db::name('huiyuan')->where('id', $userid)->setDec('score', $scorenum);
            Db::name('huiyuan')->where('id', $getid)->setInc('score', $scorenum);
            jfliushui($userid, '', 'reduce', '总管理员积分转让给会员:' . $xm, $scorenum, 2, 1);
            jfliushui($getid, '', 'add', '积分接收来自总管理员:' . $userinfo['xm'] . '，转让', $scorenum, 2, 1);
            $data = ['code' => 100, 'msg' => 'success'];
            return json($data);
        }

        $groupid = userinfo($getid)['groupid'];

        if (!$groupid) {
            $data = ['code' => 0, 'msg' => 'fail'];
            return json($data);
        }
        $res = strstr($groupid, $userid);
        if (!$res) {
            $data = ['code' => 0, 'msg' => 'fail'];
            return json($data);
        } else {
            Db::name('huiyuan')->where('id', $userid)->setDec('score', $scorenum);
            Db::name('huiyuan')->where('id', $getid)->setInc('score', $scorenum);
            $xm = xm($getid);
            $sjxm = xm($userid);
            jfliushui($userid, '', 'reduce', '积分转让给下级会员:' . $xm, $scorenum, 2, 1);
            jfliushui($getid, '', 'add', '积分接收来自上级会员:' . $sjxm . '，转让', $scorenum, 2, 1);
            $data = ['code' => 100, 'msg' => 'success'];
            return json($data);
        }
    }

    public function mmset($userid, $password, $zcpassword)
    {
        if (md5($password) != userinfo($userid)['password']) {
            $data = ['code' => 20, 'msg' => 'fail'];
            return json($data);
        }
        $res = Db::name('huiyuan')
            ->where(['id' => $userid])
            ->data(['password' => md5($zcpassword)])
            ->update();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success'];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    public function mmchongzhi($sj, $password)
    {

        $res = Db::name('huiyuan')
            ->where(['sj' => $sj])
            ->data(['password' => md5($password)])
            ->update();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success'];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    //订单记录
    public function order_jilu($ddh)
    {
        $res = Db::name('dhliushui')
            ->where(['ddh' => $ddh])
            ->order('id asc')
            ->select();
        if ($res) {
            foreach ($res as $k => $val) {
                $res[$k]['content'] = unserialize($val['content']);
                $res[$k]['creatime'] = date('Y-m-d H:i', $val['creatime']);
            }
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

}

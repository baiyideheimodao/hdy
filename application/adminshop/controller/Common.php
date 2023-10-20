<?php

namespace
app\adminshop\controller;

use think\Controller;
use think\db;
class Common extends Controller
{    //上传图片
    public function upload()
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move('upload');
            if ($info) {
                $result = [
                    'code' => 1, 'msg' => '上传成功', 'filename' => '/upload/' . str_replace('\\', '/', $info->getSaveName())];
                return json_encode($result);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //通用缩略图上传接口
    public function dtupload()
    {
        if ($this->request->isPost()) {
            $res['code'] = 1;
            $res['msg'] = '上传成功！';
            $file = $this->request->file('file');
            $info = $file->move('upload');
            if ($info) {
                $res['name'] = $info->getFilename();
                $res['filepath'] = 'upload/admin/' . $info->getSaveName();
                $res['filepath'] = '/upload/' . str_replace('\\', '/', $info->getSaveName());
            } else {
                $res['code'] = 0;
                $res['msg'] = '上传失败！' . $file->getError();
            }
            return $res;
        }
    }
  
    //一键发货
   public function fahuo($id)
    {
        //proid=61&userid=10000&mdid=10115
        //卖家发货
        $proid = $id;
        $mdid = Db::name('pmpro')->where(['id' => $proid])->value('mdid');
        $userid = Db::name('pmpro')->where(['id' => $proid])->value('userid');
        Db::name('pmorder')->where(['proid' => $proid,'userid' => $mdid,'mdid' => $userid])->data(['status' => 1, 'fhtime' => time(), 'overtime' => time()])->update();
        $mdid = Db::name('pmpro')->where(['id' => $proid])->value('mdid');
        $res = Db::name('pmpro')->where(['id' => $proid])->data(['userid' => $mdid, 'status' => 2, 'mdid' => null, 'lock' => null, 'msg' => null])->update();
//        $orderinfo = Db::name('pmorder')->where(['proid' => (int)$proid])->find();
//        $userid = $orderinfo['userid'];
//        $proname = cp_name($proid);
        //退还200积分手续费
        // jfliushui($userid,$orderinfo['ddh'],'add','退还拍卖暂扣押金积分，订单号:'.$orderinfo['ddh'].'，'.'购买商品id:'.$proid.'，商品名称:'.$proname,200,0,1);
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }

    //通用单点删除
    public function del($biao,$id)
    {
        db($biao)->where('id',$id)->delete();
        return 1;
    }

    //通用删除
    public function delall()
    {
        $data=$this->request->post();
        db($data['biao'])->whereIn('id',$data['arr'])->delete();
        return 1;
    }



}
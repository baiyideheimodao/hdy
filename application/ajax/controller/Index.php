<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;
use think\Controller;

class Index extends Controller
{
    public  function banner($type)
    {
        $res = Db::name('banner')->where('type',$type)->order('px asc')->select();
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }

    //图片上传
    public function upload_img()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        header('Access-Control-Allow-Headers: content-type,token');
        $data = $this->request->param();//获取传参
        $files = $this->request->file('file');//获取图片
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $files->move('./upload');//保存到xcx目录下
        $path = xtsz('1') . '/upload/' . date('Ymd', time()) . '/' . $info->getFilename();//图片上传后的地址
        if ($info) {
            // 成功上传后 获取上传信息
            return $path;
        } else {
            // 上传失败获取错误信息
            return 0;
        }

    }
public  function dan($id)
    {
        $res = Db::name('xtsz')->where('id',$id)->value('content');
        if ($res) {
            $data = ['code' => 100, 'msg' => 'success', 'data' => $res];
        } else {
            $data = ['code' => 0, 'msg' => 'fail'];
        }
        return json($data);
    }
   //图片上传
    public function ewm($userid)
    {
        $ewm = qrcode_create($userid);
        return json($ewm);
    }

    public function wangzhi(){
        return 'http://app.zpyhr.com/';
    }
}
<?php
/**
 *  Encoding : UTF-8
 *  Separator : Unix and OS X (\n)
 *  File Name : school.php
 *  Create Date : 2021/12/31 9:04
 *  Version : 0.1
 *  Copyright : skylong School Team Copyright (C)
 *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
 */

namespace app\admin\controller\v2;

use app\common\controller\AdminController;

class Bases extends AdminController
{

    private $webUrl = '';

    public function initialize() {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->webUrl = config('sys_data.web_site');
    }

    public function index()
    {
        if ($this->request->isPost()) {
            $page = $this->request->post('page', $this->page);
            $limit = $this->request->post('limit', $this->limit);
            $list = db('base')
                ->alias('sv')
                ->join('area s', 's.id = sv.area_id and s.state =' . $this->status['NORMAL'])
                ->field('sv.id,s.name area_name,sv.name,sv.state,sv.type')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $total = db('base')
                ->alias('sv')
                ->join('area s', 's.id = sv.area_id and s.state =' . $this->status['NORMAL'])
                ->field('sv.id,s.name area_name,sv.name,sv.state')
                ->count();
            return $this->returnTabelJson($list, '查询成功', $total);
        }
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $name = $this->request->post('name', '');
            if (empty($name)) {
                return $this->returnJson([], '创建基地失败：基地名称不能为空');
            }
            $province_id = $this->request->post('province_id', 0);
            $city_id = $this->request->post('city_id', 0);
            $area_id = $this->request->post('area_id', 0);
            if (empty($area_id)) {
                return $this->returnJson([], '创建基地失败：所属区域请选择');
            }
            $type = $this->request->post('type', 1);

            $sort = $this->request->post('sort', 0);
            $id = db('base')->insertGetId([
                'name' => $name,
                'sort' => $sort,
                'province_id' => $province_id,
                'city_id' => $city_id,
                'area_id' => $area_id,
                'type' => $type,
                'admin_id' => cookie('id'),
                'create_time' => time()
            ]);
            if ($id > 0) {
                insert_admin_log('创建基地成功');
                return $this->returnJson(['id' => $id], '创建基地成功', 1);
            }
            return $this->returnJson([], '创建基地失败');
        }
        $area = [];
        $area['province'] = $this->addressList(0, 1);
        $area['city'] = [];
        $area['area'] = [];
        return $this->fetch('save', compact('area'));
    }



    public function edit()
    {
        $id = $this->request->param('id', 0);
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $update = [];
            if (isset($data['name'])) {
                if (!empty($data['name'])) {
                    $update['name'] = $data['name'];
                } else {
                    return $this->returnJson([], '更新基地失败：基地名称不能为空');
                }
            }
            if (isset($data['area_id'])) {
                if (!empty($data['area_id'])) {
                    $update['area_id'] = $data['area_id'];
                } else {
                    return $this->returnJson([], '更新基地失败：所属区域请选择');
                }
            }
            $type = $this->request->post('type', 1);
            $update['type'] = $type;
            $sort = $this->request->post('sort', 'sss');
            if ($sort !== 'sss') {
                $update['sort'] = $sort;
            }
            $state = $this->request->post('state', -999);
            if ($state !== -999) {
                $update['state'] = $state;
            }
            $res = db('base')->where("id = {$id}")->update($update);
            if ($res !== false) {
                insert_admin_log('更新基地成功');
                return $this->returnJson(['id' => $id], '更新基地成功', 1);
            }
            return $this->returnJson([], '更新基地失败');
        }
        $info = db('base')->where("id = {$id}")->find();
        $area['province'] = $this->addressList(0, 1);
        $area['city'] = $this->addressList($info['province_id'], 1);
        $area['area'] = $this->addressList($info['city_id'], 1);
        $info['url'] = '/admin/v2.bases/edit';
        return $this->fetch('save', compact('info', 'area'));
    }

    public function del()
    {
        $id = $this->request->param('id', 0);
        if (db('teacher')->where("base_id = {$id}")->count() > 0) {
            return $this->returnJson([], '删除基地失败：该基地下存在教师');
        }
        $res = db('base')->where("id = {$id}")->delete();
        if ($res !== false) {
            insert_admin_log('删除基地成功');
            return $this->returnJson([], '删除基地成功', 1);
        }
        return $this->returnJson([], '删除基地失败');
    }

    public function addressList($id, $type = 0, $area_type = 0)
    {
        $res = db('area')->where("p_id = $id and state=1")->select();
        if ($type == 1) {
            return $res;
        }
        if ($res) {
            return json(['code' => 100, 'msg' => '获取成功', 'data' => $res]);
        } else {
            return json(['code' => 0, 'msg' => '请添加县区']);
        }
    }

    //注册用二维码
    public function qrcode(){
        $id = $this->request->param('id',0);
        if(!file_exists("../public/upload/register_code/".$id."-2.jpeg")){
            $base_code = $this->create_code(['class_id'=>$id,'type'=>2]);
        }else{
            $base_code = $this->webUrl."/upload/register_code/".$id."-2.jpeg";
        }
        return $this->fetch('qrcode',compact('base_code'));
    }

    public function create_code($canshu,$page='pages/userinfo/register'){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx32d11f1898c690d6&secret=f0f5285f163324b11d3b2c0cb09ff8ac";
        $resultData = https_getRequest($url);
        $token = $resultData['access_token'];
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
        $data = ['scene' =>$canshu['class_id']."&".$canshu['type'],'width'=>480,'page'=>$page];
        $res = httpRequest( $url, json_encode($data),"POST");
        $res_base64="data:image/jpeg;base64,".base64_encode($res);
        $code_img= $this->get_base64_img($res_base64,'../public/upload/register_code/',$canshu['type'],$canshu['class_id']);
        return $code_img;
    }

    /**
     * base64转码图片
     * @param $base64
     * @param string $path
     * @return bool|string
     */
    function get_base64_img($base64,$path = '',$code_type,$class_id){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
            $type = $result[2];
            $new_file = $path.$class_id.'-'.$code_type.".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))){
                $new_file = str_replace("../public/","/",$new_file);
                return $this->webUrl.$new_file;
            }else{
                return false;
            }
        }
    }
}
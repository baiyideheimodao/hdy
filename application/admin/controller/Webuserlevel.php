<?php
    /**
     *  Author : Dream <34650064@QQ.com>
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Webuserlevel.php
     *  Create Date : 2022/6/17 22:23
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  Email Address : yxly330@126.com
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\admin\controller;
    use app\common\controller\AdminController;
    use think\facade\Env;

    class Webuserlevel extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function index()
        {
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $list = db('web_user_level')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
                $total = db('web_user_level')
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        public function add()
        {
            if($this->request->isPost()){
                $data = $this->request->post();
                if(isset($data['file'])){unset($data['file']);}
                $info = db('web_user_level')->where("name = '{$data['name']}'")->find();
                if(!empty($info)){
                    return json(['code'=>0,'message'=>'添加失败：名称已存在']);
                }
                $result = db('web_user_level')->insertGetId($data);
                if($result !== false){
                    insert_admin_log('等级添加成功');
                    return json(['code'=>200,'message'=>'等级添加成功']);
                }
                return json(['code'=>0,'message'=>'等级添加失败']);
            }
            return $this->fetch('save');
        }

        public function edit($id)
        {
            $id = $this->request->param('id',0);
            if ($this->request->isPost()) {
                $data = $this->request->post();
                if(isset($data['file'])){unset($data['file']);}
                $info = db('web_user_level')->where("id != {$data['id']} and name = '{$data['name']}'")->find();
                if(!empty($info)){
                    return json(['code'=>0,'message'=>'添加失败：名称已存在']);
                }
                $result = db('web_user_level')
                            ->where('id', $data['id'])
                            ->data($data)
                            ->update();
                if($result !== false){
                    insert_admin_log('等级修改成功');
                    return json(['code'=>200,'message'=>'等级修改成功']);
                }
                return json(['code'=>0,'message'=>'等级修改失败']);
            }
            $info = db('web_user_level')->where("id = {$id}")->find();
            $info['url'] = '/admin/webuserlevel/edit';
            return $this->fetch('save',compact('info'));
        }

        public function del()
        {
            $params = request()->param();
            if (!isset($params['id'])) {
                return $this->returnJson([],'ID不存在');
            }
            $res = db('web_user_level')->where("id = {$params['id']}")->delete();
            if($res !== false){
                insert_admin_log('删除了等级');
                return $this->returnJson([],'操作成功',1);
            }
            return showData(0, '操作失败');
        }

        public function state(){
            $id = $this->request->param('id',0);
            $state = $this->request->param('state',0);
            $res = db('web_user_level')->where("id = {$id}")->update(['status'=>$state]);
            if($res !== false){
                insert_admin_log('更新等级状态');
                return $this->returnJson([],'操作成功',1);
            }
            return $this->returnJson([],'操作失败');
        }

        public function upload_pic(){
            $files = request()->file('file');
            $path = Env::get('root_path') . DIRECTORY_SEPARATOR. 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'level_image';
            $info = $files->move($path);
            if($info){
                $url = '/upload/level_image/' . str_replace('\\', '/', $info->getSaveName());
                // 此处为迎合layuiedit图片显示，成功code返回0
                return $this->returnJson(['src'=>$url,'title'=>''],'success');
            }else{
                return $this->returnJson([],'error',1);
            }
        }
    }
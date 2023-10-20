<?php
    /**
     *  科目
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : grade.php
     *  Create Date : 2021/12/31 8:20
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\controller;
    use app\common\controller\AdminController;

    class Grade extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function index(){
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $list = db('grade')
                    ->limit(($page-1)*$limit,$limit)
                    ->order('sort asc')
                    ->select();
                $total = db('grade')
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        public function add(){
            if($this->request->isPost()){
                $name = $this->request->post('name','');
                if(empty($name)){
                    return $this->returnJson([],'创建年级失败：年级名称不能为空');
                }
                $sort = $this->request->post('sort',0);
                $id = db('grade')->insertGetId([
                    'name' => $name,
                    'sort' => $sort,
                    'admin_id' => cookie('id'),
                    'create_time' => time()
                                           ]);
                if($id > 0){
                    insert_admin_log('创建年级成功');
                    return $this->returnJson(['id'=>$id],'创建年级成功',1);
                }
                return $this->returnJson([],'创建年级失败');
            }
            return $this->fetch('save');
        }

        public function edit(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $data = $this->request->param();
                $update = [];
                if(isset($data['name'])){
                    if(!empty($data['name'])){
                        $update['name'] = $data['name'];
                    }else{
                        return $this->returnJson([],'更新年级失败：年级名称不能为空');
                    }
                }
                $sort = $this->request->post('sort','sss');
                if($sort !== 'sss'){
                    $update['sort'] = $sort;
                }
                $state = $this->request->post('state',-999);
                if($state !== -999){
                    $update['state'] = $state;
                }
                $res = db('grade')->where("id = {$id}")->update($update);
                if($res !== false){
                    insert_admin_log('更新年级成功');
                    return $this->returnJson(['id'=>$id],'更新年级成功',1);
                }
                return $this->returnJson([],'更新年级失败');
            }
            $info = db('grade')->where("id = {$id}")->find();
            $info['url'] = '/admin/grade/edit';
            return $this->fetch('save',compact('info'));
        }

        public function del(){
            $id = $this->request->param('id',0);
            if(db('user')->where("grade_id = {$id}")->count() > 0){
                return $this->returnJson([],'删除失败：该年级下存在学员');
            }
            $res = db('grade')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除年级成功');
                return $this->returnJson([],'删除成功',1);
            }
            return $this->returnJson([],'删除失败');
        }

    }
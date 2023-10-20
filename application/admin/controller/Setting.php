<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Setting.php
     *  Create Date : 2021/12/30 11:10
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\controller;
    use app\common\controller\AdminController;

    class Setting extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function area(){
            if($this->request->isPost()){
                $list = db('area')->order('sort asc')->select();
                $total = db('area')->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            $list = json_encode(list_to_tree(db('area')->select(),'id','p_id'));
            return $this->fetch('area',compact('list'));
        }

        public function add_area(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $name = $this->request->param('name','');
                if(empty($name)){
                    return $this->returnJson([],'区域创建失败：区域名称不能为空');
                }
                $sort = $this->request->param('sort',0);
                $id = $this->request->param('id',0);
                $p_id = $this->request->param('p_id',0);
                // 判断当前是否存在一级区域
//                if(db('area')->where("p_id = 0")->count() > 0 && $p_id == 0){
//                    return $this->returnJson([],'区域创建失败：顶级区域已存在');
//                }
                $insert = [
                  'name' => $name,
                  'p_id' => intval($p_id),
                  'sort' => intval($sort),
                  'create_time' => time()
                ];
                $id = db('area')->insertGetId($insert);
                if($id > 0){
                    insert_admin_log('区域创建成功');
                    return $this->returnJson(['id'=>$id],'区域创建成功',1);
                }
                return $this->returnJson([],'区域创建失败');
            }
            $info = db('area p')
                ->field('p.name p_name,p.id p_id')
                ->where("p.id = {$id}")
                ->find();
            $areas = db('area')->where('p_id = 0')->select();
            $action = strtolower(request()->action());
            return $this->fetch('save_area',compact('action','info','areas'));
        }

        public function edit_area(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $name = $this->request->param('name','');
                $p_id = $this->request->param('p_id',0);
                // 判断当前是否存在一级区域
//                if(db('area')->where("p_id = 0 and id != {$id}")->count() > 0 && $p_id == 0){
//                    return $this->returnJson([],'更新创建失败：顶级区域已存在');
//                }
                if(empty($name)){
                    return $this->returnJson([],'更新区域失败：区域名称不能为空');
                }
                $sort = $this->request->param('sort',0);
                $updata = [
                  'name' => $name,
                  'sort' => $sort
                ];
                $res = db('area')->where("id = {$id}")->update($updata);
                if($res !== false){
                    insert_admin_log('更新区域成功');
                    return $this->returnJson(['id'=>$id],'更新区域成功',1);
                }
                return $this->returnJson([],'更新区域失败：区域名称不能为空');
            }
            $info = db('area a')
                ->join('area p','p.id = a.p_id')
                ->field('p.name p_name,p.id p_id,a.*')
                ->where("a.id = {$id}")
                ->find();
            $info['type'] = 'edit';
            $info['url'] = '/'.trim(request()->module() . '/' . request()->controller() . '/' . request()->action());
            $areas = db('area')->where('p_id = 0')->select();
            return $this->fetch('save_area',compact('info','areas'));
        }

        public function del_area(){
            $id = $this->request->param('id',0);
            if(db('area')->where("p_id = {$id}")->count() > 0){
                return $this->returnJson([],'删除失败：当前区域下存在子区域');
            }
            $res = db('area')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除区域成功');
                return $this->returnJson(['id'=>$id],'删除区域成功',1);
            }
            return $this->returnJson([],'删除区域失败');
        }
    }
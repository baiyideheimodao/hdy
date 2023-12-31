<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Subjectversion.php
     *  Create Date : 2021/12/31 9:04
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\controller;
    use app\common\controller\AdminController;

    class Subjectversion extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function index(){
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $list = db('subject_version')
                    ->alias('sv')
                    ->join('subject s','s.id = sv.subject_id and s.state ='.$this->status['NORMAL'])
                    ->join('school sch','sch.id = sv.school_id','left')
                    ->field('sv.id,s.name subject_name,sv.name,sv.state,sch.name school_name')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
                $total = db('subject_version')
                    ->alias('sv')
                    ->join('subject s','s.id = sv.subject_id and s.state ='.$this->status['NORMAL'])
                    ->field('sv.id,s.name subject_name,sv.name,sv.state')
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        public function add(){
            if($this->request->isPost()){
                $name = $this->request->post('name','');
                if(empty($name)){
                    return $this->returnJson([],'创建科目版本失败：科目版本名称不能为空');
                }
                $subject_id = $this->request->post('subject_id',0);
                if(empty($subject_id)){
                    return $this->returnJson([],'创建科目版本失败：所属科目请选择');
                }
                $school_id = $this->request->post('school_id','');
                if(!empty($subject_id)){
                    $school_id = substr($school_id,1);
                }

                $sort = $this->request->post('sort',0);

                $id = db('subject_version')->insertGetId([
                                                     'name' => $name,
                                                     'sort' => $sort,
                                                     'subject_id' => $subject_id,
                                                     'school_id' => $school_id,
                                                     'admin_id' => cookie('id'),
                                                     'create_time' => time()
                                                 ]);
                if($id > 0){
                    insert_admin_log('创建科目版本成功');
                    return $this->returnJson(['id'=>$id],'创建科目版本成功',1);
                }
                return $this->returnJson([],'创建科目版本失败');
            }
            $subjects = db('subject')->where("state = 1")->select();
            return $this->fetch('save',compact('subjects'));
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
                        return $this->returnJson([],'更新科目版本失败：科目版本名称不能为空');
                    }
                }
                if(isset($data['subject_id'])){
                    if(!empty($data['subject_id'])){
                        $update['subject_id'] = $data['subject_id'];
                    }else{
                        return $this->returnJson([],'更新科目版本失败：所属科目请选择');
                    }
                }

                if((isset($data['school_id']))&&$data['school_id']>=0){
                    $data['school_id'] = substr($data['school_id'],1);
                    $update['school_id'] = $data['school_id'];
                }
                $sort = $this->request->post('sort','sss');
                if($sort !== 'sss'){
                    $update['sort'] = $sort;
                }
                $state = $this->request->post('state',-999);
                if($state !== -999){
                    $update['state'] = $state;
                }
                $res = db('subject_version')->where("id = {$id}")->update($update);
                if($res !== false){
                    insert_admin_log('更新科目版本成功');
                    return $this->returnJson(['id'=>$id],'更新科目版本成功',1);
                }
                return $this->returnJson([],'更新科目版本失败');
            }
            $info = db('subject_version su')
                ->field('su.*,sch.name school_name')
                ->join('school sch','sch.id = su.school_id','left')

                ->where("su.id = {$id}")->find();
            $info['school_id'] = 'b'.$info['school_id'];
            $subjects = db('subject su')
                ->where("su.state = 1")
                ->select();
            $info['url'] = '/admin/subjectversion/edit';

            return $this->fetch('save',compact('info','subjects'));
        }

        public function del(){
            $id = $this->request->param('id',0);
            if(db('subject_version_maker')->where("version_id = {$id}")->count() > 0){
                return $this->returnJson([],'删除科目版本失败：该科目版本下存在其他类目');
            }
            $res = db('subject_version')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除科目版本成功');
                return $this->returnJson([],'删除科目版本成功',1);
            }
            return $this->returnJson([],'删除科目版本失败');
        }
    }
<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Paper.php
     *  Create Date : 2022/1/1 12:59
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\admin\controller;
    use app\common\controller\AdminController;
    use \think\facade\Cookie;
    use think\facade\Env;

    class Paper extends AdminController {
        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function index(){
            $where = [];
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $admin_id = Cookie::get('id');
                if((int)cookie('group_id') !== config('rx.groupId')){
                    $where[] = ['n.admin_id','=',$admin_id];
                }
                $list = db('area_paper')
                    ->alias('n')
                    ->join('area a','n.area_id = a.id and a.state = '.$this->status['NORMAL'],'left')
                    ->where($where)
                    ->field("n.id,ifnull(a.name,'') area,n.name,n.desc,FROM_UNIXTIME(n.create_time) create_time,n.state")
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
                $total = db('area_paper')
                    ->alias('n')
                    ->join('area a','n.area_id = a.id and a.state = '.$this->status['NORMAL'],'left')
                    ->where($where)
                    ->field("n.id")
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        public function add(){
            if($this->request->isPost()){
                $data = [];
                $name = $this->request->param('name','');
                if(empty($name)){
                    return $this->returnJson([],'创建项目失败：项目名称不能为空');
                }
                $data['name'] = $name;
                $desc = $this->request->param('desc','');
                $data['desc'] = $desc;
                $data['area_id'] = Cookie::has('area_id')?Cookie::get('area_id'):'';

                $data['create_time'] = time();
                $data['admin_id'] = Cookie::get('id');
                $id = db('area_paper')->insertGetId($data);
                if($id > 0){
                    insert_admin_log('创建项目成功');
                    return $this->returnJson(['id'=>$id],'创建项目成功',1);
                }
                return $this->returnJson([],'创建项目失败');
            }
            // 区域列表
            $areas = db('area')->where("p_id != 0")->select();
            return $this->fetch('save',compact('areas'));
        }

        public function edit(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $data = $this->request->param();
                $update = [];
                if(isset($data['name'])){
                    if(empty($data['name'])){
                        return $this->returnJson([],'更新项目失败：项目名称不能为空');
                    }
                    $update['name'] = $data['name'];
                }
                if(isset($data['desc'])){
                    $update['desc'] = $data['desc'];
                }
                if(isset($data['state'])){
                    $update['state'] = $data['state'];
                }
                $res = db("area_paper")->where("id = {$id}")->update($update);
                if($res !== false){
                    insert_admin_log('更新项目成功');
                    return $this->returnJson(['id'=>$id],'更新项目成功',1);
                }
                return $this->returnJson([],'更新项目失败');
            }
            $info = db('area_paper')->where("id = {$id}")->field("id,name,`desc`")->find();
            $info['url'] = '/admin/paper/edit';
            // 区域列表
            $areas = db('area')->where("p_id != 0")->select();
            return $this->fetch('save',compact('info','areas'));
        }

        public function del(){
            $id = $this->request->param('id',0);
            // 判断当前任务是否已开启
            $taskIds = db('task')->where("paper_id = {$id}")->field('id')->select();
            if(!empty($taskIds)){
                $task_arr = array_column($taskIds,'id');
                $taskStr = implode(',',$task_arr);
                if(db('task_log')->where("task_id in ({$taskStr})")->count() > 0){
                    return $this->returnJson([],'删除失败:当前项目已开启');
                }
            }
            $res = db('area_paper')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除项目成功');
                return $this->returnJson([],'删除成功',1);
            }
            return $this->returnJson([],'删除失败');
        }

        public function bind(){

        }
    }
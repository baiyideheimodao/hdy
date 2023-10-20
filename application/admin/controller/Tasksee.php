<?php
/**
 *  Encoding : UTF-8
 *  Separator : Unix and OS X (\n)
 *  File Name : Task.php
 *  Create Date : 2022/1/4 9:24
 *  Version : 0.1
 *  Copyright : skylong Project Team Copyright (C)
 *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
 */

namespace app\admin\controller;
use app\common\controller\AdminController;
use think\Db;
use think\Exception;
use \think\facade\Cookie;
use think\facade\Env;

class Tasksee extends AdminController {

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
                $where[] = ['t.admin_id','=',$admin_id];
            }
            $type = $this->request->param('type','');
            if($type !== ''){
                $where[] = ['t.type','=',$type];
            }
            $state = $this->request->param('state','');
            if($state !== ''){
                $where[] = ['t.state','=',$state];
            }
            // todo::2022-01-24 废除任务列表中的执行日期，改为分配任务时，再设置执行日期
            // $done_time = $this->request->param('done_time','');
            // if(!empty($done_time) && trim($done_time) !== '-'){
            //     list($start_date,$end_date) = explode('-',trim($done_time));
            //     $where[] = ['t.done_time','between',[strtotime($start_date.' 00:00:00'),strtotime($end_date.' 23:59:59')]];
            // }
            $area_id = $this->request->param('area_id','');
            if(!empty($area_id)){
                $where[] = ['cla.area_id','=',$area_id];
            }
            $school_id = $this->request->param('school_id','');
            if(!empty($school_id)){
                $where[] = ['cla.school_id','=',$school_id];
            }
            $grade_id = $this->request->param('grade_id','');
            if(!empty($grade_id)){
                $where[] = ['cla.grade_id','=',$grade_id];
            }

            $name = $this->request->param('name','');
            if(!empty($name)){
                $where[] = ['t.name','like',"%{$name}%"];
            }

            $list = db('task_allot_log')
                ->alias('tal')
                ->join('task t','tal.task_id = t.id','left')
                ->join("admin a",'t.admin_id = a.id','left')
                ->join("task_index ti",'ti.task_id = t.id','left')
                ->join("class_group cla",'cla.id = tal.class_or_group_id','left')
                ->join("area ar",'ar.id = cla.area_id','left')
                ->field("tal.id,ar.name area_name,t.admin_id,cla.name class_name,tal.class_or_group_id,t.type,t.name,t.state,a.name admin_name,FROM_UNIXTIME(t.create_time) create_time,if(tal.done_time=0,'',FROM_UNIXTIME(tal.done_time,'%Y-%m-%d')) done_time,ti.level,ti.subject_id")
                ->where($where)
                ->where('tal.type = 1')
                ->limit(($page-1)*$limit,$limit)
                ->select();
            foreach ($list as $k =>$val){
                $list[$k]['subject_name'] = db('subject')->where('id',$val['subject_id'])->value('name');
                if($val['level']==1){
                    $list[$k]['level_name'] = 'A';
                }else if($val['level']==2){
                    $list[$k]['level_name'] = 'B';
                }else{
                    $list[$k]['level_name'] = 'C';
                }
            }
            $total = db('task_allot_log')
                ->alias('tal')
                ->join('task t','tal.task_id = t.id','left')
                ->join("class_group cla",'cla.id = tal.class_or_group_id','left')
                ->where($where)
                ->where('tal.type = 1')
                ->field("tal.id")
                ->count();
            return $this->returnTabelJson($list,'查询成功',$total);
        }
        $area = db('area')->where("p_id = 9")->select();
        $school = db('school')->where("state = 1")->select();
        $grade = db('grade')->where("state = 1")->select();
        //项目
        $maker = db('subject_version_maker')->where("state = 1")->select();
        return $this->fetch('index',compact('area','school','grade'));
    }

    public function add(){
        $admin_id = cookie('id');
        if($this->request->isPost()){
            $data = $this->request->param();
            unset($data['id']);

            if(!isset($data['question_id']) || count($data['question_id']) == 0){
                return $this->returnJson([],'请选择试题');
            }
            $question_ids = $data['question_id'];
            unset($data['question_id']);

            // 处理索引信息
            $dataIndex = [];
            $dataIndex['project_id'] = $data['project_id'];
            $dataIndex['subject_id'] = $data['subject_id'];
            $dataIndex['version_id'] = $data['version_id'];
            $dataIndex['page_num_start'] = $data['page_num_start'];
            $dataIndex['page_num_end'] = $data['page_num_end'];
            $dataIndex['level'] = $data['level'];

            unset($data['project_id'],$data['subject_id'],$data['version_id'],$data['page_num_start'],$data['page_num_end'],$data['level'],$data['file']);


            // todo::2022-01-24 废除任务列表中的执行日期，改为分配任务时，再设置执行日期
            // $done_time = $this->request->param('done_time','');
            // if(empty($done_time)){
            //     return $this->returnJson([],'创建项目失败：项目执行日期不能为空');
            // }
            // if(strtotime($done_time) <= strtotime(date('Y-m-d').' 23:59:59')){
            //     return $this->returnJson([],'创建项目失败：项目执行日期不能早于今天日期');
            // }
            // $data['done_time'] = strtotime($done_time);
            $data['admin_id'] = $admin_id;
            $data['update_time'] = time();
            $res = false;
            db()->startTrans();
            $data['create_time'] = time();
            $id = db('task')->insertGetId($data);
            if($id > 0){
                $inserts = [];
                foreach ($question_ids as $item){
                    $inserts[] = [
                        'task_id' => $id,
                        'question_id' => $item
                    ];
                }
                $res = db('task_question_log')->insertAll($inserts);

                // 处理索引
                $dataIndex['task_id'] = $id;
                db('task_index')->insertGetId($dataIndex);
            }
            if($res !== false){
                db()->commit();
                insert_admin_log('任务创建成功');
                return $this->returnJson($data,'任务创建成功',1);
            }
            db()->rollback();
            return $this->returnJson([],'任务创建失败');

        }
        $subject = db('subject')->where("state = 1")->select();
        $version = db('subject_version')->where("state = 1")->select();
        $project = db('project')->where("state = 1")->select();
        $questions = db('question')->where("state = {$this->status['NORMAL']}")->select();

        //项目
        $maker = db('subject_version_maker')->where("state = 1")->select();
        return $this->fetch('save',compact('questions','subject','version','project','maker'));
    }

    public function edit(){
        $id = $this->request->param('id',0);
        $info = db('task')->where("id = {$id}")->find();
        if($this->request->isPost()){
            $data = $this->request->param();
            // 判断当前任务是否已经下发
            // if(db('task_log')->where("task_id = {$id}")->count() > 0){
            if(db('task_log')->alias('tl')->join('task_allot_log tal','tal.id = tl.task_id')->where("tal.task_id = {$id}")->count('tl.id') > 0){
                return $this->returnJson([],'不可修改，当前任务已下发');
            }
            if(isset($data['state'])){
                $data['update_time'] = time();
                $res = db('task')->where("id = {$id}")->update($data);
            }else{
                if(!isset($data['question_id']) || count($data['question_id']) == 0){
                    return $this->returnJson([],'请选择试题');
                }
                $question_ids = $data['question_id'];
                unset($data['question_id']);

                // todo::2022-01-24 废除任务列表中的执行日期，改为分配任务时，再设置执行日期
                // $done_time = $this->request->param('done_time','');
                // if(empty($done_time)){
                //     return $this->returnJson([],'创建项目失败：项目执行日期不能为空');
                // }
                // if(strtotime($done_time) <= strtotime(date('Y-m-d').' 23:59:59')){
                //     return $this->returnJson([],'创建项目失败：项目执行日期不能早于今天日期');
                // }
                // $data['done_time'] = strtotime($done_time);
                $data['update_time'] = time();

                // 处理索引信息
                $dataIndex = [];
                $dataIndex['project_id'] = $data['project_id'];
                $dataIndex['subject_id'] = $data['subject_id'];
                $dataIndex['version_id'] = $data['version_id'];
                $dataIndex['page_num_start'] = $data['page_num_start'];
                $dataIndex['page_num_end'] = $data['page_num_end'];
                $dataIndex['level'] = $data['level'];

                unset($data['project_id'],$data['subject_id'],$data['version_id'],$data['page_num_start'],$data['page_num_end'],$data['level'],$data['file']);

                db()->startTrans();
                db('task_question_log')->where("task_id = {$id}")->delete();
                db('task')->where("id = {$id}")->update($data);
                $inserts = [];
                foreach ($question_ids as $item){
                    $inserts[] = [
                        'task_id' => $id,
                        'question_id' => $item
                    ];
                }
                $res = db('task_question_log')->insertAll($inserts);
                db('task_index')->where("task_id = {$id}")->update($dataIndex);
            }
            if($res !== false){
                db()->commit();
                insert_admin_log('任务修改成功');
                return $this->returnJson($data,'操作成功',1);
            }
            db()->rollback();
            return $this->returnJson([],'操作失败');
        }
        // $info['done_time'] = date('Y-m-d',$info['done_time']);
        $question_ids = db('task_question_log')->where("task_id = {$id}")->field('question_id')->select();
        $info['question_ids'] = implode(',',array_column($question_ids,'question_id'));

        $info['url'] = '/admin/task/edit';
        $info['questions'] = db('question')->where("id in ({$info['question_ids']})")->select();
        $task_index = db('task_index')->where("task_id = {$id}")->find();
        $info['index'] = empty($task_index) ? ['project_id'=>0,'subject_id'=>0,'version_id'=>0,'page_num_start'=>'','page_num_end'=>'','level'=>0] : $task_index;
        $subject = db('subject')->where("state = 1")->select();
        $version = db('subject_version')->where("state = 1")->select();
        $project = db('project')->where("state = 1")->select();
        $questions = db('question')->where("state = {$this->status['NORMAL']}")->select();
        //项目
        $maker = db('subject_version_maker')->where("state = 1")->select();
        //return json($info);
        return $this->fetch('save',compact('info','questions','subject','version','project','maker'));
    }

    public function allot(){
        $task_id = $this->request->param('id',0);
        $task_ids = $this->request->param('ids','');
        if($this->request->isPost()){
            $class_ids = $this->request->param('class_ids',[]);
            $group_ids = $this->request->param('group_ids',[]);
            if(empty($class_ids) && empty($group_ids)){
                return $this->returnJson([],'请勾选班组或群组');
            }
            if(!empty($task_ids)){
                $allot_type = $this->request->param('allot_type',0); // 分配类型：默认统一分配
                switch (intval($allot_type)){
                    case 0:
                        // 统一分配
                        $done_time = $this->request->param('done_time','');
                        if(empty($done_time)){
                            return $this->returnJson([],'请选择任务执行日期');
                        }
                        $task_ids_arr = array_filter(explode(',',$task_ids));
                        $datas = [];
                        foreach ($class_ids as $item){
                            foreach ($task_ids_arr as $ids){
                                // todo::判断当前日期下当前任务是否已经分配当当前组
                                if($this->checkIsAllot(strtotime($done_time),$item,$ids)){
                                    continue;
                                }
                                $datas[] = [
                                    'task_id' => $ids,
                                    'type' => 1,
                                    'class_or_group_id' => $item,
                                    'done_time' => strtotime($done_time),
                                    'create_time' => time()
                                ];
                            }
                        }
                        break;
                    case 1:
                        // 间隔天数分配
                        $start_time = $this->request->param('start_time','');
                        if(empty($start_time)){
                            return $this->returnJson([],'请选择任务开始日期');
                        }
                        $inteval_time = $this->request->param('inteval_time','');
                        if(empty($inteval_time) || intval($inteval_time) < 0){
                            return $this->returnJson([],'请填写正确的间隔天数');
                        }
                        $task_ids_arr = array_filter(explode(',',$task_ids));
                        $sortList = $this->request->param('sort',[]);
                        $rangeTimes = record_all_days(strtotime($start_time),count($task_ids_arr),$inteval_time);
                        $datas = [];
                        asort($sortList);
                        $sort_task_ids = [];
                        foreach ($sortList as $key => $item){
                            if(in_array($key,$task_ids_arr)){
                                $sort_task_ids[] = $key;
                            }
                        }
                        foreach ($class_ids as $item){
                            foreach ($sort_task_ids as $key => $ids){
                                // todo::判断当前日期下当前任务是否已经分配当当前组
                                if($this->checkIsAllot($rangeTimes[$key],$item,$ids)){
                                    continue;
                                }
                                $datas[] = [
                                    'task_id' => $ids,
                                    'type' => 1,
                                    'class_or_group_id' => $item,
                                    'done_time' => $rangeTimes[$key],
                                    'create_time' => time()
                                ];
                            }
                        }
                        break;
                    case 2:
                        // 单一分配
                        $task_ids_arr = array_filter(explode(',',$task_ids));
                        $task_list = $this->request->param('task_list',[]);
                        foreach ($class_ids as $item){
                            foreach ($task_list as $key => $done_time){
                                if(!in_array($key,$task_ids_arr)){
                                    continue;
                                }
                                // todo::判断当前日期下当前任务是否已经分配当当前组
                                if($this->checkIsAllot(strtotime($done_time),$item,$key)){
                                    continue;
                                }
                                $datas[] = [
                                    'task_id' => $key,
                                    'type' => 1,
                                    'class_or_group_id' => $item,
                                    'done_time' => strtotime($done_time),
                                    'create_time' => time()
                                ];
                            }
                        }
                        break;
                    default:
                        return $this->returnJson([],'任务分配失败');
                        break;
                }
                db()->startTrans();
                try{
                    db('task_allot_log')->insertAll($datas);
                    db()->commit();
                    insert_admin_log('任务分配成功');
                    return $this->returnJson([],'任务分配成功',1);
                }catch (Exception $exception){
                    db()->rollback();
                    return $this->returnJson([],'任务分配失败');
                }
            }

            return $this->returnJson([],'');
        }
        $class_group_id = [];
        if(!empty($task_id)){
            $class_ids = db('task_allot_log')->where("task_id = {$task_id} and type = 1")->field('class_or_group_id')->select();
            $class_group_id = array_column($class_ids,'class_or_group_id');
        }
        $group_group_id = [];
        if(!empty($task_id)){
            $group_ids = db('task_allot_log')->where("task_id = {$task_id} and type = 2")->field('class_or_group_id')->select();
            $group_group_id = array_column($group_ids,'class_or_group_id');
        }
        $classes = db('class_group')->where("state = {$this->status['NORMAL']} and admin_id = ".\cookie('id'))->select();
        $groups = db('group_group')->where("state = {$this->status['NORMAL']} and admin_id = ".\cookie('id'))->select();
        // 获取任务列表
        $task_lists = Db::name('task')->where("id in ({$task_ids})")->select();
        return $this->fetch('allot',compact('classes','groups','class_group_id','group_group_id','task_id','task_ids','task_lists'));
    }


    public function del(){
        $id = $this->request->param('id',0);
        if(db('task_log')->alias('tl')->join('task_allot_log tal','tal.id = tl.task_id')->where("tal.task_id = {$id}")->count('tl.id') > 0){
            return $this->returnJson([],'删除失败：当前任务进行中');
        }
        $res = db('task')->where("id = {$id}")->delete();
        $res = db('task_allot_log')->where("task_id = {$id}")->delete();
        db('task_index')->where("task_id = {$id}")->delete();
        if($res !== false){
            insert_admin_log('任务删除成功');
            return $this->returnJson([],'删除成功',1);
        }
        return $this->returnJson([],'删除失败');
    }

    /**
     * 判断当前日期下当前任务是否已经分配当当前组
     * @param int $done_time
     * @param int $class_id
     * @param int $task_id
     * @return bool
     */
    private function checkIsAllot($done_time = 0,$class_id = 0,$task_id = 0,$type = 1){

        return db('task_allot_log')->where("task_id = {$task_id} and class_or_group_id = {$class_id} and type = {$type} and done_time = {$done_time}")->count() > 0;
    }


}
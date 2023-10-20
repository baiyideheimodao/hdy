<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Census.php
     *  Create Date : 2022/8/18 14:07
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\admin\controller\v2;
    use app\common\controller\AdminController;

    class Census extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function person_index(){
            //yyyy-M-d ~ yyyy-M-d
            $rangeDate = date('Y-1-1').' ~ '.date('Y-12-31');
            return $this->fetch('person_num',compact('rangeDate'));
        }

        public function time_index(){
            $rangeDate = date('Y-1-1').' ~ '.date('Y-12-31');
            return $this->fetch('time_num',compact('rangeDate'));
        }

        public function school_index(){
            $rangeDate = date('Y-1-1').' ~ '.date('Y-12-31');
            $classData = db('class_group cg')
                ->field('cg.id,cg.name,cg.city_id,cg.area_id,cg.school_id,cg.grade_id')
                ->where(['cg.school_id'=>cookie('school_id')])
                ->select();
            $grade = db('grade')->where("state=1")->select();
            return $this->fetch('school_index',compact('rangeDate','classData','grade'));
        }

        public function timeNums(){//getLessonNums(){
            $gradeId = $this->request->param('grade_id');
            //
            $list = array_map(function($v){
                // $v['class']
                // var_dump($v);

                //查询学生是否完成
                // $info = db('user_type_task_table')->where('task_over_time','<>',0)->where('type_task_table',);
                $list = array_values(array_filter(
                    db('type_task_log_table')->where('class_id',$v['id'])->select(),
                    function($v){
                        $taskInfo = db('type_task_table')->where('id',$v['type_task_id'])->find();
                        if(!$taskInfo){
                            return false;
                        }
                        if($taskInfo['type']!==14){
                            // printLog($taskInfo);
                            return false;
                        }
                        //查到有一个以上完成的学生
                        return db('user_type_task_table')->where('task_over_time','<>',0)->where('type_task_id',$v['type_task_id'])->find();
                    }
                )) ;#----------
                $list['class_name'] = $v['name'];
                $list['num'] = count($list)-1;
                $list['teacher_name'] = null;
                $list['teacher_id'] = null;
                if(array_key_exists(0,$list)){
                    $teacherInfo = db('teacher')->where('id',$list['0']['teacher_id'])->find();
                    if($teacherInfo){
                        $list['teacher_name'] = $teacherInfo['username'];
                        $list['teacher_id'] = $teacherInfo['id'];
                    }
                }
                // $list['class_name'] = $v[]
                return $list;
            },db('class_group')->where('grade_id',$gradeId)->select());
            $data = [
                'title' => '课时数',
                'categories' => array_column($list,'class_name'),
                'values' => array_column($list,'num'),
                'teacher_values' => array_column($list,'teacher_name'),
                'list' => $list,
                'data' => $list,
                'msg' => '查询成功',
                'code'=> 0,
                'count' => count($list)
            ];
            return json($data);
        }

        //获取老师已经上的课
        public function geTaskList(){
            //
            $params = $this->request->param();
            //
            $teacherId = $params['teacher_id'];
            $teacherInfo = db('teacher')->where('id',$teacherId)->find();
            if(!$teacherInfo){
                return json(['msg'=>'获取不到老师','code'=>0],400);
            }
            $classId = $teacherInfo['class_id'];
            $list = array_values(array_filter(
                array_map(function($v){
                    $taskInfo = db('type_task_table')->where('id',$v['type_task_id'])->find();
                    if(!$taskInfo){
                        return false;
                    }
                    if($taskInfo['type']!==14){
                        return false;
                    }
                    //查到有一个以上完成的学生
                    $isComplete =  db('user_type_task_table')->where('task_over_time','<>',0)->where('type_task_id',$v['type_task_id'])->select();
                    if(!$isComplete){
                        return $isComplete;
                    }
                    $count = db('user_type_task_table')->where('type_task_id',$v['type_task_id'])->select();
                    $taskInfo['rate'] = count(array_filter($count,function($v){
                        return $v['status'] === 9;
                    })).'/'.count($count);
                    $taskInfo['image'] = 'http://teacher.beirundq.com/'.$taskInfo['image'];
                    $taskInfo['type'] = null;
                    $markInfo = db('task')->where('id',$taskInfo['tasked_id'])->find();
                    // var_dump($markInfo);
                    if($markInfo){
                        $tagInfo = db('course_index')->where('id',$markInfo['sub_course_index_id'])->find();
                        // var_dump($tagInfo);
                        if($tagInfo){
                             $taskInfo['type'] = $tagInfo['name'];
                        }
                    }
                    return $taskInfo;
                },db('type_task_log_table')->where('class_id',$classId)->select())
            ));
            // var_dump($list);
            $data = [
                'title' => '任务详情',
                'list' => $list,
                'data' => $list,
                'msg' => '查询成功',
                'code'=> 0,
                'count' => count($list)
            ];
            return json($data);
        }

        //将变量分配给user_task_info模板
        public function get_task_id(){
            $teacherId  = $this->request->param('teacher_id');
            return $this->fetch('user_task_info',['teacher_id'=>$teacherId]);
        }

        public function timeNums_bak(){
            $where = [];
            $rangeDate = $this->request->param('range_date','');
            $gradeId = $this->request->param('grade_id',1);
            if($rangeDate == ' ~ ' || empty($rangeDate)){
                $where[] = ['ttt.start_date','between',strtotime(date('Y-01-01 00:00:00')).','.strtotime(date('Y-12-31 23:59:59'))];
            }else{
                list($startDate,$endDate) = explode(' ~ ',$rangeDate);
                $where[] = ['ttt.start_date','between',strtotime(date($startDate.' 00:00:00')).','.strtotime(date($endDate.' 23:59:59'))];
            }
            if(cookie('school_id') > 0){
                if(!empty($gradeId)){
                    $where[] = ['cg.grade_id','=',$gradeId];
                }
                $title = '课时数';
                $list = db('class_group cg')
                    ->join('teacher t','t.class_id = cg.id')
                    ->join('type_task_log_table utlt','utlt.class_id = cg.id')
                    ->join('type_task_table ttt','ttt.id = utlt.type_task_id')
                    ->field('ttt.id,cg.name class_name,t.username teacher_name,count(ttt.id)*'.self::TIME_RATE_NUM.' num')
                    ->where($where)
                    ->select();
                $total = count($list);
                if($total==1&&$list[0]['class_name']==null){
                    $list = [];
                    $total = 0;
                }
            }else{
                $where[] = ['s.state','=',1];
                if(cookie('id') != config('rx.adminId')){
                    $where[] = ['s.area_id','=',cookie('area_id')];
                }
                $title = '课时数';
                $list = db('school')
                    ->alias('s')
                    ->join('class_group cg','cg.school_id = s.id')
                    ->join('user_class_log ucl','ucl.class_group_id = cg.id')
                    ->join('user_type_task_table uttt','ucl.u_id = uttt.user_id')
                    ->join('type_task_table ttt','ttt.id = uttt.type_task_id')
                    ->join('type_task_log_table ttlt','ttlt.type_task_id = ttt.id')
                    ->join('teacher t','t.id = ttlt.teacher_id')
                    ->where($where)
                    ->field('s.id,s.`name`,t.username teacher_name,count(uttt.id)*'.self::TIME_RATE_NUM.' num')
                    ->group('s.id,s.name')
                    ->select();
            }
            $data = [
                'title' => $title,
                'categories' => array_column($list,'class_name'),
                'values' => array_column($list,'num'),
                'teacher_values' => array_column($list,'teacher_name'),
                'list' => $list,
                'data' => $list,
                'msg' => '查询成功',
                'code'=> 0,
                'count' => $total
            ];
            return json($data);
        }



        // 参与人数
        public function personNums(){
            $where = [];
            $rangeDate = $this->request->param('range_date','');
            if($rangeDate == ' ~ ' || empty($rangeDate)){
                $where[] = ['ttt.start_date','between',strtotime(date('Y-01-01 00:00:00')).','.strtotime(date('Y-12-31 23:59:59'))];
            }else{
                list($startDate,$endDate) = explode(' ~ ',$rangeDate);
                $where[] = ['ttt.start_date','between',strtotime(date($startDate.' 00:00:00')).','.strtotime(date($endDate.' 23:59:59'))];
            }
            if(cookie('school_id') > 0){
                $where[] = ['cg.school_id','=',cookie('school_id')];
                $title = '年级参加人数';
                $list = db('user')
                    ->alias('u')
                    ->join('user_type_task_table uttt','u.id = uttt.user_id')
                    ->join('user_class_log ucl','ucl.u_id = u.id')
                    ->join('class_group cg','ucl.class_group_id = cg.id')
                    ->join('grade g','g.id = u.grade_id')
                    ->join('type_task_table ttt','ttt.id = uttt.type_task_id')
                    ->where($where)
                    ->field('g.id,g.`name`,count(distinct u.id) num')
                    ->group('g.id,g.name')
                    ->select();
            }else{
                $where[] = ['s.state','=',1];
                if(cookie('id') != config('rx.adminId')){
                    $where[] = ['s.area_id','=',cookie('area_id')];
                }
                $title = '校参与人数';
                $list = db('school')
                    ->alias('s')
                    ->join('class_group cg','cg.school_id = s.id')
                    ->join('user_class_log ucl','ucl.class_group_id = cg.id')
                    ->join('user_type_task_table uttt','ucl.u_id = uttt.user_id')
                    ->join('type_task_table ttt','ttt.id = uttt.type_task_id')
                    ->where($where)
                    ->field('s.id,s.`name`,count(distinct uttt.id) num')
                    ->group('s.id,s.name')
                    ->select();
            }
            $data = [
                'title' => $title,
                'categories' => array_column($list,'name'),
                'values' => array_column($list,'num')
            ];
            return json($data);
        }

        //详细情况查看
        public function user_task_info(){
            $task_id  = $this->request->param('id');

            if($this->request->isPost()){
                $task_id = $this->request->param('task_id');
                $list = db('user_type_task_table')
                    ->alias('uttt')
                    ->join('user u','u.id = uttt.user_id')
                    ->where('uttt.type_task_id',$task_id)
                    ->field("u.id,u.avatar,u.sex,u.name username,u.nickname,uttt.status")
                    ->select();
                $total = count($list);
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch('user_task_info_detail',compact('task_id'));

        }


    }
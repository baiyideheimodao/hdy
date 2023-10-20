<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Sign.php
     *  Create Date : 2022/7/12 14:27
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\admin\controller\v2;
    use app\common\controller\AdminController;
    use think\exception\PDOException;

    class Sign extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function index(){
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $where = [];
                $where[] = ['ttst.status','=',0];
                $list = db('type_task_sign_table')
                    ->alias('ttst')
                    ->join('type_task_table ttt','ttst.type_task_id = ttt.id')
                    ->join('user u','ttst.user_id = u.id')
                    ->join('school s','u.school_id = s.id','left')
                    ->join('grade g','u.grade_id = g.id','left')
                    ->field('ttst.id,ttst.status,FROM_UNIXTIME(ttst.create_time) create_time,ttt.type,u.avatar,if(u.`name` = "",u.nickname,u.`name`) student_name,s.`name` school_name,g.`name` grade_name')
                    ->where($where)
                    ->order('ttst.status asc')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
                $total = db('type_task_sign_table')
                    ->alias('ttst')
                    ->join('type_task_table ttt','ttst.type_task_id = ttt.id')
                    ->join('user u','ttst.user_id = u.id')
                    ->where($where)
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        public function history(){
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $where = [];
                $where[] = ['ttst.status','>',0];
                $list = db('type_task_sign_table')
                    ->alias('ttst')
                    ->join('type_task_table ttt','ttst.type_task_id = ttt.id')
                    ->join('user u','ttst.user_id = u.id')
                    ->join('school s','u.school_id = s.id','left')
                    ->join('grade g','u.grade_id = g.id','left')
                    ->field('ttst.id,ttst.status,FROM_UNIXTIME(ttst.create_time) create_time,ttt.type,u.avatar,if(u.`name` = "",u.nickname,u.`name`) student_name,s.`name` school_name,g.`name` grade_name')
                    ->where($where)
                    ->order('ttst.status asc')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
                $total = db('type_task_sign_table')
                    ->alias('ttst')
                    ->join('type_task_table ttt','ttst.type_task_id = ttt.id')
                    ->join('user u','ttst.user_id = u.id')
                    ->where($where)
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        // 审核
        public function audit(){
            $id = $this->request->param('id',0);
            $ids = $this->request->param('ids','');
            if($this->request->isPost()){
                $status = $this->request->param('status',0);
                $content = $this->request->param('content','');
                $ids = explode(',',$ids);
                $ids[] = $id;
                $ids = array_filter($ids);
                db()->startTrans();
                try{
                    $res = db('type_task_sign_table')->whereIn('id',$ids)->update(['status'=>$status,'content'=>$content,'update_time'=>time(),'admin_id'=>cookie('id')]);
                    if($res !== false){
                        $signs = db('type_task_sign_table')->whereIn('id',$ids)->select();
                        $userIds = array_column($signs,'user_id');
                        // 同步活动到用户活动表
                        if($status == self::AUDIT_PASS){
                            if (!$this->synToUserDones($ids)){
                                db()->rollback();
                                return $this->returnJson([],'操作失败');
                            }
                        }
                        db()->commit();
                        // 发送站内信
                        $this->messageToUids('报名审核通知',"您提交的活动报名申请".($status == 1?'已通过':'未通过'),'',$userIds,3);
                        return $this->returnJson([],'操作成功',1);
                    }
                }catch (PDOException $PDOException){
                    db()->rollback();
                    return $this->returnJson([],'操作失败:'.$PDOException->getMessage());
                }
            }
            return $this->fetch('audit',compact('id','ids'));
        }

        /**
         * 获取审核通过的用户报名的考勤或评委教师id
         * @param int $type_task_id
         * @param int $type
         * @return mixed
         */
        public function getTeacherIdOfTypeTask($type_task_id = 0,$type = self::TYPE_WORK){
            $is_admin = 0;
            if($type == self::TYPE_WORK){
                // 劳动大赛，返回评委老师id
                $teacher_id = db('type_task_log_table')->where('type_task_id',$type_task_id)->value('teacher_id');
                $is_admin = 1;
            }else{
                // 职业体验、公益活动，返回基地老师id
                $teacher_id = db('type_task_log_table')->where('type_task_id',$type_task_id)->value('base_teacher_id');
            }
            return [$teacher_id,$is_admin];
        }

        /**
         * 设置阶段任务执行表
         * @param int   $id
         * @param array $item
         * @param array $arr
         * @return array
         */
        private function setLevelTaskLogs($id = 0,$item = [],$arr = []){
            $list = db('type_task_done_table')
                ->where('type_task_id',$item['type_task_id'])
                ->whereIn('type',[self::CLASS_BEFORE,self::CLASS_BEFORE_R])
                ->order('done_time asc')
                ->select();
            list($teacher_id,$is_admin) = $this->getTeacherIdOfTypeTask($item['type_task_id'],$item['type']);
            foreach ($list as $key => $value){
                array_push($arr,[
                    'user_id' => $item['user_id'],
                    'user_type_task_id' => $id,
                    'task_id' => $item['task_id'],
                    'create_time' => time(),
                    'type' => 1,
                    'teacher_id' => $teacher_id,
                    'is_admin' => $is_admin,
                    'status' => $key === 0 ? self::STATUS_ING : self::STATUS_NO,
                    'done_time' => $value['done_time']
                ]);
            }
            return $arr;
        }

        /**
         * 同步任务到用户
         * @param array $ids
         * @return bool
         */
        private function synToUserDones($ids = []){
            $list = db('type_task_sign_table')
                ->alias('ttst')
                ->join('type_task_table ttt','ttst.type_task_id = ttt.id')
                ->whereIn('ttst.id',$ids)
                ->field('ttst.id,ttst.user_id,ttst.type_task_id,ttt.task_id,ttt.type,ttst.create_time')
                ->select();
            $logs = [];
            foreach ($list as $key => $item){
                // todo::record 审核活动默认人员未考勤,状态设置为【1课前待完成】
                $id = db('user_type_task_table')->insertGetId(['user_id'=>$item['user_id'],'type_task_id' => $item['type_task_id'],'record'=>0,'create_time'=>$item['create_time'],'status'=>self::STATUS_BEFORE_N]);
                // todo::同步阶段任务
                $logs = $this->setLevelTaskLogs($id,$item,$logs);
            }
            $res = db('user_type_task_log_table')->insertAll($logs);
            if($res !== false){
                insert_admin_log('同步阶段任务成功');
                return true;
            }
            insert_admin_log('同步阶段任务失败');
            return false;
        }
    }
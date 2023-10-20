<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : BaseController.php
     *  Create Date : 2021/12/16 8:17
     *  Version : 0.1
     *  Copyright : test-master Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\common\controller;
    use app\admin\model\Message;
    use OSS\Core\OssException;
    use think\Controller;
    use think\Exception;
    use think\exception\PDOException;
    use think\facade\Cookie;
    use think\facade\Db;

    class BaseController extends Controller {

        public $status = [
            'NORMAL' => 1,
            'DELSTATUS' => 2,
            'ZEROSTATUS' => 0
        ];
        public $prefix = '';

        /*配置项 -- start*/
        const MODE_ONE = 1; // 必选
        const MODE_TWO = 2; // 自选

        const TASK_LEVEL_TYPE_B = 1; // 任务阶段类型-课前
        const TASK_LEVEL_TYPE_A = 2; // 任务阶段类型-课后

        const TYPE_HOME = 11; // 家庭
        const TYPE_SCHO = 12; // 校内
        const TYPE_BASE = 13; // 基地
        const TYPE_SOFT = 14; // 校内轻课
        const TYPE_JOBS = 21; // 职业
        const TYPE_BPUB = 22; // 公益
        const TYPE_WORK = 23; // 劳动
        const TYPE_HAPPY = 24; //幸福村落

        const CLASS_BEFORE_R = 11; // 课前记录题
        const CLASS_BEFORE = 10; // 课前非记录题
        const CLASS_AFTER_R = 21; // 课后记录题
        const CLASS_AFTER = 20; // 课后非记录

        const STATUS_NO = 0; // 未开始
        const STATUS_ING = 1; // 进行中
        const STATUS_ED = 2; // 已完成

        const STATUS_BEFORE_N = 1; // 课前待完成
        const STATUS_BEFORE_ED = 2; // 课前已完成
        const STATUS_WAIT_R = 3; // 课中待考勤
        const STATUS_WAIT_SCAN = 4; // 课中待打卡
        const STATUS_EVAL_N = 5; // 课中待评价
        const STATUS_UPLOAD_N = 6; // 课中待上传
        const STATUS_EVAL_ED = 7; // 课中已上传且课后待完成
        const STATUS_AFTER_ED = 8; // 课后已完成
        const STATUS_AFTER_EVAL_ED = 9; // 课后已评分

        const TEACHER_SCHOOL = 1; // 学校教师
        const TEACHER_BASE = 2; // 基地教师

        const LOG_STATUS_ING = 1; // 阶段任务进行中状态
        const LOG_STATUS_ED = 2; // 阶段任务已完成状态

        const ONE_DAY = 86400; // 一天包含的秒

        const EVAL_NO = 0; // 未评价状态
        const EVAL_ED = 1; // 已评价状态
        const DEFAULT_NO = 0; // 通用未选中状态
        const DEFAULT_ED = 1; // 通用选中状态

        const AUDIT_PASS = 1; // 审核通过
        const AUDIT_NO_PASS = 2; // 审核不通过

        const STATE_OPEN = 1; // 类型任务已开启
        const STATE_CLOSE = 0; // 类型任务未开启

        const OBJ_LIST = [1,3,5]; // 客观题类型列表 const OBJ_LIST = [3,5];

        const TASK_RECORD_TYPE = 1; // 任务记录题类型
        const TASK_NO_RECORD_TYPE = 0; // 任务非记录题类型

        const ONCE_NUM = 1.0; // 及时
        const NO_ONCE_NUM = 0.8; // 不及时

        const ONE_QUEST_SORCE = 3; //每道试题分数

        const BEAN_RATE_NUM = 100; //劳豆兑换比【默认1学分兑换100劳豆】
        const TIME_RATE_NUM = 1; // 课时兑换比【默认1项目兑换1课时】
        /*配置项 -- end*/

        /**
         * 给所有人发送站内信
         * @param string $title
         * @param string $content
         * @param string $body
         * @param int    $type
         * @param array  $images
         * @return bool|int|string
         */
        public function messageToAll($title = '',$content = '',$body = '',$type = 0,$images = []){
            $lists = db('user')->select();
            if(empty($lists)){
                return false;
            }
            $uids = array_column($lists,'id');
            return $this->messageToUids($title,$content,$body,$uids,$type,$images);
        }

        /**
         * 站内信群发到指定人群
         * @param string $title
         * @param string $content
         * @param string $body
         * @param array  $u_ids
         * @param int    $type
         * @param array  $images
         * @return int|string
         */
        public function messageToUids($title = '',$content = '',$body = '',$u_ids = [],$type = 0,$images = []){
            $datas = [];
            if(empty($u_ids)){
                return 1;
            }
            foreach ($u_ids as $item){
                $datas[] = [
                    'title' => $title,
                    'content' => $content,
                    'type' => $type,
                    'body' => $body,
                    'u_id' => $item,
                    'admin_id' => Cookie::has('id')?Cookie::get('id'):0,
                    'area_id' => Cookie::has('area_id')?Cookie::get('area_id'):0,
                    'images' => empty($images)?'':serialize($images),
                    'create_time' => time()
                ];
            }
            return (new Message())->insertAll($datas);
        }

        /**
         * 多班组发送站内信
         * @param string $title
         * @param string $content
         * @param string $body
         * @param string $class_ids
         * @param int    $type
         * @param array  $images
         * @return int
         */
        public function messageToClasses($title = '',$content = '',$body = '',$class_ids = '',$type = 0,$images = []){
            $class_ids_arr = explode(',',$class_ids);
            foreach ($class_ids_arr as $item){
                $this->messageToClass($title,$content,$body,$item,$type,$images);
            }
            return 1;
        }

        /**
         * 站内信班组发
         * @param string $title
         * @param string $content
         * @param string $body
         * @param int    $class_id
         * @param int    $type
         * @param array  $images
         * @return bool|int|string
         */
        public function messageToClass($title = '',$content = '',$body = '',$class_id = 0,$type = 0,$images = []){
            $lists = db('user_class_log')->where("class_group_id = {$class_id}")->field('u_id')->select();
            if(empty($lists)){
                return false;
            }
            $uids = array_column($lists,'u_id');
            return $this->messageToUids($title,$content,$body,$uids,$type,$images);
        }

        /**
         * 站内信群组发
         * @param string $title
         * @param string $content
         * @param string $body
         * @param int    $group_id
         * @param int    $type
         * @param array  $images
         * @return bool|int|string
         */
        public function messageToGroup($title = '',$content = '',$body = '',$group_id = 0,$type = 0,$images = []){
            $lists = db('user_group_log')->where("group_group_id = {$group_id}")->field('u_id')->select();
            if(empty($lists)){
                return false;
            }
            $uids = array_column($lists,'u_id');
            return $this->messageToUids($title,$content,$body,$uids,$type,$images);
        }

        /**
         * 积分增加
         * @param int    $u_id
         * @param int    $ponit_num
         * @param string $desc
         * @param int    $is_system
         */
        public function pointIncLog($u_id = 0,$ponit_num = 0,$desc = '',$is_system = 1){
            db('user_point_log')->insertGetId(['num'=>$ponit_num,'desc'=>$desc,'is_system'=>$is_system,'type'=>0,'u_id'=>$u_id,'create_time'=>time(),'admin_id'=>Cookie::has('id')?cookie('id'):0]);
            $this->messageToClient(config('message.user_point_inc'),"【{$desc}】积分：{$ponit_num}。 ",$u_id);
        }

        /**
         * 站内信单发
         * @param string $title
         * @param string $content
         * @param string $body
         * @param int    $u_id
         * @param int    $type
         * @param array  $images
         * @return int|string
         */
        public function messageToClient($title = '',$content = '',$body = '',$u_id = 0,$type = 0,$images = []){
            return (new Message())->insertGetId([
                'title' => $title,
                'content' => $content,
                'body' => $body,
                'u_id' => $u_id,
                'admin_id' => Cookie::has('id')?Cookie::get('id'):0,
                'area_id' => Cookie::has('area_id')?Cookie::get('area_id'):0,
                'images' => empty($images)?'':serialize($images),
                'create_time' => time()
                                         ]);
        }

        /**
         * 扣除积分
         * @param int    $u_id
         * @param int    $ponit_num
         * @param string $desc
         * @param int    $is_system
         */
        public function pointDecLog($u_id = 0,$ponit_num = 0,$desc = '',$is_system = 1){
            db('user_point_log')->insertGetId(['num'=>$ponit_num,'desc'=>$desc,'is_system'=>$is_system,'type'=>1,'u_id'=>$u_id,'create_time'=>time(),'admin_id'=>Cookie::has('id')?cookie('id'):0]);
            $this->messageToClient(config('message.user_point_dec'),"【{$desc}】积分：{$ponit_num}。 ",$u_id);
        }

        public function dealUserPids($pid = 0){

        }

        public function returnObj($data = [],$msg = '',$code = 0){
            return compact('data','msg','code');
        }

        public function returnJson($data = [],$message = '',$code = 0){
            $re = ['code' => $code, 'msg' => $message, 'data' => $data];
            return json($re);
        }

        protected function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
            $this->prefix = config('database.prefix');
            $this->setSysConfig();
        }

        protected function setSysConfig(){
            $config = cache('db_system_data');
            if (!$config) {
                $config = [];
                foreach (db('system')->select() as $v) {
                    $config['sys_data'][$v['name']] = $v['content'];
                }
                cache('db_system_data', $config);
            }
            config($config);
        }

        /*
         * 获取学分类型
         */
        private function combinDescOfReckon($type = self::TYPE_HOME){
            $desc = '家庭项目';
            switch ($type){
                case self::TYPE_HOME:
                    $desc = '家庭项目';
                    break;
                case self::TYPE_SCHO:
                    $desc = '校内项目';
                    break;
                case self::TYPE_BASE:
                    $desc = '基地项目';
                    break;
                case self::TYPE_JOBS:
                    $desc = '职场活动';
                    break;
                case self::TYPE_BPUB:
                    $desc = '公益活动';
                    break;
                case self::TYPE_WORK:
                    $desc = '劳动大赛';
                    break;
            }
            return $desc;
        }

        /**
         * 检查任务答案是否提交及时
         * @param int $user_type_task_id
         * @param int $end_date
         * @param int $now
         * @throws Exception
         * @throws PDOException
         */
        public function checkTaskIsOnce($user_type_task_id = 0,$end_date = 0,$now = 0){
            $nowTime = $now;
            $endTime = strtotime(date('Y-m-d',$end_date).' 00:00:00') + self::ONE_DAY - 1;
            $is_once = 2;
            if($nowTime <= $endTime){
                // 若提交时间在范围时间内，则及时，更新当前用户的类型任务及时状态
                $is_once = 1;
            }
            db('user_type_task_table')->where('id',$user_type_task_id)->update(['is_once'=>$is_once]);
        }

        /**
         * 批量检查任务答案是否提交及时
         * @param array $user_type_task_ids
         * @param int   $type_task_id
         * @param int   $type
         */
        public function batchCheckTaskIsOnce($user_type_task_ids = [],$type_task_id = 0,$type = 0){
            $nowTime = time();
            $is_once = 2;
            if($type == 0){
                $endTime = db('type_task_table')->where('id',$type_task_id)->value('end_date') + self::ONE_DAY - 1;
                if($nowTime <= $endTime){
                    // 若提交时间在范围时间内，则及时，更新当前用户的类型任务及时状态
                    $is_once = 1;
                }
                db('user_type_task_table')->whereIn('id',$user_type_task_ids)->update(['is_once'=>$is_once]);
            }else{
                // todo::由于每个用户的最终提交时间不一致，所以需遍历所有记录，分别检查提交是否及时
                $list = db('user_type_task_table')->whereIn('id',$user_type_task_ids)->select();
                $onceList = [];
                $noOnceList = [];
                foreach ($list as $item){
                    if($nowTime <= $item['task_over_time']){
                        array_push($onceList,$item['id']);
                    }else{
                        array_push($noOnceList,$item['id']);
                    }
                }
                db('user_type_task_table')->whereIn('id',$onceList)->update(['is_once'=>1]);
                db('user_type_task_table')->whereIn('id',$noOnceList)->update(['is_once'=>2]);
            }
        }

        /**
         * 检查指定用户必选项目是否完成10次
         * @param int $user_id
         * @return bool
         */
        public function checkIsTensBrage($user_id = 0){
            $count = db('user_brage_log_table')->where('user_id',$user_id)->where('type',1)->count();
            if($count > 0){
                // 必选项目已完成10次并且已经获取徽章
                return true;
            }
            $list = db('user_reckon_score_table')
                ->alias('urst')
                ->join('user_type_task_table uttt','uttt.id = urst.user_type_task_id')
                ->join('type_task_table ttt','ttt.id = uttt.type_task_id')
                ->where('urst.user_id',$user_id)
                ->where('ttt.mode',self::MODE_ONE)
                ->field('urst.id,urst.per_score,urst.total_score,urst.once_num')
                ->limit(10)
                ->select();
            if(count($list) < 10){
                // 必选项目完成不足10次
                return false;
            }
            // 获取徽章分值
            $brages = db('brage_table')->select();
            // 计算分值
            /**
             * 分值=及时次数/10*100*10次得分率
            （10次得分率=10次总得分/10次总分）
             */
            // 1. 10次总得分
            $tensPerScore = array_sum(array_column($list,'per_score'));
            // 2. 10次总分
            $tensTotalScore = array_sum(array_column($list,'total_score'));
            // 3. 及时次数
            $onceCount = count(searchData($list,'once_num',self::ONCE_NUM));
            // 4. 计算分值
            $score = $onceCount / 10 * 100 * ($tensPerScore / $tensTotalScore);
            $userBrage = [];
            // 5. 比较分值，获取对应徽章
            foreach ($brages as $item){
                if($score >= $item['min_num'] && $score <= $item['max_num']){
                    $userBrage = $item;
                    break;
                }
            }
            if(empty($userBrage)){
                // 分值不在已设置的徽章分值内
                return false;
            }
            db('user_brage_log_table')->insertGetId([
                'user_id' => $user_id,
                'user_type_task_id' => 0,
                'create_time' => time(),
                'brage_name' => $userBrage['name'],
                'brage_image' => $userBrage['image'],
                'score' => $score,
                'type' => 1,
                'descript' => '学生达到必选项目完成10次'
            ]);
            return true;
        }

        /**
         * 批量计入学分
         * @param array $user_type_task_ids
         */
        public function batchReckonScore($user_type_task_ids = []){
            $list = db('user_type_task_table')
                ->alias('uttt')
                ->join('type_task_table ttt','uttt.type_task_id = ttt.id')
//                ->whereIn('ttt.id',$user_type_task_ids)
                ->whereIn('uttt.id',$user_type_task_ids)
                ->field('ttt.type,uttt.user_id,uttt.id')
                ->select();
            $inserts = [];
            $workBeans = [];
            foreach ($list as $item){
                $insert['user_id'] = $item['user_id'];
                $insert['year'] = date('Y');
                $insert['month'] = date('m');
                $insert['day'] = date('d');
                $insert['create_time'] = time();
                $insert['type'] = 1;
                $insert['status'] = 1;
                $insert['desc'] = $this->combinDescOfReckon($item['type']);
                $insert['user_type_task_id'] = $item['id'];
                $beans = $insert;
                list($scoring_rate,$difficulty_points,$once_num,$pre_score,$total_score,$score) = $this->calculateCredits($item['id']);
                $insert['score'] = $score;
                $beans['num'] = $score * self::BEAN_RATE_NUM;
                $insert['difficulty_points'] = $difficulty_points;
                $insert['once_num'] = $once_num;
                $insert['per_score'] = $pre_score;
                $insert['total_score'] = $total_score;
                $insert['scoring_rate'] = $scoring_rate;

                array_push($inserts,$insert);
                array_push($workBeans,$beans);
            }
            db('user_reckon_score_table')->insertAll($inserts);
            db('user_work_bean_table')->insertAll($workBeans);
        }

        /**
         * 计算学分
         * @param int $user_type_task_id
         * @return array
         */
        public function calculateCredits($user_type_task_id = 0){
            $info = db('user_type_task_table')
                ->alias('uttt')
                ->join('type_task_table ttt','uttt.type_task_id = ttt.id')
                ->where('uttt.id',$user_type_task_id)
                ->field('ttt.type,ttt.task_id,ttt.tasked_id,ttt.difficulty_points,is_once')
                ->find();
            $difficulty_points = $info['difficulty_points'];
            $once_num = self::ONCE_NUM;
            if($info['type'] == self::TYPE_WORK){
                // 如果是劳动大赛，只考虑最终分数
                // 单项学分=得分率*项目难度分*及时系数
                // 劳动大赛均分/题目总分*项目难度分*1
                // 1.获取【用户阶段任务】分数
                $pre_score = db('user_type_task_log_table')->where('user_type_task_id',$user_type_task_id)->value('sroce');
                // 获取当前项目的task_id,并且检查对应任务是否是记录任务
                $type = db('task')->where('id',$info['task_id'])->value('type');
                if($type == self::TASK_RECORD_TYPE){
                    $total_score = self::ONE_QUEST_SORCE;
                }else{
                    $total_score = self::ONE_QUEST_SORCE * db('task_question_log')->where('task_id',$info['task_id'])->count();
                }
                $scoring_rate = round($pre_score/$total_score,2);
                $score = round($scoring_rate * $difficulty_points * $once_num,2);
                return [$scoring_rate,$difficulty_points,$once_num,$pre_score,$total_score,$score];
            }
            // 反之包含：
            // 1.客观题系统评判分数 A
            $truth_num = db('user_type_task_log_table')->where('user_type_task_id',$user_type_task_id)->sum('truth_num');
            $pre_score_1 = $truth_num * self::ONE_QUEST_SORCE;
            // 2.主观题对应教师评判分数 B
            $pre_score_2 = db('user_mark_table')->where('user_type_task_id',$user_type_task_id)->sum('score');
            $pre_score = $pre_score_1 + $pre_score_2;
            // 获取当前项目的课前任务task_id,并且检查对应任务是否是记录任务
            $type_1 = db('task')->where('id',$info['task_id'])->value('type');
            $pre_num_1 = $type_1 == self::TASK_RECORD_TYPE ? 1 : db('task_question_log')->where('task_id',$info['task_id'])->count();
            $pre_num_2 = 0;
            if($info['type'] != self::TYPE_HOME){
                // 获取当前项目的课后任务tasked_id,并且检查对应任务是否是记录任务
                $type_2 = db('task')->where('id',$info['tasked_id'])->value('type');
                $pre_num_2 = $type_2 == self::TASK_RECORD_TYPE ? 1 : db('task_question_log')->where('task_id',$info['tasked_id'])->count();
            }
            $total_score = ($pre_num_1 + $pre_num_2) * self::ONE_QUEST_SORCE;
            if($info['is_once'] == 2){
                $once_num = self::NO_ONCE_NUM;
            }
            $scoring_rate = round($pre_score/$total_score,2);
            $score = round($scoring_rate * $difficulty_points * $once_num,2);
            return [$scoring_rate,$difficulty_points,$once_num,$pre_score,$total_score,$score];
        }

        // 分数计入
        public function reckonScore($type_task_id = 0,$user_id = 0,$user_type_task_log_id = 0){
            if(!empty($user_type_task_log_id)){
                $info = db('user_type_task_log_table')
                    ->alias('uttlt')
                    ->join('user_type_task_table uttt','uttt.id = uttlt.user_type_task_id')
                    ->join('type_task_table ttt','uttt.type_task_id = ttt.id')
                    ->where('uttlt.id',$user_type_task_log_id)
                    ->field('ttt.type,uttt.id')
                    ->find();
            }else{
                $info = db('user_type_task_table')
                    ->alias('uttt')
                    ->join('type_task_table ttt','uttt.type_task_id = ttt.id')
                    ->where('uttt.user_id',$user_id)
                    ->where('uttt.type_task_id',$type_task_id)
                    ->field('ttt.type,uttt.id')
                    ->find();
            }
            $insert['user_id'] = $user_id;
            $insert['year'] = date('Y');
            $insert['month'] = date('m');
            $insert['day'] = date('d');
            $insert['create_time'] = time();
            $insert['type'] = 1;
            $insert['status'] = 1;
            $insert['desc'] = $this->combinDescOfReckon($info['type']);
            $insert['user_type_task_id'] = $info['id'];

            $beans = $insert;

            list($scoring_rate,$difficulty_points,$once_num,$pre_score,$total_score,$score) = $this->calculateCredits($info['id']);
            $insert['score'] = $score;
            $beans['num'] = $score * self::BEAN_RATE_NUM;
            $insert['difficulty_points'] = $difficulty_points;
            $insert['once_num'] = $once_num;
            $insert['per_score'] = $pre_score;
            $insert['total_score'] = $total_score;
            $insert['scoring_rate'] = $scoring_rate;

            db('user_reckon_score_table')->insertGetId($insert);
            db('user_work_bean_table')->insertGetId($beans);
        }

        /**
         * 判断当前类型任务是否需要考勤、评价等
         * @param int $user_task_log_id
         * @param int $type_task_id
         * @return array
         */
        public function checkTypeTaskEditor($user_task_log_id = 0,$type_task_id = 0){
            if(empty($type_task_id)){
                $user_type_task_id = db('user_type_task_log_table')->where('id',$user_task_log_id)->value('user_type_task_id');
                $typeTaskInfo = db('user_type_task_table')
                    ->alias('uttt')
                    ->join('type_task_table ttt','uttt.type_task_id = ttt.id')
                    ->where('uttt.id',$user_type_task_id)
                    ->field('ttt.edit_tasking,ttt.type,ttt.is_record,ttt.is_eval,ttt.is_upt_img,ttt.is_upt_word,ttt.id,ttt.avail_days')
                    ->find();
            }else{
                $typeTaskInfo = db('type_task_table')
                    ->alias('ttt')
                    ->where('ttt.id',$type_task_id)
                    ->field('ttt.edit_tasking,ttt.type,ttt.is_record,ttt.is_eval,ttt.is_upt_img,ttt.is_upt_word,ttt.id,ttt.avail_days')
                    ->find();
            }
            $flag = false;
            $status = -1;
            if($typeTaskInfo['type'] == self::TYPE_HOME){
                return [true,9999];
            }
            if($typeTaskInfo['edit_tasking'] == 0){
                return [$flag,$status];
            }
            $flag = true;
            $status = "{$typeTaskInfo['is_record']}{$typeTaskInfo['is_eval']}{$typeTaskInfo['is_upt_img']}{$typeTaskInfo['is_upt_word']}";
            return [$flag,$status];
        }

        /**
         * 检查当前是否存在剩余阶段任务
         * @param int $user_task_log_id
         * @return bool
         * @throws Exception
         * @throws PDOException
         * @throws \think\db\exception\DataNotFoundException
         * @throws \think\db\exception\ModelNotFoundException
         * @throws \think\exception\DbException
         */
        public function checkIsHasOtherLogs($user_task_log_id = 0){
            $logTable = db('user_type_task_log_table')->where('id',$user_task_log_id)->find();
            $info = db('user_type_task_log_table')->where("user_type_task_id = {$logTable['user_type_task_id']} and type = {$logTable['type']} and status = 0")->order('id asc')->find();
            logs('检查是否存在阶段任务','');
            if(empty($info)){
                logs('不存在','检查是否存在阶段任务');
                return false;
            }
            // todo::存在其他同类型的未完成的阶段任务，将此阶段任务状态更新未进行中
            db('user_type_task_log_table')->where('id',$info['id'])->update(['status' => self::LOG_STATUS_ING]);
            logs('存在，并更新完成','检查是否存在阶段任务');
            return true;
        }

        private function sendMsgOfStatusToClient($user_ids = [],$status = 0){

        }

        /**
         * 更新用户总类型任务状态表状态
         * @param int $user_task_log_id
         * @param int $user_id
         * @param int $status
         * @param int $user_type_task_id
         */
        public function changeTypeTaskStatus($user_task_log_id = 0,$user_id = 0,$status = 0,$user_type_task_id = 0){
            $upt['status'] = $status;
            if($status == self::STATUS_AFTER_EVAL_ED){
                $upt['done_time'] = time();
            }
            if(empty($user_type_task_id)){
                $logTable = db('user_type_task_log_table')->where('id',$user_task_log_id)->find();
                db('user_type_task_table')->where('id',$logTable['user_type_task_id'])->where('user_id',$user_id)->update($upt);
            }else{
                db('user_type_task_table')->where('id',$user_type_task_id)->where('user_id',$user_id)->update($upt);
            }
        }

        /**
         * 批量更新用户总类型任务状态表状态
         * @param array $user_type_task_ids
         * @param int   $status
         * @throws Exception
         * @throws PDOException
         */
        public function batchChangeTypeTaskStatus($user_type_task_ids = [],$status = 0){
            if(!empty($user_type_task_ids)){
                $upt['status'] = $status;
                if($status == self::STATUS_AFTER_EVAL_ED){
                    $upt['done_time'] = time();
                }
                db('user_type_task_table')->whereIn('id',$user_type_task_ids)->update($upt);
            }
        }

        /**
         * 实例化阿里云OSS
         * @return \OSS\OssClient
         * @throws \OSS\Core\OssException
         */
        public function new_oss(){
            //实例化OSS/*
            $oss = new \OSS\OssClient(config('sys_data.accessKeyId'),config('sys_data.accessKeySecret'),config('sys_data.endpoint'));
            return $oss;
        }

        /**
         * 上传指定的本地文件内容
         * @param $bucket
         * @param $object
         * @param $Path
         * @return string|\think\response\Json
         * @throws \OSS\Core\OssException
         */
        public function uploadFile($bucket,$object,$Path){
            //try 要执行的代码,如果代码执行过程中某一条语句发生异常,则程序直接跳转到CATCH块中,由$e收集错误信息和显示
            try{
                //没忘吧，new_oss()是我们上一步所写的自定义函数
                $ossClient =$this->new_oss();
                //uploadFile的上传方法
                $res=    $ossClient->uploadFile($bucket, $object, $Path);
                return json($res);
            } catch(OssException $e) {
                //如果出错这里返回报错信息
                return $e->getMessage();
            }
        }

        /**
         * 处理记录题答案提交格式
         * @param array $answers
         * @return array
         */
        private function dealSubAnswers($answers = []){
            $fromatAnswers = [];
            foreach ($answers as $key => $answer){
                $id = $answer['id'];
                $answer = current($answer)['text'];
                $itemAns = [];
                foreach ($answer as $k => $v){
                    $value = $v['text'];
                    $itemAns[$k] = $value;
                }
                array_push($fromatAnswers,[
                    'id' => $id,
                    'answer' => $itemAns
                ]);
            }
            return $fromatAnswers;
        }


        /**
         * 拼接答案列表
         * @param array $ans
         * @param array $files
         * @return array
         * @throws \think\db\exception\DataNotFoundException
         * @throws \think\db\exception\ModelNotFoundException
         * @throws \think\exception\DbException
         */
        public function combinAnswerList($ans = [],$files = []){
            foreach ($ans as $key => $item){
                $question = db('question')->where('id',$item['id'])->find();
                $ext = empty($question['ext'])?[]:unserialize($question['ext']);
                if(!empty($ext)){
                    // 记录题
                    $ans = $this->dealSubAnswers($ans);
                    $answers = $ans[$key]['answer'];
                    foreach ($answers as $k => $vo){
                        $ans[$key]['answer'][$k] = "{$ext[$k]['name']}:{$vo}{$ext[$k]['unit']}";
                    }
                }
                $ans[$key]['questionName'] = $question['name'];
                $ans[$key]['images'] = $question['images'];
                $ans[$key]['desc'] = $question['desc'];
                $options = html_entity_decode($question['options']);
                $ans[$key]['content'] = @unserialize($options) === false ? $options : @unserialize($options);
                $ans[$key]['file_type'] = $question['file_type'];
                $images = searchData($files,'id',$item['id']);
                $answerImages = [];
                if(!empty($images) && is_array($images)){
                    foreach ($images as $image){
                        $img = $image['image'];
                        if(is_array($img)){
                            $answerImages = array_merge($answerImages,$img);
                        }else{
                            array_push($answerImages,$img);
                        }
                    }
                }else{
                    $answerImages = $images;
                }
                $ans[$key]['answer']['image'] = $answerImages;
            }
            return $ans;
        }

        /**
         * 获取教师id
         * @param int $type_task_id
         * @return mixed
         */
        public function getTeacherIdOfTypeTask($type_task_id = 0){
            $mode = db('type_task_table')->where('id',$type_task_id)->value('mode');
            if($mode == self::MODE_ONE){
                // 必选项目返回校内教师id
                return db('type_task_log_table')->where('type_task_id',$type_task_id)->value('teacher_id');
            }
            // 自选项目返回基地教师id
            return db('type_task_log_table')->where('type_task_id',$type_task_id)->value('base_teacher_id');
        }

        /**
         * 路径处理
         * @param string $url
         * @return string
         */
        public function changeUrl($url = ''){
            if(empty($url)) return $url;
            if(preg_match("/^http/",$url)){
                return $url;
            }
            return config('sys_data.web_site') . $url;
        }

        /**
         * 同步用户答案
         * @param int   $user_id
         * @param int   $user_task_log_id
         * @param array $answers
         */
        public function synUserAnswers($user_id = 0,$user_task_log_id = 0,$answers = []){
            list($sub_ans,$obj_ans,$sub_files,$obj_files) = $answers;
            $ansList = array_merge($sub_ans,$obj_ans);
            $fileList = array_merge($sub_files,$obj_files);
            $fileList = array_column($fileList,'image','id');
            $insert = [];
            foreach ($ansList as $key => $item){
                $questionInfo = db('question')->where('id',$item['id'])->field('id,images,answer,type')->find();
                $insert[] = [
                    'user_id' => $user_id,
                    'user_type_task_log_id' => $user_task_log_id,
                    'question_id' => $item['id'],
                    'answer_text' => is_array($item['answer']) ? json_encode($item['answer']) : $item['answer'],
                    'answer_file' => isset($fileList[$item['id']]) ? json_encode($fileList[$item['id']]) : '',
                    'type' => $questionInfo['type'],
                    'truth_text' => $questionInfo['answer'],
                    'truth_file' => '',
                    'create_time' => time()
                ];
            }
            $res = db('user_question_answer_table')->insertAll($insert);
            if($res !== false){
                logs('用户答题同步成功','答题同步');
            }
        }

        /**
         * 批量同步课后任务
         * @param array $ids
         * @throws PDOException
         * @throws \think\db\exception\BindParamException
         * @throws \think\db\exception\DataNotFoundException
         * @throws \think\db\exception\ModelNotFoundException
         * @throws \think\exception\DbException
         */
        public function bachSynUserAfterTask($ids = []){
            $list = db('user_type_task_table')->whereIn('id',$ids)->select();
            $logs = [];
            $updateSql = '';
            $updateDatas = [];
            foreach ($list as $item){
                $info = db('type_task_table')->where('id',$item['type_task_id'])->find();
                // 获取课后执行关系
                $doneList = db('type_task_done_table')
                    ->where('type_task_id',$item['type_task_id'])
                    ->where('task_id',$info['tasked_id'])
                    ->whereIn('type',[self::CLASS_AFTER_R,self::CLASS_AFTER])
                    ->order('done_day asc')
                    ->select();
                $teacher_id = $this->getTeacherIdOfTypeTask($item['type_task_id']);
                foreach ($doneList as $key => $value){
                    $done_time = 0;
                    if($value['type'] == self::CLASS_AFTER_R){
                        // 记录题
                        $done_time = strtotime(date('Y-m-d').' 00:00:01') + self::ONE_DAY * intval($value['done_day']);
                    }
                    array_push($logs,[
                        'user_id' => $item['user_id'],
                        'user_type_task_id' => $item['id'],
                        'task_id' => $info['tasked_id'],
                        'create_time' => time(),
                        'teacher_id' => $teacher_id,
                        'type' => self::TASK_LEVEL_TYPE_A,
                        'status' => $key === 0 ? self::STATUS_ING : self::STATUS_NO,
                        'done_time' => $done_time
                    ]);
                }
                $overTime = strtotime(date('Y-m-d').' 23:59:59') + self::ONE_DAY * intval($info['avail_days']);
                array_push($updateDatas,[
                    'id' => $item['id'],
                    'task_over_time' => $overTime
                ]);
//                $updateSql .= "UPDATE `{$this->prefix}user_type_task_table`  SET `task_over_time` = {$overTime}  WHERE  `type_task_id` = {$item['type_task_id']} AND `user_id` = {$item['user_id']};";
            }

            if(!empty($logs)){
                db('user_type_task_log_table')->insertAll($logs);
            }
            // todo::更新项目截止时间
            if(!empty($updateDatas)){
                foreach ($updateDatas as $item){
                    $id = $item['id'];
                    unset($item['id']);
                    db('user_type_task_table')->where('id',$id)->update($item);
                }
//                $res = model('user_type_task_table')->saveAll($updateDatas);
//                if($res !== false){
//                    insert_admin_log('批量同步课后任务成功，更新项目截止时间成功');
//                }
//                db()->execute($updateSql);
            }
        }

        /**
         * 同步用户课后任务
         * @param array $user_type_task
         * @throws Exception
         * @throws PDOException
         * @throws \think\db\exception\DataNotFoundException
         * @throws \think\db\exception\ModelNotFoundException
         * @throws \think\exception\DbException
         */
        public function synUserAfterTask($user_type_task = []){
            $info = db('type_task_table')->where('id',$user_type_task['type_task_id'])->find();
            // 获取课后执行关系
            $doneList = db('type_task_done_table')
                ->where('type_task_id',$user_type_task['type_task_id'])
                ->where('task_id',$info['tasked_id'])
                ->whereIn('type',[self::CLASS_AFTER_R,self::CLASS_AFTER])
                ->order('done_day asc')
                ->select();
            $logs = [];
            $teacher_id = $this->getTeacherIdOfTypeTask($user_type_task['type_task_id']);
            foreach ($doneList as $key => $item){
                $done_time = 0;
                if($item['type'] == self::CLASS_AFTER_R){
                    // 记录题
                    $done_time = strtotime(date('Y-m-d').' 00:00:01') + self::ONE_DAY * intval($item['done_day']);
                }
                array_push($logs,[
                    'user_id' => $user_type_task['user_id'],
                    'user_type_task_id' => $user_type_task['id'],
                    'task_id' => $info['tasked_id'],
                    'create_time' => time(),
                    'teacher_id' => $teacher_id,
                    'type' => self::TASK_LEVEL_TYPE_A,
                    'status' => $key === 0 ? self::STATUS_ING : self::STATUS_NO,
                    'done_time' => $done_time
                ]);
            }
            if(!empty($logs)){
                db('user_type_task_log_table')->insertAll($logs);
            }
            // todo::更新项目截止时间
            db('user_type_task_table')->where('id',$user_type_task['id'])->update(['task_over_time' => strtotime(date('Y-m-d').' 23:59:59') + self::ONE_DAY * intval($info['avail_days'])]);
        }
        /**
         * 任务类型数量统计
         * @param string $title
         * @param string $content
         * @param int    $class_id
         * @param int    $type
         * @param array  $images
         * @return bool|int|string
         */
        public function form_number($info){
            // todo::获取待批阅项目数量
            $where['ttt.type'] = $info['type'];
            $pyxm_total = db('type_task_table')
                ->alias('ttt')
                ->join('user_type_task_table uttt','ttt.id = uttt.type_task_id')
                ->join('user_type_task_log_table uttlt','uttt.id = uttlt.user_type_task_id')
                ->join('task t','t.id = uttlt.task_id')
                ->where("uttlt.teacher_id = {$info['id']} and uttlt.is_admin = 0")
                ->where('uttt.status',self::STATUS_AFTER_ED||self::STATUS_BEFORE_ED)
                ->where('ttt.status',self::STATE_OPEN)
                ->where($where)
                ->distinct(true)
                ->field("ttt.id,ttt.image")
                ->count();
            // todo::获取待考勤项目数量
            // todo::校内待考勤项目数量
            $kqxn_total = db('type_task_table')
                ->alias('ttt')
                ->join('user_type_task_table uttt','ttt.id = uttt.type_task_id')
                ->join('user_type_task_log_table uttlt','uttt.id = uttlt.user_type_task_id')
                ->join('task t','t.id = uttlt.task_id')
                ->where("uttlt.teacher_id = {$info['id']} and uttlt.is_admin = 0")
                ->where('uttt.status',self::STATUS_WAIT_R)
                ->where('uttlt.type',self::TASK_LEVEL_TYPE_B)
                ->where('ttt.status',self::STATE_OPEN)
                ->distinct(true)
                ->field("ttt.id")
                ->count();
            if($info['type']==1){
                // 学校老师
                // todo::轻课待考勤项目数量
                $kqqk_total = count(db('type_task_table')
                    ->alias('ttt')
                    ->join('user_type_task_table uttt','ttt.id = uttt.type_task_id')
                    ->join('task t','t.id = ttt.tasked_id')
                    ->where('uttt.status',self::STATUS_WAIT_R)
                    ->where('ttt.status',self::STATE_OPEN)
                    ->where('ttt.admin_id',$info['id'])
                    ->distinct(true)
                    ->field("ttt.id,ttt.avail_days")
                    ->select());
                // todo::校内待评价项目数量
                $pj_total = db('type_task_table')
                    ->alias('ttt')
                    ->join('user_type_task_table uttt','ttt.id = uttt.type_task_id')
                    ->join('user_type_task_log_table uttlt','uttt.id = uttlt.user_type_task_id')
                    ->join('task t','t.id = uttlt.task_id')
                    ->where("uttlt.teacher_id = {$info['id']} and uttlt.is_admin = 0")
                    ->where('uttt.status',self::STATUS_EVAL_N)
                    ->where('uttlt.type',self::TASK_LEVEL_TYPE_B)
                    ->where('ttt.status',self::STATE_OPEN)
                    ->whereNotIn('ttt.type',[self::TYPE_BASE,self::TYPE_JOBS,self::TYPE_BPUB,self::TYPE_WORK])
                    ->distinct(true)
                    ->field("ttt.id,ttt.avail_days")
                    ->count();
                // todo::轻课待评价项目数量
                $pjqk_total = count(db('type_task_table')
                    ->alias('ttt')
                    ->join('user_type_task_table uttt','ttt.id = uttt.type_task_id')
                    ->join('task t','t.id = ttt.tasked_id')
                    ->where('uttt.status',self::STATUS_EVAL_N)
                    ->where('ttt.status',self::STATE_OPEN)
                    ->where('ttt.admin_id',$info['id'])
                    ->distinct(true)
                    ->field("ttt.id")
                    ->select());
            }else{
                // 基地
                $kqqk_total = 0;
                // todo::基地目数量
                $pj_total = db('type_task_table')
                    ->alias('ttt')
                    ->join('user_type_task_table uttt','ttt.id = uttt.type_task_id')
                    ->join('type_task_log_table ttlt','ttt.id = ttlt.type_task_id')
                    ->join('task t','t.id = ttt.task_id')
                    ->where("ttlt.base_teacher_id = {$info['id']}")
                    ->where('uttt.status',self::STATUS_EVAL_N)
                    ->where('ttt.status',self::STATE_OPEN)
                    // todo::筛选未取消任务
                    ->where('ttlt.is_cancel',self::DEFAULT_NO)
                    ->whereIn('ttt.type',[self::TYPE_BASE,self::TYPE_JOBS,self::TYPE_BPUB,self::TYPE_WORK])
                    ->distinct(true)
                    ->field("ttt.id,ttt.image,ttt.avail_days")
                    ->count();
                $pjqk_total = 0;
            }
            $task_form_number = [
                //待批阅项目数量
                'pyxm_number'=>$pyxm_total,
                //学校老师和基地老师待考勤项目数量（不包含轻课）
                'kqxm_number'=>$kqxn_total,
                //学校轻课待考勤项目数量（包含轻课）
                'qkxm_number'=>$kqqk_total,
                //学校老师和基地老师待评价项目数量（不包含轻课）
                'pjxm_number'=>$pj_total,
                //轻课待评价项目数量
                'pjqkxm_number'=>$pjqk_total,
            ];
            return $task_form_number;
        }

    }
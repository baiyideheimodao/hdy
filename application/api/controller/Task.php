<?php
/**
 *  任务类
 *  Encoding : UTF-8
 *  Separator : Unix and OS X (\n)
 *  File Name : Task.php
 *  Create Date : 2022/1/10 19:59
 *  Version : 0.1
 *  Copyright : skylong Project Team Copyright (C)
 *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
 */
namespace app\api\controller;
use app\common\controller\WeChatFactory;
use app\common\model\UserWeekRate;
use OSS\Core\OssException;
use think\Db;
use think\facade\Env;

class Task extends WeChatFactory {

    private $u_id = 0;
    private $openid = '';
    private $joinTime = 0;

    public function initialize() {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->openid = $this->request->param('openid');
        $info = db('user')->where("openid = '{$this->openid}'")->find();
        if(!empty($info)){
            $this->u_id = $info['id'];
            $this->joinTime = $info['create_time'];
            (new UserWeekRate())->addOrUpt($this->u_id);
        }
    }

    // 分班组获取当天任务
    public function day_class_task(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        // 1.获取当前用户所在班组
        $class_list = db('class_group')
            ->alias('cg')
            ->join('user_class_log ucl','cg.id = ucl.class_group_id')
            ->where("ucl.u_id = {$this->u_id} and cg.state = {$this->status['NORMAL']}")
            ->field('cg.id,cg.name')
            ->select();
        foreach ($class_list as $key => $item){
            $task = $this->getItemTask(getSomeTime(),$item['id'],[],true);
            if(empty($task)){
                unset($class_list[$key]);
                continue;
            }
            $class_list[$key]['task'] = $task;
        }
        $class_task = $class_list;
        return $this->returnJson(compact('class_task'),'获取成功',1);
    }

    // 不分班组获取当天任务
    public function day_task(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        // 1.获取当前用户所在班组
        $userClassIds = $this->getUserClassIds();
        $class_task = [];
        if(empty($userClassIds)){
            return $this->returnJson(compact('class_task'),'获取成功',1);
        }
        $class_task = $this->getAllTask(getSomeTime(),$userClassIds,[],true);
        return $this->returnJson(compact('class_task'),'获取成功',1);
    }

    // 分班组获取未完成任务
    public function day_class_no_task(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        $class_list = db('class_group')
            ->alias('cg')
            ->join('user_class_log ucl','cg.id = ucl.class_group_id')
            ->where("ucl.u_id = {$this->u_id} and cg.state = {$this->status['NORMAL']}")
            ->field('cg.id,cg.name')
            ->select();
        foreach ($class_list as $key => $item){
            $task = $this->getItemTask(getSomeTime(),$item['id'],"tl.done_time is NULL or tl.done_time = 0",false);
            if(empty($task)){
                unset($class_list[$key]);
                continue;
            }
            $class_list[$key]['task'] = $task;
        }
        $class_task = $class_list;
        return $this->returnJson(compact('class_task'),'获取成功',1);
    }

    // 不分班组当天未完成任务
    public function day_no_task(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        // 1.获取当前用户所在班组
        $userClassIds = $this->getUserClassIds();
        $class_task = [];
        if(empty($userClassIds)){
            return $this->returnJson(compact('class_task'),'获取成功',1);
        }
        $class_task = $this->getAllTask(getSomeTime(),$userClassIds,"tl.done_time is NULL or tl.done_time = 0",true);
        return $this->returnJson(compact('class_task'),'获取成功',1);
    }

    /**
     * 获取指定用户的周计划
     * @return \think\response\Json
     */
    public function week_task(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        // 获取当前周所有日期
        $weeks = get_week();
        $userClassIds = $this->getUserClassIds();
        $week_task = [];
        // if(empty($userClassIds)){
        //     return $this->returnJson(compact('week_task'),'获取成功',1);
        // }
        $weekArr = ["日","一","二","三","四","五","六"];
        foreach ($weeks as $key => $item){
            array_push($week_task,[
                'current' => $item['current'],
                'date' => date('n月j日',strtotime($item['date'])),
                'which' => "星期{$weekArr[date('w',strtotime($item['date']))]}",
                'task' => $this->getAllTask(getSomeTime($item['date']),$userClassIds)
            ]);
        }
        return $this->returnJson(compact('week_task'),'获取成功',1);
    }

    // todo::新 - 周任务列表，添加当前应显示周几的任务
    public function week_task_new(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        // 获取当前周所有日期
        $weeks = get_week();
        $userClassIds = $this->getUserClassIds();
        $week_task = [];
        $tasks = [];
        $week_task['index'] = 0;
        // if(empty($userClassIds)){
        //     return $this->returnJson(compact('week_task'),'获取成功',1);
        // }
        $weekArr = ["日","一","二","三","四","五","六"];
        foreach ($weeks as $key => $item){
            array_push($tasks,[
                'current' => $item['current'],
                'date' => date('n月j日',strtotime($item['date'])),
                'which' => "星期{$weekArr[date('w',strtotime($item['date']))]}",
                'task' => $this->getAllTask(getSomeTime($item['date']),$userClassIds)
            ]);
            if($item['current'] == 1){
                $week_task['index'] = $key - 1;
            }
        }
        $week_task['task'] = $tasks;
        return $this->returnJson(compact('week_task'),'获取成功',1);
    }

    // 获取任务详情
    public function task_info(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        $allot_task_id = $this->request->param('id',0);
        // 根据分配id获取任务id
        $allot_task_info = db('task_allot_log')->where("id = {$allot_task_id}")->find();
        // 获取任务详情
        $task_info = db('task')
            ->alias('t')
            ->where("t.state = {$this->status['NORMAL']} and t.id = {$allot_task_info['task_id']}")
            ->find();
        if(empty($task_info)){
            return $this->returnJson([],'任务不存在');
        }
        $task_info['is_can'] = $this->checkTimeBewteen($allot_task_info['done_time']);
        $current_times = 1; // 当题型为记录题时有效，当前记录次数
        // 判断任务是否已经完成
        $info['task_id'] = $allot_task_id; // 任务分配表id
        // $info['task_id'] = $task_id;
        $info['u_id'] = $this->u_id;
        $log_info = db('task_log')->where($info)->find();
        if(empty($log_info)){
            // 记录任务
            $info['create_time'] = time();
            // 初始化任务中的题目总个数
            $info['total_num'] = $task_info['type'] == 1?1:db('task_question_log')->where("task_id = {$task_info['id']}")->count();
            $task_log_id = db('task_log')->insertGetId($info);
            if($task_log_id > 0){
                $info['done_time'] = '';
                $info['state'] = 0;
                $info['id'] = $task_log_id;
                $info['options'] = [];
            }
        }else{
            $info = $log_info;
            $info['options'] = empty($info['options'])?[]:unserialize($info['options']);
            $info['answer_file'] = empty($info['options'])?[]:unserialize($info['answer_file']);
            $info['done_time'] = empty($info['done_time'])?'':date('Y-m-d H:s:i',$info['done_time']);
            // 记录题处理
            if(empty($info['options'])){
                // 0.答案反馈为空，说明首次记录
            }else{
                // 判断是否记录题
                if($task_info['type'] == 1){ // 1.获取已记录次数
                    $current_times = count($info['options'])+1;
                    // 2.计算下次记录时间
                    $next_step_time = end($info['options'])['time'] + $task_info['inteval_time'] * 24*60*60;
                    // 2.1 更新记录中的下次记录时间，若当前任务只被查看而没有去做时
                    db('task_log')->where("id = {$log_info['id']}")->update(['next_record_time'=>$next_step_time]);
                    // 3.判断【2】中得到的时间是否是今天
                    $task_info['is_can'] = $this->checkTimeBewteen($next_step_time);
                    //@buyang 添加
                    foreach ($info['options'] as $k => $val){
                        $info['options'][$k]['time']=date('Y-m-d',$val['time']);
                    }
                }else{
                    // 其他题型
                    // 完成日期存在，说明当前任务已完成，不可重复答题
                    if(!empty($info['done_time'])){
                        $task_info['is_can'] = false;
                    }
                }
            }
        }
        $task_info['current_times'] = $current_times;
        // 获取当前任务下的问题
        $questions = db('question')
            ->alias('q')
            ->join("subject s",'q.subject_id = s.id','left')
            ->join("subject_version sv",'q.version_id = sv.id','left')
            ->join("subject_version_maker svm",'q.maker_id = svm.id','left')
            ->join("data_log dl",'q.data_log_id = dl.id','left')
            ->join("task_question_log tql",'q.id = tql.question_id')
            ->where("tql.task_id = {$task_info['id']}")
            ->order("tql.question_sort asc")
            // ->where("tql.task_id = {$task_id}")
            ->field('q.id,s.name subject_name,sv.name version_name,q.question_num,svm.name maker_name,q.level,q.pages,q.page_num,q.answer_file_image,q.answer_file_text,q.type,q.name,q.desc,q.options,q.images,q.file_type,q.answer,q.ext')
            ->select();

        $fillTypeNum = 0;
        foreach ($questions as $key => $item){
            if($item['type'] == 2){
                $questions[$key]['options'] = str_replace('/kindeditor/php/../../',config('sys_data.web_site'),$item['options']);
                $questions[$key]['options'] = str_replace('/upload/image/',config('sys_data.web_site')."/upload/image/",$item['options']);
            }else{
                $questions[$key]['options'] = empty($item['options'])?'':unserialize($item['options']);
            }
            if($item['type'] == 4){
                $fillTypeNum ++;
            }
            $questions[$key]['answer'] = empty($item['answer'])?'':unserialize($item['answer']);
            $questions[$key]['ext'] = empty($item['ext'])?'':unserialize($item['ext']);
            $questions[$key]['subject_name'] = empty($item['subject_name'])?'':$item['subject_name'];
            $questions[$key]['version_name'] = empty($item['version_name'])?'':$item['version_name'];
            $questions[$key]['maker_name'] = empty($item['maker_name'])?'':$item['maker_name'];

        }
        if(intval($fillTypeNum) > 0){
            // 更新记录表对应数据的总题数【排除填空】
            db('task_log')->where("id = {$info['id']}")->update(['total_num' => count($questions) - $fillTypeNum]);
        }
        $task_info['task_log'] = $info;
        $task_info['list'] = $questions;
        return $this->returnJson(compact('task_info'),'获取成功',1);
    }

    // 提交答案反馈
    public function sub_answer(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        $task_log_id = $this->request->param('id',0);
        $answer = $this->request->param('answer',[]);
        $answer_file = $this->request->param('answer_file',[]);
        if(empty($answer)){
            return $this->returnJson([],'请提交有效答案');
        }
        // 根据记录id反查任务类型，若为记录任务，需向反馈中添加提交时间
        $task_info = Db::name('task')
            ->alias('t')
            ->join("task_allot_log tal",'t.id = tal.task_id')
            ->join("task_log tl",'tal.id = tl.task_id')
            ->where("tl.id = {$task_log_id}")
            ->field('t.*,tal.done_time')
            ->find();

        if($task_info['type'] == 1){

            // 记录任务，1.获取当前记录中已有的答案反馈
            $options = db('task_log')->where("id = {$task_log_id}")->value('options');
            if(empty($options)){
                // 说明首次提交答案反馈
                $options = [];
            }else{
                $options = unserialize($options);
                // 1.1 为了保险，此处需判断当前答案反馈提交时间是否在有效提交范围内
                // 1.1.1 获取上次提交时间，并计算本次应当提交的时间
                $next_step_time = end($options)['time'] + $task_info['inteval_time'] * 24*60*60;
                // 1.1.2 更新记录中的下次记录时间
                Db::name('task_log')->where("id = {$task_log_id}")->update(['next_record_time'=>$next_step_time]);
//                if(!$this->checkTimeBewteen($next_step_time)){
//                    return $this->returnJson([],'提交失败');
//                }
            }
            // 2.向当前答案反馈中添加提交时间，并加入总反馈中

            $answer['time'] = time();

            array_push($options,$answer);
            $update['options'] = serialize($options);
            // 3.需判断当前任务需提交反馈次数
            if(count($options) == $task_info['times']){
                $this->messageToClient('学习任务完成通知',date('m月d日',$task_info['done_time']).'发布的'.$task_info['name'].'学习记录任务已经完成，请继续加油！','',$this->u_id,2);
                // 4.更新任务完成时间
                $update['done_time'] = time();
                $update['state'] = $this->status['NORMAL'];
            }

            $res = Db::name('task_log')->where("id = {$task_log_id}")->update($update);
            if($res !== false){
                return $this->returnJson([],'提交成功',1);
            }
            return $this->returnJson([],'提交失败');
        }
        // 非记录题答案反馈
        $this->messageToClient('学习任务完成通知',date('m月d日',$task_info['done_time']).'发布的'.$task_info['name'].'学习任务已经完成，请继续加油！','',$this->u_id,2);
        $truth_num = $this->request->param('truth_num',0);
        $total_num = $this->request->param('total_num',0);
        $update['options'] = serialize($answer);
        $update['answer_file'] = serialize($answer_file);
        $update['done_time'] = time();
        $update['truth_num'] = $truth_num;
        if(intval($total_num) > 0){
            $update['total_num'] = $total_num;
        }
        $update['state'] = $this->status['NORMAL'];
        $res = Db::name('task_log')->where("id = {$task_log_id}")->update($update);
        if($res !== false){
            return $this->returnJson([],'提交成功',1);
        }
        return $this->returnJson([],'提交失败');
    }

    // 获取记录题数据
    public function record_data(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        $task_log_id = $this->request->param('id',0);
        $allot_task_id = $this->request->param('task_id',0); // 任务分配表id
        if(empty($task_log_id) && empty($allot_task_id)){
            return $this->returnJson([],'参数错误');
        }
        if(empty($task_log_id)){
            // 用记录id反查任务
            $task_log_info = Db::name('task_log')->where("u_id = {$this->u_id} and task_id = {$allot_task_id}")->find();
        }else{
            $task_log_info = Db::name('task_log')->where("id = {$task_log_id}")->find();
        }
        $options = $task_log_info['options'];
        if(empty($options)){
            return $this->returnJson([],'记录不存在');
        }
        // 获取记录任务名称
        $task_name = Db::name('task')->alias('t')->join('task_allot_log tal','t.id = tal.task_id')->where("tal.id = {$task_log_info['task_id']}")->value('name');
        $record_data = @unserialize($options);
        return $this->returnJson(compact('record_data','task_name'),'获取成功',1);
    }

    // 获取记录题数据 - 新
    public function record_data_new(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            return $error;
        }
        $task_log_id = $this->request->param('id',0);
        $allot_task_id = $this->request->param('task_id',0); // 任务分配表id
        if(empty($task_log_id) && empty($allot_task_id)){
            return $this->returnJson([],'参数错误');
        }
        if(empty($task_log_id)){
            // 用记录id反查任务
            $task_log_info = Db::name('task_log')->where("u_id = {$this->u_id} and task_id = {$allot_task_id}")->find();
        }else{
            $task_log_info = Db::name('task_log')->where("id = {$task_log_id}")->find();
        }
        $options = $task_log_info['options'];
        if(empty($options)){
            return $this->returnJson([],'记录不存在');
        }
        // 获取记录任务名称
        $task_name = db('task')->alias('t')->join('task_allot_log tal','t.id = tal.task_id')->where("tal.id = {$task_log_info['task_id']}")->value('name');
        $record_data = @unserialize($options);
        $categories = [];
        // 获取data_x,data_y的值
        $taskInfo = Db::name('question')
            ->alias('q')
            ->join("task_question_log tql","tql.question_id = q.id")
            ->join("task t","t.id = tql.task_id")
            ->join("task_allot_log tal","tal.task_id = tql.task_id")
            ->where("tal.id = {$task_log_info['task_id']} and q.type = 2")
            ->field("tal.id,t.name,q.ext")
            ->find();
        if(empty($taskInfo)){
            return $this->returnJson([],'记录不存在');
        }
        $ext = unserialize($taskInfo['ext']);
        $data_x_list = array_column($record_data,'data_x');
        $data_y_list = array_column($record_data,'data_y');
        $categories_list = array_column($record_data,'time');
        foreach ($categories_list as $item){
            array_push($categories,date('m月d日',$item));
        }
        $series = [
            [
                'name' => $ext['data_x'],
                'data' => $data_x_list
            ],
            [
                'name' => $ext['data_y'],
                'data' => $data_y_list
            ]
        ];
        $carouselList = [];
        $carouselList[] = [
            'name' => $task_name,
            'categories' => $categories,
            'series' => $series
        ];
        return $this->returnJson(compact('carouselList'),'获取成功',1);
    }

    // 图片上传
    public function img_upload(){
        list($isLogin,$error) = $this->checkLogin();
        if(!$isLogin){
            // return $error;
        }
        $files = request()->file('file');
        $path = Env::get('root_path') . DIRECTORY_SEPARATOR. 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'record' . DIRECTORY_SEPARATOR . md5($this->openid);
        try {
            //code... 
            $info = $files->move($path);
        } catch (\Throwable $th) {
            throw $th;
        }
       
        if($info){
            $filePath = $info->getSaveName();
            $fileType = explode("/",$info->getInfo("type"))[0];
            $filePathA = explode("/",$filePath);
            $newFile= md5($filePathA[1]);
            $filePath = "$filePathA[0]/$newFile.mp4";
            $oldPath = $info->getRealPath();
            $newPath = '/var/www/html/public/upload/record/'.md5($this->openid).'/'.$filePath;
            if($fileType==="video"){
                exec("ffmpeg -i $oldPath  -c:v h264 -c:a aac $newPath");
            }
            printLog($info->getRealPath());
            $object = 'upload/record/'.md5($this->openid).'/' . str_replace('\\', '/', $filePath);
            if(config('sys_data.upload_mode') == 1){
                if(config('sys_data.accessKeyId') && config('sys_data.accessKeySecret') && config('sys_data.endpoint')){
                    $bucket = config('sys_data.bucket');
                    $filePath = $path . DIRECTORY_SEPARATOR .$filePath;
                    try{
                        $this->uploadFile($bucket, $object, $filePath);
                        $filePath = 'http://'.$bucket.'.'.config('sys_data.endpoint').DIRECTORY_SEPARATOR.$object ;
                        @unlink($object);
                        return $filePath;
                    }catch (OssException $ossException){
                        return json(['msg'=>'上传失败'],501);
                    }
                }
            }
            return DIRECTORY_SEPARATOR.$object;
        }else{
            // var_dump($info ,$files, $info = $files->move($path),error_get_last());
            return json(['msg'=>'上传失败'],500);
        }
    }

    /**
     * 检查指定时间戳是否在指定时间范围内
     * @param string $time
     * @param array  $bewteen
     * @return bool
     */
    private function checkTimeBewteen($time = '',$bewteen = []){
        if(empty($bewteen)){
            // 默认检查时间戳是否在今天范围内
            list($start_time,$end_time) = getSomeTime();
        }else{
            list($start_time,$end_time) = [strtotime(current($bewteen),strtotime(end($bewteen)))];
        }
        return ($time >= $start_time && $time <= $end_time);
    }

    /**
     * 获取当前用户所属班组
     * @return array|string
     */
    private function getUserClassIds(){
        $class_list = Db::name('class_group')
            ->alias('cg')
            ->join('user_class_log ucl','cg.id = ucl.class_group_id')
            ->where("ucl.u_id = {$this->u_id} and cg.state = {$this->status['NORMAL']}")
            ->field('cg.id,cg.name')
            ->select();
        if(empty($class_list)){
            return '';
        }
        return array_column($class_list,'id');
    }

    /**
     * 获取指定时间段，指定班组的任务
     * @param array $bewteen_time
     * @param int   $class_id
     * @param array $where
     * @return array|\PDOStatement|string|\think\Collection
     */
    private function getItemTask($bewteen_time = [],$class_id = 0,$where = [],$is_today = false){
        if(empty($class_id)){
            return [];
        }
        list($start_time,$end_time) = $bewteen_time;
        // 1.获取日期范围内非记录题记录
        if($is_today){
            $list =  Db::name('task_allot_log')
                ->alias('tal')
                ->join('task t',"t.id = tal.task_id and t.state = {$this->status['NORMAL']} and t.type = 0")
                ->join("task_log tl","tal.id = tl.task_id and tl.u_id = {$this->u_id}",'left')
                ->join("task_index ti",'ti.task_id = t.id','left')
                ->where("tal.type = 1 and tal.class_or_group_id = {$class_id}")
                ->where("tal.done_time >= {$start_time} and tal.done_time <= {$end_time}")
                ->where($where)
                ->field("DISTINCT tal.id,t.type,t.name,t.image,t.desc,if(tl.done_time is null or tl.done_time = 0,0,1) do_type,FROM_UNIXTIME(t.create_time) create_time,FROM_UNIXTIME(tal.done_time,'%Y-%m-%d') done_time,tal.done_time done_time1,ti.level,ti.subject_id,ti.version_id")
                ->order("done_time")
                ->select();
        }else{
            $list =  Db::name('task_allot_log')
                ->alias('tal')
                ->join('task t',"t.id = tal.task_id and t.state = {$this->status['NORMAL']} and t.type = 0")
                ->join("task_log tl","tal.id = tl.task_id and tl.u_id = {$this->u_id}",'left')
                ->join("task_index ti",'ti.task_id = t.id','left')
                ->where("tal.type = 1 and tal.class_or_group_id = {$class_id}")
                ->where("tal.done_time >= 0 and tal.done_time <= {$end_time}")
                ->where($where)
                ->field("DISTINCT tal.id,t.type,t.name,t.image,t.desc,if(tl.done_time is null or tl.done_time = 0,0,1) do_type,FROM_UNIXTIME(t.create_time) create_time,FROM_UNIXTIME(tal.done_time,'%Y-%m-%d') done_time,tal.done_time done_time1,ti.level,ti.subject_id,ti.version_id")
                ->order("id desc")
                ->select();
        }
        $joinClassTime = $this->joinClassTime($class_id);
        foreach ($list as $k => $val){
            $ctime = date('Y-m-d',$start_time);
            // $ctime = date('Y-m-d',time());
            if($val['do_type'] == 0){
                if(strtotime($ctime) > strtotime($val['done_time'])){
                    $list[$k]['do_status'] = 2;
                }elseif(strtotime($ctime) == strtotime($val['done_time'])){
                    $list[$k]['do_status'] = 3;
                    if(date('Y-m-d') == $ctime){
                        $list[$k]['do_status'] = 0;
                    }
                }else{
                    $list[$k]['do_status'] = 3;
                }
            }else{
                $list[$k]['do_status'] = 1;
            }
            // todo::新增列表等级,缩率图,学科
            $list[$k]['image'] = config('sys_data.web_site').$list[$k]['image'];
            $list[$k]['subject_name'] = db('subject')->where('id',$val['subject_id'])->value('name');
            $list[$k]['version_name'] = db('subject_version')->where('id',$val['version_id'])->value('name');
            if($val['level']==1){
                $list[$k]['level_name'] = 'A';
            }else if($val['level']==2){
                $list[$k]['level_name'] = 'B';
            }else if($val['level']==3){
                $list[$k]['level_name'] = 'C';
            }else{
                $list[$k]['level_name'] = '无';
            }

            // todo::处理新增用户显示创建前任务问题
            if($val['done_time1'] < $joinClassTime){
                unset($list[$k]);
            }
        }
        // 2.获取日期范围内的记录题记录
        $task_list =  Db::name('task_allot_log')
            ->alias('tal')
            ->join('task t',"t.id = tal.task_id and t.state = {$this->status['NORMAL']} and t.type = 1")
            ->join("task_log tl","tal.id = tl.task_id and tl.u_id = {$this->u_id}",'left')
            ->join("task_index ti",'ti.task_id = tal.task_id','left')
            ->where("tal.type = 1 and tal.class_or_group_id = {$class_id}")
            ->where($where)
            ->field("DISTINCT tal.id,t.`name`,t.`image`,t.`desc`,t.type,t.times,t.inteval_time,if(tl.done_time is null or tl.done_time = 0,0,1) do_type,FROM_UNIXTIME(t.create_time) create_time,FROM_UNIXTIME(tal.done_time,'%Y-%m-%d') done_time,tal.done_time done_time1,if(tl.`options` is null or tl.`options` = '','',tl.`options`) `options`,if(tl.next_record_time is null or tl.next_record_time = 0,'',tl.next_record_time) next_record_time,ti.level,ti.subject_id,ti.version_id")
            ->select();
        foreach ($task_list as $k => $item){
            $days = $this->record_all_days($item['done_time1'],$item['times'],$item['inteval_time']);
            // todo::处理新增用户显示创建前任务问题
            if($item['done_time1'] < $joinClassTime){
                continue;
            }
            $ctime = date('Y-m-d',$start_time);
            //$ctime = date('Y-m-d',time());
            if(in_array($start_time,$days)){
                $options = empty($item['options'])?[]:unserialize($item['options']);
                $done_options_times = array_column($options,'time');
                $format_times = $this->timeToFormatTime($done_options_times);
                if(!empty($where)){
                    // 未完成状态
                    if(in_array($start_time,$format_times)){
                        continue;
                    }
                }
                $item['do_status'] = $this->getDoStatus($item,$start_time,$days);
                // todo::新增列表等级,缩率图,学科
                $item['image'] = config('sys_data.web_site').$item['image'];
                $item['subject_name'] = db('subject')->where('id',$item['subject_id'])->value('name');
                $item['version_name'] = db('subject_version')->where('id',$item['version_id'])->value('name');
                if($item['level']==1){
                    $item['level_name'] = 'A';
                }else if($item['level']==2){
                    $item['level_name'] = 'B';
                }else if($item['level']==3){
                    $item['level_name'] = 'C';
                }else{
                    $item['level_name'] = '无';
                }
                // todo::$is_today 为true 时，剔除已逾期任务
                if($is_today && $item['do_status'] == 2){
                    continue;
                }
                unset($item['done_time1'],$item['times'],$item['inteval_time'],$item['options']);
                array_push($list,$item);
            }

        }
        return $list;
    }

    /**
     * 批量格式化时间戳
     * @param array $times
     * @return array
     */
    private function timeToFormatTime($times = []){
        $format = [];
        foreach ($times as $item){
            $format[] = strtotime(date('Y-m-d 00:00:00',$item));
        }
        return $format;
    }

    /**
     * 获取答题状态
     * @param $item
     * @param $start_time
     * @param $days
     * @return int
     */
    private function getDoStatus($item,$start_time,$days){
        $options = empty($item['options'])?[]:unserialize($item['options']);
        if(empty($options)){
            // 为空，说明没有任何答题记录
            // 获取范围日期在可记录日期的索引
            $key_index = array_search($start_time,$days);
            if($key_index === 0 && strtotime(date('Y-m-d 00:00:00')) == $start_time){
                // 若是第一天
                $do_status = 0; // 未完成
            }else{
                // 若是其他天
                // 判断当前日期time()是否小于范围日期$start_time
                if($key_index > 0 && strtotime(date('Y-m-d 00:00:00')) <= $start_time){
                    $do_status = 3; // 未开始
                }else{
                    $do_status = 2; // 已逾期
                }
            }
        }else{
            // 不为空，说明已有答题记录
            // 获取已答题的所有答题日期
            $times = $this->timeToFormatTime(array_column($options,'time'));
            if(in_array($start_time,$times)){
                // 范围日期在答题记录日期内
                $do_status = 1; // 已完成
            }else{
                // 范围日期不在答题记录日期内
                // 判断下次记录日期是否为范围日期
                $next_record_time = strtotime(date('Y-m-d 00:00:00',(int)$item['next_record_time']));
                $key_index = array_search($start_time,$days);
                if(strtotime(date('Y-m-d 00:00:00')) == $start_time){
                    // 判断当前范围日期是否等于下次记录日期
                    if($key_index === 0 && $next_record_time <= $start_time){
                        // 是，说明当前范围日期为下次记录日期
                        $do_status = 0; // 未完成
                    }else{
                        // 若是其他天
                        // 判断当前日期time()是否小于范围日期$start_time
                        if($key_index > 0 && $next_record_time <= $start_time){
                            $do_status = 0; // 未完成
                        }else{
                            $do_status = 2; // 已逾期
                        }
                    }
                }elseif(strtotime(date('Y-m-d 00:00:00')) < $start_time){
                    if($key_index === 0 && $next_record_time <= $start_time){
                        // 是，说明当前范围日期为下次记录日期
                        $do_status = 0; // 未完成
                    }else{
                        $do_status = 3; // 未开始
                    }
                }else{
                    $do_status = 2; // 已逾期
                }

            }
        }
        return $do_status;
    }

    /**
     * 获取指定时间段，所有班组内的全部任务
     * @param array $bewteen_time
     * @param array $class_ids
     * @param array $where
     * @return array|\PDOStatement|string|\think\Collection
     */
    private function getAllTask($bewteen_time = [],$class_ids = [],$where = [],$is_today = false){
        if(empty($class_ids)){
            return [];
        }
        list($start_time,$end_time) = $bewteen_time;
        $class_ids = implode(',',$class_ids);
        // 1.获取日期范围内非记录题记录
        $list =  Db::name('task_allot_log')
            ->alias('tal')
            ->join('task t',"t.id = tal.task_id and t.state = {$this->status['NORMAL']} and t.type = 0")
            ->join("task_log tl","tal.id = tl.task_id and tl.u_id = {$this->u_id}",'left')
            ->join("task_index ti",'ti.task_id = t.id','left')
            ->join("subject su",'su.id = ti.subject_id','left')
            ->where("tal.type = 1 and tal.class_or_group_id in ({$class_ids})")
            ->where("tal.done_time >= {$start_time} and tal.done_time <= {$end_time}")
            ->where($where)
            ->field("DISTINCT tal.id,t.`name`,su.`name` subject_name,ti.`level`,t.`image`,t.`desc`,t.type,if(tl.done_time is null or tl.done_time = 0,0,1) do_type,FROM_UNIXTIME(t.create_time) create_time,FROM_UNIXTIME(tal.done_time,'%Y-%m-%d') done_time,tal.done_time done_time1,tal.class_or_group_id")
            ->select();
        foreach ($list as $k => $val){
            //$ctime = date('Y-m-d',$start_time);
            $ctime = date('Y-m-d',time());
            if($val['do_type'] == 0){
                if(strtotime($ctime) > strtotime($val['done_time'])){
                    $list[$k]['do_status'] = 2;
                }elseif(strtotime($ctime) == strtotime($val['done_time'])){
                    $list[$k]['do_status'] = 3;
                    if(date('Y-m-d') == $ctime){
                        $list[$k]['do_status'] = 0;
                    }
                }else{
                    $list[$k]['do_status'] = 3;
                }
            }else{
                $list[$k]['do_status'] = 1;
            }
            // todo::处理新增用户显示创建前任务问题
            if($val['done_time1'] < $this->joinClassTime($val['class_or_group_id'])){
                unset($list[$k]);
            }
            // todo::新增列表等级,缩率图,学科
            $list[$k]['image'] = config('sys_data.web_site').$val['image'];
            if($val['level']==1){
                $list[$k]['level_name'] = 'A';
            }else if($val['level']==2){
                $list[$k]['level_name'] = 'B';
            }else if($val['level']==3){
                $list[$k]['level_name'] = 'C';
            }else{
                $list[$k]['level_name'] = '无';
            }
        }
        // 2.获取日期范围内的记录题记录
        $task_list =  Db::name('task_allot_log')
            ->alias('tal')
            ->join('task t',"t.id = tal.task_id and t.state = {$this->status['NORMAL']} and t.type = 1")
            ->join("task_log tl","tal.id = tl.task_id and tl.u_id = {$this->u_id}",'left')
            ->join("task_index ti",'ti.task_id = t.id','left')
            ->join("subject su",'su.id = ti.subject_id','left')
            ->where("tal.type = 1 and tal.class_or_group_id in ({$class_ids})")
            ->where($where)
            ->field("DISTINCT tal.id,t.`name`,su.`name` subject_name,ti.`level`,t.`image`,t.`desc`,t.type,t.times,t.inteval_time,if(tl.done_time is null or tl.done_time = 0,0,1) do_type,FROM_UNIXTIME(t.create_time) create_time,FROM_UNIXTIME(tal.done_time,'%Y-%m-%d') done_time,tal.done_time done_time1,if(tl.`options` is null or tl.`options` = '','',tl.`options`) `options`,if(tl.next_record_time is null or tl.next_record_time = 0,'',tl.next_record_time) next_record_time,tal.class_or_group_id")
            ->select();
        foreach ($task_list as $item){
            $days = $this->record_all_days($item['done_time1'],$item['times'],$item['inteval_time']);
            // todo::处理新增用户显示创建前任务问题
            if($item['done_time1'] < $this->joinClassTime($item['class_or_group_id'])){
                continue;
            }
            $ctime = date('Y-m-d',$start_time);
            unset($item['done_time1'],$item['times'],$item['inteval_time']);
            if(in_array($start_time,$days)){
                $options = empty($item['options'])?[]:unserialize($item['options']);
                $done_options_times = array_column($options,'time');
                $format_times = $this->timeToFormatTime($done_options_times);
                if(!empty($where)){
                    // 未完成状态
                    if(in_array($start_time,$format_times)){
                        continue;
                    }
                }
                $item['do_status'] = $this->getDoStatus($item,$start_time,$days);
                // todo::新增列表等级,缩率图,学科
                $item['image'] = config('sys_data.web_site').$item['image'];
                if($item['level']==1){
                    $item['level_name'] = 'A';
                }else if($item['level']==2){
                    $item['level_name'] = 'B';
                }else if($item['level']==3){
                    $item['level_name'] = 'C';
                }else{
                    $item['level_name'] = '无';
                }
                // todo::$is_today 为true 时，剔除已逾期任务
                if($is_today && $item['do_status'] == 2){
                    continue;
                }
                unset($item['options']);
                array_push($list,$item);
            }
        }
        return $list;
    }

    /**
     * 获取用户进组时间
     * @param int $class_id
     * @return int|mixed
     */
    private function joinClassTime($class_id = 0){
        if(empty($class_id)){
            return 0;
        }
        $time = Db::name('user_class_log')->where("u_id = {$this->u_id} and class_group_id = {$class_id}")->value('create_time');
        return intval($time) + 86400; // todo::这样处理，为了解决当天当天任务分配当天入组问题
    }

    /**
     * 获取记录题可以记录的日期组
     * @param int $done_time
     * @param int $times
     * @param int $inteval_time
     * @return array
     */
    private function record_all_days($done_time = 0,$times = 1,$inteval_time = 0){
        $days = [];
        $start_day = $done_time;
        for ($i = 0;$i < $times;$i ++){
            array_push($days,$start_day);
            $start_day += ($inteval_time + 1) * 86400;
        }
        return $days;
    }

    /**
     * 检查用户是否登录
     * @return array
     */
    private function checkLogin(){
        if(intval($this->u_id) <= 0){
            return [false,$this->returnJson([],'用户不存在')];
        }
        return [true,[]];
    }
}
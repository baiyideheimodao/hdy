<?php
    /**
     *  计划任务
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Cron.php
     *  Create Date : 2022/1/18 21:17
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\api\controller;
    use app\common\controller\BaseController;

    class Cron extends BaseController {

        // 当天任务提醒
        public function issueTask(){
            // 获取执行日期为今天的所有任务
            $now_date = strtotime(date('Y-m-d'));
            $task_list = db('task')
                ->alias('t')
                ->join("task_allot_log tal",'tal.type = 1 and tal.task_id = t.id')
                ->where("t.state = {$this->status['NORMAL']} and tal.done_time = {$now_date}")
                ->field('t.id,t.name')
                ->select();
            foreach ($task_list as $key => $task_info){
                // 获取当前任务下的所有学员id
                $users = db('user')
                    ->alias('u')
                    ->join('user_class_log ucl','ucl.u_id = u.id')
                    ->join('task_allot_log tal','tal.type = 1 and tal.class_or_group_id = ucl.class_group_id')
                    ->where("tal.task_id = {$task_info['id']}")
                    ->field('DISTINCT u.id')
                    ->select();
                if(empty($users)){
                    continue;
                }
                $this->messageToUids(config('message.user_task_tip'),"你的任务【{$task_info['name']}】即将开始！",array_column($users,'id'),1);
            }
        }

        // 记录任务提醒
        public function issueRecordTask(){
            // 获取执行日期为今天的已下发的记录任务
            list($start_date,$end_date) = getSomeTime();
            $user_tasks = db('task_log')
                ->alias('tl')
                ->join('task_allot_log tal','tl.task_id = tal.id')
                ->join('task t','tal.task_id = t.id')
                ->where('t.type = 1 and tl.done_time = 0')
                ->where("tl.next_record_time >= {$start_date} and tl.next_record_time <= {$end_date}")
                ->field('DISTINCT tl.u_id,t.name')
                ->select();
            foreach ($user_tasks as $key => $item){
                if(empty($item['name'])){
                    continue;
                }
                $this->messageToClient(config('message.user_task_tip'),"你的记录任务【{$item['name']}】要记录了哦！",$item['u_id'],1);
            }
        }
    }
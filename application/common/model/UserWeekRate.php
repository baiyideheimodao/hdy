<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : UserWeekRate.php
     *  Create Date : 2022/1/22 18:12
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\common\model;
    use think\Db;
    use think\Model;

    class UserWeekRate extends Model {
        protected $name = 'user_week_rate';

        public function addOrUpt($u_id = 0){
            // 获取当前用户所在群组
            $group_group_ids = Db::name('user_group_log')->where("u_id = {$u_id}")->field('group_group_id')->select();
            if(!empty($group_group_ids)){
                $group_group_ids = array_column($group_group_ids,'group_group_id');
                $group_group_ids = implode(',',$group_group_ids);
                // 获取当前所有群组下所有用户
                $users = Db::name('user_group_log')->where("group_group_id in ({$group_group_ids})")->field('DISTINCT u_id')->select();
                // 逐个计算组内用户本周内的任务完成率情况
                $weeks = get_week();
                $week_start_date = strtotime(current($weeks)['date'].' 00:00:00');
                $week_end_date = strtotime(end($weeks)['date'].' 23:59:59');
                foreach ($users as $key => $item){
                    self::dealDone($week_start_date,$week_end_date,$u_id);
                }
            }
        }

        /**
         * 处理完成率
         * @param $start_time
         * @param $end_time
         * @param $u_id
         * @return bool
         */
        static function dealDone($start_time,$end_time,$u_id){
            // 1.获取总任务数
            $total_count = Db::name('task_allot_log')
                ->alias('tal')
                ->join('user_class_log ucl','ucl.class_group_id = tal.class_or_group_id')
                ->where("tal.done_time >= {$start_time} and tal.done_time <= {$end_time} and ucl.u_id = {$u_id} and tal.type = 1")
                // todo::当前用户的进班组时间需早于任务的分配日期
                ->where("tal.done_time > ucl.create_time")
                ->field('DISTINCT tal.id')
                ->count();
            // 2.获取已完成任务数
            $truth_count = Db::name('task_allot_log')
                ->alias('tal')
                ->join('user_class_log ucl','ucl.class_group_id = tal.class_or_group_id')
                ->join('task_log tl','tl.task_id = tal.task_id')
                ->where("tal.done_time >= {$start_time} and tal.done_time <= {$end_time} and ucl.u_id = {$u_id} and tl.done_time > 0 and tal.type = 1")
                // todo::当前用户的进班组时间需早于任务的分配日期
                ->where("tal.done_time > ucl.create_time")
                ->field('DISTINCT tal.id')
                ->count();
            $rate = intval($total_count) <= 0 ? 0 : ($truth_count/$total_count);

            $insertDate = [
                'u_id' => $u_id,
                'week_start_date' => $start_time,
                'week_end_date' => $end_time
            ];

            $info = Db::name('user_week_rate')->where($insertDate)->find();
            if(empty($info)){
                $insertDate = [
                    'u_id' => $u_id,
                    'week_start_date' => $start_time,
                    'week_end_date' => $end_time,
                    'done_num' => $truth_count,
                    'total_num' => $total_count,
                    'rate' => floatval($rate),
                    'create_time' => time(),
                    'update_time' => time()
                ];
                Db::name('user_week_rate')->insertGetId($insertDate);
                return true;
            }
            $insertDate = [
                'done_num' => $truth_count,
                'total_num' => $total_count,
                'rate' => floatval($rate),
                'update_time' => time()
            ];
            Db::name('user_week_rate')->where("id = {$info['id']}")->update($insertDate);
            return true;
        }

    }
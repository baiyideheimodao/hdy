<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/11/19
 * Time: 9:19
 */

namespace app\common\controller;
use think\Controller;
use think\facade\Cache;
use think\facade\Cookie;

class AdminController extends BaseController
{
    protected $base_url = '';
    protected $page = 1;
    protected $limit = 20;
    protected $noLoginList = [

    ];
    // 无需验证的action
    protected $noCheckAction = [
      'index'
    ];

    protected function initialize()
    {
        parent::initialize();
        $this->checkList();
        $this->checkDatas();
        $this->permBtns();
        //如果该用户没用此按钮权限则将按钮隐藏掉
        $param = $this->request->param();
        $this->base_url = base_url();

        //分页
        if(isset($param['page']) && intval($param['page']) > 0){
            $this->page=$param['page'];
        }
        if(isset($param['limit']) && intval($param['limit']) > 0){
            $this->limit=$param['limit'];
        }
    }
    protected function checkList(){
        if($this->isLogin() && !in_array($this->request->action(),$this->noLoginList)){

        }
    }

    protected function isLogin(){
        if(!Cookie::has('name')){
            $this->redirect('/admin/login');
            // header("Location:'/admin/login'");
           return false;
        }
        return true;
    }

    /**
     * 统一回参-- 此处用于表格渲染
     * @param array  $data
     * @param string $message
     * @param int    $total
     * @param int    $code
     * @return \think\response\Json
     */
    public function returnTabelJson($data = [],$message = '',$total = 0,$code = 0){
        $re = ['code' => $code, 'msg' => $message, 'count' => $total, 'data' => $data];
        return json($re);
    }

    /**
     * 获取菜单列表
     * @return array
     */
    public function getMenus()
    {
        $where = ['type' => 'nav', 'status' => 1];
        $access = db('auth_group')->where('id = '.Cookie::get('group_id'))->find();
        $list = db('auth_rule')->where("id in ({$access['rules']})")->where($where)->order('sort_order asc')->select();
        $tree = list_to_tree($list);
        return $tree;
    }

    /**
     * 批量检查指定字段格式
     * @return \think\response\Json
     */
    public function checkDatas(){
        if(!in_array(request()->action(),$this->noCheckAction)){
            $phone = $this->request->post('phone','');
            $email = $this->request->post('email','');
            $sfz = $this->request->post('sfz','');

//            if(strlen($phone) > 0 && !is_phone($phone) && !is_tel($phone)){
//                die(json_encode(['code'=>0,'message'=>'【手机号】格式错误']));
//            }
//            if(strlen($email) > 0 && !is_email($email)){
//                die(json_encode(['code'=>0,'message'=>'【邮箱】格式错误']));
//            }
//            if(strlen($sfz) > 0 && !is_idCard($sfz)){
//                die(json_encode(['code'=>0,'message'=>'【身份证】格式错误']));
//            }
        }
    }

    /**
     * 检查指定任务是否进行
     * @param int $task_id
     * @param int $question_id
     * @return bool
     */
    public function checkTaskIsPross($task_id = 0,$question_id = 0){
        if(!empty($question_id)){
            $task_id = db('task_question_log')->where('question_id',$question_id)->value('task_id');
        }
        if(empty($task_id)){
            return false;
        }
        $status = db('type_task_table')->where("task_id = {$task_id} or tasked_id = {$task_id}")->value('status');
        if($status == self::STATE_OPEN){
            return true;
        }
        return false;
    }

    /**
     * 插入记录
     * @param string $name 模型
     * @param array $data 数据
     * @param bool $rule 是否开启认证
     * @param string|array $field 允许写入的字段 如果为true只允许写入数据表字段
     * @param string $key 获取模型的主键，默认是id
     * @return bool|mixed
     */
    public function insert($name, $data, $rule = true, $field = true, $key = 'id')
    {
        try {
            $this->model = model($name);
            if ($this->model->allowField($field)->save($data)) {
                $this->insertId = $this->model->$key;
                return true;
            } else {
                return $this->errorMsg = $this->model->getError();
            }
        } catch (\Exception $e) {
            return $this->errorMsg = config('app_debug') ? $e->getMessage() : '请求错误';
        }
    }

    /**
     * 更新记录
     * @param string $name 模型
     * @param array $data 数据
     * @param bool $rule 是否开启认证
     * @param string|array $field 允许写入的字段 如果为true只允许写入数据表字段
     * @param string $key 更新条件字段 多个用逗号隔开
     * @return bool|mixed
     */
    public function update($name, $data, $rule = true, $field = true, $key = 'id')
    {
        try {
            $where = [];
            foreach (explode(',', $key) as $v) {
                $where[$v] = $data[$key];
            }
            $this->model = model($name);
            if ($this->model->allowField($field)->save($data, $where)) {
                return true;
            } else {
                return $this->errorMsg = $this->model->getError();
            }
        } catch (\Exception $e) {
            return $this->errorMsg = config('app_debug') ? $e->getMessage() : '请求错误';
        }
    }

    /**
     * 删除记录
     * @param string $name 模型
     * @param array $data 数据
     * @param string $key 主键
     * @return bool|mixed
     */
    public function remove($name, $data, $key = 'id')
    {
        try {
            $this->model = model($name);
            if ($this->model->where($key, 'in', $data[$key])->delete()) {
                return true;
            } else {
                return $this->errorMsg = '删除失败';
            }
        } catch (\Exception $e) {
            return $this->errorMsg = config('app_debug') ? $e->getMessage() : '请求错误';
        }
    }

    /**
     * 保存多个数据到当前数据对象
     * @param string $name 模型
     * @param array $data 数据
     * @param bool $rule 是否开启认证
     * @param string|array $field 允许写入的字段 如果为true只允许写入数据表字段
     * @return bool|mixed
     */
    protected function saveAll($name, $data, $rule = true, $field = true)
    {
        try {
            $this->model = model($name);
            if ($this->model->allowField($field)->saveAll($data)) {
                return true;
            } else {
                return $this->errorMsg = $this->model->getError();
            }
        } catch (\Exception $e) {
            return $this->errorMsg = config('app_debug') ? $e->getMessage() : '请求错误';
        }
    }

    // 按钮权限管理
    private function permBtns(){
        if(Cookie::has('name')){
            // 获取用户当前页面的按钮权限
            $topBtns = []; // 顶部按钮
            $rightBtns = []; // 右部按钮
            $searchBtns = []; // 搜索模块
            $group_id = Cookie::get('group_id');
            $rules = db('auth_group')->where("id = {$group_id}")->value('rules');
            $rule_ids = array_filter(explode(',',$rules));
            $rule_ids = implode(',',$rule_ids);
            $urlNode = '/' . trim(request()->module() . '/' . request()->controller() . '/' . request()->action());
            $top_info = db('auth_rule')->where("url = '{$urlNode}'")->find();
            if(!empty($top_info)){
                $rule_list = db('auth_rule')->where("pid = {$top_info['id']} and id in ({$rule_ids}) and type = 'auth' and perm != ''")->order('sort_order')->select();
                foreach ($rule_list as $key => $item){
                    if($item['perm'] == 'top'){
                        array_push($topBtns,$item);
                    }elseif ($item['perm'] == 'right'){
                        array_push($rightBtns,$item);
                    }elseif ($item['perm'] == 'search'){
                        array_push($searchBtns,$item);
                    }
                }
                $this->assign('pageName',$top_info['name']);
                $this->assign('permBtns',compact('topBtns','rightBtns','searchBtns'));
            }
        }
    }

}
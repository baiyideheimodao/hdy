<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Classorgroup.php
     *  Create Date : 2021/12/31 11:20
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\controller;
    use app\common\controller\AdminController;
    use \think\facade\Cookie;
    use think\facade\Env;

    class Classorgroup extends AdminController {

        private $webUrl = '';

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
            $this->webUrl = config('sys_data.web_site');
            // printLog($this->webUrl);
            $this->webUrl = "https://ht.hld.cool/";
        }

        public function class_index(){
            $where = [];
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $admin_id = Cookie::get('id');
                if((int)cookie('group_id') !== config('rx.groupId')){
                    $where[] = ['n.admin_id','=',$admin_id];
                }
                $area_id = $this->request->param('area_id','');
                if(!empty($area_id)){
                    $where[] = ['n.area_id','=',$area_id];
                }
                $school_id = $this->request->param('school_id','');
                if(!empty($school_id)){
                    $where[] = ['n.school_id','=',$school_id];
                }
                $grade_id = $this->request->param('grade_id','');
                if(!empty($grade_id)){
                    $where[] = ['n.grade_id','=',$grade_id];
                }
                $subSql = db('type_task_log_table')
                    ->alias('ttlt')
                    ->join('type_task_table ttt','ttt.id = ttlt.type_task_id')
                    ->field('ttlt.*')
                    ->select(false);
                $list = db('class_group')
                    ->alias('n')
                    ->join("user_class_log ucl","ucl.class_group_id = n.id",'left')
                    ->join("({$subSql}) tal","tal.class_id = n.id and tal.class_id > 0",'left')
                    ->join("area ar",'ar.id = n.area_id','left')
                    ->join("school sch",'sch.id = n.school_id','left')
                    ->join("grade gra",'gra.id = n.grade_id','left')
                    ->where($where)
                    ->field("n.id,n.name,ar.name area_name,sch.name school_name,gra.name grade_name,FROM_UNIXTIME(n.create_time) create_time,n.state,count(DISTINCT ucl.id) user_num,count(DISTINCT tal.id) task_num")
                    ->limit(($page-1)*$limit,$limit)
                    ->group("n.id")
                    ->select();
                $total = db('class_group')
                    ->alias('n')
                    ->join("area ar",'ar.id = n.area_id','left')
                    ->join("school sch",'sch.id = n.school_id','left')
                    ->join("grade gra",'gra.id = n.grade_id','left')
                    ->where($where)
                    ->field("n.id")
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            $area['province'] = action('admin/school/addressList',['id'=>0,'type'=>1]);
            $area['city'] = [];
            $area['area'] = [];
            $school = db('school')->where("state = 1")->select();
            $grade = db('grade')->where("state=1")->select();
            return $this->fetch('class_index',compact('area','school','grade'));
        }

        public function class_add(){
            if($this->request->isPost()){
                $data = [];
                $name = $this->request->param('name','');

                if(empty($name)){
                    return $this->returnJson([],'创建班组失败：名称不能为空');
                }
                $data['name'] = $name;
                $data['create_time'] = time();
                $data['update_time'] = time();
                $data['admin_id'] = Cookie::get('id');
                $data['province_id'] = $this->request->param('province_id','');
                $data['city_id'] = $this->request->param('city_id','');
                $data['area_id'] = $this->request->param('area_id','');
                $data['school_id'] = $this->request->param('school_id','');
                $data['grade_id'] = $this->request->param('grade_id','');
                $id = db('class_group')->insertGetId($data);
                if($id > 0){
                    insert_admin_log('创建班组成功');
                    return $this->returnJson(['id'=>$id],'创建班组成功',1);
                }
                return $this->returnJson([],'创建班组失败');
            }
            $area['province'] = action('admin/school/addressList',['id'=>0,'type'=>1]);
            $area['city'] = [];
            $area['area'] = [];
            $school = db('school')->where("state = 1")->select();
            $grade = db('grade')->where("state=1")->select();
            return $this->fetch('class_save',compact('area','school','grade'));
        }

        //批量创建
        public function class_padd(){
            if($this->request->isPost()){
                $data = [];
                $rData = $this->request->param();
                $res_area = db('school')->field('province_id,city_id')->where('id',$rData['school_id'])->find();
                $create_time = time();
                $update_time = time();
                $admin_id = Cookie::get('id');
                for($i = 0 ; $i < $rData['number'] ; $i ++){
                    $num = $i+1;
                    $data[] = [
                        'name'=>$rData['grade_name'].'<'.$num.'>班',
                        'admin_id'=>$admin_id,
                        'create_time'=>$create_time,
                        'state'=>1,
                        'update_time'=>$update_time,
                        'province_id'=>$res_area['province_id'],
                        'city_id'=>$res_area['city_id'],
                        'area_id'=>$rData['area_id'],
                        'school_id'=>$rData['school_id'],
                        'grade_id'=>$rData['grade_id']
                    ];
                }

                $id = db('class_group')->insertall($data);
                if($id > 0){
                    insert_admin_log('批量创建班组成功');
                    return $this->returnJson(['id'=>$id],'批量创建班组成功',1);
                }
                return $this->returnJson([],'批量创建班组失败');
            }
            $grade = db('grade')->where("state=1")->select();
            return $this->fetch('class_psave',compact('grade'));
        }

        public function class_edit(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $data = $this->request->param();
                $update = [];
                if(isset($data['name'])){
                    if(empty($data['name'])){
                        return $this->returnJson([],'更新班组失败：名称不能为空');
                    }
                    $update['name'] = $data['name'];
                }
                if(isset($data['state'])){
                    $update['state'] = $data['state'];
                }
                $update['area_id'] = $data['area_id'];
                $update['school_id'] = $data['school_id'];
                $update['grade_id'] = $data['grade_id'];
                $update['update_time'] = time();
                $res = db("class_group")->where("id = {$id}")->update($update);
                if($res !== false){
                    insert_admin_log('更新班组成功');
                    return $this->returnJson(['id'=>$id],'更新班组成功',1);
                }
                return $this->returnJson([],'更新班组失败');
            }
            $info = db("class_group")->where("id = {$id}")->find();
            $area['province'] = action('admin/school/addressList',['id'=>0,'type'=>1]);
            $area['city'] = action('admin/school/addressList',['id'=>$info['province_id'],'type'=>1]);
            $area['area'] = action('admin/school/addressList',['id'=>$info['city_id'],'type'=>1]);
            $school = db('school')->where("state = 1")->select();
            $grade = db('grade')->where("state=1")->select();
            $info['url'] = '/admin/classorgroup/class_edit';
            return $this->fetch('class_save',compact('info','area','school','grade'));
        }

        public function class_del(){
            $id = $this->request->param('id',0);
            // 判断当前任务是否已开启
            $count = db('user_class_log')->where("class_group_id = {$id}")->count();
            if($count > 0){
                return $this->returnJson([],'删除失败:当前班组下已存在学生');
            }
            $res = db('class_group')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除班组成功');
                return $this->returnJson([],'删除班组',1);
            }
            return $this->returnJson([],'删除失败');

        }

        public function group_index(){
            $where = [];
            if($this->request->isPost()){
                $page = $this->request->post('page',$this->page);
                $limit = $this->request->post('limit',$this->limit);
                $admin_id = Cookie::get('id');
                if((int)cookie('group_id') !== config('rx.groupId')){
                    $where[] = ['n.admin_id','=',$admin_id];
                }
                $list = db('group_group')
                    ->alias('n')
                    ->join("user_group_log ugl","ugl.group_group_id = n.id",'left')
                    ->join("task_allot_log tal","tal.class_or_group_id = n.id and tal.type = 2",'left')
                    ->where($where)
                    ->field("n.id,n.name,FROM_UNIXTIME(n.create_time) create_time,n.state,count(ugl.id) user_num,count(tal.id) task_num")
                    ->limit(($page-1)*$limit,$limit)
                    ->group('n.id')
                    ->select();
                $total = db('group_group')
                    ->alias('n')
                    ->where($where)
                    ->field("n.id")
                    ->count();
                return $this->returnTabelJson($list,'查询成功',$total);
            }
            return $this->fetch();
        }

        public function group_add(){
            if($this->request->isPost()){
                $data = [];
                $name = $this->request->param('name','');
                if(empty($name)){
                    return $this->returnJson([],'创建群组失败：名称不能为空');
                }
                $data['name'] = $name;
                $data['create_time'] = time();
                $data['update_time'] = time();
                $data['admin_id'] = Cookie::get('id');
                $id = db('group_group')->insertGetId($data);
                if($id > 0){
                    insert_admin_log('创建群组成功');
                    return $this->returnJson(['id'=>$id],'创建群组成功',1);
                }
                return $this->returnJson([],'创建群组失败');
            }
            return $this->fetch('group_save');
        }

        public function group_edit(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $data = $this->request->param();
                $update = [];
                if(isset($data['name'])){
                    if(empty($data['name'])){
                        return $this->returnJson([],'更新群组失败：名称不能为空');
                    }
                    $update['name'] = $data['name'];
                }
                if(isset($data['state'])){
                    $update['state'] = $data['state'];
                }
                $update['update_time'] = time();
                $res = db("group_group")->where("id = {$id}")->update($update);
                if($res !== false){
                    insert_admin_log('更新群组成功');
                    return $this->returnJson(['id'=>$id],'更新群组成功',1);
                }
                return $this->returnJson([],'更新群组失败');
            }
            $info = db("group_group")->where("id = {$id}")->find();
            $info['url'] = '/admin/classorgroup/group_edit';
            return $this->fetch('group_save',compact('info'));
        }

        public function group_del(){
            $id = $this->request->param('id',0);
            // 判断当前任务是否已开启
            $count = db('user_group_log')->where("group_group_id = {$id}")->count();
            if($count > 0){
                return $this->returnJson([],'删除失败:当前群组下已存在学生');
            }
            $res = db('group_group')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除群组成功');
                return $this->returnJson([],'删除群组',1);
            }
            return $this->returnJson([],'删除失败');
        }

        // 班组学员分配
        public function class_allot(){
            return $this->allot(1);
        }

        // 群组学员分配
        public function group_allot(){
            return $this->allot(2);
        }

        public function allot($type){
            // $type = $this->request->param('type',1);
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $datas = [];
                $user_ids = $this->request->param('user_ids',[]);
                foreach ($user_ids as $item){
                    array_push($datas,[
                        'u_id' => $item,
                        ($type == 1?'class_group_id':'group_group_id') => $id,
                        'create_time' => time()
                    ]);
                }
                if(empty($datas)){
                    db('user_class_log')->where("class_group_id = {$id}")->delete();
                    db('user_group_log')->where("group_group_id = {$id}")->delete();
                    insert_admin_log('学员分配成功');
                    return $this->returnJson([],'分配成功',1);
                }
                switch ($type){
                    case 1:
                        // 分配班组
                        $name = db('class_group')->where("id = {$id}")->value('name');
                        db('user_class_log')->where("class_group_id = {$id}")->delete();
                        db('user_class_log')->insertAll($datas);
                        $this->messageToUids('班组变更',"您被分配到班组【{$name}】中",$user_ids);
                        break;
                    case 2:
                        // 分配群组
                        $name = db('group_group')->where("id = {$id}")->value('name');
                        db('user_group_log')->where("group_group_id = {$id}")->delete();
                        db('user_group_log')->insertAll($datas);
                        $this->messageToUids('群组变更',"您被分配到群组【{$name}】中",$user_ids);
                        break;
                }
                insert_admin_log('学员分配成功');
                return $this->returnJson([],'分配成功',1);
            }
            // 获取当前组所在区域
            $where = [];
            $where['state'] = $this->status['NORMAL'];
            $area_id = $type == 1 ? db('class_group')->alias('cg')->join('admin a','a.id = cg.admin_id')->where("cg.id = {$id}")->value('a.area_id') : db('group_group')->alias('cg')->join('admin a','a.id = cg.admin_id')->where("cg.id = {$id}")->value('a.area_id') ;
            if(!empty($area_id)){
                $where['area_id'] = $area_id;
            }
            $users = db('user')->where($where)->select();
            // 获取当前组已包含的所有用户
            $users_info = $type == 1 ? db('user_class_log')->where("class_group_id = {$id}")->field('u_id')->select() : db('user_group_log')->where("group_group_id = {$id}")->field('u_id')->select();
            $users_info = array_column($users_info,'u_id');
            return $this->fetch('allot',compact('users','users_info','id','type'));
        }


        //注册用二维码
        public function qrcode(){
            $id = $this->request->param('id',0);
            if(!file_exists("../public/upload/register_code/".$id."-0.jpeg")){
                $stu_code = $this->create_code(['class_id'=>$id,'type'=>0]);
            }else{
                $stu_code = $this->webUrl."/upload/register_code/".$id."-0.jpeg";
            }
            if(!file_exists("../public/upload/register_code/".$id."-1.jpeg")){
                $tea_code = $this->create_code(['class_id'=>$id,'type'=>1]);
            }else{
                $tea_code = $this->webUrl."/upload/register_code/".$id."-1.jpeg";
            }
            return $this->fetch('qrcode',compact('stu_code','tea_code'));
        }

        public function create_code($canshu,$page='pages/userinfo/register'){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx32d11f1898c690d6&secret=f0f5285f163324b11d3b2c0cb09ff8ac";
            $resultData = https_getRequest($url);
            $token = $resultData['access_token'];
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
            $data = ['scene' =>$canshu['class_id']."&".$canshu['type'],'width'=>480,'page'=>$page,
            // 'env_version'=>'trial'//'develop'//体验版，开发版，正式版切换
        ];
            $res = httpRequest( $url, json_encode($data),"POST");
            $res_base64="data:image/jpeg;base64,".base64_encode($res);
            $code_img= $this->get_base64_img($res_base64,'../public/upload/register_code/',$canshu['type'],$canshu['class_id']);
            return $code_img;
        }

        /**
         * base64转码图片
         * @param $base64
         * @param string $path
         * @return bool|string
         */
        function get_base64_img($base64,$path = '',$code_type,$class_id){
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
                $type = $result[2];
                $new_file = $path.$class_id.'-'.$code_type.".{$type}";
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))){
                    $new_file = str_replace("../public/","/",$new_file);
                    return $this->webUrl.$new_file;
                }else{
                    return false;
                }
            }
        }

    }
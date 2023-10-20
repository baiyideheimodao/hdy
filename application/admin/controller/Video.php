<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Video.php
     *  Create Date : 2021/12/31 16:43
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\controller;
    use app\common\controller\AdminController;
    use \think\facade\Cookie;
    use think\facade\Env;

    class Video extends AdminController {

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
                $is_top = $this->request->param('is_top',-1);
                if(intval($is_top) >= 0 && $is_top !== ''){
                    $where[] = ['n.is_top','=',$is_top];
                }
                $state = $this->request->param('state',-1);
                if(intval($state) >= 0 && $state !== ''){
                    $where[] = ['n.state','=',$state];
                }
                $title = $this->request->param('title','');
                if(!empty($title)){
                    $where[] = ['n.title','like',"%{$title}%"];
                }
                $list = db('video')
                    ->alias('n')
                    // ->join('area a','n.area_id = a.id and a.state = '.$this->status['NORMAL'],'left')
                    ->where($where)
                    ->field("n.id,n.show_all,n.video_url,n.title,n.desc,FROM_UNIXTIME(n.create_time) create_time,n.state,n.is_top")
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
                $total = db('video')
                    ->alias('n')
                    // ->join('area a','n.area_id = a.id and a.state = '.$this->status['NORMAL'],'left')
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
                $image = $this->request->param('image','');
                $title = $this->request->param('title','');
                if(empty($title)){
                    return $this->returnJson([],'创建视频失败：标题不能为空');
                }
                $data['image'] = $image;
                $title = $this->request->param('title','');
                if(empty($title)){
                    return $this->returnJson([],'创建视频失败：视频标题不能为空');
                }
                $data['title'] = $title;
                $desc = $this->request->param('desc','');
                $data['desc'] = $desc;
                // if(\cookie('id') !== 1 && empty($area_id)){
                //     return $this->returnJson([],'创建视频失败：区域未选择');
                // }
                $data['area_id'] = Cookie::has('area_id')?Cookie::get('area_id'):'';

                // 获取当前登陆人所属区域，判断是否为总区管理员
                $area_id = Cookie::has('area_id')?Cookie::get('area_id'):0;
                if(!check_admin_area()){
                    // 分区管理员
                    $data['show_all'] = 0;
                    $data['area_ids'] = ','.$area_id.',';
                }else{
                    $show_all = $this->request->param('show_all',0);
                    if($show_all == 'on'){
                        $data['show_all'] = 1;
                    }else{
                        $area_ids = $this->request->param('area','');
                        if(empty($area_ids)){
                            return $this->returnJson([],'创建视频失败：显示区域未选择');
                        }
                        $data['area_ids'] = ','.implode(',',$area_ids).',';
                    }
                }
                $is_top = $this->request->param('is_top',0);
                if($is_top == 'on'){
                    $data['is_top'] = 1;
                }
                $url = $this->upload();
                if(empty($url)){
                    return $this->returnJson([],'创建视频失败：视频文件未上传');
                }
                $data['video_url'] = $url;
                $sort = $this->request->param('sort',0);
                $data['sort'] = $sort;
                $data['create_time'] = time();
                $data['admin_id'] = Cookie::get('id');
                $id = db('video')->insertGetId($data);
                if($id > 0){
                    insert_admin_log('创建视频成功');
                    return $this->returnJson(['id'=>$id],'创建视频成功',1);
                }
                return $this->returnJson([],'创建视频失败');
            }
            // 区域列表
            // $areas = db('area')->where("p_id != 0")->select();
            $area['province'] = action('admin/school/addressList',['id'=>0,'type'=>1]);
            $area['city'] = [];
            $area['area'] = action('admin/school/addressList',['id'=>0,'type'=>1]);
            return $this->fetch('save',compact('area'));
        }

        public function edit(){
            $id = $this->request->param('id',0);
            if($this->request->isPost()){
                $data = $this->request->param();
                $update = [];
                if(isset($data['image'])){
                    if(empty($data['image'])){
                        return $this->returnJson([],'更新视频失败：封面未上传');
                    }
                    $update['image'] = $data['image'];
                }
                if(isset($data['title'])){
                    if(empty($data['title'])){
                        return $this->returnJson([],'更新视频失败：标题不能为空');
                    }
                    $update['title'] = $data['title'];
                }
                if(isset($data['desc'])){
                    $update['desc'] = $data['desc'];
                }
                $url = $this->upload();
                if(!empty($url)){
                    $update['video_url'] = $url;
                }
                if(isset($data['state'])){
                    $update['state'] = $data['state'];
                }
                if(isset($data['sort'])){
                    $update['sort'] = $data['sort'];
                }
                // 获取当前登陆人所属区域，判断是否为总区管理员
                if(check_admin_area()){
                    if(isset($data['show_all'])){
                        if($data['show_all'] == 'on'){
                            $data['show_all'] = 1;
                            $update['area_ids'] = '';
                        }else{
                            if(!isset($data['area']) || empty($data['area'])){
                                return $this->returnJson([],'更新视频失败：显示区域未选择');
                            }
                            $update['area_ids'] = ','.implode(',',$data['area']).',';
                            $data['show_all'] = 0;
                        }
                        $update['show_all'] = $data['show_all'];
                    }
                }
                if(isset($data['is_top'])){
                    if($data['is_top'] == 'on'){
                        $data['is_top'] = 1;
                    }
                    $update['is_top'] = $data['is_top'];
                }
                $res = db("video")->where("id = {$id}")->update($update);
                if($res !== false){
                    insert_admin_log('更新视频成功');
                    return $this->returnJson(['id'=>$id],'更新视频成功',1);
                }
                return $this->returnJson([],'更新视频失败');
            }
            $info = db('video')->where("id = {$id}")->find();
            $info['url'] = '/admin/video/edit';
            // 区域列表
            // $areas = db('area')->where("p_id != 0")->select();
            $area['province'] = action('admin/school/addressList',['id'=>0,'type'=>1]);
            $area['city'] = [];
            $area['area'] = db('area')->where("p_id != 0")->select();;
            return $this->fetch('save',compact('info','area'));
        }

        public function upload(){
            $url = '';
            try {
                $file = $this->request->file('file');
                if(empty($file)){
                    return $url;
                }
                $info = $file->move(Env::get('root_path') . DIRECTORY_SEPARATOR. 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'video');
                if ($info) {
                    $url = '/upload/video/' . str_replace('\\', '/', $info->getSaveName());
                } else {
                }
            } catch (\Exception $e) {
            }
            return $url;
        }

        public function del(){
            $id = $this->request->param('id',0);
            $video_url = db('video')->where("id = {$id}")->value('video_url');
            @unlink(Env::get('root_path') . DIRECTORY_SEPARATOR. 'public'.$video_url);
            $res = db('video')->where("id = {$id}")->delete();
            if($res !== false){
                insert_admin_log('删除视频成功');
                return $this->returnJson([],'删除成功',1);
            }
            return $this->returnJson([],'删除失败');
        }
    }
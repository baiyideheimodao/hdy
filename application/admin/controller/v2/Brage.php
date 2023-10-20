<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Brage.php
     *  Create Date : 2022/8/14 12:40
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\admin\controller\v2;
    use app\common\controller\AdminController;

    class Brage extends AdminController {

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
        }

        public function index()
        {
            if ($this->request->isPost()) {
                $page = $this->request->post('page', $this->page);
                $limit = $this->request->post('limit', $this->limit);
                $list = db('brage_table')
                    ->limit(($page - 1) * $limit, $limit)
                    ->select();
                $total = db('brage_table')
                    ->count();
                return $this->returnTabelJson($list, '查询成功', $total);
            }
            return $this->fetch();
        }

        public function add(){
            return $this->returnJson([],'无效请求');
        }

        public function edit()
        {
            $id = $this->request->param('id', 0);
            if ($this->request->isPost()) {
                $data = $this->request->param();
                $update = [];
                if (isset($data['name'])) {
                    if (!empty($data['name'])) {
                        $update['name'] = $data['name'];
                    } else {
                        return $this->returnJson([], '更新失败：名称不能为空');
                    }
                }
                if (isset($data['image'])) {
                    if (!empty($data['image'])) {
                        $update['image'] = $data['image'];
                    } else {
                        return $this->returnJson([], '更新失败：图片不能为空');
                    }
                }
                $res = db('brage_table')->where("id = {$id}")->update($update);
                if ($res !== false) {
                    insert_admin_log('更新虚拟徽章成功');
                    return $this->returnJson(['id' => $id], '更新虚拟徽章成功', 1);
                }
                return $this->returnJson([], '更新虚拟徽章失败');
            }
            $info = db('brage_table')->where("id = {$id}")->find();
            $info['url'] = '/admin/v2.brage/edit';
            return $this->fetch('save', compact('info'));
        }
    }
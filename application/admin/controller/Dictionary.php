<?php

namespace app\admin\controller;

use think\facade\Cookie;
use app\common\controller\AdminController;

class Dictionary extends AdminController
{

    protected function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $dictionary = db('dictionary')
            ->where(['is_del' => 0])
            ->order('id asc')
            ->fetchCollection()
            ->select()
            ->toArray();
        return $this->fetch('index', ['dictionary' => json_encode(list_to_tree($dictionary))]);
    }

    // 获取字典列表
    public function getList()
    {
        if (request()->isPost()) {
            $params = request()->param();
            $type = 1; // 默认树结构返回
            if (isset($params['type'])) {
                $type = intval($params['type']);
            }
            $dictionary = db('dictionary')
                ->where(['is_del' => 0])
                ->order('id asc')
                ->fetchCollection()
                ->select()
                ->toArray();
            if (intval($type) == 1) {
                $dictionary = list_to_tree($dictionary);
            }

            $this->success('获取成功', '', $dictionary);
        }
        $this->error('获取失败');
    }

    // 获取字典详情
    public function getItem()
    {
        if (request()->isPost()) {
            $params = request()->param();
            if (!isset($params['id']) || intval($params['id']) <= 0) {
                $this->error('ID不存在');
            }
            $id = $params['id'];
            $info = db('dictionary')->where(['id' => "{$id}"])->find();
            if (!empty($info)) {
                $this->success('获取成功', '', $info);
            }
        }
        $this->error('获取失败');
    }


    // 新增字典
    public function add()
    {
        if (request()->isPost()) {
            $param = $this->request->param();
            $result = $this->validate($param, 'dictionary');
            if ($result !== true) {
                return showData(0, '验证失败');
            }
            if (db('dictionary')->where("is_del = 0 and dm = '{$param['dm']}'")->count() > 0) {
                return showData(0, '代码已被其他数据字典占用');
            }
            $param['create_time'] = time();
            $param['create_user'] = Cookie::get('id');
            unset($param['id']);
            $res = db('dictionary')->insertGetId($param);
            if ($res !== false) {
                insert_admin_log('添加了数据字典');
                clear_cache();
                return showData(200, '操作成功');
            }
            return showData(0, '操作失败');
        }
        return $this->fetch('saveDictionary', ['action' => 'add']);
    }

    public function edit()
    {
        if (request()->isPost()) {
            $params = request()->param();
            if (!isset($params['id'])) {
                return showData(0, 'ID不存在');
            }
            $result = $this->validate($params, 'dictionary');
            if ($result !== true) {
                return showData(0, $result);
            }
            if (db('dictionary')->where("is_del = 0 and dm = '{$params['dm']}' and id <> {$params['id']}")->count() > 0) {
                return showData(0, '代码已被其他数据字典占用');
            }
            $res = db('dictionary')->where("id = {$params['id']}")->update($params);
            if ($res !== false) {
                insert_admin_log('修改了字典');
                clear_cache();
                return showData(200, '操作成功');
            }
            return showData(0, '操作失败');
        }
        $params = request()->param();
        if (!isset($params['id']) || intval($params['id']) <= 0) {
            return showData(0, 'ID不存在');
        }
        $id = $params['id'];
        $info = db('dictionary')->where("id = {$id}")->find();
        return $this->fetch('saveDictionary', ['data' => $info, 'action' => 'edit']);
    }

    // 删除指定字典
    public function del()
    {
        if (request()->isPost()) {
            $params = request()->param();
            if (!isset($params['id'])) {
                return showData(0, 'ID不存在');
            }
            if (db('dictionary')->where(" id in ({$params['id']})")->count() <= 0) {
                return showData(0, '操作失败');
            }
            // 判断是否存在子项
            if (db('dictionary')->where("pid in ({$params['id']})")->count() > 0) {
                return showData(0, '当前字典存在子项,不可删除');
            }
            $res = db('dictionary')->where("id in ({$params['id']})")->update(['is_del' => 1]);
            if ($res !== false) {
                insert_admin_log('删除了指定字典');
                return showData(200, '操作成功');
                clear_cache();
            }
            return showData(0, '操作失败');
        }
        return showData(0, '请求失败');
    }

    /******************************区域配置******************************/
    /**
     * 地区管理
     * */
    public function region()
    {
        $region = model('region')->where(['status' => 1])->order('id asc')->fetchCollection()->select()->toArray();
        return $this->fetch('region', ['region' => json_encode(list_to_tree($region))]);
    }

    public function addRegion()
    {
        if ($this->request->isPost()) {
            if ($this->insert('region', $this->request->param()) === true) {
                insert_admin_log('添加了地区');
                return showData(200, '添加成功');
            } else {
                return showData(0, $this->errorMsg);
            }
        }
        return $this->fetch('saveRegion', ['action' => 'addRegion']);
    }

    public function editRegion()
    {
        if ($this->request->isPost()) {
            if ($this->update('region', $this->request->param()) === true) {
                insert_admin_log('修改了地区');
                return showData(200, '修改成功');
            } else {
                return showData(0, $this->errorMsg);
            }
        }
        return $this->fetch('saveRegion', ['data' => model('region')->where('id', input('id'))->find(), 'action' => 'editRegion']);
    }

    public function delRegion()
    {
        if ($this->request->isPost()) {
            if ($this->remove('region', $this->request->param()) === true) {
                insert_admin_log('删除了地区');
                return showData(200, '删除成功');
            } else {
                return showData(0, $this->errorMsg);
            }
        }
    }

    /**
     * 创建地区json
     *
     * */
    public function createRegion()
    {
        $param = $this->request->param();
        switch (isset($param['type']) && intval($param['type']) > 0 ? intval($param['type']) : 1) {
            case 1:
                if (file_put_contents(ROOT_PATH . './area.json', json_encode(getRegion()), LOCK_EX)) {
                    return showData(200, '生成成功');
                } else {
                    return showData(0, '生成失败！');
                }
                break;
        }
    }

    /******************************组织架构******************************/
    public function organizational()
    {
        $organizational = model('organizational')->where(['status' => 1])->order('id asc')->fetchCollection()->select()->toArray();
        return $this->fetch('organizational', ['organizational' => json_encode(list_to_tree($organizational))]);
    }

    /******************************组织架构******************************/
    public function listorganizational()
    {
        $organizational = model('organizational')->where(['status' => 1])->order('id asc')->fetchCollection()->select()->toArray();
        return $this->fetch('listorganizational', ['organizational' => json_encode(list_to_tree($organizational))]);
    }

    public function addOrganizational()
    {
        $param = $this->request->param();
        if ($this->request->isPost()) {
            $param['level'] = db('organizational')->where('id', '=', $param['pid'])->value('level') + 1;
            if ($this->insert('organizational', $param) === true) {
                insert_admin_log('添加了组织架构');
                return showData(200, '添加成功');
            } else {
                return showData(0, $this->errorMsg);
            }
        }
        return $this->fetch('saveOrganizational', ['action' => 'addOrganizational']);
    }

    //设置全局组织架构
    public function dictionarySet($id)
    {
        Cookie::set('storeid',$id);
        // if(Cookie::get('storeid')){
            return showData(200, '修改成功');
        // }else{
        //     return showData(0, '修改失败');
        // }
    }

    public function editOrganizational()
    {
        $param = $this->request->param();
        if ($this->request->isPost()) {
            $param['level'] = db('organizational')->where('id', '=', $param['pid'])->value('level') + 1;
            if ($this->update('organizational', $param) === true) {
                insert_admin_log('修改了组织架构');
                return showData(200, '修改成功');
            } else {
                return showData(0, $this->errorMsg);
            }
        }
        $level = db('organizational')->where('id', '=', $param['id'])->value('level') + 1;
        return $this->fetch('saveOrganizational', ['data' => model('organizational')->where('id', input('id'))->find(), 'action' => 'editOrganizational']);
    }

    public function delOrganizational()
    {
        if ($this->request->isPost()) {
            if ($this->remove('organizational', $this->request->param()) === true) {
                insert_admin_log('删除了组织架构');
                return showData(200, '删除成功');
            } else {
                return showData(0, $this->errorMsg);
            }
        }
    }
}
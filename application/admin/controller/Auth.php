<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\Cookie;
class Auth extends AdminController
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function auto()
    {
        $insertId = db('authRule')->insertGetId([
            'pid' => 3,
            'name' => '数据管理',
            'url' => 'admin/column/index',
            'icon' => 'fa fa-th-list',
            'type' => 'nav',
        ]);
        db('authRule')->insertAll([
            [
                'pid' => $insertId,
                'name' => '还原',
                'url' => 'admin/database/import',
                'type' => 'nav',
            ],
            [
                'pid' => $insertId,
                'name' => '备份',
                'url' => 'admin/database/backup',
                'type' => 'auth',
            ],
            [
                'pid' => $insertId,
                'name' => '优化',
                'url' => 'admin/database/optimize',
                'type' => 'auth',
            ],
            [
                'pid' => $insertId,
                'name' => '修复',
                'url' => 'admin/database/repair',
                'type' => 'auth',
            ],
            [
                'pid' => $insertId,
                'name' => '下载',
                'url' => 'admin/database/download',
                'type' => 'auth',
            ],
            [
                'pid' => $insertId,
                'name' => '删除',
                'url' => 'admin/database/del',
                'type' => 'auth',
            ],
        ]);
    }

    public function group()
    {
        if ($this->request->isPost()) {
            $map[] = ['id','>',0];
            $map[] = ['admin_id','=',cookie('id')];
            $list = model('authGroup')->where($map)->order("id desc")->page($this->page,$this->limit)->field("*,if(status = 1,'正常','禁用') statusname,if(type = 1,'后台用户','前台用户') typename")->select();
            $listNumber = model('authGroup')->where($map)->count();
            return $this->returnTabelJson($list,'查询成功',$listNumber);
        }
        return $this->fetch();
    }

    public function add_group()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if(isset($param['type']) && $param['type']=='2'){
                $param['rules'] = !empty($param['rule']) && is_array($param['rule'])  ? implode(',', array_filter($param['rule'])) : '';
                unset($param['rule']);
            }
            $result = $this->validate($param,'authGroup');
            if($result !== true){
                return showData(0,$result);
            }
            $param['admin_id'] = cookie('id');
            if(db('authGroup')->where("name = '{$param['name']}' and status = 1")->count() > 0){
                return showData(0,'当前用户组名称已存在');
            }
            if ($this->insert('authGroup', $param , false) === true) {
                insert_admin_log('添加了用户组');
                return showData(200,'添加成功');
            } else {
                return showData(0,'添加失败');
            }
        }
        // 获取当前人拥有的菜单权限id
        $rules = db('auth_group')->where('id',cookie('group_id'))->value('rules');
        $map[] = ['status','=',1];
        if(cookie('group_id') != config('rx.groupId')){
            $map[] = ['id','in',explode(',',$rules)];
        }
        $authRule = model('authRule')->where($map)->order('sort_order asc')->fetchCollection()->select()->toArray();
        return $this->fetch('saveGroup', ['authRule' => json_encode(list_to_tree($authRule)),'action'=>'add_group']);
    }

    public function edit_group()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if ($this->update('authGroup', $param, input('_verify', true)) === true) {
                insert_admin_log('修改了用户组');
                return showData(200,'修改成功');
            } else {
                return showData(0,'修改失败');
            }
        }
        $data = model('authGroup')->where('id', input('id'))->find();
        $rules = db('auth_group')->where('id',config('rx.groupId'))->value('rules');
        $map[] = ['status','=',1];
        if(cookie('group_id') != config('rx.groupId')){
            $map[] = ['id','in',explode(',',$rules)];
        }
        $authRule = model('authRule')->where($map)->order('sort_order asc')->fetchCollection()->select()->toArray();
        foreach ($authRule as $k => $v) {
            $authRule[$k]['checked'] = in_array($v['id'], explode(',', $data['rules']));
        }
        return $this->fetch('saveGroup', ['data' => $data, 'authRule' => json_encode(list_to_tree($authRule)),'action'=>'edit_group']);
    }

    public function del_group()
    {
        if ($this->request->isPost()) {
            if ($this->remove('authGroup', $this->request->param()) === true) {
                insert_admin_log('删除了用户组');
                return showData(200,"删除成功");
            } else {
                return showData(0,$this->errorMsg);
            }
        }
    }

    public function rule()
    {
        if($this->request->isPost()){
            $list = db('auth_rule')->order('sort_order asc')->select();
            $total = db('auth_rule')
                ->count();
            return $this->returnTabelJson($list,'查询成功',$total);
        }
        $auth_rule = db('auth_rule')->order('sort_order asc')->select();
        return $this->fetch('rule', compact('auth_rule'));
        // $authRule = model('authRule')->where(['status' => 1])->order('sort_order asc')->fetchCollection()->select()->toArray();
        // return $this->fetch('rule', ['authRule' => json_encode(list_to_tree($authRule))]);
    }

    public function add_rule()
    {
        if ($this->request->isPost()) {
            if ($this->insert('authRule', $this->request->param()) === true) {
                insert_admin_log('添加了权限规则');
                return showData(200,'添加成功');
            } else {
                return showData(0,$this->errorMsg);
            }
        }
        $auth_rule = list_to_tree(db('auth_rule')->where("type = 'nav'")->order('sort_order asc')->select());
        $action = 'add_rule';
        return $this->fetch('saveRule',compact('auth_rule','action'));
    }

    public function edit_rule()
    {
        if ($this->request->isPost()) {
            if ($this->update('authRule', $this->request->param()) === true) {
                insert_admin_log('修改了权限规则');
                return showData(200,'修改成功');
            } else {
                return showData(0,$this->errorMsg);
            }
        }
        $auth_rule = list_to_tree(db('auth_rule')->where("type = 'nav'")->order('sort_order asc')->select());
        $action = 'edit_rule';
        $data = model('authRule')->where('id', input('id'))->find();
        return $this->fetch('saveRule', compact('data','auth_rule','action'));
    }

    public function del_rule()
    {
        if ($this->request->isPost()) {
            if ($this->remove('authRule', $this->request->param()) === true) {
                insert_admin_log('删除了权限规则');
                return showData(200,'删除成功');
            } else {
                return showData(0,$this->errorMsg);
            }
        }
    }

    public function state(){
        $id = $this->request->param('id',0);
        $state = $this->request->param('state',0);

        $list = db('auth_rule')->field("id,pid")->select();
        $uptId = get_all_child($list,$id);
        $uptId[] = $id;

        $res = db('auth_rule')->whereIn('id',$uptId)->update(['status' => $state]);
        if($res !== false){
            insert_admin_log('更新权限规则状态');
            return $this->returnJson([],'操作成功',1);
        }
        return $this->returnJson([],'操作失败');
    }
}

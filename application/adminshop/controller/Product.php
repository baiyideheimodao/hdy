<?php

namespace app\adminshop\controller;


use think\Controller;
use think\db;
use think\facade\Cookie;

class Product extends Controller
{

    private $location = '/adminshop/product';

    //验证登录


    public function login()
    {
        return $this->success('请先登录', '/admin/login');
    }


    public function index($zt = '', $key = '')
    {
        $template = [
            'title' => "商品列表",
            'name' => "product",
            'location' => $this->location
        ];
        $this->assign('localhost', $template);
        $arr = ($this->request->param());
        $where = '1=1';

        if (@$arr['pid']) {
            $proIdData = implode(',', groupid($arr['pid']));
            $where .= "  and typeid in (" . $proIdData . ")";
        }
        if ($key != null) {
            $where .= " and (title like '%" . $key . "%')";
        }
        $res = Db::name('pro')->order('sort asc,id asc')->where($where)->paginate(10, false, ['query' => request()->param()]);
        $num = count($res);
        $data = [
            "code" => 0, "msg" => "", "count" => $num, 'data' => $res
        ];
        $location = [
            'title' => "商品列表",
            'name' => "pro",
        ];
        $this->assign('data', $data);
        $this->assign('location', $location);
        $this->assign('list', $res);
        $page = $res->render();
        $this->assign('page', $page);
        return $this->fetch();
    }


    public function sort()
    {
        $template = [
            'title' => "商品分类",
            'name' => "product",
            'location' => $this->location
        ];
        $this->assign('location', $template);
        return $this->fetch();
    }

    /**
     * 导航菜单JSON数据
     */
    public function menujsondata()
    {
        $res = Db::name('menu')->order('sort', 'asc')->select();
        $count = count(db('menu')->order('sort', 'asc')->where('pid', 0)->select());
        foreach ($res as $k => $rs) {
            $data[$k]['id'] = $rs['id'];
            $data[$k]['name'] = $rs['name'];
            //先判断是否有下级数据  有则加上参数is_open
            $where1['pid'] = $rs['id'];
            $checkRepeat = 1;//一种有条件(比如自身修改)的查询重复
            if ($checkRepeat) {
                $data[$k]['is_open'] = true;//true=展开  false=收起
            }
            $data[$k]['sort'] = $rs['sort'];//排序
            $data[$k]['icon'] = $rs['image'];
            $data[$k]['hide'] = $rs['hide'];
            $data[$k]['position'] = $rs['position'];
            $data[$k]['recommend'] = $rs['recommend'];
            $data[$k]['pId'] = $rs['pid'];
        }
        $result = array('code' => 0, 'msg' => '', 'count' => $count, 'data' => $data); //输出json echo json_encode($result); exit;
        //return json_encode($result);
        return json($result);
        // json(0, '', $count, $data);

    }

    /**
     * 导航菜单JSON数据
     */
    public function menuSortdata($type = '')
    {
        $res = Db::name('menu')->order('sort', 'asc')->select();
        $count = count(db('menu')->order('sort', 'asc')->where('pid', 0)->select());
        foreach ($res as $k => $rs) {
            $data[$k]['id'] = $rs['id'];
            $data[$k]['name'] = $rs['name'];
            //先判断是否有下级数据  有则加上参数is_open
            $where1['pid'] = $rs['id'];
            $checkRepeat = 1;//一种有条件(比如自身修改)的查询重复
            if ($checkRepeat) {
                $data[$k]['is_open'] = true;//true=展开  false=收起
            }
            $data[$k]['sort'] = $rs['sort'];//排序
            $data[$k]['icon'] = $rs['image'];
            $data[$k]['hide'] = $rs['hide'];
            $data[$k]['position'] = $rs['position'];

            $data[$k]['pId'] = $rs['pid'];
        }
        array_unshift($data, ['id' => 0, 'pId' => 0, 'name' => '顶级栏目']);
        $result = array('code' => 0, 'msg' => '', 'count' => $count, 'data' => $data); //输出json echo json_encode($result); exit;
        //return json_encode($result);
        return json($result);
        // json(0, '', $count, $data);

    }


    /**
     * 添加栏目、保存
     */
    public function add($action = '')
    {
        $location = [
            'title' => "商品添加",
            'name' => "product",
        ];
        $paramData = ($this->request->param());
        if ($action == 'save') {
            unset($paramData['pc_src']); //删除某个元素
            unset($paramData['action']); //删除某个元素
            unset($paramData['file']);
            unset($paramData['upfile']);
            unset($paramData['province']);
            unset($paramData['city']);
            $paramData['sales'] = rand(100, 500);
            $paramData['creatime'] = time();
            if(isset($paramData['show_all'])){
                if($paramData['show_all'] == 'on'){
                    $paramData['show_all'] = 1;
                    $paramData['area_ids'] = '';
                }else{
                    $paramData['area_ids'] = $paramData['area'];
                    $paramData['area_ids'] = ','.implode(',',$paramData['area']).',';
                    $paramData['show_all'] = 0;
                }
            }
            unset($paramData['area']);
            $res = Db::name('pro')->data($paramData)->insert();
            return json(1);
        }
        // 区域列表
        $area['province'] = action('admin/common/area_list',['area_type'=>0]);
        $area['city'] = action('admin/common/area_list',['area_type'=>1]);
        $area['area'] = action('admin/common/area_list',['area_type'=>2]);
        return $this->fetch('add',compact('area','location'));
    }

    /**
     * 编辑栏目、保存
     */
    public function edit($id, $action = '')
    {
        $location = [
            'title' => "商品信息修改",
            'name' => "product",
        ];
        $this->assign('location', $location);
        $paramData = ($this->request->param());
        if ($action == 'edit') {

            unset($paramData['pc_src']); //删除某个元素
            unset($paramData['action']); //删除某个元素
            unset($paramData['file']);
            unset($paramData['upfile']);
            unset($paramData['province']);
            unset($paramData['city']);
            $paramData['sales'] = rand(100, 500);
            $paramData['creatime'] = time();
            if(isset($paramData['show_all'])){
                if($paramData['show_all'] == 'on'){
                    $paramData['show_all'] = 1;
                    $paramData['area_ids'] = '';
                }else{
                    $paramData['area_ids'] = $paramData['area'];
                    $paramData['area_ids'] = ','.implode(',',$paramData['area']).',';
                    $paramData['show_all'] = 0;
                }
            }

            unset($paramData['area']);
            $paramData['updatetime'] = time();
            $res = Db::name('pro')->where('id', $paramData['id'])->data($paramData)->update();
            return json(1);
        }
        $info = Db::name('pro')->where('id', $paramData['id'])->find();
        $litpics = explode(",", $info['images']);
        // 区域列表
        $area['province'] = action('admin/common/area_list',['area_type'=>0]);
        $area['city'] = action('admin/common/area_list',['area_type'=>1]);
        $area['area'] = action('admin/common/area_list',['area_type'=>2]);
        return $this->fetch('edit',compact('area','litpics','info'));
    }


//栏目列表 读出接口数据
    public function columntable()
    {
        $pid = input('pid');
        $flid = input('flid');
        $ts_id = input('ts_id');//上报教学站
        $page = input('page');//页数
        $limit = input('limit');//一页多少条记录
        $type = input('type');//搜索分类
        $key = input('key');//搜索关键词

        $where = new Where;
        if ($key == "") {//不搜索
            //级别
            if ($pid) {
                $where['pid'] = $pid;
            } else {
                $where['pid'] = 0;
            }
        } else {//搜索

            //类别
            if ($type == "id") {
                $where[$type] = $key;
            } else {
                $where['flid'] = $flid;
                $where[$type] = ['like', "%" . $key . "%"];
            }
        }

        $rs = Db::name('menu')->where($where)->order('id desc')->limit($limit)->page($page)->select();
        $rs1 = Db::name('menu')->where($where)->select();

        $count = count($rs1);//取得记录集总条数
        json(0, '数据返回成功', $count, $rs);

    }

    /**
     * 编辑栏目、保存
     */
    public function sortEdit($id, $action = '')
    {
        $location = [
            'title' => "商品分类管理",
            'name' => "menu",
        ];
        $this->assign('location', $location);
        $data = ($this->request->param());
        if ($action == 'edit') {
            //$data=array_filter($data); //删除空元素
            unset($data['pc_src']); //删除某个元素
            unset($data['action']); //删除某个元素
            unset($data['file']);
            unset($data['upfile']);
            $data['updatetime'] = time();

            $res = Db::name('menu')->where('id', $data['id'])->data($data)->update();
            return json(1);

        } else if ($action == 'ajax_update') {
            //$data=array_filter($data); //删除空元素
            if($data['type']=='recommend'){
                $data1=[
                    'id'=>$data['id'],
                    'recommend'=>$data['value']
                ];
            }else{
                $data1=[
                    'id'=>$data['id'],
                    'hide'=>$data['value']
                ];
            }
            $res = Db::name('menu')->where('id', $data['id'])->data($data1)->update();
            return json(['msg'=>'修改成功']);

        }
        $res = Db::name('menu')->where('id', $data['id'])->find();
        $this->assign('data', $res);
        return $this->fetch();
    }

    /**
     * 编辑栏目、保存
     */
    public function sortAdd($pid='', $action = '')
    {
        $location = [
            'title' => "商品分类管理",
            'name' => "menu",
        ];
        $this->assign('location', $location);
        $data = ($this->request->param());
        if ($action == 'add') {
            //$data=array_filter($data); //删除空元素
            unset($data['pc_src']); //删除某个元素
            unset($data['action']); //删除某个元素
            unset($data['file']);
            unset($data['upfile']);
            $data['px'] = $data['sort'];
            $res = Db::name('menu')->data($data)->insert();
            return json(1);
        }
        $this->assign('pid', $pid);
        return $this->fetch();
    }


//通用删除
    public function del()
    {
        $id = input('id');//id
        $dataname = "menu";//表名
        if (!$id && !$dataname) {
            $this->error('错误：参数不为空！');
        }
        $del_info = del($dataname, $id, 1, $dataname, 'pid');
        $del_info = explode(',', $del_info);
        if ($del_info[0] == 1) {//1=成功
            $date = [1, $del_info[1]];
        } elseif ($del_info[0] == 2) {//失败
            $date = [0, $del_info[1]];
        }
        return $date;
    }

    //栏目删除
    public function sortdel($action='')
    {
        if($action=='piliang'){
            $data= json_decode(html_entity_decode(stripslashes(input('data'))), true);
            $idData=[];
            foreach ($data as $k =>$val){
                array_push($idData,$val['id']);
            }
        }else{
            $id = input('id');//id
            $idData = groupid($id);
        }

        $res = db('menu')->whereIn('id',$idData)->delete();
        if ($res) {
            return json(['code' => 100, 'msg' => '删除成功']);
        } else {
            return json(['code' => 0, 'msg' => '网络异常']);
        }
    }

//图标
    public function icon()
    {
        return $this->fetch();
    }


    public function shangpin()
    {
        $location = [
            'title' => "门店商品管理",
            'name' => "mendian",
        ];

        $this->assign('location', $location);

        $arr = ($this->request->param());

        if (@$arr) {

            $tiaojian = [

                'pid' => $arr["pid"],

                'pcid' => $arr['pcid']

            ];

            if ($arr["pid"] == "") {

                unset($tiaojian['pid']); //删除某个元素

            }

            if ($arr["pcid"] == "") {

                unset($tiaojian['pcid']); //删除某个元素

            }

            $res = Db::name('product')->order('id', 'asc')->where($tiaojian)->paginate(10);
        } else {

            $res = Db::name('product')->order('id', 'asc')->paginate(10);
        }

        $shangjia = Db::name('product')->distinct(true)->field('pid')->order('id', 'desc')->select();

        $spfl = Db::name('product')->distinct(true)->field('pcid')->order('id', 'desc')->select();

        $this->assign('shangjia', $shangjia);

        $this->assign('spfl', $spfl);

        $this->assign('list', $res);

        $page = $res->render();

        $this->assign('page', $page);

        return $this->fetch();
    }


    public function jinyong()
    {

        $data = ($this->request->param());

        $arr = [];

        if ($data['action'] == 'tingyong') {

            $res = Db::name('ruzhu')->where('id', $data['id'])->data('zhtype', 2)->update();

            $arr['msg'] = '停用成功！';
        } else {

            $res = Db::name('ruzhu')->where('id', $data['id'])->data('zhtype', 1)->update();

            $arr['msg'] = '启用成功！';
        }

        if ($res) {

            $arr['code'] = 1;

            return json($arr);
        } else {

            $arr['code'] = 0;

            return json($arr);
        }
    }


    public function tuijian()
    {
        $data = ($this->request->param());
        if ($data['type'] == 'true') {
            $type = 1;
        } else {
            $type = 0;
        }
        $res = Db::name('pro')->where('id', $data['id'])->data('recommend', $type)->update();
        if ($res) {
            return json(1);
        } else {
            return json(2);
        }
    }

    public function xiajia()
    {
        $data = ($this->request->param());
        if ($data['type'] == 'true') {
            $type = 0;
        } else {
            $type = 1;
        }
        $res = Db::name('pro')->where('id', $data['id'])->data('status', $type)->update();
        if ($res) {
            return json(1);
        } else {
            return json(2);
        }
    }


    public function chongzhi()
    {
        $data = ($this->request->param());
        $arr = [];
        $res = Db::name('ruzhu')->where('id', $data['id'])->data('password', md5(123456))->update();
        if ($res) {
            $arr['code'] = 1;
        } else {
            $arr['code'] = 0;
        }
    }


    public function protype()
    {
        return $this->fetch();
    }


}

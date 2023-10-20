<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;
use think\facade\Cookie;


/**
 * 记录日志
 * @param mixed $message 要打印的信息
 * @author 朱兴宇
 * */ 
function printLog($message)
{
    $info = debug_backtrace()[0];
    error_log(
        var_export(
            [
                'path'=>$info['file'].':'.$info['line'],
                'content'=>$message,
                'time'=>date("Y-m-d,H:i:s"),
                // 'other'=>[debug_backtrace()[1],debug_backtrace()[2]]
            ],
            true
        )
        ,3,'log.txt');
}

function checkPassword($userId,$password){
    $userInfo = db('user')->where('id',$userId)->find();
    if(!$userInfo){
        return false;
    }
    return $userInfo['password'] === md5($password.'xckj');
}

function getRankClass(){
    return [
        0,
        // 77=>'red',
        // 78=>'green',
        // 79=>'golden',
        //81=>'red',
        82=>'red',
        83=>'green',
        84=>'golden',
        // 85=>'red'
    ];
}

function getHappyVillage($status = [
    'b0'=>0,
    'b1'=>0,
    'b2'=>0,
    'b3'=>0,
    'b4'=>0,
    'b5'=>0,
    'b6'=>0,
    'b7'=>0,
    'b8'=>0,
    'b9'=>0,
    'b10'=>0,
    'b11'=>0,
    'b12'=>0,
    'b13'=>0,
    'b14'=>0,
    'b15'=>0,
    'b16'=>0,
    'b17'=>0
],$class_id=0){
    //https://teacher.beirundq.com/
    $class_group = [
        0=>[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17],
        'red'=>[0,2,5,6,14,16],
        'green'=>[1,4,7,9,11,13],
        'golden'=>[3,8,10,12,15,17]
    ];
    $class_id_array = getRankClass();
    $class_task_list = $class_group[$class_id_array[$class_id]];
    $villageInfo = [
        ['id'=>0,'name'=>'挑战无人机','pic'=>'https://teacher.beirundq.com/upload/image/base/wrj.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b0'])],
        ['id'=>1,'name'=>'擦鞋子','pic'=>'https://teacher.beirundq.com/upload/image/base/cxz.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b1'])],
        ['id'=>2,'name'=>'手磨豆浆','pic'=>'https://teacher.beirundq.com/upload/image/base/smdj.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b2'])],
        ['id'=>3,'name'=>'我是快乐的小木匠','pic'=>'https://teacher.beirundq.com/upload/image/base/jym.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b3'])],
        ['id'=>4,'name'=>'碾米粉','pic'=>'https://teacher.beirundq.com/upload/image/base/nmcm.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b4'])],
        ['id'=>5,'name'=>'手工剁椒','pic'=>'https://teacher.beirundq.com/upload/image/base/sgdj.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b5'])],
        ['id'=>6,'name'=>'剪窗花','pic'=>'https://teacher.beirundq.com/upload/image/base/jsjth.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b6'])],
        ['id'=>7,'name'=>'我是收纳小能手','pic'=>'https://teacher.beirundq.com/upload/image/base/ybdy.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b7'])],
        ['id'=>8,'name'=>'包书皮这件小事','pic'=>'https://teacher.beirundq.com/upload/image/base/bsp.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b8'])],
        ['id'=>9,'name'=>'我与春天“扭”个约定','pic'=>'https://teacher.beirundq.com/upload/image/base/zzrh.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b9'])],
        ['id'=>10,'name'=>'刻纹样','pic'=>'https://teacher.beirundq.com/upload/image/base/jz.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b10'])],
        ['id'=>11,'name'=>'创意蛋挞','pic'=>'https://teacher.beirundq.com/upload/image/base/zdt.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b11'])],
        ['id'=>12,'name'=>'庄桥“麻雀头”','pic'=>'https://teacher.beirundq.com/upload/image/base/bht.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b12'])],
        ['id'=>13,'name'=>'凉拌马兰头','pic'=>'https://teacher.beirundq.com/upload/image/base/lbmlt.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b13'])],
        ['id'=>14,'name'=>'制作煎饼','pic'=>'https://teacher.beirundq.com/upload/image/base/zzjb.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b14'])],
        ['id'=>15,'name'=>'移栽菜苗','pic'=>'https://teacher.beirundq.com/upload/image/base/yzym.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b15'])],
        ['id'=>16,'name'=>'播种蔬菜','pic'=>'https://teacher.beirundq.com/upload/image/base/bzsc.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b16'])],
        ['id'=>17,'name'=>'椅子组装','pic'=>'https://teacher.beirundq.com/upload/image/base/zzyz.png','subject'=>'劳动教育','time'=>'2023年3月','mark'=>'这是描述','status'=>($status['b17'])],          
    ];
    $result = array_filter($villageInfo,function($v)use($class_task_list){
        return in_array($v['id'],$class_task_list);
    });
    return function($id=null)use($result,$class_task_list,$villageInfo){
        if(is_null($id)){
            return $result;
        }
        if(in_array($id,$class_task_list)){
            return $villageInfo[$id];
        }
        throw new Error('无获取权限',403);
    };
}

function sys()
{
    if (Cookie::get('storeid')) {
        $storeid = Cookie::get('storeid');
    } else {
        $storeid = 0;
    }
//    $storeidData = storeidData($storeid);
//    array_push($storeidData,$storeid);
    $storeidData = storeidData_new($storeid);
    $arr = [];
    $arr['storeid'] = $storeid;
    $arr['storeidData'] = $storeidData;
    return $arr;
}

function todayTime()
{
    $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
    $map = [$beginToday, $endToday];
    return $map;
}

function user_xm($id)
{
    $name = Db::name('user')->where('id', $id)->value('name');
    return $name;
}

function khname($khid)
{
    $result = Db::name('khlist')->field('name')->where('id', $khid)->find();
    return $result['name'];
}

//递归查询所有产品id树
function groupid($tid, &$result = array())
{
    // 创建storeid
    array_push($result, (int)$tid);
    $pids = Db::name('menu')->where('pid', $tid)->field('id')->select();
    if (count($pids) > 0) {
        $result = getIdsByPids($pids, $result);
    }
    return $result;
}

function zy_name($id)
{
    $result = Db::name('user')->field('name')->where('id', $id)->find();
    return $result['name'];
}

function zz_name($id)
{
    $result = Db::name('admin')->field('name')->where('id', $id)->find();
    return $result['name'];
}

function xzname($xzname)
{
    $result = implode("^", $xzname);
    return $result;
}

function messageCount($user_id,$state){
    $msgCount = db('message')->where("u_id = $user_id and state = $state")->count();
    return $msgCount;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $result 返回的层级数组
 * @param string $pid 上级id
 * @param string $storeid 选择地区的id
 * @return array
 * @author 卜阳
 */
function storeidData($storeid, &$result = array())
{
    // 创建storeid
    $pid = Db::name('menu')->where('pid', $storeid)->value('id');
    if (strlen($pid) == 1) {
        // 创建基于主键的数组引用
        // $result[]=array_push($result,$pid);
        $result[] = $pid;
        storeidData($pid, $result);
    }
    return $result;
}

function storeidData_new($storeid, &$result = array())
{
    // 创建storeid
    array_push($result, $storeid);
    $pids = Db::name('organizational')->where('pid', $storeid)->field('id')->select();
    if (count($pids) > 0) {
        $result = getIdsByPids($pids, $result);
    }
    return $result;
}

function getIdsByPids($pids = [], &$result = [])
{
    if (count($pids) > 0) {
        $idsArr = array_column($pids, 'id');
        $where[] = ['pid', 'in', implode(',', $idsArr)];
        $pids = Db::name('menu')->where($where)->field('id')->select();
        $result[] = array_merge(getIdsByPids($pids, $result), $idsArr);
        $res = [];
        array_walk_recursive($result, function ($value) use (&$res) {
            array_push($res, $value);
        });
        return array_values(array_unique($res));
    }
    return [];
}

/**
 * 系统邮件发送函数
 * @param string $receiveMail 接收邮件的邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件标题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @throws \PHPMailer\PHPMailer\Exception
 */
function sendMail($data, $receiveMail, $name, $subject = '', $body = '', $attachment = null)
{
    $mail = new PHPMailer\PHPMailer\PHPMailer();           // 实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';                              // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                                       // 设定使用SMTP服务
    $mail->SMTPDebug = 0;       // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;         // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';     // 使用安全协议
    $mail->Host = $data['smtp_server'];                 // SMTP 服务器
    $mail->Port = $data['cache'];                 // SMTP服务器的端口号
    $mail->Username = $data['send_username'];         // SMTP服务器用户名
    $mail->Password = $data['send_password'];         // SMTP服务器密码
    $mail->SetFrom($data['send_email'], $data['send_nickname']);//设置发件人邮箱和发件名名称
    $replyEmail = '';                                      //留空则为发件人邮箱
    $replyName = '';                                       //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($receiveMail, $name);
    if (is_array($attachment)) {                           // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
if (!function_exists('list_to_tree')) {
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}

/**
 * 返回tree json对象
 */
if (!function_exists('showData')) {
    function showData($code = 200, $msg = "无", $data = [], $errorCode = null)
    {
        $res = [
            "code" => $code,
            "msg" => $msg,
            "data" => $data,
            "errorCode" => $errorCode
        ];
        return json($res);
    }
}
/**
 * 清除系统缓存
 * @param null $directory
 * @return bool
 */
if (!function_exists('clear_cache')) {
    function clear_cache($directory = null)
    {
        $directory = empty($directory) ? dirname(realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/')) . '/runtime/cache/' : $directory;
        if (is_dir($directory) == false) {
            return false;
        }
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                is_dir($directory . '/' . $file) ?
                    clear_cache($directory . '/' . $file) :
                    unlink($directory . '/' . $file);
            }
        }
        if (readdir($handle) == false) {
            closedir($handle);
            is_file($directory) ? rmdir($directory) : null;
        }
    }
}
/**
 * 创建后台日志
 * @param null $remark
 * @return bool
 */
if (!function_exists('insert_admin_log')) {
    function insert_admin_log($remark)
    {
        if (Cookie::has('name')) {
            $table = get_indexes(1);
            if (!$table) {
                return false;
            }
            $id = set_log($table, [
                'admin_id' => Cookie::get('id'),
                'role_id' => Cookie::get('group_id'),
                'username' => Cookie::get('name'),
                'useragent' => request()->server('HTTP_USER_AGENT'),
                'ip' => request()->ip(),
                'url' => request()->url(true),
                'method' => request()->method(),
                'type' => request()->type(),
                'param' => json_encode(request()->param()),
                'remark' => $remark,
                'year' => date('Y', time()),
                'create_time' => time(),
            ], 1, ['user_id' => Cookie::get('id'), 'role_id' => Cookie::get('group_id')]);
            return [$table, $id];
        }
        return false;
    }
}

/**
 * 分表存储系统日志信息
 * 1.用户创建日志时，先去日志索引表中查询，是否存在可以插入的表，如果有，则直接插入，否则创建一张表用于存储
 * 2.用户日志索引表区分用户类型，分别为后台用户和客户端用户，每张表单独存储和记录相关数据
 * 3.这里是一个通用的日志流程方法，这里根据用户属性：type=>前后台用户
 */
//根据相关情况创建索引和记录表
if (!function_exists('get_indexes')) {
    function get_indexes($type = 1)
    {
        $data['type'] = $type;
        switch (intval($type)) {
            case 1:
                //此处为后台日志表
                $name = 'admin';
                break;
            case 2:
                //此处为前台日志表
                $name = 'user';
                break;
        }
        if (db("indexes_log")->where("num <" . config('rx.syslog')['max'])->where($data)->count() === 0) {
            //新增一条日志索引表记录并创建一张日志表
            if (db("indexes_log")->where($data)->count() > 0) {
                $t_num = intval(db("indexes_log")->where($data)->order("t_num desc")->value("t_num")) + 1;
            } else {
                $t_num = 1;
            }
            $data['name'] = $name . '_log' . $t_num;
            $data['t_num'] = $t_num;
            $data['create_time'] = time();
            $tableName = config('database.prefix') . $data['name'];
            db()->startTrans();
            try {
                db("indexes_log")->insert($data);
                switch (intval($type)) {
                    case 1:
                        //此处为后台日志表
                        $createTableSql = "
                        CREATE TABLE IF NOT EXISTS `{$tableName}` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `admin_id` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '管理员id',
                            `role_id` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '角色id',
                            `username` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员用户名',
                            `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
                            `ip` varchar(16) NOT NULL DEFAULT '' COMMENT 'ip地址',
                            `url` varchar(255) NOT NULL DEFAULT '' COMMENT '请求链接',
                            `method` varchar(32) NOT NULL DEFAULT '' COMMENT '请求类型',
                            `type` varchar(32) NOT NULL DEFAULT '' COMMENT '资源类型',
                            `param` text NOT NULL COMMENT '请求参数',
                            `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
                            `year` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建年份',
                            `create_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
                          PRIMARY KEY (`id`)
                        ) ENGINE = INNODB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员日志';
                        ";
                        break;
                    case 2:
                        //此处为前台日志表
                        $createTableSql = "
                        CREATE TABLE IF NOT EXISTS `{$tableName}` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `user_id` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '管理员id',
                            `role_id` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '角色id',
                            `username` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员用户名',
                            `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
                            `ip` varchar(16) NOT NULL DEFAULT '' COMMENT 'ip地址',
                            `url` varchar(255) NOT NULL DEFAULT '' COMMENT '请求链接',
                            `method` varchar(32) NOT NULL DEFAULT '' COMMENT '请求类型',
                            `type` varchar(32) NOT NULL DEFAULT '' COMMENT '资源类型',
                            `param` text NOT NULL COMMENT '请求参数',
                            `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
                            `year` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建年份',
                            `create_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
                            PRIMARY KEY (`id`)
                        ) ENGINE = INNODB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户日志';
                        ";
                        break;
                }
                db()->execute($createTableSql);
                db()->commit();
            } catch (\Exception $e) {
                db()->rollback();
                return false;
            }
            return $data['name'];
        } else {
            return db("indexes_log")->where("type = '{$type}'")->order("t_num desc")->value("name");
        }
    }
}

/**
 * 日志写入公共方法
 * 参数：table=>表名
 *      data=>写入数据
 *      type=>日志类型
 *      user=>用户信息
 * */
if (!function_exists('set_log')) {
    function set_log($table, $data, $type, $user)
    {
        $id = 0;
        db()->startTrans();
        try {
            $id = db($table)->insertGetId($data);
            $map['type'] = $type;
            $map['user_id'] = $user['user_id'];
            $map['role_id'] = $user['role_id'];
            $map['indexes'] = $table;
            $map['year'] = date('Y', time());
            //如果本条记录已存在，则更新记录数，否则创建一条新记录
            if (db("record_log")->where($map)->count() > 0) {
                db("record_log")->where($map)->setInc("num");
            } else {
                $map['num'] = 1;
                $map['create_time'] = time();
                db("record_log")->insert($map);
            }
            db("indexes_log")->where("type = '{$type}' and name = '{$table}'")->setInc('num');
            db()->commit();
        } catch (\Exception $e) {
            db()->rollback();
            // return false;
        }
        return $id;
        // return true;
    }
}

/**
 * 根据规则获取省市区联动
 * */
if (!function_exists('getRegion')) {
    function getRegion($pid = 0)
    {
        $result = [];
        $region = db("region")->where("pid", $pid)->where("status", 1)->field("id,name,level")->order("sort asc")->select();
        foreach ($region as $key => $val) {
            switch ($val['level']) {
                case '1':
                    //省
                    $result[$key] = [
                        'provinceCode' => $val['id'],
                        'provinceName' => $val['name']
                    ];
                    if (db("region")->where("pid", $val['id'])->where("status", 1)->count() > 0) {
                        $result[$key]['mallCityList'] = getRegion($val['id']);
                    }
                    break;
                case '2':
                    //市
                    $result[$key] = [
                        'cityCode' => $val['id'],
                        'cityName' => $val['name']
                    ];
                    if (db("region")->where("pid", $val['id'])->where("status", 1)->count() > 0) {
                        $result[$key]['mallAreaList'] = getRegion($val['id']);
                    }
                    break;
                case '3':
                    //区
                    $result[$key] = [
                        'areaCode' => $val['id'],
                        'areaName' => $val['name']
                    ];
                    break;
            }
        }
        return $result;
    }
}

/**
 * 获取当前域名及根路径
 * @return string 返回结果
 * @author 牧羊人
 * @date 2019/4/5
 */
if (!function_exists('base_url')) {
    function base_url()
    {
        static $base_url = '';
        if (empty($base_url)) {
            $subDir = str_replace('\\', '/', dirname(\request()->server('PHP_SELF')));
            $base_url = \request()->scheme() . '://' . \request()->host() . $subDir . ($subDir === '/' ? '' : '/');
        }
        return $base_url;
    }
}

/**
 * 将时间区间字符串转换成一维数组
 * */
if (!function_exists('betweenToArray')) {
    function betweenToArray($between = null, $format = 'Y年m月d日')
    {
        if (empty($between)) {
            return false;
        }
        $between_time = explode(" - ", $between);
        //格式化开始时间
        $start = date_parse_from_format($format, $between_time[0]);
        $start_time = mktime($start['hour'], $start['minute'], $start['second'], $start['month'], $start['day'], $start['year']);
        //格式化结束时间
        $end = date_parse_from_format($format, $between_time[1]);
        $end_time = mktime($end['hour'], $end['minute'], $end['second'], $end['month'], $end['day'], $end['year']);
        return [$start_time, $end_time];
    }
}

/**
 * 加密和解密
 * */
if (!function_exists('authcode')) {
    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;

        // 密匙
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}
if (!function_exists('logs')) {
    /**
     * 打印日志信息
     * @param array $content
     * @param string $title
     * @return bool
     */
    function logs($content = [], $title = '')
    {
        $title = empty($title) ? '执行日志' : trim($title);
        // 准备日志数据
        $startTime = date('Y-m-d H:i:s', time());
        $urlNode = trim(request()->module() . '/' . request()->controller() . '/' . request()->action());
        $logs = "\n================[{$title} : {$startTime}  BEGIN]================\n";
        $logs .= "node ： {$urlNode}\n";
        if (!empty($content) && is_array($content)) {
            $logs .= \think\facade\Debug::dump($content, false);
        } else {
            $logs .= "value ：" . (string)$content . "\n";
        }
        $endTime = date('Y-m-d H:i:s', time());
        $logs .= "\n================[{$title} : {$endTime}  END]================\n\n";
        // 开始写入日志文件
        $years = date('Ymd', time());
        $filename = dirname(realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/')) . "/runtime/{$years}.debug";
        return (boolean)file_put_contents($filename, $logs, FILE_APPEND | LOCK_EX);
    }
}
if (!function_exists('dict_type_value')) {
    // 获取数据字典指定类型的值
    function dict_type_value($key_name, $type)
    {
        return config('app.dict.' . $key_name)[$type];
    }
}
if (!function_exists('area_name')) {
    function area_name($id)
    {
        $info = db('organizational')->where("id = {$id}")->field('name')->find();
        return empty($info) ? '' : $info['name'];
    }
}
if (!function_exists('is_phone')) {
    /**
     * 手机号验证
     * @param string $phone
     * @return bool
     */
    function is_phone($phone = '')
    {
        //@2017-11-25 14:25:45 https://zhidao.baidu.com/question/1822455991691849548.html
        //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
        //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
        //中国电信号码：133、153、180、181、189、177、173、149、199
        $g = "/^1[34578]\d{9}$/";
        $g2 = "/^19[89]\d{8}$/";
        $g3 = "/^166\d{8}$/";
        if (preg_match($g, $phone)) {
            return true;
        } else if (preg_match($g2, $phone)) {
            return true;
        } else if (preg_match($g3, $phone)) {
            return true;
        }

        return false;
    }
}
if (!function_exists("is_tel")) {
    /**
     * 是否座机
     * @param string $tel
     * @return bool
     */
    function is_tel($tel = '')
    {
        $chars = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";
        if (preg_match($chars, $tel)) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists("is_email")) {
    function is_email($email)
    {
        $chars = "/^[0-9a-zA-Z]+(?:[\_\.\-][a-z0-9\-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+$/i";
        if (preg_match($chars, $email)) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists("is_idCard")) {
    /**
     * 身份证验证
     * @param string $idCard
     * @return bool
     */
    function is_idCard($idCard = "")
    {
        $idcard_len = strlen($idCard);
        if ($idCard == '' || !in_array($idcard_len, array(15, 18))) {
            return false;
        }
        $id = strtoupper($idCard);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if (!preg_match($regx, $id)) {
            return false;
        }
        if (15 == strlen($id)) {
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

            @preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            if (!strtotime($dtm_birth)) {
                return false;
            } else {
                return true;
            }
        } else {
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            if (!strtotime($dtm_birth)) {
                return false;
            } else
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign = 0;
            for ($i = 0; $i < 17; $i++) {
                $b = (int)$id[$i];
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id, 17, 1)) {
                return false;
            } //phpfensi.com
            else {
                return true;
            }
        }
    }
}
if (!function_exists('parse_attr')) {
    /**
     * 配置值解析成数组
     * @param string $value 配置值
     * @return array|string
     */
    function parse_attr($value)
    {
        if (is_array($value)) {
            return $value;
        }
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        logs(json_encode($value), 'value');
        return $value;
    }
}
/**
 * 获取本周所有日期
 */
function get_week($time = '', $format = 'Y-m-d')
{
    $time = $time != '' ? $time : time();
    //获取当前周几
    $week = date('w', $time);
    $week = intval($week) == 0 ? 7 : $week;
    $date = [];
    for ($i = 1; $i <= 7; $i++) {
        $date[$i]['date'] = date($format, strtotime($i - $week . ' days', $time));
        $date[$i]['current'] = 0;
        if ($date[$i]['date'] == date($format)) {
            $date[$i]['current'] = 1;
        }
    }
    return $date;

}

/**
 * 获取新闻分类
 * @return array|PDOStatement|string|\think\Collection
 */
function get_news_type()
{
    return \app\common\model\NewsType::getType();
}

/**
 * 获取当前登陆人所属区域，判断是否为总区管理员
 * @return bool
 */
function check_admin_area()
{
    $area_id = Cookie::has('area_id') ? Cookie::get('area_id') : 0;
    return !(!empty($area_id) && db('area')->where("id = {$area_id}")->value('p_id') != 0);
}

/**
 * 获取指定时间范围
 * @param string $date
 * @return array
 */
function getSomeTime($date = '')
{
    if (empty($date)) {
        return [strtotime(date('Y-m-d') . ' 00:00:00'), strtotime(date('Y-m-d') . ' 23:59:59')];
    }
    return [strtotime($date . ' 00:00:00'), strtotime($date . ' 23:59:59')];
}

function xtsz($id)
{
    return Db::name('system')->where('id', $id)->value('content');
}

function checkadmin()
{
    if (!Session::get('name')) {
        redirect('/admin/login/');
    }
}

function product_name($id)
{
    if ($id == 0) {
        $name = '顶级栏目';
    } else {
        $name = Db::name('menu')->where('id', $id)->value('name');
    }
    return $name;
}

function upload($config = [])
{
    $file = request()->file('file');
    $info = $file->validate(['ext' => implode(',', $config['exts']), 'size' => $config['maxSize']])->move($config['rootPath']);
    return $info;
}

function record_all_days($done_time = 0, $times = 1, $inteval_time = 0)
{
    $days = [];
    $start_day = $done_time;
    for ($i = 0; $i < $times; $i++) {
        array_push($days, $start_day);
        $start_day += ($inteval_time + 1) * 86400;
    }
    return $days;
}

//递归查询所有产品id树
function ProGroupId($tid, &$result = array())
{
    // 创建storeid
    array_push($result, (int)$tid);
    $pids = db('menu')->where('pid', $tid)->field('id')->select();
    if (count($pids) > 0) {
        $result = getMenuIdsByPids($pids, $result);
    }
    return $result;
}

function getMenuIdsByPids($pids = [], &$result = [])
{
    if (count($pids) > 0) {
        $idsArr = array_column($pids, 'id');
        $where[] = ['pid', 'in', implode(',', $idsArr)];
        $pids = db('menu')->where($where)->field('id')->select();
        $result[] = array_merge(getMenuIdsByPids($pids, $result), $idsArr);
        $res = [];
        array_walk_recursive($result, function ($value) use (&$res) {
            array_push($res, $value);
        });
        return array_values(array_unique($res));
    }
    return [];
}

function getClassBySchoolId($school_id = 0)
{
    $s_id = str_replace('s', '', $school_id);
    $class_group = db('class_group')->where("school_id = {$s_id} and state = 1")->field("id,name title,'{$school_id}' parentId,2 type")->select();
    foreach ($class_group as $key => $item) {
        $class_group[$key]['checkArr'] = array('type' => 0, 'isChecked' => 0);
    }
    return empty($class_group) ? [] : $class_group;
}

function getSchoolAndClassesByAreaId($area_id = 0)
{
    $schools = db('school')->where("area_id = {$area_id} and state = 1")->field("concat('s',id) id,name title,{$area_id} parentId,1 type")->select();
    foreach ($schools as $key => $item) {
        $schools[$key]['checkArr'] = array('type' => 0, 'isChecked' => 0);
        $schools[$key]['children'] = getClassBySchoolId($item['id']);
    }
    return empty($schools) ? [] : $schools;
}

/**
 * 获取学校
 * @param int $area_id
 * @return array|PDOStatement|string|\think\Collection
 */
function getSchoolsByAreaId($area_id = 0)
{
    $schools = db('school')->where("area_id = {$area_id} and state = 1")->field("concat('s',id) id,name title,{$area_id} parentId,1 type")->select();
    foreach ($schools as $key => $item) {
        $schools[$key]['checkArr'] = array('type' => 0, 'isChecked' => 0);
    }
    return empty($schools) ? [] : $schools;
}

/**
 * 获取基地
 * @param int $area_id
 * @return array|PDOStatement|string|\think\Collection
 */
function getBasesByAreaId($area_id = 0)
{
    $schools = db('base')->where("area_id = {$area_id} and state = 1")->field("concat('b',id) id,name title,{$area_id} parentId,1 type")->select();
    foreach ($schools as $key => $item) {
        $schools[$key]['checkArr'] = array('type' => 0, 'isChecked' => 0);
    }
    return empty($schools) ? [] : $schools;
}

/**
 * 获取学校
 * @param int $area_id
 * @return array|PDOStatement|string|\think\Collection
 */
function getSchoolByAreaId($area_id = 0)
{
    $schools = db('school')->where("area_id = {$area_id} and state = 1")->field("concat('b',id) id,id school_id,name title,{$area_id} parentId,1 type")->select();
    foreach ($schools as $key => $item) {
        $schools[$key]['checkArr'] = array('type' => 0, 'isChecked' => 0);
    }
    return empty($schools) ? [] : $schools;
}

function getUidsByAreaId($area_id = 0, $type = 0)
{
    if ($type == 1) {
        // 区
        $where = "cg.area_id = {$area_id}";
    } elseif ($type == 2) {
        // 市
        $where = "cg.city_id = {$area_id}";
    } else {
        // 省
        $where = "cg.province_id = {$area_id}";
    }
    $list = db('user_class_log')
        ->alias('ucl')
        ->join('class_group cg', 'cg.id = class_group_id and cg.state = 1')
        ->where($where)
        ->field('ucl.u_id')
        ->select();
    return array_column($list, 'u_id');
}

function getUidsBySchoolId($school_id = 0)
{
    $where = "cg.school_id = {$school_id}";
    $list = db('user_class_log')
        ->alias('ucl')
        ->join('class_group cg', 'cg.id = class_group_id and cg.state = 1')
        ->where($where)
        ->field('ucl.u_id')
        ->select();
    return array_column($list, 'u_id');
}

function getUidsByClassId($class_id = 0)
{
    $list = db('user_class_log')->where("class_group_id = {$class_id}")->field('u_id')->select();
    return array_column($list, 'u_id');
}

function getUsersBySome($where = [])
{
    $list = db('user')
        ->where($where)
        ->select();
    return $list;
}

if (!function_exists('get_all_child')) {
    /**
     * 递归获取所有子id
     * @param $array
     * @param $id
     * @return array
     */
    function get_all_child($array, $id)
    {
        $arr = array();
        foreach ($array as $v) {
            if ($v['pid'] == $id) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, get_all_child($array, $v['id']));
            };
        };
        return $arr;
    }
}

function searchData($list, $key, $value)
{
    $arr = [];
    foreach ($list as $item) {
        if ($item[$key] == $value) {
            array_push($arr, $item);
        }
    }
    return $arr;
}

/**
 * 返回指定表的指定键值
 * @param string $table
 * @param int $id
 * @param string $key_index
 * @return int|mixed
 */
function getKeyValueById($table = '', $id = 0, $key_index = 'name')
{
    $info = db($table)->where('id', $id)->find();
    return empty($info) ? 0 : $info[$key_index];
}

function groupTeachers($type = 0, $selectedId = 0)
{
    $html = '<option value="">请选择</option>';
    if ($type === 0) {
        // 评委老师
        $list = db('admin')->where('group_id', config('sys_data.teacher_group'))->field('id,`name`,phone')->select();
        foreach ($list as $key => $item) {
            $html .= '<option value="' . $item['id'] . '">' . $item['name'] . "({$item['phone']})" . '</option>';
        }
        return $html;
    }
    $list = db('teacher')->where('type', $type)->where('status', 1)->select();
    $key_index = $type == 1 ? 'school_id' : 'base_id';
    $table = $type == 1 ? 'school' : 'base';
    $key_ids = array_unique(array_column($list, $key_index));
    foreach ($key_ids as $item) {
        $html .= '<optgroup label="' . getKeyValueById($table, $item) . '">';
        $child = searchData($list, $key_index, $item);
        foreach ($child as $ch) {
            $html .= '<option value="' . $ch['id'] . '" ' . ($selectedId > 0 ? 'selected' : '') . '>' . $ch['username'] . "({$ch['phone']})" . '</option>';
        }
        $html .= '</optgroup>';
    }
    return $html;
}

function retHtmlMedia($src = '')
{
    $ext_imgs = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'tiff', 'swf', 'svg', 'pcx', 'dxf', 'emf', 'tga', 'bmp', 'lic'];
    $ext_video = ['mp4', '3gp', 'wmv', 'asf', 'asx', 'rm', 'rmvb', 'mov', 'm4v', 'avi', 'dat', 'mkv', 'flv', 'vob'];
    $ext_audio = ['wav', 'flac', 'ape', 'alac', 'wv', 'mp3', 'aac', 'ogg'];
    if (is_array($src)) {
        $html = "<div class='layui-file baguetteBox'>";
        foreach ($src as $item) {
            $type = strtolower(substr($item, strrpos($item, ".") + 1));
            if (in_array($type, $ext_imgs)) {
                $html .= "<div class='item-list item-image'><a href='{$item}'><img class='layui-upload-img' src='{$item}'></a></div>";
            } else if (in_array($type, $ext_video) || in_array($type, $ext_audio)) {
                $html .= "<div class='item-list item-video'><video class='layui-upload-img' controls='controls' src='{$item}'>当前浏览器版本过低</video></div>";
            } else {
                $html .= "<div class='item item-desc layui-form-item layui-form-text'><label class='layui-form-label'>回答的文字内容</label><div class='quest-con-c layui-input-block'>{$src}</div></div>";
            }
        }
        $html .= "</div>";
        return $html;
    } else {
        $type = strtolower(substr($src, strrpos($src, ".") + 1));
        if (in_array($type, $ext_imgs)) {
            return "<img class='layui-upload-img' src='{$src}'>";
        } else if (in_array($type, $ext_video) || in_array($type, $ext_audio)) {
            return "<video class='layui-upload-img' controls='controls' src='{$src}'>当前浏览器版本过低</video>";
        } else {
            return "<div class='item item-desc layui-form-item layui-form-text'><label class='layui-form-label'>回答的文字内容</label><div class='quest-con-c layui-input-block'>{$src}</div></div>";
        }
    }
}

function retAnswerHtml($item = [], $key = '')
{
    $chooseLabel = $key;
    if (empty($item['text']) && empty($item['image'])) {
        return '';
    }
    $imgHtml = "";
    if (!empty($item['image'])) {
        if ($item['file_type'] == 'image') {
            $imgHtml = "<a href='{$item['image']}'><img src='{$item['image']}' width='500px' height='auto' /></a>";
        }
    }
    return "<div class='layui-form-item'><label class='layui-form-label'>{$chooseLabel}</label><div class='quest-con-c layui-input-block'><div class='title'>{$item['text']}</div><div class='item-list item-image'>{$imgHtml}</div></div></div>";
}

function get_course_index_id($sub_id = 0)
{
    $info = db('course_index')->where("id", $sub_id)->field('pid')->find();
    return empty($info) ? 0 : $info['pid'];
}

/**
 * 微信小程序进行网络
 * @param string $table
 * @param int $id
 * @param string $key_index
 * @return int|mixed
 */
function https_getRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $jsonData = json_decode($output, true);
    return $jsonData;
}

function httpRequest($url, $data='', $method='GET'){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    if($method=='POST')
    {
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data != '')
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

?>
<?php
namespace app\ajax\controller;

use think\App;
use think\Controller;
use think\Db;

class Wxpay extends Controller
{
    public function index()
    {
        $data=$this->request->param();
        //$status = $this->notify_url($data);
        //return json($status);
        $appid = 'wx5c673046a8374237';//你的appId
        $attach = '支付成功';
        $body = '小程序支付';
        $mch_id = '1619308171';//你的商户号
        $nonce_str = '5K8264ILTKCH16CQ2502SI8ZNMTM67VS';//32位随机字符串（字母大写）
        $notify_url = 'https://pay.szjfsy.com.cn/ajax/wxpay/notify_url/';//回调通知地址
        $out_trade_no = $data['ddh'];//订单号
        $spbill_create_ip = $this -> get_client_ip();//客户IP
        $total_fee =$data['total']*100;
        $trade_type = 'JSAPI';
        $key ='IuFuWppxhpL5fHbIRCW5HcAbD3AxWVDn';//你的key
        $openid=$data['openid'];
        //签名算法
        $wechat_sign ="appid=$appid&attach=$attach&body=$body&mch_id=$mch_id&nonce_str=$nonce_str&notify_url=$notify_url&openid=$openid&out_trade_no=$out_trade_no&spbill_create_ip=$spbill_create_ip&total_fee=$total_fee&trade_type=$trade_type&key=$key";
        $sign = strtoupper(MD5($wechat_sign));
        $param ="<xml>
			   <appid>$appid</appid>
			   <attach>$attach</attach>
			   <body>$body</body>
			   <mch_id>$mch_id</mch_id>
			   <nonce_str>$nonce_str</nonce_str>
			   <notify_url>$notify_url</notify_url>
			   <openid>$openid</openid>
			   <out_trade_no>$out_trade_no</out_trade_no>
			   <spbill_create_ip>$spbill_create_ip</spbill_create_ip>
			   <total_fee>$total_fee</total_fee>
			   <trade_type>$trade_type</trade_type>
			   <sign>$sign</sign>
		  </xml>";

        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data= $this -> HttpPost($url,$param);
        //return json($data);
        $ob= simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA);//将字符串转化为变量
        $json = json_encode($ob);//将对象转化为JSON格式的字符串
        $configData = json_decode($json, true);//将JSON格式的字符串转化为数组
        //print_r( $configData);
        $prepay_id=$configData['prepay_id'];

        $shijian=time();
        $paysign="appId=$appid&nonceStr=$nonce_str&package=prepay_id=$prepay_id&signType=MD5&timeStamp=$shijian&key=$key";
        $signok = strtoupper(MD5($paysign));
        $package = "prepay_id=$prepay_id";
        $dataarr = [
            'timeStamp' =>  $shijian,
            'nonceStr' =>  $nonce_str,
            'package' =>  $package,
            'signType' =>  'MD5',
            'paySign' =>  $signok,
        ];
        return json($dataarr);
    }

    public function HttpPost($url,$param){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function get_client_ip() {
        $onlineip='';
        if(getenv('HTTP_CLIENT_IP')&&strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')){
            $onlineip=getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')&&strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')){
            $onlineip=getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'),'unknown')){
            $onlineip=getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR'])&&$_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')){
            $onlineip=$_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }

    //回调
    public function notify_url(){
        $data=$this->request->param();
        $res= db('orderlist')->where('ddh',$data['ddh'])->where(['state'=>1])->find();
        $updatetime = mktime(0,0,0,date('m'),date('d'),date('Y')+1);
        if($res['state']==1){
            $r = db('orderlist')->where('ddh',$res['ddh'])
                ->data(['state'=>2,'addressid'=>$res['addressid'],'msg'=>$res['msg'],'state'=>2,'updatetime'=>time()])
                ->update();
            $reid = db('user')->where('id',$res['userid'])->value('reid');
            $bili = db('hygroup')->where(['id'=>userinfo($reid)['hygroup']])->value('percentage');
            $total = (int)($bili*$data['total']/100);
            if($reid!=0){
                //添加平台消费流水记录
                jeliushui($reid,$total,'add',1,'会员商城购物，获得下级返现金额',$data['ddh']);
                jeliushui($res['userid'],$data['total'],'reduce',1,'会员商城购物，消费金额',$data['ddh']);
                //会员追加待返现余额
                db('user')->where('id', $reid)->setInc('score', $total);
                return 1;
            }else{
                return 2;
            }
        }
    }

    public function zhifumsg(){
        $now_time = date('Y-m-d H:i:s',time());
        zhifu_msg('ok_cu5IP2CkIGTdSJoaV7Y69ih08','哈哈',2021061320305,'某某产品','1980.00',$now_time);
        chouceng_msg('ok_cu5IP2CkIGTdSJoaV7Y69ih08','直推返利','700.00',$now_time,'北辰');
        chouceng_msg('ok_cu5IP2CkIGTdSJoaV7Y69ih08','转介绍返利','280.00',$now_time,'北辰');
    }




    protected static function formatQueryParaMap($paraMap, $urlEncode = false) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    public function curlPost($url = '', $postData = '', $options = array()) {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, "zhengshu/apiclient_cert.pem");//绝对路径
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, "zhengshu/apiclient_key.pem");//绝对路径
        //第二种方式，两个文件合成一个.pem文件
//    curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            curl_close($ch);
            return false;
        }
    }
    public function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}

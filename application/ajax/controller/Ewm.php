<?php
namespace app\ajax\controller;

use think\App;
use think\Controller;
use think\Db;
use think\facade\Log;

class Ewm extends Controller
{
    protected $aaa=array();
    public function index()
    {
        return 11;
    }

    public function my_code()
    {
        $list=($this->request->param());
        $ewm=$this->ewm($list['userid'],'pages/index/index','../public/upload/code/');
        return json(['ewm'=>$ewm]);
    }
    public function jdewm()
    {
        $list=($this->request->param());
        $ewm=$this->ewm($list['id'],'pages/home/index','../longjue/image/jdewm/');
        return json(['ewm'=>$ewm]);
    }
    function ewm($canshu,$page='',$path){
        //$page='pages/index/sign_gzz';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx32d11f1898c690d6&secret=f0f5285f163324b11d3b2c0cb09ff8ac";
        $resultData = https_getRequest($url);
        $token = $resultData['access_token'];
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
        $data = ['scene' =>$canshu,'width'=>350,'page'=>$page];
        $res = httpRequest( $url, json_encode($data),"POST");
        $res1="data:image/jpeg;base64,".base64_encode($res);
        $wdewm = $this->get_base64_img($res1,$path);
        return $wdewm;
    }


    /**
     * base64转码图片
     * @param $base64
     * @param string $path
     * @return bool|string
     */
    function get_base64_img($base64,$path = ''){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
            $type = $result[2];
            $new_file = $path.time().".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))){
                $new_file = str_replace("../public/","/",$new_file);
                return xtsz(1).$new_file;
            }else{
                return false;
            }
        }
    }

}

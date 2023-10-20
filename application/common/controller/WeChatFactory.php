<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : WeChatFactory.php
     *  Create Date : 2022/1/5 17:10
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\common\controller;
    use EasyWeChat\Factory;
    use Psr\Container\ContainerInterface;
    use think\Controller;
    use think\facade\Env;

    class WeChatFactory extends BaseController {

        protected $app;
        protected $min_app;
        protected $config;

        public function initialize(){
            parent::initialize();
            $this->config = [
              'app_id' => config('wx.min_program.app_id'),
              'secret' => config('wx.min_program.app_secret'),
              'response_type' => 'array',
              'log' => [
                  'level' => 'debug',
                  'file' => Env::get('root_path').'/public/logs/wx.log'
              ]
            ];
            $this->min_app = Factory::miniProgram($this->config);
        }

        /**
         * http_curl
         * @param        $url
         * @param string $type
         * @param string $res
         * @param string $arr
         * @return mixed
         */
        public function http_curl($url,$type='get',$res='json',$arr=''){
            //1.初始化curl
            $ch = curl_init();
            //2.设置curl的参数
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if ($type == 'post') {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
            }
            //3.采集
            $output = curl_exec($ch);
            //4.关闭
            curl_close($ch);
            if ($res == 'json') {
                return json_decode($output,true);
            }
        }

        public function min_login(){
            $code = $this->request->param('code','');
            if(!$code){
                return ['code'=>0,'msg'=>'缺少必要参数code'];
            }
            $result =  $this->min_app->auth->session($code);
            // ["session_key"] =&gt; string(24) "gDTTTUd7r4FILmaVjGzpbg=="
            // ["openid"] =&gt; string(28) "oZCjl5Q3fPFCTE1RIU7v73_qE74E"
            if(!$result){
                return ['code'=>0,'msg'=>'获取sessionKey和openId异常'];
            }
            if(!empty($result['errcode']) && $result['errcode'] != 0){
                return ['code'=>$result['errcode'],'msg'=>$result['errmsg']];
            }
            return ['code'=>1,'msg'=>'获取信息成功','data'=>$result];
        }

    }
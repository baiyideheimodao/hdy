<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Upload.php
     *  Create Date : 2022/1/25 9:40
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */

    namespace app\admin\controller;
    use app\common\controller\AdminController;
    use think\facade\Env;

    class Upload extends AdminController {

        public function upload(){
            $files = request()->file('file');
            $baseUrl = DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR;
            $path = Env::get('root_path') . DIRECTORY_SEPARATOR. 'public' . $baseUrl;
            $info = $files->move($path);
            if($info){
                $url = str_replace('\\', '/', $baseUrl . $info->getSaveName());
                // 此处为迎合layuiedit图片显示，成功code返回0
                return $this->returnJson(['src'=>$url,'title'=>''],'success');
            }else{
                return $this->returnJson([],'error',1);
            }
        }
    }
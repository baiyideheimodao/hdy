<?php
    namespace app\admin\controller;

    use think\Controller;
    use PHPExcel_IOFactory;
    use PHPExcel;
    use think\Db;
    use think\facade\Env;
    use PHPExcel_Cell;

    class Execl extends Controller {
        public function ABC2decimal($abc) {
            $ten = 0;
            $len = strlen($abc);
            for ($i = 1; $i <= $len; $i++) {
                $char = substr($abc, 0 - $i, 1);//反向获取单个字符
                $int = ord($char);
                $ten += ($int - 65) * pow(26, $i - 1);
            }
            return $ten;
        }

        public function onlyosn() {
            @date_default_timezone_set("PRC");
            $order_id_main = date('YmdHis') . rand(10000000, 99999999);
            //订单号码主体长度
            $order_id_len = strlen($order_id_main);
            $order_id_sum = 0;
            for ($i = 0; $i < $order_id_len; $i++) {
                $order_id_sum += (int)(substr($order_id_main, $i, 1));
            }
            $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT); //生成唯一订单号
            return $osn;
        }

        public function index() {
            $time = time();
            //获取上传文件（此处应判断文件是否合法，可自行添加判断条件）
            $imgfile = request()->file('file');
            //生成路径和文件（路径可自定义，TP5.0版本可直接使用常量ROOT_PATH和DS）
            $imgpath = Env::get('root_path') . DIRECTORY_SEPARATOR . 'upload/execlfile/' . $time . '.xls';
            $imgfile->move(Env::get('root_path') . DIRECTORY_SEPARATOR . 'upload/execlfile/', $time . '.xls', true);
            //读取并返回数据
            $inputFileName = $imgpath;//读取的excel文件
            date_default_timezone_set('PRC');
            // 读取excel文件
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader     = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel   = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('加载文件发生错误："' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $sheet = $objPHPExcel->getSheet(0);
            $exceldata     = $sheet->toArray();//该方法读取不到图片 图片需单独处理
            $imageFilePath = '../public/upload/upFile/image/';//图片在本地存储的路径
            if (!file_exists($imageFilePath)) {
                mkdir("$imageFilePath", 0777, true);
            }
            $data = [];
            //处理表格中空白的列，只保留到H
            foreach ($exceldata as $key => $value) {
                if ($key != 0) {
                    $data[] = array_slice($value, 0, 11);
                }
            }
            //处理图片
            //        foreach($sheet->getDrawingCollection() as $img) {
            //            list($startColumn,$startRow)= PHPExcel_Cell::coordinateFromString($img->getCoordinates());//获取图片所在行和列
            //
            //            $imageFileName = $this -> onlyosn();
            //
            //            switch($img->getMimeType()) {
            //                case 'image/jpeg':
            //                    $imageFileName.='.jpg';
            //                    imagejpeg($img->getImageResource(),$imageFilePath.$imageFileName);
            //                    break;
            //                case 'image/jpg':
            //                    $imageFileName.='.jpg';
            //                    imagejpeg($img->getImageResource(),$imageFilePath.$imageFileName);
            //                    break;
            //                case 'image/gif':
            //                    $imageFileName.='.gif';
            //                    imagegif($img->getImageResource(),$imageFilePath.$imageFileName);
            //                    break;
            //                case 'image/png':
            //                    $imageFileName.='.png';
            //                    imagepng($img->getImageResource(),$imageFilePath.$imageFileName);
            //                    break;
            //            }
            //            $startColumn = $this -> ABC2decimal($startColumn);//由于图片所在位置的列号为字母，转化为数字
            //            $data[$startRow-1][$startColumn]=$data[$startRow-1][$startColumn].'<img src="https://xcx.ykjnet.cn/shijuan/public/'.$imageFilePath.$imageFileName.'">';//把图片插入到数组中
            //
            //        }
            $arr = [];
            array_filter($data);
            $num = count($data);
            // todo::导入顾客限制在100个以内
            if ($num > 100) {

                return json(['message' => '导入错误【导入条数过多】','code'=>0]);
            }
            //结束
            $errArr = []; // 错误集合
            for ($i = 0; $i < $num; $i++) {
                if ($data[$i][0]) {
                    $xvhao = $data[$i][0];
                    $phone = $data[$i][5];
                    $sfz   = $data[$i][7];
                    if (!is_phone($phone)) {
                        array_push($errArr, ["序号【{$xvhao}】手机号不合法"]);
                        continue;
                    }
                    if (!is_idCard($sfz)) {
                        array_push($errArr, ["序号【{$xvhao}】身份证号码不合法"]);
                        continue;
                    }
                    $arr[$i]['xvhao']        = $xvhao;
                    $arr[$i]['name']         = $data[$i][1];
                    $arr[$i]['spouse']       = $data[$i][2];
                    $arr[$i]['sex']          = $data[$i][3];
                    $arr[$i]['age']          = $data[$i][4];
                    $arr[$i]['phone']        = $phone;
                    $arr[$i]['address']      = empty($data[$i][6])?'':$data[$i][6];
                    $arr[$i]['sfz']          = $sfz;
                    $arr[$i]['staffAccount'] = $data[$i][8];
                    if ($data[$i][9] == '储备') {
                        $type = 1;
                    } else if ($data[$i][9] == '活跃') {
                        $type = 2;
                    } else if ($data[$i][9] == '休眠') {
                        $type = 3;
                    } else if ($data[$i][9] == '未转化') {
                        $type = 4;
                    }
                    $arr[$i]['type']       = $type;
                    $arr[$i]['status']       = 1; // 导入即通过审核
                    $arr[$i]['remarks']    = $data[$i][10];
                    $arr[$i]['creatime']   = $time;
                    $arr[$i]['updatetime'] = $time;
                }
            }
            if(!empty($arr)){
                Db::name('customerlist')->insertAll($arr);
                return json(['message'=>'导入成功','errArr'=>$errArr,'code'=>200]);
            }
            return json(['message'=>'导入失败','errArr'=>$errArr,'code'=>0]);
        }
    }
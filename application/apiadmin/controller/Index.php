<?php
    /**
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Index.php
     *  Create Date : 2022/7/20 11:34
     *  Version : 0.1
     *  Copyright : skylong Project Team Copyright (C)
     *  license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
     */
    namespace app\apiadmin\controller;
    use app\common\controller\BaseController;

    class Index extends BaseController {

        private $url = '';

        public function initialize() {
            parent::initialize(); // TODO: Change the autogenerated stub
            $this->url = config('sys_data.web_site').'/api/';
        }

        public function file_get_contents_post($url = '', $post_data = array()) {
            $options = [
              'http' => [
                  'method' => 'POST',
                  'content' => http_build_query($post_data)
              ]
            ];
            $result = file_get_contents($url,false,stream_context_create($options));
            return $result;
        }

        public function curl_post($url = '',$post = []){
            $options = [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HEADER => false,
              CURLOPT_POST =>true,
              CURLOPT_POSTFIELDS => http_build_query($post)
            ];
            $ch = curl_init($url);
            curl_setopt_array($ch,$options);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }

        public function index(){
            $url = $this->url.'v2.task/sub_answer';
            $postData = [];
            $postData['task_log_id'] = 68;
            $postData['truth_num'] = 0;
            $postData['obj_num'] = 0;
            $postData['total_num'] = 1;
            $sub_ans = [];
            $sub_ans[] = [
                'id' => 132,
                'answer' => [
                    'l6bhy4xd' => 14,
                    'l6bhy98x' => 12,
                    'lutxswlqw' => 11,
                ]
            ];
            // $sub_ans[] = [
            //     'id' => 109,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 110,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 111,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 112,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 113,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 114,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 115,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 116,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 117,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            // $sub_ans[] = [
            //     'id' => 115,
            //     'answer' => [
            //         'text' => '好几个哈机房环境萨芬和技术卡的符合贷款'
            //     ]
            // ];
            $obj_ans = [];
            // $obj_ans[] = [
            //     'id' => 118,
            //     'answer' => [
            //         'text' => 'A'
            //     ]
            // ];
            // $obj_ans[] = [
            //     'id' => 119,
            //     'answer' => [
            //         'text' => 'B'
            //     ]
            // ];
            // $obj_ans[] = [
            //     'id' => 120,
            //     'answer' => [
            //         'text' => 'B'
            //     ]
            // ];
            $sub_files = [];
            // $sub_files[] = [
            //     'id' => 121,
            //     'image' => '/upload/question_image/20220423/b8bf91c329ee9838b05e2cef0a70d53b.png'
            // ];
            // $sub_files[] = [
            //     'id' => 73,
            //     'image' => '/upload/question_image/20220423/b8bf91c329ee9838b05e2cef0a70d53b.png'
            // ];
            // $sub_files[] = [
            //     'id' => 68,
            //     'image' => '/upload/question_image/20220423/b8bf91c329ee9838b05e2cef0a70d53b.png'
            // ];
            $postData['sub_ans'] = $sub_ans;
            $postData['obj_ans'] = $obj_ans;
            // $postData['sub_files'] = $sub_files;
            $postData['status'] = 1;
            $postData['openid'] = 'o7-oR1b_BNnQziPt7RBzk_fptL1s';

            $res = $this->curl_post($url,$postData);
            return $res;
            dump($res);exit();

        }

        public function markUser(){
            $url = $this->url.'v2.teacher/markUser';
            $postData = [];
            $postData['id'] = 79;
            $postData['openid'] = 'o7-oR1b_BNnQziPt7RBzk_fptL1s';
            $postData['type'] = 2;
            $userScores = [
                [
                    'user_id' => 21,
                    'score' => 3,
                    'user_type_task_id' => 24
                ]
            ];
            $postData['userScores'] = $userScores;
            $userMarks = [
                '21' => [
                    [
                        'score' => 3,
                        'user_type_task_id' => 24,
                        'question_id' => 28
                    ],
                    // [
                    //     'score' => 2,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 110
                    // ],
                    // [
                    //     'score' => 1,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 111
                    // ],
                    // [
                    //     'score' => 2,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 112
                    // ],
                    // [
                    //     'score' => 2,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 113
                    // ],
                    // [
                    //     'score' => 3,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 114
                    // ],
                    // [
                    //     'score' => 3,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 115
                    // ],
                    // [
                    //     'score' => 3,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 116
                    // ],
                    // [
                    //     'score' => 3,
                    //     'user_type_task_id' => 24,
                    //     'question_id' => 117
                    // ],
                ]
            ];
            $postData['userMarks'] = $userMarks;
            $res = $this->curl_post($url,$postData);
            dump($res);exit();
        }

        public function batchRecordUser(){
            $url = $this->url.'v2.teacher/batchRecordUser';
            $postData = [];
            $postData['type_task_id'] = 78;
            $postData['openid'] = 'o7-oR1b_BNnQziPt7RBzk_fptL1s';
            $postData['type'] = 2;
            $postData['user_ids'] = [
                21
            ];
            $res = $this->curl_post($url,$postData);
            dump($res);exit();
        }

        public function evalUsers(){
            $url = $this->url.'v2.teacher/evalUsers';
            $postData = [];
            $postData['id'] = 76;
            $postData['openid'] = 'o7-oR1b_BNnQziPt7RBzk_fptL1s';
            $postData['type'] = 1;
            $userScores = [
                [
                    'user_id' => 21,
                    'score' => 2
                ]
            ];
            $postData['userScores'] = $userScores;
            $postData['upt_word'] = '尽快哈后来的撒十分了解科室的发货记录的';
            $postData['upt_media'] = 'https://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83erjhaSibtQzfk0Pvff8TvkxtIUx9lrVFJBehkwsYp5kF1e91keHbqBIaBtGfiaZo9grJAvPnCWgkFRQ/132';
            $res = $this->curl_post($url,$postData);
            dump($res);exit();
        }

        public function uptUsers(){
            $url = $this->url.'v2.teacher/uptUsers';
            $postData = [];
            $postData['id'] = 79;
            $postData['openid'] = 'o7-oR1b_BNnQziPt7RBzk_fptL1s';
            $postData['type'] = 2;
            $postData['user_ids'] = [
                21
            ];
            $postData['upt_media'] = 'https://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83erjhaSibtQzfk0Pvff8TvkxtIUx9lrVFJBehkwsYp5kF1e91keHbqBIaBtGfiaZo9grJAvPnCWgkFRQ/132';
            $res = $this->curl_post($url,$postData);
            dump($res);exit();
        }

        public function testTalculateCredits(){
            dump($this->calculateCredits(24));
        }
    }
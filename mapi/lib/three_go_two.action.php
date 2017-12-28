<?php

//三步走接口第二步---设置交易密码

    class three_go_two{

        public function index(){
            $root = get_baseroot();
            $user = $GLOBALS['user_info'];
            $root['session_id'] = es_session::id();
            $pay_pwd = strim(base64_decode($GLOBALS['request']['pay_pwd'])); //交易密码
            $confirm_pay_pwd = strim(base64_decode($GLOBALS['request']['pay_pwd_confirm'])); //确认交易密码
            if($user['id']>0){
                if(empty($pay_pwd) || empty($confirm_pay_pwd)){
                    $root['response_code'] = 0;
                    $root['show_err'] = '密码或确认密码不能为空';
                    output($root);
                }
                if($pay_pwd != $confirm_pay_pwd){
                    $root['response_code'] = 0;
                    $root['show_err'] = '密码不一致，请重新输入';
                    output($root);
                }
                $user_pwd = $GLOBALS['db']->getOne("select user_pwd from ".DB_PREFIX."user where id=".$user['id']);
                if($user_pwd ==$pay_pwd){
                    $root['response_code'] = 0;
                    $root['show_err'] = '交易密码不能和登录密码相同。';
                    output($root);
                }

                $re = $GLOBALS['db']->autoExecute(DB_PREFIX."user",array("paypassword"=>$pay_pwd),"UPDATE","id=".$user['id']);
                if($re){
                    $root['response_code'] = 1;
                    $root['three_go_code'] = 3;
                    $root['show_err'] = '设置成功';
                    output($root);
                }else{
                    $root['response_code'] = 0;
                    $root['show_err'] = '设置失败';
                    output($root);
                }
                
            }else{
                $root['response_code'] = 0;
                $root['show_err'] = '请先登录';
                output($root);
            }
        }
    }
?>
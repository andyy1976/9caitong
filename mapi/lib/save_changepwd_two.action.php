<?php

//修改登录密码,交易密码 第二步步接口
class save_changepwd_two{

    public function index(){
        $mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['email'])))); //手机号码
        $old_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['pwd'])))); //原密码
        $new_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['new_pwd'])))); //新密码
        $new_pwd_confirm = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['new_pwd_confirm'])))); //确认新密码
        $type = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['type'])))); //修改密码类型密码
        $root = get_baseroot();
        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $user_pwd = $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user WHERE id='".$user['id']."'");
        $user_pay_pwd = $GLOBALS['db']->getOne("SELECT paypassword FROM ".DB_PREFIX."user WHERE id='".$user['id']."'");
        if($user['id']>0){
            //比对原密码是否正确
            if(empty($type)){

                if($old_pwd != $user_pwd){
                    $root['response_code'] = 0;
                    $root['show_err'] = "原密码错误";
                    output($root);
                }
                if($new_pwd == $user_pay_pwd){
                    $root['response_code'] = 0;
                    $root['show_err'] = "新密码不能和交易密码一样";
                    output($root);
                }
            }else{

                if($old_pwd != $user_pay_pwd){
                    $root['response_code'] = 0;
                    $root['show_err'] = "原始交易密码错误";
                    output($root);
                }
                if($new_pwd == $user_pwd){
                    $root['response_code'] = 0;
                    $root['show_err'] = "交易密码不能和登录密码相同。";
                    output($root);
                }
            }

            //比对手机是否正确
            $phone = $GLOBALS['db']->getOne("select AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."')  from ".DB_PREFIX."user where id=".$user['id']);
            if($mobile != $phone){
                $root['response_code'] = 0;
                $root['show_err'] = "手机号码错误";
                output($root);
            }
            if($old_pwd == $new_pwd){
                $root['response_code'] = 0;
                $root['show_err'] = "新密码不能和原密码相同";
                output($root);
            }
            if($new_pwd != $new_pwd_confirm){
                $root['response_code'] = 0;
                $root['show_err'] = "两次输入密码不一致";
                output($root);
            }

            if(empty($type)){
                $GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd='".$new_pwd."' where id = ".$user['id']);
                $root['response_code'] = 1;
                $root['show_err'] = "修改成功,请重新登录";
            }else{
                $GLOBALS['db']->query("update ".DB_PREFIX."user set paypassword='".$new_pwd."' where id = ".$user['id']);
                $root['response_code'] = 1;
                $root['show_err'] = "密码修改成功!";
            }


            if(es_session::is_set('send_changepwd_code_num'))
                es_session::delete('send_changepwd_code_num'); //成功后删除修改密码时的获取短信验证码的次数
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }

    }
}
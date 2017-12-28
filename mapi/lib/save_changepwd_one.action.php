<?php

//修改登录密码,交易密码 第一步接口
class save_changepwd_one{

    public function index(){
        $mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['email'])))); //手机号码
        $mobile_code = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile_code'])))); //图形验证码
        $old_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['pwd'])))); //原密码
        $type = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['type'])))); //修改密码类型
        $root = get_baseroot();
        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();

        if($user['id']>0){
            //判断验证码是否错误
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".strim($mobile_code)."'")==0){
                $root['response_code'] = 0;
                $root['show_err'] = "手机验证码出错";
                output($root);
            }
            //判断验证码是过期
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".strim($mobile_code)."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
                $root['response_code'] = 0;
                $root['show_err'] = "手机验证码已过期";
                output($root);
            }

            if(!empty($old_pwd) && strlen($old_pwd)<32){
                $old_pwd = md5($old_pwd);
            }

            //比对密码是否正确
            if($type=="pwd"){
                $user_pwd = $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user WHERE id='".$user['id']."'");
                if($old_pwd != $user_pwd){
                    $root['ssss'] = $old_pwd.'--'.$user_pwd;
                    $root['response_code'] = 0;
                    $root['show_err'] = "原密码错误";
                    output($root);
                }
            }else{
                $user_pwd = $GLOBALS['db']->getOne("SELECT paypassword FROM ".DB_PREFIX."user WHERE id='".$user['id']."'");
                if($old_pwd != $user_pwd){
                    $root['ssss'] = $old_pwd.'--'.$user_pwd;
                    $root['response_code'] = 0;
                    $root['show_err'] = "原始交易密码错误";
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
            $root['response_code'] = 1;
            $root['show_err'] = "操作成功";
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }

    }
}
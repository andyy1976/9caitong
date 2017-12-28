<?php
require_once APP_ROOT_PATH."system/libs/user.php";
//自动投标接口

class uc_auto_invest_switch{

    public function index(){
        $user = $GLOBALS['user_info'];
        $id = intval(strim(base64_decode($GLOBALS['request']['id'])));
        $status = intval(strim(base64_decode($GLOBALS['request']['status'])));
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            if(empty($id)){
                $root['response_code'] = 0;
                $root['user_login_status'] = 0;
                $root['show_err'] = '参数错误';
                output($root);
            } 
            $time = time();
            if($status == 1){
                $data = $GLOBALS['db']->getRow("select money,end_time,is_long from ".DB_PREFIX."auto_invest_config where id=".$id);
                if(empty($data['is_long']) && $data['end_time'] < time()){
                    $root['response_code'] = 0;
                    $root['show_err'] = "有效期已过期，请重新设置";
                    ajax_return($root);
                }
                $cunguan_money = $GLOBALS['db']->getOne("select AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where id=".$user['id']);
                if($data['money'] > $cunguan_money){
                    $root['response_code'] = 0;
                    $root['show_err'] = "余额不足";
                    output($root);
                }
                $root['response_code'] = 1;
                $root['jump'] = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=uc_autoinvest&act=switch_check_pwd&id=".$id;
                output($root);
            }else{
                $old_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."auto_invest_config where id=".$id." and status=1");
                if(empty($old_money)) {
                    $root['response_code'] = 0;
                    $root['show_err'] = "请求频繁，请稍后再试";
                    ajax_return($root);
                }
                //将之前设置金额解冻
                modify_account(array('cunguan_money'=>$old_money,'cunguan_lock_money'=>-$old_money),$user['id'],"自动投标返还",48,"自动投标返还",1);
                $data['status'] = 0;
                $data['update_time'] = time();
                $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config",$data,"UPDATE","id=".$id." and user_id=".$user['id']);
                
            }
            if($rs){
                $root['response_code'] = 1;
                $root['show_err'] = '关闭成功';
                output($root);
            }else{
                $root['response_code'] = 0;
                $root['show_err'] = '系统繁忙，请稍后再试';
                output($root);
            }
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }

}
<?php
require_once APP_ROOT_PATH.'app/Lib/uc_func.php';
class withdraw{
    public function index(){
        $switch = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."switch_conf where switch_id = 1 or switch_id = 5");
        foreach ($switch as $k=>$v){
            if($v['status']!=1){
                $root['response_code'] = 0;
                $root['show_err'] = '系统正在升级，请稍后再试';
                output($root);
            }
        }
        $root = get_baseroot();
        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $amount = base64_decode($GLOBALS['request']['amount']);
        $paypassword = base64_decode($GLOBALS['request']['paypassword']);
        $bid = intval(base64_decode($GLOBALS['request']['bid']));
        $withdraw_acc = intval(base64_decode($GLOBALS['request']['withdraw_acc']));
        if($user['id'] > 0){
            if($withdraw_acc == 1){
                if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
                    $root['response_code'] = 0;
                    $root['tip'] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
                    output($root);
                }  
            }
        	$root['user_login_status'] = 1;
        	$status = getUcSaveCarry($amount,$paypassword,$bid,$withdraw_acc);
        	if($status['status'] == 2){//存管提现跳转校验密码
        	    $root['response_code'] = 1;
        	    $root['status'] = $status['status'];
        	    $root['jump'] = $status['jump'];
        	    output($root);
        	}
        	if($status['status'] == 0){
        		$root['response_code'] = 0;
        		$root['show_err'] = $status['show_err'];
        	}else{
        		$root['response_code'] = 1;
        		$root['show_err'] = $status['show_err'];
        	}        	
        }else{
        	$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
        }
        $root['program_title'] = "提现";
		output($root);        
    }
}
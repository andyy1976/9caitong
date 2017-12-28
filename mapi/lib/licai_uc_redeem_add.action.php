<?php

class licai_uc_redeem_add
{
	public function index(){
		
		$root = get_baseroot();
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			
			require_once APP_ROOT_PATH.'system/libs/licai.php';
			$root['user_login_status'] = 1;
			
			$id = intval(base64_decode($GLOBALS['request']["id"]));
			$redeem_money = floatval(base64_decode($GLOBALS['request']["redeem_money"]));
			$paypassword = strim(base64_decode($GLOBALS['request']["paypassword"]));
			
			if(md5($paypassword)!=$user['paypassword']){
				$root["status"] = 0;
				$root["show_err"] = "支付密码错误";
				output($root);	
			}else{
				$root["status"] = 1;
				$root["show_err"] = "申请成功";
			}
			
			$result = create_redempte($user_id,$id,$redeem_money);
			
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "理财申请";
		output($root);		
	}
}
?>

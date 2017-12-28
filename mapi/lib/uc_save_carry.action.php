<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_save_carry
{
	public function index(){
		
		$root = array();
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';
			
			$root['user_login_status'] = 1;
									
			$paypassword = strim(base64_decode($GLOBALS['request']['paypassword']));
			$amount = floatval(base64_decode($GLOBALS['request']['amount']));
			$bid = intval(base64_decode($GLOBALS['request']['bid']));
			
			$result = getUcSaveCarry($amount,$paypassword,$bid);
			 
			$root['response_code'] = $result['status'];
			$root['show_err'] = $result['show_err'];
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);		
	}
}
?>

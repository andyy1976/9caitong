<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_do_transfer
{
	public function index(){
		
		$root = array();
		
		$id = intval(base64_decode($GLOBALS['request']['dlid']));
		$tid = intval(base64_decode($GLOBALS['request']['dltid']));
		$paypassword = strim(base64_decode($GLOBALS['request']['paypassword']));
		$transfer_money = floatval(base64_decode($GLOBALS['request']['transfer_money']));
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';
			
			$root['user_login_status'] = 1;
									
			$result = getUcDoTransfer($id,$tid,$paypassword,$transfer_money);
			
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

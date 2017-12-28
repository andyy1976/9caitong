<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_learn_do_interest
{
	public function index(){
		
		$root = array();
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require_once APP_ROOT_PATH.'system/libs/learn.php';
			$dltid = intval(base64_decode($GLOBALS['request']['dltid']));
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			do_receive_benefits();
			$root['show_err'] ="领取收益成功";
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "领取收益";
		output($root);		
	}
}
?>

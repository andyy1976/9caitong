<?php

class licai_bid
{
	public function index(){
		
		$root = get_baseroot();
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			
			$id = intval(base64_decode($GLOBALS['request']['id']));
			$ajax = intval(base64_decode($GLOBALS['request']['ajax']));
			$money =  floatval(base64_decode($GLOBALS['request']['money']));
			$paypassword = trim(base64_decode($GLOBALS['request']['paypassword']));
			require_once APP_ROOT_PATH.'system/libs/licai.php';
			$result = licai_bid($id,$money,$paypassword);
			if($result['status']==0){
				$root['response_code'] = 0;
				$root['show_err'] = $result['info'];
			}
			else{
				$root['response_code'] = 1;
				$root['show_err'] = $result['info'];
			}
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);		
	}
}
?>

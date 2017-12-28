<?php
class gesture_cipher
{
	public function index(){
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		$gesture_cipher = base64_decode($GLOBALS['request']['gesture_cipher']);
		$date['gesture_cipher'] = $gesture_cipher;
		$res = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$date,"UPDATE","id=".$user_id);
		if($res){
			$root['response_code'] = 1;
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="手势密码设置失败";
		}
		$root['program_title'] = "手势密码";
		output($root);
	}
}
?>

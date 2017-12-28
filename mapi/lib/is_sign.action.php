<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class is_sign
{
	public function index(){
		/*
		Id:贷款单ID
		load_id：出借记录id
		*/
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$start_time = strtotime(date("Y-m-d"));
			$end_time = strtotime(date("Y-m-d 23:59:59"));
			$sign=intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_sign_log where sign_date >=".$start_time." and sign_date<=".$end_time." and user_id=".$user_id));
			if($sign>0){
				$root['is_sign'] = "1";
				$root['info'] = "已签到";
			}else{
				$root['is_sign'] = "0";
				$root['info'] = "未签到";
			}
				
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['score'] = $user['score'];
		$root['program_title'] = "签到";
		output($root);
	}
}
?>

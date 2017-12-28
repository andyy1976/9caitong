<?php
class share{
	public function index()
	{
		$user_id = $GLOBALS['user_info']['id'];
		if($user_id){
			require_once APP_ROOT_PATH."system/libs/user.php";
			$user_data['user_id'] = $user_id;
			$user_data['share_time'] = time();
			$share_rs = is_share($user_id);
	        if($share_rs){
	            $rs = $GLOBALS['db']->query("update ".DB_PREFIX."user_share_log set share_count = share_count+1,share_time =".$user_data['share_time']." where id=".$share_rs['id']);
	        }else{
	            $user_data['share_count'] = 1;
	            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."user_share_log",$user_data);
	        }
			if($rs){
	            $root['show_err'] = '分享成功';
	            $root['response_code'] = 1;
	        }else{
	            $root['show_err'] = '分享失败';
	            $root['response_code'] = 0;
	        }
			$root['user_login_status'] = 1;//用户登陆状态：1:成功登陆;0：未成功登陆
		}else{
			$root['response_code'] = 0;
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			$root['show_err'] = "未登录";
			output($root);
		}
		$root['program_title'] = "分享";		
		output($root);		
	}
}
?>
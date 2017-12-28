<?php
class loginout
{
	public function index()
	{	
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$user_id  = intval($user['id']);
		$root['session_id'] = es_session::id();
		require_once APP_ROOT_PATH."system/libs/user.php";
		$result = loginout_user();
		if($result['status'])
		{
			$root['response_code'] = 1;
			$s_user_info = es_session::get("user_info");
			es_cookie::delete("user_name");
			es_cookie::delete("user_pwd");
			
			$root['user_loginout_status'] = 1;//退出登录状态：1:成功登陆;0：未成功登陆
			$root['show_err'] = "退出登录成功";	
		}else{
			$root['response_code'] = 0;
			$root['show_err'] = "退出登录失败";	
		}
		$root['program_title'] = "退出登录";
		output($root);		
	}
}
?>
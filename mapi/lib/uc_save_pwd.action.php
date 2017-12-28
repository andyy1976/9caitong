<?php

class uc_save_pwd{
	
	public function index()
	{		
		$root = array();
		$old_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['old_pwd']))));
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile']))));
		$mobile_code = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile_code']))));
		$user_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['user_pwd']))));
		$user_pwd_confirm = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['user_pwd_confirm']))));
		if($user_pwd != $user_pwd_confirm)
		{
			$root['response_code'] = 0;
			$root['show_err'] = "密码不一致，请重新输入";
			output($root);
		}			
		if($user_pwd == null || $user_pwd =='')
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['USER_PWD_ERROR'];
			output($root);
		}

		if(strlen($old_pwd) == 32){
			if($old_pwd == $user_pwd){
				$root['response_code'] = 0;
				$root['show_err'] = "新密码不能和原密码相同哦";
				output($root);
			}
		}else{
			if($old_pwd == $user_pwd){
				$root['response_code'] = 0;
				$root['show_err'] = "新密码不能和原密码相同哦";
				output($root);
			}
		}				
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){		
			$code = $user['code'];
			if(strlen($old_pwd)==32){
				$pwd = $old_pwd;
			}else{
				$pwd = MD5($old_pwd);
			}
			if($GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user WHERE id=".$user_id)!=$pwd){
				$root['response_code'] = 0;
				$root['user_login_status'] = 1;
				$root['show_err'] = "原始登录密码错误.";
				output($root);
			}elseif($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".strim($mobile_code)."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				$root['response_code'] = 0;
				$root['user_login_status'] = 1;
				$root['show_err'] = "手机验证码出错,或已过期.";
				output($root);
			}else{							
				$new_pwd = $user_pwd.$code;				
				$GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd='".$new_pwd."', bind_verify = '', verify_create_time = 0 where id = ".$user_id);
				$root['response_code'] = 1;
				$root['show_err'] = "修改成功!";
				output($root);
			}
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="用户未登录成功(旧密码验证失败)";
			$root['user_login_status'] = 0;
		}
		output($root);
	}
}
?>
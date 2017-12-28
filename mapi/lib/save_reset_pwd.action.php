<?php

class save_reset_pwd{
	
	public function index()
	{
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile']))));
		$user_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['user_pwd']))));
		$user_pwd_confirm = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['user_pwd_confirm']))));		
		$root = get_baseroot();
		$root['program_title'] = "修改密码";
		
			if($user_pwd != $user_pwd_confirm)
			{
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
				output($root);	
			}		
			if(empty($user_pwd)){
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['USER_PWD_ERROR'];
				output($root);
			}else{
				$sql = "select id,code,paypassword from ".DB_PREFIX."user where mobile_encrypt = AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."')";	
				$user_info = $GLOBALS['db']->getRow($sql);
				$user_id = intval($user_info['id']);
				$code = $user_info['code'];										
				$new_pwd = $user_pwd.$code;
				if($user_info['paypassword'] == $new_pwd){
					$root['response_code'] = 0;
					$root['show_err'] = "登录密码不能与交易密码相同";
					output($root);
				}			
				$sql = "update ".DB_PREFIX."user set user_pwd='".$new_pwd."', bind_verify = '', verify_create_time = 0 where id = ".$user_id;
				$GLOBALS['db']->query($sql);			
				$root['response_code'] = 1;
				$root['show_err'] = "密码找回成功!请重新登录";
				output($root);
			}	

		}
	}
?>
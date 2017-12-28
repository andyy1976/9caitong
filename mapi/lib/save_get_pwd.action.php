<?php

class save_get_pwd{	
	public function index()
	{
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile']))));
		$mobile_code = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile_code']))));	
		$root = get_baseroot();		
		$root['program_title'] = "修改密码1";
		if(empty($mobile)){
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];
			output($root);			
		}
		$sql = "select id from ".DB_PREFIX."user where mobile_encrypt = AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."')";	
		$user = $GLOBALS['db']->getOne($sql);
		if(!$user){
			$root['response_code'] = 0;
			$root['show_err'] = '手机号码尚未注册';
			output($root);
		}
		if(empty($mobile_code)){
			$root['response_code'] = 0;
			$root['show_err'] = '短信验证码不能为空';
			output($root);
		}else{
			if($GLOBALS['db']->getOne("select bind_verify from ".DB_PREFIX."user where id =".$user." and is_delete = 0") != $mobile_code){
				$root['response_code'] = 0;
				$root['show_err'] = "短信验证码错误";
				output($root);
			}else{
				$root['response_code'] = 1;
				$root['show_err'] = "短信验证码正确";
				output($root);
			}
		
		}
		
	}
}
?>
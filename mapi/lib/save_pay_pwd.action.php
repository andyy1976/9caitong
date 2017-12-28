<?php

//重置交易密码接口(第二步)
class save_pay_pwd{
	
	public function index(){
		
		$root = get_baseroot();
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile']))));//手机号码
		$user_pwd = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['pay_pwd']))));//新支付密码
		$user_pwd_confirm = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['pay_pwd_confirm']))));//确认支付密码
        $userpwd = $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user WHERE id='".$GLOBALS['user_info']['id']."'");

		if( empty($user_pwd) || empty($user_pwd_confirm)) {
			$root['response_code'] = 0;
			$root['show_err'] = '密码和确认密码不能为空';
			output($root);
		}
		if($user_pwd != $user_pwd_confirm){
			$root['response_code'] = 0;
			$root['show_err'] = '密码和确认密码不一致';
			output($root);
		}
		if($user_pwd==$userpwd){
            $root['response_code'] = 0;
            $root['show_err'] = '交易密码不能和登录密码相同。';
            output($root);
        }
		//检查用户,用户密码
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		if ($user['id'] >0){
			$user_info = $GLOBALS['db']->getRow("select id,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile from ".DB_PREFIX."user where id = '".$user['id']."' and is_delete = 0");
			if($mobile != $user_info['mobile']){
				$root['response_code'] = 0;
				$root['show_err'] = '手机号码错误';
				output($root);
			}
			$sql = "update ".DB_PREFIX."user set paypassword='".$user_pwd."' where id = ".$user_info['id'];
			$GLOBALS['db']->query($sql);
			$root['response_code'] = 1;
			$root['show_err'] = "交易密码重置成功!";
			es_session::delete('send_resetpaypwd_code_num');
			output($root);
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "重置交易密码";
		output($root);

	}
}
?>
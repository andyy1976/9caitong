<?php

class send_reset_pwd_code{
	
	public function index()
	{
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile']))));
		$verify = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['verify']))));
		$root = array();	
		if(app_conf("SMS_ON")==0)
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['SMS_OFF'];
			output($root);
		}	
		if($mobile == '')
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];
			output($root);
		}	
		if(!check_mobile($mobile))
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			output($root);
		}
		/*if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0))
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST'];
			output($root);
		}*/
		//短信验证码错误三次以后,验证图形验证码
		if($verify) {
			if (!checkVeifyCode($verify)) {
				$root['img_verify'] = 0;
				$root['response_code'] = 0;
				$root['show_err'] = "图形验证码有误";
				output($root);
			}
		}
		//获取短信验证码发送的次数  如果大于三次显示图形验证码
		$send_code_num = es_session::get('send_reset_pwd_code_num');
		if(!$verify){
			if($send_code_num >=2){
				$root['img_verify'] = 1;
				$root['response_code'] = 0;
				$root['show_err'] = "获取次数过多，请输入图文验证码";//请输入你的手机号
				output($root);
			}
		}

		$sql = "select id,bind_verify from ".DB_PREFIX."user where mobile_encrypt = AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') and is_delete = 0";
		$user_info = $GLOBALS['db']->getRow($sql);
		$user_id = intval($user_info['id']);
		$code = intval($user_info['bind_verify']);	
		if($user_id == 0)
		{
			$root['response_code'] = 0;
			$root['show_err'] = '手机号码不存在或被禁用';
			output($root);
		}
		//开始生成手机验证
		if ($code == 0){
			//已经生成过了，则使用旧的验证码；反之生成一个新的
			$code = rand(111111,999999);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set bind_verify = '".$code."',verify_create_time = '".TIME_UTC."' where id = ".$user_id);
		}	
		//使用立即发送方式
		$result = send_verify_sms($mobile,$code,$user_id,true);

		$root['response_code'] = $result['status'];
		
		if ($root['response_code'] == 1){
			$root['show_err'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
			//验证码发送成功后记录发送次数
			$send_reset_pwd_code_num =1 + es_session::get('send_reset_pwd_code_num');
			es_session::set('send_reset_pwd_code_num',$send_reset_pwd_code_num);
		}else{
			$root['show_err'] = $result['msg'];
			if ($root['show_err'] == null || $root['show_err'] == ''){
				$root['show_err'] = "验证码发送失败";
			}
		}
		output($root);
	}
}
?>
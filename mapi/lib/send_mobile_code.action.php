<?php

class send_mobile_code{
	public function index()
	{
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile'])))); //手机号码
		$type = trim(base64_decode($GLOBALS['request']['type'])); //修改密码时传入
		$verify = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['verify']))));
		$root = get_baseroot();
		
		if(app_conf("SMS_ON")==0)
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['SMS_OFF'];//短信未开启
			output($root);
		}
		$search ='/^(1(([38][0-9])|[7][356780]|[4][57]|[5][012356789]))\d{8}$/';
		if(!preg_match($search,$mobile)) {
			$root['response_code'] = 0;
			$root['show_err'] = "您输入的手机号有误";//您输入的手机号有误
			output($root);
		}
		if($mobile == '')
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];//请输入你的手机号
			output($root);
		}
		if(!$type){
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile_encrypt = AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ")>0)
			{ 
				$field_show_name = $GLOBALS['lang']['USER_TITLE_mobile'];//手机号码
				$root['response_code'] = 0;
				$root['show_err'] = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$field_show_name); //已存在，请重新输入
				output($root);
			}
		}
		
		if(!check_mobile($mobile))
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];//请填写正确的手机号码
			output($root);
		}
	
		if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0))
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST']; //短信发送太快
			output($root);
		}
		//短信验证码错误三次以后,验证图形验证码
		if($verify){
			if(!checkVeifyCode($verify))
			{
				$root['img_verify'] = 0;
				$root['response_code'] = 0;
				$root['show_err'] = "图形验证码有误";
				output($root);
			}
		}

		//获取短信验证码发送的次数  如果大于三次显示图形验证码
		$send_code_num = es_session::get('send_reset_pwd_code_num');
		if(!$verify) {
			if ($send_code_num >= 2) {
				$root['img_verify'] = 1;
				$root['response_code'] = 0;
				$root['show_err'] = "获取次数过多，请输入图文验证码";//请输入你的手机号
				output($root);
			}
		}


	
		//删除超过5分钟的验证码
		//$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."mobile_verify_code WHERE create_time <=".TIME_UTC-300);
		
		$begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));
		
		if($GLOBALS['db']->getOne("SELECT send_count FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'  AND  create_time between ".$begin_time." and  ".($begin_time+24*3600)."") >= 100){
			$root['response_code'] = 0;
			$root['show_err'] = "你今天已经不能再发验证码了";
			output($root);
		}

		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = "请稍后再试";
			output($root);
		}*/
	
	
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
	
		//使用立即发送方式
		$result = send_verify_sms($mobile,$verify_data['verify_code'],null,true);//
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
<?php

class send_register_code{
	public function index()
	{
		$mobile = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['mobile']))));
		$verify = trim(base64_decode($GLOBALS['request']['verify']));
		$root = get_baseroot();
		
		if(app_conf("SMS_ON")==0)
		{
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['SMS_OFF'];//短信未开启
			output($root);
		}
		$search ='/^1[3|4|5|7|8]\d{9}$/';
		if(!preg_match($search,$mobile)) {
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = "请输入正确的手机号码";//您输入的手机号有误
			output($root);
		}
		//if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$mobile."'")>0)
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile_encrypt = AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ")>0)
		{ 
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = '手机号码已被注册'; //已存在，请重新输入
			output($root);
		}
		$send_count = $GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where  mobile =".$mobile);
		if(!$verify){
			if($send_count >= 2){
				$root['img_verify'] = 1;			
				$root['response_code'] = 0;
				$root['show_err'] = "获取次数过多，请输入图文验证码";//请输入你的手机号
				output($root);
			}
		}
		if($verify){
			if(!checkVeifyCode($verify))
			{	
				$root['img_verify'] = 0;
				$root['response_code'] = 0;
				$root['show_err'] = "图形验证码错误";
				output($root);		
			}
		}
		if($mobile == '')
		{
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];//请输入你的手机号
			output($root);
		}
	
		if(!check_mobile($mobile))
		{
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = '请输入正确的手机号码';//请填写正确的手机号码
			output($root);
		}	
		
		if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0))
		{
			$root['img_verify'] = 0;
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST']; //短信发送太快
			output($root);
		}
	
		//删除超过5分钟的验证码
		//$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."mobile_verify_code WHERE create_time <=".TIME_UTC-300);
		
		$begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));
		
		if($GLOBALS['db']->getOne("SELECT send_count FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'  AND  create_time between ".$begin_time." and  ".($begin_time+24*3600)."") >= 100){
			$root['img_verify'] = 0;
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
		}else{
			$root['show_err'] = $result['msg'];
			if ($root['show_err'] == null || $root['show_err'] == ''){
				$root['show_err'] = "验证码发送失败";
			}
		}
		//../system/sms/FW_sms.php  提示账户或密码错误地址
		
		output($root);
	}
	
}
?>
<?php

//重置交易密码获取短信验证码接口
class send_reset_pay_code{
	public function index(){

		$realname =strim(base64_decode($GLOBALS['request']['realname'])); //真实姓名
		$idno = strim(base64_decode($GLOBALS['request']['idno'])); //身份证号码
		$verify =strim(base64_decode($GLOBALS['request']['verify'])); //图形验证码
		if(app_conf("SMS_ON")==0)
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['SMS_OFF'];//短信未开启
			output($root);
		}
		
		if(empty($realname) || empty($idno )){
			$root['response_code'] = 0;
			$root['show_err'] = '真实姓名或身份证号码不能为空';
			output($root);
		}
        $regx = '/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/';
		if(!preg_match($regx,$idno)){
            $root['response_code'] = 0;
            $root['show_err'] = "身份证格式不正确";
            output($root);
        }
		if($verify){
			if(!checkVeifyCode($verify)) {
				$root['img_verify'] = 0;
				$root['response_code'] = 0;
				$root['show_err'] = "图形验证码有误";
				output($root);
			}
			
		}
				
		//检查用户,用户密码
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		$root['user_id'] = $user_id;
		if ($user_id >0){
			$mobile = $user['mobile'];
			if($mobile == '') {
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];
				output($root);
			}
			if(!check_mobile($mobile)) {
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
				output($root);
			}
			if(!$verify){
				if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0)) {
					$root['response_code'] = 0;
					$root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST']; //短信发送太快
					output($root);
				}
			}

			$uinfo = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$user['id']);
			if($realname != $uinfo['real_name'] || $idno != $uinfo['idno']){
				$root['response_code'] = 0;
				$root['show_err'] ='实名信息与当前账户不一致';
				output($root);
			}
			//获取短信验证码发送的次数  如果大于三次显示图形验证码
			$send_code_num = es_session::get('send_resetpaypwd_code_num');
			if(!$verify){
				if($send_code_num >=2){
					$root['img_verify'] = 1;
					$root['response_code'] = 0;
					$root['show_err'] = "获取次数过多，请输入图文验证码";//请输入你的手机号
					output($root);
				}
			}

			$begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));

			if($GLOBALS['db']->getOne("SELECT send_count FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'  AND  create_time between ".$begin_time." and  ".($begin_time+24*3600)."") >= 100){
				$root['response_code'] = 0;
				$root['show_err'] = "你今天已经不能再发验证码了";
				output($root);
			}
			//开始生成手机验证
			$verify_data['verify_code'] = rand(111111,999999);
			$verify_data['mobile'] = $mobile;
			$verify_data['create_time'] = TIME_UTC;
			$verify_data['client_ip'] = CLIENT_IP;
			$verify_data['send_count'] = 1;

			if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'")){
				if($info['create_time'] < $begin_time){
					$verify_data['send_count'] = 1;
				}else{
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
				$send_reset_pwd_code_num =1 + es_session::get('send_resetpaypwd_code_num');
				es_session::set('send_resetpaypwd_code_num',$send_reset_pwd_code_num);
			}else{
				$root['show_err'] = $result['msg'];
				if ($root['show_err'] == null || $root['show_err'] == ''){
					$root['show_err'] = "验证码发送失败";
				}
			}
			output($root);
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;			
		}
		output($root);
	}
	
}
?>
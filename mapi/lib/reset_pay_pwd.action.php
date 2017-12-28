<?php
//重置交易密码接口(第一步)
class reset_pay_pwd{
	
	public function index(){

		$root = get_baseroot();
		$realname = strim(base64_decode($GLOBALS['request']['realname'])); //真实姓名
		$idno = strim(base64_decode($GLOBALS['request']['idno'])); //身份证号码
		$mobile = strim(base64_decode($GLOBALS['request']['mobile'])); //手机号码
		$mobile_code = strim(base64_decode($GLOBALS['request']['mobile_code'])); //短信验证码
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$root['user_id']  = intval($user['id']);
		$userinfo = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE id=".$user['id']);
		if ($user['id'] >0){
			if(empty($realname) || empty($idno)){
				$root['response_code'] = 0;
				$root['show_err'] = '真实姓名或身份证号码不能为空';
				output($root);
			}
			if(empty($mobile) || empty($mobile_code)){
				$root['response_code'] = 0;
				$root['show_err'] = '手机号码或短信验证码不能为空';
				output($root);
			}

			if($userinfo['mobile'] != $mobile){
				$root['response_code'] = 0;
				$root['show_err'] = '手机号码错误';
				output($root);
			}
			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$mobile_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				$root['response_code'] = 0;
				$root['show_err'] = "手机验证码出错,或已过期.";
				output($root);
			}else{
				if($realname == $userinfo['real_name'] && $idno == $userinfo['idno']){
					$root['response_code'] = 1;
					$root['show_err'] = "操作成功";
					output($root);
				}else{
					$root['response_code'] = 0;
					$root['show_err'] = "真实姓名或身份证号码和实名认证的不相符";
					output($root);
				}
			}

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
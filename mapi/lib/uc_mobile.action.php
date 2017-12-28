<?php

// 修改    修改绑定手机号接口
class uc_mobile{
	public function index()
	{
		/*
		* mobile = 手机号码 
		* mobile_code = 手机验证码
		*/
		$root = get_baseroot();
		$mobile = strim(base64_decode($GLOBALS['request']['mobile'])); 
		$mobile_code = strim(base64_decode($GLOBALS['request']['mobile_code']));
		$step = addslashes(htmlspecialchars(trim(base64_decode($GLOBALS['request']['step'])))); //新号码需要传此参数
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		$root['program_title'] = '修改绑定手机号';
		if($user_id > 0){
			//判断验证码是否正确
			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".strim($mobile_code)."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				$root['response_code'] = 0;
				$root['show_err'] = "手机验证码出错,或已过期.";
				output($root);
			}else{
				$root['response_code'] = 1;
				if($step){
					$data['mobile_encrypt'] = "AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."')";
					$result = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."user where mobile_encrypt=".$data['mobile_encrypt']);
					if($result){
						$root['response_code'] = 0;
						$root['show_err'] = "该手机号码已存在";
						output($root);
					}
					$data['user_name'] = "w".$mobile;
					$data['mobile'] = $mobile;

					$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$user_id);
					$root['show_err'] = "绑定手机修改成功";
					es_session::delete('send_changemobile_code_num');
				}else{
					$root['show_err'] = "操作成功";
				}
				output($root);
			}
		}else{
			$root['response_code'] = 0;
			$root['show_err'] = '未登录';
			output($root);
		}
		return $user_id;
	}
	
}
?>
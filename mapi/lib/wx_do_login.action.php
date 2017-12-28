<?php
class wx_do_login{
	public function index()
	{
		
		$root = array(); //用于返回的数据
		$user_data['wx_openid']=strim(base64_decode($GLOBALS['request']['wx_openid']));
		$open_id = $GLOBALS['db']->getOne("select count(*) FROM ".DB_PREFIX."user where wx_openid='".$user_data['wx_openid']."'");
		if(!$open_id){
			$root['response_code'] = 0;
			$root['show_msg'] = "微信授权成功，请绑定玖财通帐号";
			output($root);
		}else{
			$user = $GLOBALS['db']->getRow("select mobile,user_pwd FROM ".DB_PREFIX."user where wx_openid='".$user_data['wx_openid']."'");
			$result = user_login($user['mobile'],$user['user_pwd']);
			if($result['status']){
				$user_data = $GLOBALS['user_info'];
				$root['response_code'] = 1;
				$root['user_login_status'] = 1;//用户登陆状态：1:成功登陆;0：未成功登陆
				$root['show_err'] = "用户登录成功";		
				$root['id'] = $user_data['id'];
				$root['user_name'] = $user_data['mobile'];
				$root['mobile'] = $user_data['mobile'];
				$root['user_pwd'] = $user_data['user_pwd'];
				$root['gesture_cipher'] = ($user_data['gesture_cipher']==null) ? "" : $user_data['gesture_cipher'];
				//session_id存入数据库
				$sess['session_id'] = es_session::id();
				$sess['session_data'] = date("Y-m-d H:i:s",TIME_UTC);
				$sess['session_time'] = TIME_UTC;
				$sess['wx_login_status'] = 1;
				$sess['mobile'] = $user_data['mobile'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."session",$sess,"in","user_id=".$user_data['id']);
				$root['user_money'] = number_format($user_data['money'],2);
				$root['user_money_format'] = number_format($user_data['money'],2);//用户金额	
				$root['total_money'] = number_format($user_data['money'],2);  //总金额  		
				$root['yesterday_invert'] = number_format($user_data['money'],2);  //昨日金额
				$root['cum_money'] = number_format($user_data['money'],2); //累计收益
				if($user_data['idno'] == ""){
					$root['real_name'] = "";
					$root['idno'] = "";
				}else{
					$root['real_name'] = $user_data['real_name'];
					$root['idno'] = $user_data['idno'];
				}
			}
		}
		output($root);
	}
	
}
?>
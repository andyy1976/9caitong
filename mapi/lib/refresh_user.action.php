<?php

class refresh_user
{
	public function index(){
		$root = get_baseroot();
		
		
		$id = intval(base64_decode($GLOBALS['request']['id']));
		
		$type = intval(base64_decode($GLOBALS['request']['type']));
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
				
			$root['response_code'] = 1;
			
			
			$root['open_ips'] = intval(app_conf("OPEN_IPS"));
			$root['ips_acct_no'] = $user['ips_acct_no'];
			$root['idno'] = $user['idno'];//身份证号
			$root['real_name'] = $user['real_name'];
						
			$app_url = APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$user_id."&from=".base64_decode($GLOBALS['request']['from']);
			$root['acct_url'] = str_replace("/mapi", "", WAP_SITE_DOMAIN.$app_url);
			
			
			if ($type == 0){
				$deal_id = $id;
			}else if ($type == 1){
				$deal_id = $GLOBALS['db']->getOne("SELECT deal_id FROM ".DB_PREFIX."deal_load_transfer WHERE id = ".$id);
			}
			
			$ips_bill_no = $GLOBALS['db']->getOne("SELECT ips_bill_no FROM ".DB_PREFIX."deal WHERE id = ".$deal_id);
			$root['ips_bill_no'] = $ips_bill_no;
			
			//第三方托管标	
//			if (!empty($root['ips_bill_no'])){
//				
//				if (!empty($user['ips_acct_no'])){
//					$result = GetIpsUserMoney($user_id,0);
//						
//					$root['user_money'] = $result['pBalance'];
//				}else{
//					$root['user_money'] = 0;
//				}
//			}else{
//				$root['user_money'] = $user['money'];
//			}
			
			if (!empty($user['ips_acct_no']) && intval(app_conf("OPEN_IPS")) > 0){
				$result = GetIpsUserMoney($user_id,0);
				$root['user_money'] = $result['pBalance'];
			}else{
				$root['user_money'] = $user['money'];
			}
				
			$root['user_money_format'] = format_price($root['user_money']);//用户金额
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		
		output($root);		
	}
}
?>

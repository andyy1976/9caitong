<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_debts{
	public function index(){
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$load_id = intval(base64_decode($GLOBALS['request']['load_id']));
		$deal_id = intval(base64_decode($GLOBALS['request']['deal_id']));
		$deal_repay = $GLOBALS['db']->getOne("select has_repay from ".DB_PREFIX."deal_load_repay where load_id =".$load_id." order by repay_id desc limit 1");
		if($deal_repay==1){
			$root['response_code'] = 0;
			$root['show_err'] ="此标的已还款，无法转让";
			output($root);
		}
		if($user['id']>0){
			$deal_info = $GLOBALS['db']->getRow("select sub_name,rate,deal_sn,repay_time from ".DB_PREFIX."deal where id=".$deal_id);
			$load_info = $GLOBALS['db']->getRow("select total_money,create_time,create_date from ".DB_PREFIX."deal_load where id=".$load_id);
			$now = strtotime(date("Y-m-d"));
			$create_date = strtotime($load_info['create_date']);
			$debts['total_money'] = $load_info['total_money'];
			$debts['debts_money'] = $load_info['total_money'];
			$debts['hold_days'] = ceil(($now-$create_date)/86400);
			$debts['fee'] = $load_info['total_money']*0.5/100;
			$debts['sub_name'] = "【转】".$deal_info['sub_name'];
			$debts['id'] = $load_id;
			$root['item'] = $debts;
			$root['response_code'] = 1;
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			
		}
		output($root);
	}


}
<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_debts_details{
	
	public function index(){
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$load_id = intval(base64_decode($GLOBALS['request']['load_id']));
		if($user['id']>0){
			$load_info = $GLOBALS['db']->getRow("select deal_id,total_money,create_time,create_date from ".DB_PREFIX."deal_load where id=".$load_id);
			$deal_status = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$load_info['deal_id']);
			if($deal_status!=4){
				$root['response_code'] = 0;
				$root['show_err'] ="该标的尚未放款，不能进行转让操作";
				output($root);
			}
			$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id=".$deal_id);
			$debts['total_money'] = $deal_info['total_money'];
			$debts['debts_money'] = $deal_info['total_money'];
			$debts['hold_days'] = ceil(($deal_info['create_time']-$load_info['create_date'])/86400);
			$debts['fee'] = isset($deal_info['fee'])?$deal_info['fee']:0.00;
			$debts['sub_name'] = "【转】".$deal_info['sub_name'];
			$debts['progress'] = round((($deal_info['loan_money']/$deal_info['borrow_amout'])/100),2)."%";
			$root['item'] = $debts;
			$root['response_code'] = 1;
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			
		}
 
		output($root);
 
	}
}
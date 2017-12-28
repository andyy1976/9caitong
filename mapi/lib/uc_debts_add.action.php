<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_debts_add{
	
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
			$deal_info['sub_name'] ="【转】".$deal_info['sub_name'];
			$deal_info['name'] = $deal_info['sub_name'];
			$deal_info['create_time'] = TIME_UTC;
			$deal_info['start_time'] = TIME_UTC;
			$deal_info['create_date'] = to_date(TIME_UTC,"Y-m-d");
			$deal_info['debts'] = 1;
			$deal_info['deal_status'] = 1;
			$deal_info['sort'] = $deal_info['sort']+1;
			$deal_info['user_id'] = $user['id'];
			$deal_info['old_deal_id'] = $deal_info['id'];
			$deal_info['borrow_amount'] = $load_info['total_money'];
			$deal_info['repay_time_type'] = 0;
			$deal_info['old_load_id'] = $load_id;
			$deal_info['is_advance'] = 0;
			$deal_info['enddate'] = 20;
			if($deal_info['is_new']==1){
				$deal_info['max_loan_money'] = 1000;
				$deal_info['is_new']=0;
			}
			unset($deal_info['load_money']);
			unset($deal_info['success_time']);
			unset($deal_info['id']);
			unset($deal_info['objectaccno']);
			unset($deal_info['load_seqno']);
			unset($deal_info['xuni_seqno']);
		
			if($GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_info,"INSERT")){
				$root['response_code'] = 1;
				
			}else{
				$root['response_code'] = 0;
				$root['show_err'] ="请稍后再试";
				
			}
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			
		}
 
		output($root);
 
	}
}
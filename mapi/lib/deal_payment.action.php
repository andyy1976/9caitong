<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class deal_payment
{
	public function index(){
		
		$root = array();
		/*
		Id:贷款单ID
		buy_money：投标金额
		cash_money：代金券金额
		red_money：代金券金额
		*/
		$root['session_id'] = es_session::id();	
		$id = intval(base64_decode($GLOBALS['request']['id']));
		$deal = get_deal($id);
		$buy_money = intval(base64_decode($GLOBALS['request']["buy_money"]));
		$cash_money = intval(base64_decode($GLOBALS['request']["cash_money"]));
		$red_money = intval(base64_decode($GLOBALS['request']["red_money"]));
		$c_money = 	($buy_money + $cash_money + $red_money);
		if($deal['loantype'] == "1"){
			if($deal['repay_time_type'] != 0){
				$i=1;
				for($i;$i<=$deal['repay_time'];$i++){
				if($i != $deal['repay_time']){
					$data['benxi'] = sprintf("%.2f", ($c_money*$deal['rate'])/12/100);
					$data['interest'] = sprintf("%.2f",($c_money*$deal['rate'])/12/100);
					$data['benjin'] = "0.00";
				}else{
					$data['benxi'] = sprintf("%.2f", (($c_money*$deal['rate'])/12/100)+$c_money);
					$data['interest'] = sprintf("%.2f",($c_money*$deal['rate'])/12/100);
					$data['benjin'] = sprintf("%.2f",$c_money);
				}				
					
					$data['time'] = date("Y-m-d",strtotime("+$i month"));
					$list[] = $data;
				}
			}
		}
		if($deal['loantype'] == "3"){
			if($deal['repay_time_type'] != 0){
				$i=1;
				for($i;$i<=$deal['repay_time'];$i++){					
					$data['benxi'] = sprintf("%.2f", (($c_money*$deal['rate'])/12/100)+($c_money/$deal['repay_time']));
					$data['interest'] = sprintf("%.2f",($c_money*$deal['rate'])/12/100);
					$data['benjin'] = sprintf("%.2f",$c_money/$deal['repay_time']);
					$data['time'] = date("Y-m-d",strtotime("+$i month"));
					$list[] = $data;
				}
			}
		}
		$root['item'] = $list;
		$root['response_code'] = 1;
		output($root);		
	}
}
?>

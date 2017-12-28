<?php
require APP_ROOT_PATH.'app/Lib/uc.php';
//普通账户
class uc_money
{
	public function index(){		
		$root = get_baseroot();	
		$user_data = $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user_data['id']);
		if ($user_id >0){
			$user_statics = get_user_money_info($user_id);
			//普通账户总资产
			$root['user_money_format'] = strval($user_statics['total_money']).'元';
			//普通账户可用余额
			$root['balance'] = strval(sprintf('%.2f',$user_data['money'])).'元'; 
			//普通账户在投金额
			$root['is_invert'] = strval($user_statics["invest_money"]).'元';
			//普通账户冻结金额  提现冻结
			if($user_statics['cash_money']){
				$lock_money = strval($user_statics['cash_money']);
			}else{
				$lock_money = 0;
			}

            //普通现金红包
            $root['cash_red_sum'] = '0元';
            //普通出借红包
            $root['deal_red_sum'] = '0元';
			$root['lock_money'] = strval($lock_money).'元';
			//普通账户红包	
			$root['red'] = strval($user_statics['red_money']).'元'; 
			//普通账户代金券金额					
			$root['cash'] = strval(intval($user_statics["ecv_money"])).'元';
			//普通账户累计出借收益
			$invest_total_money = sprintf('%.2f',$user_statics["invest_total_money"]);				
			$root['cum_profit'] = strval($invest_total_money).'元';
			//普通账户已收收益总计
			$load_repay_money = sprintf('%.2f',$user_statics["load_repay_money"]);
			$root['al_profit'] = strval($load_repay_money).'元';
			//普通账户待收收益总计
			$load_wait_earnings = sprintf('%.2f',$user_statics["load_wait_earnings"]);
			$root['take_profit'] = strval($load_wait_earnings).'元';
			//普通账户体验金收益
			$taste = sprintf('%.2f',$user_statics["taste"]);  	
			$root['ex_gold'] = strval($taste).'元'; 				
			$root['response_code'] = 1;
			$root['user_login_status'] = 1;
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "资金详览";
		output($root);		
	}
}
?>

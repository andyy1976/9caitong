<?php
require APP_ROOT_PATH.'app/Lib/uc.php';
//总账户
class uc_money_count
{
	public function index(){		
		$root = get_baseroot();	
		$user_data = $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user_data['id']);
		if ($user_id >0){
			$user_statics = get_user_money_info($user_id);
			
			//账户总资产
			$root['user_money_format'] = strval($user_statics['cunguan_total_money']+$user_data['lock_money']+$user_data['money']).'元';
			//可用余额
			$root['balance'] = strval($user_data['money']+$GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id'])).'元';  		
			//自动投标冻结金额
			$autoinvest_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."auto_invest_config where user_id=".$user_data['id']." and is_delete=0 and status=1");
			//在投金额
			$root['is_invert'] = strval(round($user_statics["cunguan_invest_money"]+$user_statics["invest_money"],2)).'元';
			//自动投标冻结金额
			$root['lock_autoinvest_money'] = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."auto_invest_config where user_id=".$user_id." and status=1 and is_delete=0");
			$root['lock_autoinvest_money'] = $root['lock_autoinvest_money'] ? strval(intval($root['lock_autoinvest_money'])).'元' : '0元';
			//冻结金额
//			$lock_money=$GLOBALS['db']->getOne("select cunguan_lock_money from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']."");
//			if($lock_money){
//				$lock_money = $user_statics['cash_money']+$lock_money;
//			}else{
//				$lock_money = 0;
//			}

			$rec_type=$GLOBALS['user_info']['user_type'];//1是企业 0是存管
			if($rec_type == "1"){ 
				$user_statics['cunguan_cash_money']= sprintf('%.2f', floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id= ".$user_id." and status in (0,3) and cunguan_tag=1 and user_type=2")));
			}else{ 
				$user_statics['cunguan_cash_money']=$user_statics['cunguan_cash_money'];
			}
            // 提现冻结金额
            $lock_money=$user_statics['cunguan_cash_money']+$user_statics['cash_money'];

            //存管现金红包
            $root['cash_red_sum']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$user_id." and rp.status=0 and rpn.red_type=3 and rp.end_time>".time());
            $root['cash_red_sum'] = $root['cash_red_sum'] ? $root['cash_red_sum']."元" : '0元';
            //存管出借红包
            $root['deal_red_sum']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$user_id." and rp.status=0 and rpn.red_type=1 and rp.end_time>".time());
            $root['deal_red_sum'] = $root['deal_red_sum'] ?$root['deal_red_sum']."元":'0元';

			$root['lock_money'] = strval($lock_money).'元';
			//红包	
			$root['red'] = strval($user_statics['red_money']+$user_statics['cunguan_red_money']).'元'; 
			//代金券金额					
			$root['cash'] = strval($user_statics["ecv_money"]+$user_statics["cunguan_ecv_money"]).'元';
			//累计出借收益
			$invest_total_money = sprintf('%.2f',$user_statics["invest_total_money"]+$user_statics["cunguan_invest_total_money"]);		
			$root['cum_profit'] = strval($invest_total_money).'元';
			//已收收益总计
			$load_repay_money = sprintf('%.2f',$user_statics["load_repay_money"]+$user_statics["cunguan_load_repay_money"]);
			$root['al_profit'] = strval($load_repay_money).'元';
			//待收收益总计
			$load_wait_earnings = sprintf('%.2f',$user_statics["load_wait_earnings"]+$user_statics["cunguan_load_wait_earnings"]);
			$root['take_profit'] = strval($load_wait_earnings).'元';
			//体验金收益 	
			$taste = sprintf('%.2f',$user_statics["taste"]+$user_statics["cunguan_taste"]);
		     
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

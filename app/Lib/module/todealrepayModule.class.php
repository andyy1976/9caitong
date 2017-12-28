<?php
/*
	禁止使用
	特别注意
*/
//require APP_ROOT_PATH.'app/Lib/deal.php';
class todealrepayModule extends SiteBaseModule
{
	/////任何人不要掉用该文件 --  1010
	public function gogogo(){
		//手动生成生成用户回款计划
		if($_REQUEST['test']=='123456'){
			$load_id=$_REQUEST['load_id'];
			//$arr=array('256785','256786','256787','256790','256797','256798');
			//SELECT id FROM jctp2p_deal_load WHERE `create_time`>1494518538 and id not in(SELECT `load_id` from  `jctp2p_deal_load_repay` );
			$arr=$GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."deal_load where create_time>1494518538 and id not in(SELECT load_id from jctp2p_deal_load_repay) limit 50");
			//var_dump($arr);exit;
			foreach($arr as $key=>$val){
				$load_id=$val['id'];
				//var_dump($load_id);exit;
				
				$deal_id=$GLOBALS['db']->getRow("SELECT deal_id,user_id,total_money,create_time FROM ".DB_PREFIX."deal_load where id=".$load_id);
				//var_dump($deal_id);
				$deal_con_two = $GLOBALS['db']->getRow("SELECT borrow_amount,load_money,buy_count,repay_time,rate,loantype FROM ".DB_PREFIX."deal where id=".$deal_id['deal_id']);
				$is_get=$GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."deal_load_repay where load_id=".$load_id);
				if($is_get){
					echo "已生成还款记录";exit;
				}else{
					$month_repay_money_f = av_it_formula($deal_id['total_money'],$deal_con_two['rate']/12/100);
					//月还利息--精确到小数点后两位
					$month_repay_money = round($month_repay_money_f,2);
					//var_dump($month_repay_money);exit;
					for($i=0;$i<$deal_con_two['repay_time'];$i++){
						//var_dump($deal_con_two);exit;
						$repay_data['u_key'] = $deal_con_two['buy_count']-1;
						$repay_data['l_key'] = $i;
						$repay_data['deal_id'] = $deal_id['deal_id'];
						$repay_data['load_id'] = $load_id;
						$repay_data['repay_id'] = 0;
						$repay_data['t_user_id'] = 0;
						$repay_data['user_id'] = $GLOBALS['user_info']['id'];
						$repay_data['repay_time'] = strtotime("+" . $i+1 . " months", $deal_id['create_time']);
						$repay_data['repay_date'] = to_date($repay_data['repay_time']);
						if($i+1 == $deal_con_two['repay_time']){
							$repay_data['repay_money'] = ($deal_id['total_money'] + round($month_repay_money_f*$deal_con_two['repay_time'],2)) - $month_repay_money*($deal_con_two['repay_time']-1);
							$repay_data['self_money'] = $deal_id['total_money'];
						}else{
							$repay_data['repay_money'] = $month_repay_money;
							$repay_data['self_money'] = 0;
						}
						$repay_data['raise_money'] = 0;
						$repay_data['interest_money'] = $repay_data['repay_money']-$repay_data['self_money'];
						$repay_data['repay_manage_money'] = 0;
						$repay_data['loantype'] = $deal_con_two['loantype'];
						$repay_data['has_repay'] = 0;
						$repay_data['manage_money'] = 0;
						$repay_data['reward_money'] = 0;
						$repay_data['interestrate_money'] = 0; //加息券
						//var_dump($repay_data);exit;
						$testhah=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data);
					}
				}
			}
			
			
		}else{
			echo "不符合条件";exit;
		}
	}
	
}
?>

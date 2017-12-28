<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class uc_invest_payment
{
	public function index(){
		/*
		Id:贷款单ID
		load_id：出借记录id
		*/
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$id = intval(base64_decode($GLOBALS['request']['id']));
		$load_id = intval(base64_decode($GLOBALS['request']['load_id']));
		$information_status = intval(base64_decode($GLOBALS['request']['information_status']));
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			if($information_status==1){
				$day_repay_list=getUcPlanReay($user['id'],$load_id);
				foreach($day_repay_list['list'] as $key =>$value){
					$data['benxi'] = sprintf("%.2f",$value['self_money']+$value['interest_money']);
					$data['interest_money'] = sprintf("%.2f",$value['interest_money']);
					$data['self_money'] = sprintf("%.2f",$value['self_money']);
					$data['repay_date'] = date("Y-m-d",$value['repay_time']);
					$user_load['self_money'] += $value['self_money'];
					$user_load['raise_money'] += 0;
					$user_load['interest_money'] += $value['interest_money'];
					$user_load['total_money'] +=  $value['self_money']+$value['interest_money'];
					$list[]=$data;
				}  
			}else{
			
			$user_loads = $GLOBALS['db']->getRow("select dl.total_money as total_money,dl.create_time,dl.id as id,d.repay_time as repay_time,d.rate as rate,d.old_deal_id from ".DB_PREFIX."deal_load as dl left join  ".DB_PREFIX."deal d on d.id = dl.deal_id  WHERE dl.id=".$load_id." and dl.user_id=".$user_id);
			$load = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load_repay where deal_id =".$id." and user_id=".$user_id." and load_id=".$load_id." order by repay_time asc");
			//转让标回款计划处理
			if($user_loads['old_deal_id']>0){
				$load = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load_repay where deal_id =".$user_loads['old_deal_id']." and user_id=".$user_id." and load_id=".$load_id." order by repay_time asc");
			}
			$root['load'] = $user_loads;
			if($load){
				foreach ($load as $k => $v) {
					$data['repay_date'] = $v['repay_date'];
					$data['benxi'] = $v['interest_money']+$v['self_money']+$v['raise_money']+$v['interestrate_money']+$v['increase_interest'];
					$data['interest_money'] = $v['interest_money']+$v['raise_money']+$v['interestrate_money']+$v['increase_interest'];
					$data['self_money'] = $v['self_money'];
					$list[]=$data;
				}
			}else{
				$i=1;
				for($i;$i<=$user_loads['repay_time'];$i++){
					if($i != $user_loads['repay_time']){
						$data['benxi'] = sprintf("%.2f",($user_loads['total_money'] * $user_loads['rate'])/12/100);
						$data['interest_money'] = sprintf("%.2f",($user_loads['total_money'] * $user_loads['rate'])/12/100);
						$data['self_money'] = "0.00";
					}else{
						$data['benxi'] = sprintf("%.2f",(($user_loads['total_money'] * $user_loads['rate'])/12/100)+$user_loads['total_money']);
						$data['interest_money'] = sprintf("%.2f",($user_loads['total_money'] * $user_loads['rate'])/12/100);
						$data['self_money'] = $user_loads['total_money'];
					}				
						
					$data['repay_date'] = date("Y-m-d",strtotime("+$i month",$user_loads['create_time']));
					$list[]=$data;
				}
			}

			$user_load = $GLOBALS['db']->getRow("SELECT self_money,raise_money,(interest_money+raise_money+increase_interest+interestrate_money) as interest_money,(self_money+interest_money+raise_money+increase_interest+interestrate_money) as total_money FROM  ".DB_PREFIX."deal_load_repay WHERE load_id=".$load_id." and user_id=".$user_id." order by repay_time desc limit 1");
				
			}
			$root['raise_money'] = $user_load['raise_money']; //募集期间利息
			$root['self_money'] = $user_load['self_money'];	//应收本金
			$root['interest_money'] = format_price($user_load['interest_money']);//应收利息
			$root['total_money'] = format_price($user_load['total_money']);
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$root['item'] = $list;
				
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "回款列表";
		output($root);		
	}
}
?>

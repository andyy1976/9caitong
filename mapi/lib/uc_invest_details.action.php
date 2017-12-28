<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_invest_details
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
        $machine_info=explode('|||',base64_decode($GLOBALS['request']['MachineInfo']));
        $information_status = intval(base64_decode($GLOBALS['request']['information_status']));
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			if($information_status == 1){
				$deal = get_plandeal($id);
			}else{
				$deal = get_deal($id);
			}
				
			$root['user_login_status'] = 1;
			if($information_status == 1){
				//理财计划
				$list = $GLOBALS['db']->getRow("SELECT de.id,de.deal_status,de.name,de.repay_time_type,de.interest_rate,de.repay_start_time,de.cunguan_tag,dl.id,dl.money,dl.total_money,dl.interestrate_id,de.rate,de.borrow_amount,de.repay_time,de.repay_time_type,dl.load_time as create_time,dl.red,dl.ecv_id FROM ".DB_PREFIX."plan_load dl left join ".DB_PREFIX."plan de on de.id = dl.plan_id WHERE dl.id=".$load_id." and dl.user_id=".$user_id." and de.id=".$id);
				$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
				$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
				$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
			}else{
				//普通项目
				$list = $GLOBALS['db']->getRow("SELECT de.id,de.debts,de.old_deal_id,de.is_new,de.deal_status,de.name,de.repay_time_type,de.interest_rate,de.repay_start_time,de.cunguan_tag,dl.id,dl.money,dl.total_money,dl.interestrate_id,dl.increase_interest,dl.rebate_money,de.rate,de.borrow_amount,de.repay_time,de.repay_time_type,dl.create_time,dl.red,dl.ecv_id,dl.debts as ldebts FROM ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal de on de.id = dl.deal_id WHERE dl.id=".$load_id." and dl.user_id=".$user_id." and de.id=".$id);
				$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
				$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
				$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
			}
			
			$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
			$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
			$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
			$load = ($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
            if($list['interestrate_id']){
                $interest_card=$GLOBALS['db']->getRow("select ic.rate,cp.interest_time from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon cp on ic.coupon_id=cp.id where ic.user_id = ".$user_id." and ic.id=".$list['interestrate_id']);
				$list['interest_card_rate'] = $interest_card['rate'];
				$list['interest_day'] =strval($interest_card['interest_time']).'天';
				if($interest_card['interest_time']==0){
                    $list['interest_day']='全程加息';
				}
            }else{
                $list['interest_card_rate'] = '';
                $list['interest_day'] ='';
			}

			//$list['interest'] = format_price($load)?format_price($load):0; //已收收益
			$list['interest'] = round($load,2);
			if($information_status == 1){
				$date_day = strtotime(date("Y-m-d",strtotime('+1 day',$list['create_time'])));
				$list['check_debt_status'] ="1";
				if($list['repay_start_time'])
					$list['check_debt'] = "";
				else
					//$list['check_debt'] = "次日显示";
					$list['check_debt'] = "满标后显示";
			}else{
				$list['check_debt_status'] ="0";
			}			
			$list['loantype_format'] = $deal['loantype_format']; //还款方式
			$list['create_time'] = date("Y-m-d",$list['create_time']); //起息日期
            $list['rate'] =strval($list['rate'] +  $list['interest_rate']);
			$list['rate'] = sprintf("%.1f",$list['rate']); //预计年化收益率
			if($list['interest_rate']>0){
				$list['interest_rate'] = sprintf("%.1f",$list['interest_rate']); //预计年化收益率
			}
			$list['cash'] = strval(ceil($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."ecv  where id in(".$list['ecv_id'].")"))); //代金券面值
			$repay = $list['repay_time'];
			$list['money'] = strval(ceil($list['money']));
			$list['red'] = strval($list['total_money']-$list['money']);
			$list['interest'] = sprintf("%.2f", $list['interest']);
			if($list['cunguan_tag']==0){
				$list['deal_status'] =5;
			}
			if($list['old_deal_id']>0){
				$list['deal_status'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$list['old_deal_id']);
			}
			
			$list['increase_interest'] = $list['increase_interest']?strval($list['increase_interest']):"";

            if(mb_strlen($deal['name'],'utf8')>10)
                $list['name'] = cut_str(strval($deal['name']),15).'...';
            else
                $list['name'] =strval($deal['name']);
			$list['sum_money'] = strval($list['total_money']);

			//合同下载地址
			if(in_array($deal['deal_status'], array(4,5))){
			    $list['contract_url'] = WAP_SITE_DOMAIN."/member.php?ctl=uc_invest&act=down_contract&id=".$id."&load_id=".$load_id;
			}
			if(in_array($list['deal_status'], array(4,5))){
			    $list['contract_url'] = WAP_SITE_DOMAIN."/member.php?ctl=uc_invest&act=down_contract&id=".$id."&load_id=".$load_id;
			}
			$data['endtime'] = date("Y-m-d",strtotime("+1 day",strtotime($list['create_time']))); //结息日期
			if($list['repay_time_type'] == 0){				
				$end_time = date("Y-m-d",strtotime("+$repay day",strtotime($data['endtime']))); 
			}else{
				$end_time = date("Y-m-d",strtotime("+$repay month",strtotime($data['endtime'])));
			}
			//预期收益
			if($information_status == 1){
				$interest_money = $GLOBALS['db']->getOne("select sum(interest_money+increase_interest+raise_money+interestrate_money) as interest_money from ".DB_PREFIX."deal_load_repay  where plan_load_id =".$load_id." and plan_id =".$id);
				if($interest_money){
					$list['rebate_money'] = $interest_money;
					$list['end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$id." and plan_load_id=".$load_id." order by id desc limit 1");
				}else{
					$list['rebate_money'] ="审核中";
					$list['end_time'] = "审核中";
				}
			}else{
				$interest_money = $GLOBALS['db']->getOne("select sum(interest_money+increase_interest+raise_money+interestrate_money) as interest_money from ".DB_PREFIX."deal_load_repay  where load_id =".$load_id);
				if($interest_money){
					$list['rebate_money'] = $interest_money;
					$list['end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE  load_id=".$load_id." order by id desc limit 1");
				}else{
					$list['rebate_money'] ="审核中";
					$list['end_time'] = "审核中";
				}
			}
			

            if($machine_info[0]=='Android'){
                $machine_info[1]=intval(str_replace('.','',$machine_info[1]));
                if($list['debts']==1 && $machine_info[1]<=211){
                    $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$list['old_deal_id']." order by repay_time desc limit 1");
                    $list['repay_time']= round(($last_repay_time-time())/3600/24/31,2);
                }elseif($list['debts']==1 && $machine_info[1]>211){
                    $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$list['old_deal_id']." order by repay_time desc limit 1");
//                     $list['repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
                    $invest_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where id=".$load_id);
                    $list['repay_time'] = ceil(($last_repay_time-$invest_time)/3600/24)+1;
                }
            }elseif($machine_info[0]=='iOS'){
                $machine_info[3]=intval(str_replace('.','',$machine_info[3]));
                if($list['debts']==1 && $machine_info[3]<=211){
                    $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$list['old_deal_id']." order by repay_time desc limit 1");
                    $list['repay_time']= round(($last_repay_time-time())/3600/24/31,2);
                }elseif($list['debts']==1 && $machine_info[3]>211){
                    $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$list['old_deal_id']." order by repay_time desc limit 1");
//                     $list['repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
                    $invest_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where id=".$load_id);
                    $list['repay_time'] = ceil(($last_repay_time-$invest_time)/3600/24)+1;
                }
            }
			if($user['debts']==1){
				if($list['debts']!=1&&$list['debts']!=2){
					if($list['deal_status']==2){
						$list['ldebts'] =3;//可转让
					}elseif($list['deal_status']==1){
						$list['ldebts']=1;
					}elseif($list['deal_status']==4){
						$list['ldebts']=3;
					}else{
						$list['ldebts'] =0;//可转让
					}
					if($list['is_new']==1){//新手标不可转让
						$list['ldebts'] =0;
					}
				}else{
					$list['ldebts'] =0;
				}

			}
            $root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$root['item'] = $list;
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "出借详情";
		output($root);
	}
}
?>

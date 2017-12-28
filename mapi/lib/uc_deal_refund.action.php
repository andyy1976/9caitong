<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_deal_refund
{
	public function index(){
		/*
		Id:贷款单ID
		load_id：出借记录id
		*/
		$root = get_baseroot();
		$page = intval(base64_decode($GLOBALS['request']['page']));
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		$deal_id = intval(base64_decode($GLOBALS['request']['id']));
		if ($user_id >0){
			$root['user_login_status'] = 1;
			$sql ="select id,name,borrow_amount,repay_start_time,repay_time,rate,deal_status from ".DB_PREFIX."deal where is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and cunguan_tag=1 and user_id = ".$user_id." and id=".$deal_id;
			$root['deal_con'] = $GLOBALS['db']->getRow($sql);
			$root['deal_con']['stage_money'] = 0;//本期应还
			$root['deal_con']['surplus_money'] = 0;//剩余应还
			$root['deal_con']['all_money'] = 0;//合计应还
			$root['deal_con']['rate'] = $root['deal_con']['rate']*100/100 ."%"; //预计年化收益率
			$root['deal_con']['repay_start_time'] = date("Y-m-d",$root['deal_con']['repay_start_time']);//放款时间
			$repay_list = $GLOBALS['db']->getAll("SELECT id,do_repay_type,repay_time,true_repay_time,repay_money,self_money,interest_money,true_interest_money,status,has_repay FROM " . DB_PREFIX . "deal_repay WHERE user_id=" . $user_id . " and deal_id =".$deal_id);
			$repay_time = $GLOBALS['db']->getOne("SELECT repay_time FROM " . DB_PREFIX . "deal_repay WHERE user_id=" . $user_id . " and deal_id =".$deal_id." and has_repay=1 order by id desc limit 1");
			/*******筛选可用字段********/
			$sta = 1;
			$self_money = 0;
			$one = 0;
			foreach ($repay_list as $k => $v) {
				$deal['repay_id'] = $v['id'];
				$deal['repay_money'] = $v['repay_money'];
				$deal['l_key'] = '第'.$sta.'/'.$root['deal_con']['repay_time'].'期';
				if($v['has_repay']==0 || $v['has_repay']==3){
					$deal['repay_money'] = $v['repay_money'];
					if(time()>strtotime(date("Y-m-d 23:59:59",$v['repay_time']))){
						$deal['status_con'] = "已逾期";
					}elseif($v['has_repay']==3){
						$deal['status_con'] = "已代偿";
					}else{
						$deal['status_con'] = "待还";
					}
					if($root['deal_con']['stage_money']==0){
						if(time()>strtotime(date("Y-m-d 23:59:59",$v['repay_time']))||time()>strtotime(date("Y-m-d 23:59:59",$repay_time))){
							$one_money = $v['repay_money']-$v['self_money'];
						}
						$root['deal_con']['stage_money'] = $v['repay_money'];
						$root['refund_one']['repay_time'] =  $sta.'/'.$root['deal_con']['repay_time'].'期';
						$root['refund_one']['repay_money'] =  $v['repay_money'];
						$root['refund_one']['repay_yuqi_date'] =  ceil((time()-$v['repay_time'])/86400)<0?0:ceil((time()-$v['repay_time'])/86400);
						$root['refund_one']['repay_yuqi_money'] =  '0.00';
						$root['refund_one']['repay_yeqi_rate'] = '0.00%';
						$root['refund_one']['repay_all_money'] =  strval($v['repay_money']+$root['refunt_one']['repay_yuqi_money']);
						if($root['refund_one']['repay_all_money']>$user['cunguan_money']){
							$root['refund_one']['status'] = 0;
							$root['refund_one']['show_msg'] = "可用余额不足，请先充值";
							$root['refund_one']['charge_url'] = WAP_SITE_DOMAIN."/member.php?ctl=uc_money&act=incharge";
						}else {
							$root['refund_one']['status'] = 1;
							if($v['do_repay_type'] == 2){
								$root['refund_one']['pay_url'] = WAP_SITE_DOMAIN."/index.php?ctl=uc_deal&act=verify_trans_password&type=company_over_repay&repay_id=".$v['id'];
							}elseif($v['do_repay_type'] == 1){
								$root['refund_one']['pay_url'] = WAP_SITE_DOMAIN."/index.php?ctl=uc_deal&act=verify_trans_password&type=over_repay&repay_id=".$v['id'];
							}else{
								$root['refund_one']['pay_url'] = WAP_SITE_DOMAIN."/index.php?ctl=uc_deal&act=verify_trans_password&type=repay_money&repay_id=".$v['id'];
							}
							
						}
					}
					$root['deal_con']['surplus_money'] += $v['repay_money'];
					$self_money += $v['self_money'];
				}else{
					$deal['status_con'] = "已还";
					if($v['status']==4){
						if($one==0){
							$money = $GLOBALS['db']->getRow("SELECT sum(self_money) as self_money,sum(true_repay_money) as repay_money FROM " . DB_PREFIX . "deal_repay WHERE user_id=" . $user_id . " and deal_id =".$deal_id." and status=4 and has_repay=1");
							$deal['repay_money'] = $money['repay_money'];
							$deal['self_money'] = $money['self_money'];
							$deal['interest_money'] = $v['true_interest_money'];
							$deal['repay_date'] = date("Y", $v['true_repay_time']) . '.' . date("m", $v['true_repay_time']) . '.' . date("d", $v['true_repay_time']);
							$one = 1;
						}else {
							$deal['repay_money'] = 0;
							unset($deal['self_money']);
							unset($deal['interest_money']);
							unset($deal['repay_date']);
						}
					}else {
						$deal['repay_money'] = $v['repay_money'];
						$deal['self_money'] = $v['self_money'];
						$deal['interest_money'] = $v['interest_money'];
						$deal['repay_date'] = date("Y", $v['true_repay_time']) . '.' . date("m", $v['true_repay_time']) . '.' . date("d", $v['true_repay_time']);
					}
				}
				$deal['repay_time'] = date("m",$v['repay_time']).'.'.date("d",$v['repay_time']);
				$list[] = $deal;
				$root['deal_con']['all_money'] += $v['repay_money'];
				$sta++;
			}
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$root['item'] = $list;
			$root['xieyi_url'] ="https://jctwapcg.9caitong.com/?ctl=user&act=repayment_agreement";
			if($root['deal_con']['deal_status']==4){
				$root['refund_all']['repay_time'] =  '全部还款';
				$root['refund_all']['repay_money'] =  $one_money+$self_money;
				$root['refund_all']['repay_yuqi_date'] =  0;
				$root['refund_all']['repay_yuqi_money'] =  '0.00';
				$root['refund_all']['repay_yeqi_rate'] = '0.00%';
				$root['refund_all']['repay_all_money'] = strval($one_money+$self_money+$root['refunt_one']['repay_yuqi_money']);
				if($root['refund_all']['repay_all_money']>$user['cunguan_money']){
					$root['refund_all']['status'] = 0;
					$root['refund_all']['show_msg'] = "可用余额不足，请先充值";
					$root['refund_all']['charge_url'] = WAP_SITE_DOMAIN."/member.php?ctl=uc_money&act=incharge";
				}else {
					$root['refund_all']['status'] = 1;
					$root['refund_all']['pay_url'] = WAP_SITE_DOMAIN . "/index.php?ctl=uc_deal&act=all_verify_trans_password&deal_id=" . $deal_id;
				}
				$root['but_status'] = 1;
			}else{
				$root['but_status'] = 0;
			}
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

<?php
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class uc_invest_cunguan
{
	public function index(){
		
		$root = get_baseroot();		
		$page = intval(base64_decode($GLOBALS['request']['page']));
		$information_status = intval(base64_decode($GLOBALS['request']['information_status']));
		$order = " order by dl.id desc";
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");	
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			//获取项目分类
			/*$loan_types=$GLOBALS['db']->getAll("SELECT id,name,information_status  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0");
			
			var_dump($loan_types);die;*/
			$loan_types = array(
	            array(
	            	"id" => "11",
	                "name" => "普通标",
	                "information_status"=>0,
	            ),
	            array(
	            	"id" => "12",
	                "name" => "短贷宝",
	                "information_status"=>1,
	            ),
	        );
			$root['deal_type'] = $loan_types;
			$loan_type_id=$GLOBALS['db']->getOne("SELECT id  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0 and information_status=".$information_status);
			if($information_status != 1){
//				$condition=" and de.type_id=".$loan_type_id;
				$condition=" and de.type_id <>12";
			}
			$root['user_login_status'] = 1;
			$status = intval(base64_decode($GLOBALS['request']['status']));

			if(isset($status) && $status == "1"){
				$condition.= " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =1";
				if($information_status == 1){
					$result = get_plan_invest_log($user_id,$condition,$order,$limit);
				}else{
					$result = get_invest_log($user_id,$condition,$order,$limit);
				}
				
			}elseif(isset($status) && $status=="2"){
				$condition.= " and de.deal_status = 5 and de.cunguan_tag =1";
				if($information_status == 1){
					$result = get_plan_invest_log($user_id,$condition,$order,$limit);
				}else{
					$result = get_invest_log($user_id,$condition,$order,$limit);
				}
			}
			/*******筛选可用字段********/
			foreach ($result['list'] as $k => $v) {
				$deal['information_status'] = $information_status;
				$deal['id'] = $v['id'];
                if(mb_strlen($v['name'],'utf8')>15){
                    $deal['name'] = cut_str(strval($v['name']),15).'...';
                }else{
                    $deal['name'] = strval($v['name']);
                }
				//预期收益
				if($information_status == 1){
					if($v['old_deal_id']>0){
						$deal['deal_status'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$v['old_deal_id']);
						$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+raise_money+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE load_id=".$v['bid']);
					}else{
						$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+raise_money+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$v['id']." and plan_load_id=".$v['bid']);
					}
					if($intterest_money){
						$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$v['bid']." and status!=4 and cunguan_tag=1");
						$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$v['bid']." and status=4 and cunguan_tag=1 order by id asc limit 1");
						$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$v['bid']."  and status=4 and cunguan_tag=1 order by id desc limit 1");
						$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
						$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
						$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
						$deal['rebate_money'] = sprintf('%.2f', $cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
						if($v['old_deal_id']>0){
							
							$deal['repay_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$v['old_deal_id']." and plan_load_id=".$v['bid']." order by id desc limit 1");
						}else{
							$deal['repay_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$v['id']." and plan_load_id=".$v['bid']." order by id desc limit 1");
						}
						

					}else{
						$deal['rebate_money'] ="审核中";
						$deal['repay_time'] = "审核中";
					}
				}else{
					if($v['old_deal_id']>0){
						$deal['deal_status'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$v['old_deal_id']);
						$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+raise_money+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE load_id=".$v['bid']);
					}else{
						$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+raise_money+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$v['id']." and load_id=".$v['bid']);
					}
					if($intterest_money){
						//$deal['rebate_money'] = $GLOBALS['db']->getOne("select sum(interest_money+increase_interest+raise_money+interestrate_money) as interest_money from ".DB_PREFIX."deal_load_repay  where load_id =".$v['bid']);
						$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$v['bid']." and status!=4 and cunguan_tag=1");
						$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$v['bid']." and status=4 and cunguan_tag=1 order by id asc limit 1");
						$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$v['bid']." and status=4 and cunguan_tag=1 order by id desc limit 1");
						$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
						$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
						$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
						$deal['rebate_money'] = sprintf('%.2f', $cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
						if($v['old_deal_id']>0){
							
							$deal['repay_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$v['old_deal_id']." and load_id=".$v['bid']." order by id desc limit 1");
						}else{
							$deal['repay_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$v['id']." and load_id=".$v['bid']." order by id desc limit 1");
						}
						

					}else{
						$deal['rebate_money'] ="审核中";
						$deal['repay_time'] = "审核中";
					}
				}
				
				$deal['money'] = strval(ceil($v['total_money']));
				$deal['load_id'] = $v['bid'];
				$list[] = $deal;
			}
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$root['item'] = $list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));
			/* 新消息 更新 做红点标记 2017-12-11 */
			$root['new_invest_record_id'] =$GLOBALS['db']->getOne("SELECT dl.id FROM ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal as d ON dl.deal_id =d.id WHERE dl.user_id=".$user_id."  and d.deal_status =1 order by dl.create_time desc limit 1");
			/* 新消息 更新 做红点标记 2017-12-11 结束 */
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "我的出借";
		output($root);		
	}
}
?>

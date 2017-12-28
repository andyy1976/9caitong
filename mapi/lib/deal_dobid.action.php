<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class deal_dobid
{
	public function index(){
		
		$root = array();
		/*
		Id:贷款单ID
		buy_money：投标金额
		cash_id：代金券id
		*/
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['response_code'] = 1;
			$root['user_login_status'] = 1;			
			$id = intval(base64_decode($GLOBALS['request']['id']));
			$plan_id = intval(base64_decode($GLOBALS['request']['plan_id']));
			if(isset($plan_id) && !empty($plan_id)){
				$deal=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."plan where id=".$plan_id);
			}else{
				$deal = get_deal($id);	
			}
																//获取借款信息
			$root['need_money'] = strval($deal['borrow_amount']-$deal['load_money']); 	//剩余购买金额
			$root['user_balance'] = strval(sprintf("%.2f",$user['cunguan_money'])); 			//存管账户余额
			$buy_money = intval(base64_decode($GLOBALS['request']["buy_money"])); 						//传入购买金额
			$cash_id = base64_decode($GLOBALS['request']["cash_id"]); 									//传入代金券id 以逗号连接 6,7格式
			//$cash_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ecv  where status = 0 and user_id = ".$user_id." and money <=".($buy_money/50)." and end_time >".time()." order by money desc,end_time asc");
			//修改返回用户所有可用代金券 朱湘
//			$cash_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_virtual_money  where status = 0 and user_id = ".$user_id." and cunguan_tag=1 and end_time >".time()." order by end_time desc,money asc");
			/********匹配最佳代金券使用组合*********/
            $time=time();
			if(!$deal['repay_time_type']&&!$plan_id){//如果repay_time_type为0
				$deal['repay_time'] = ceil($deal['repay_time']/31);
				if($deal['repay_time'] > 12){
					$deal['repay_time'] = 12;
				}
			}
			if($deal['debts']==1){
	            $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal['old_deal_id']." order by repay_time desc limit 1");
	            $deal['repay_time']= ceil((((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1)/31);
        	}
            if($deal['repay_time']==1){
                $repay="one_month";
            }elseif($deal['repay_time']==3){
                $repay="three_month";
            }elseif($deal['repay_time']==6){
                $repay="six_month";
            }elseif($deal['repay_time']==12){
                $repay="twelve_month";
            }elseif($deal['repay_time']==21){
				$repay="plan_day";
			}
			$root['cash_count'] = $GLOBALS['db']->getOne("select count(rp.id) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$user_id." and rp.status=0 and rp.packet_type=1 and rpn.".$repay."=1 and rp.end_time>".$time." order by rp.end_time desc");								//可用红包数量
            if(!$root['cash_count']){
                $root['cash_count']="0";
            }
            // $root['seq'] = "select count(*) from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.status=0 and ic.user_id=".$user_id." and ic.end_time >".time()." and c.".$repay."=1 order by ic.end_time desc";
            // 可用加息券数量
            $root['interest_card_count']=intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.status=0 and ic.user_id=".$user_id." and ic.end_time >".time()." and c.".$repay."=1 order by ic.end_time desc"));
//			$cash_list = array();
////			$root['voucher_coefficient'] = "50"; 										//代金券使用系数
//			if($root['need_money'] < $deal['min_loan_money']){							//判断是否为尾标
//				$root['red_money'] = "0";
//				$cash_list['cash_code'] = 0;
//				$cash_list['money'] = "尾标不可使用";
//				$root['wb_code'] = 1;
//			}else{
//				$root['red_money'] = $GLOBALS['db']->getOne("select cunguan_red_money from ".DB_PREFIX."user  where id = ".$user_id);
//				if($root['cash_count']){
//					if($cash_id){
//						$cash_list['cash_code'] = 1;  									//1 有可用代金券 0 无代金券
//						$cash = $GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."ecv  where id in(".$cash_id.")");
//						$cash_list['money'] = $cash; 									// 使用代金券面值
//						$cid =explode(",", $cash_id);
//						$cash_list['id'] = $cid; 										// 使用代金券id
//					}else{
//						$cash_list['cash_code'] = 1;
//						$cash_list['money'] = $cash_info[0]['money'];
//						$cash_list['id'] = $cash_info[0]['id'];
//					}
//				}else{
//					$cash_list['cash_code'] = 0;
//					$cash_list['money'] = "无代金券";
//				}
//			}
            $root['deal_id']=$id;
			$root['ratio'] = ($deal['rate']/12/100)*$deal['repay_time']; 				//预计收益系数
			$root['min_loan_money'] = $deal['min_loan_money']; 							//最小出借金额
            $root['cunguan_tag'] = $deal['cunguan_tag'];                               // 1存管标   0非存管标
			//$root['cash_money'] = $cash_list;											//推荐代金券
			$root['start_time'] = date("Y-m-d",time()); 								//计息日期
			$month = intval($deal['repay_time']);										//借款期限
			$root['end_time'] = date("Y-m-d",strtotime("+$month month")); 				//预计还款日期
			//新加字段 朱湘
			$root['recharge_url'] = WAP_SITE_DOMAIN ."/member.php?ctl=uc_money&act=incharge";
//             if($root['cunguan_tag']==1){
//                 $root['agreement_name']="《玖财通存管出借服务协议》";
//                 $root['invest_agree'] = WAP_SITE_DOMAIN.'/member.php?ctl=agreement&act=service';  //存管版出借协议链接地址
//             }elseif($root['cunguan_tag']==0){
//                 $root['agreement_name']="《玖财通出借服务协议》";
//                 $root['invest_agree'] = WAP_SITE_DOMAIN.'/member.php?ctl=agreement&act=service';  //出借协议链接地址
//             }
            $root['agreement_name'] = $deal['debts'] ? '《债权转让及受让协议》' : '《玖财通存管出借服务协议》';
			$root['invest_agree'] = WAP_SITE_DOMAIN.'/member.php?ctl=agreement&act=service&id='.$deal['id'];  //出借协议链接地址
			$root['risk_warn'] = WAP_SITE_DOMAIN.'/member.php?ctl=agreement&act=warning'; //风险提示链接地址
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);		
	}
}
?>

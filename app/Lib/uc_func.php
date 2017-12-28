<?php
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
	//查询会员日志
	function get_user_log($limit,$user_id,$t='')
	{
		if(!in_array($t,array("money","score","point")))
		{
			$t = "";
		}
		if($t=='')
		{
			$condition = "";
		}
		else
		{
			$condition = " and ".$t." <> 0 ";
		}
	
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where user_id = ".$user_id." $condition");
		$list = array();
		if($count > 0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where user_id = ".$user_id." $condition order by log_time desc limit ".$limit);
			
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	/**
	 * 会员资金日志
	 * $limit 数量
	 * $user_id 用户id
	 * $status -1全部
	 * $condition 其他条件
	 */
    function get_user_money_log($limit,$user_id,$type=-1,$condition){
        $extWhere = "";
        if($type >= 0){
            $extWhere.=" AND `type`=".$type;
        }
        $user_id = intval($user_id);
        $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_money_log where user_id =".$user_id." $extWhere $condition");
        $list = array();
        if($count > 0){

            $list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_money_log where user_id =".$user_id." $extWhere $condition order by id desc limit ".$limit);
        }
        return array("list"=>$list,'count'=>$count);
    }
	
	/**
	 * 会员冻结资金日志
	 * $limit 数量
	 * $user_id 用户id
	 * $status -1全部
	 * $condition 其他条件
	 */
	function get_user_lock_money_log($limit,$user_id,$type=-1,$condition){
		$extWhere = "";
		if($type >= 0){
			$extWhere.=" AND `type`=".$type;
		}
			
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_lock_money_log where user_id = ".$user_id." $extWhere $condition");
		$list = array();
		if($count > 0){
			
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_lock_money_log where user_id = ".$user_id." $extWhere $condition order by id desc limit ".$limit);
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	/**
	 * 会员信用积分日志
	 * $limit 数量
	 * $user_id 用户id
	 * $status -1全部
	 * $condition 其他条件
	 */
	function get_user_point_log($limit,$user_id,$type=-1,$condition){
		$extWhere = '';
		if($type >= 0){
			$extWhere.=" AND `type`=".$type;
		}
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_point_log where user_id = ".$user_id." $extWhere  $condition");
		$list = array();
		if($count > 0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_point_log where user_id = ".$user_id." $extWhere  $condition order by id DESC limit ".$limit);
		return array("list"=>$list,'count'=>$count);
	}
	
	/**
	 * 会员积分日志
	 * $limit 数量
	 * $user_id 用户id
	 * $status -1全部
	 * $condition 其他条件
	 */
	function get_user_score_log($limit,$user_id,$type=-1,$condition){
		$extWhere = '';
		if($type >= 0){
			$extWhere.=" AND `type`=".$type;
		}
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where user_id = ".$user_id." $extWhere  $condition");
		$list = array();
		if($count > 0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_score_log where user_id = ".$user_id." $extWhere  $condition order by id DESC limit ".$limit);
		return array("list"=>$list,'count'=>$count);
	}
	
	/**
	 * 不可提现资金日志
	 * $limit 数量
	 * $user_id 用户id
	 * $status -1全部
	 * $condition 其他条件
	 */
	function get_user_nmc_amount_log($limit,$user_id,$type=-1,$condition){
		$extWhere = '';
		if($type >= 0){
			$extWhere.=" AND `type`=".$type;
		}
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_money_log where user_id = ".$user_id." $extWhere AND (`type`= 22 OR `type`= 28 OR `type`= 29)  $condition");
		$list = array();
		if($count > 0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_money_log where user_id = ".$user_id." $extWhere AND (`type`= 22 OR `type`= 28 OR `type`= 29)  $condition order by id DESC limit ".$limit);
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员充值订单
	function get_user_incharge($limit,$user_id)
	{
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 1 and is_delete = 0");
		$list = array();
		if($count > 0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 1 and is_delete = 0 order by create_time desc limit ".$limit);
		
			foreach($list as $k=>$v)
			{
				$list[$k]['payment_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$v['id']);
				$list[$k]['payment'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$v['payment_id']);
			}
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//new查询会员充值订单日志
	function get_user_incharge_log($limit,$user_id,$condition)
	{
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment_notice pn left join ".DB_PREFIX."payment p on pn.payment_id = p.id where  pn.user_id = ".$user_id."$condition");
		$list = array();
		if($count > 0){
			//$list = $GLOBALS['db']->getAll("select pn.*,name from ".DB_PREFIX."payment_notice pn left join ".DB_PREFIX."payment p on pn.payment_id = p.id  where pn.user_id = ".$user_id."  $condition  order by pn.pay_time desc,pn.id desc limit ".$limit);
			$list = $GLOBALS['db']->getAll("select pn.*,name  from ".DB_PREFIX."payment_notice pn left join ".DB_PREFIX."payment p on pn.payment_id = p.id  where pn.user_id = ".$user_id."$condition order by pn.id desc limit ".$limit);
            foreach($list as $k=>$v)
			{
				if($list[$k]['is_paid'] == 1){
					$list[$k]['is_paid_format']="充值-成功";
				}else if($list[$k]['is_paid'] == 2){
					$list[$k]['is_paid_format']="充值-进行中";
				}else{
					$list[$k]['is_paid_format']="充值-失败";
				}
				$list[$k]['create_time_format'] = to_date($list[$k]['create_time'],"Y-m-d H:i");
				$list[$k]['pay_time_format'] = to_date($list[$k]['pay_time'],"Y-m-d H:i");
				$list[$k]['money_format'] = format_price($list[$k]['money']);
				
			}
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员提现记录
	function get_user_carry($limit,$user_id,$cg_tag)
	{

		$rec_type=$GLOBALS['user_info']['user_type'];//1是企业 0是存管

		$user_id = intval($user_id);
		if($cg_tag == 2){
			if($rec_type=="0"){ 
				$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_carry where user_id = ".$user_id." and cunguan_tag=1 and cunguan_pwd=1");
			}else{ 
				$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_carry where user_id = ".$user_id." and cunguan_tag=1 and user_type=2");
			}
        }else{
		    $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_carry where user_id = ".$user_id." and cunguan_tag=0");
		}
		
		$list = array();
		if($count > 0){
		    if($cg_tag == 2){
		    	if($rec_type == "0"){ 
		    		 $list = $GLOBALS['db']->getAll("select uc.*,ub.bank_id,ub.bankcard,b.name as bank_name from ".DB_PREFIX."user_carry uc LEFT JOIN ".DB_PREFIX."user_bank ub ON uc.user_id=ub.user_id left join ".DB_PREFIX."bank b on ub.bank_id=b.bankid  where uc.user_id = ".$user_id." and uc.cunguan_tag=1 and uc.cunguan_pwd=1 and ub.cunguan_tag=1 and ub.status=1 order by create_time desc limit ".$limit);
		    	}else{
		    		$list = $GLOBALS['db']->getAll("select uc.money,uc.create_time,uc.fee,uc.status,cr.corpacc from ".DB_PREFIX."user_carry uc LEFT JOIN ".DB_PREFIX."company_reginfo cr ON uc.user_id=cr.user_id where cr.user_id = ".$user_id." and uc.cunguan_tag=1 and uc.user_type=2 order by uc.create_time desc limit ".$limit);
		    	} 
		       
		    }else{
		        $list = $GLOBALS['db']->getAll("select uc.*,b.name as bank_name from ".DB_PREFIX."user_carry uc LEFT JOIN ".DB_PREFIX."bank b ON b.id=uc.bank_id  where user_id = ".$user_id." and uc.cunguan_tag=0 order by create_time desc limit ".$limit);
		    }
			foreach($list as $k=>$v)
			{
			if($v['status']==0){
					$list[$k]['status_format'] = "提现审核中";
				}
				elseif($v['status']==1){
					$list[$k]['status_format'] = "提现成功";
				}
				elseif($v['status']==2){
					$list[$k]['status_format'] = "提现失败";
				}
				elseif($v['status']==3){
					$list[$k]['status_format'] = "提现审核中";//"待付款";
				}
				elseif($v['status']==4){
					$list[$k]['status_format'] = "已撤销";
				}
				elseif($v['status']==5){
				    $list[$k]['status_format'] = "存管打款中";
				}
				elseif($v['status']==6){
				    $list[$k]['status_format'] = "存管受理失败";
				}
			}
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员的团购券
	function get_user_coupon($limit,$user_id,$status=0)
	{
		$user_id = intval($user_id);
		$ext_condition = '';
		if($status==1)
		{
			$ext_condition = " and confirm_time = 0 ";
		}
		if($status==2)
		{
			$ext_condition = " and confirm_time <> 0 ";
		}
		
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition);
		$list = array();
		if($count > 0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition." order by order_id desc limit ".$limit);
		
			foreach($list as $k=>$v)
			{
				if($GLOBALS['db']->getOne("select forbid_sms from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
				{
					//禁止发券时，将已发数改为上限
					$list[$k]['sms_count'] = app_conf("SMS_COUPON_LIMIT");
				}
				$list[$k]['deal_item'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$v['order_deal_id']);
			}
		}
		
		return array("list"=>$list,'count'=>$count);		
	}
	
	
	//查询会员订单
	function get_user_order($limit,$user_id)
	{
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0");
		$list = array();
		if($count >0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0 order by create_time desc limit ".$limit);
			
			foreach($list as $k=>$v)
			{
				$list[$k]['payment_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$v['id']);
			}
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员抽奖
	function get_user_lottery($limit,$user_id)
	{
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where user_id = ".$user_id);
		$list = array();
		if($count > 0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."lottery where user_id = ".$user_id." order by create_time desc limit ".$limit);
			
			foreach($list as $k=>$v)
			{
				$list[$k]['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
				$list[$k]['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
				if($v['buyer_id']==0)
				{
					$buyer = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
				}
				else
				{
					$buyer = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['buyer_id']);
				}
				$list[$k]['buyer'] = $buyer;
			}	
		}
		
		return array("list"=>$list,'count'=>$count);
	}
	
	/**
	 * 授信额度申请
	 */
	function get_deal_quota_list($limit,$user_id,$condition)
	{
		$user_id = intval($user_id);
		$sql = "select * from ".DB_PREFIX."deal_quota_submit  where user_id = ".$user_id." $condition order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."deal_quota_submit  where user_id = ".$user_id." $condition ";
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0){
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v){
				if($list[$k]['borrow_amount'] < 100)
					$list[$k]['borrow_amount_format'] = format_price($list[$k]['borrow_amount']);
				else
					$list[$k]['borrow_amount_format'] = format_price($list[$k]['borrow_amount']/10000)."万";
				
				 $list[$k]['create_time_format'] = to_date($v['create_time'],'Y-m-d H:i');
				 $list[$k]['update_time_format'] = to_date($v['update_time'],'Y-m-d H:i');
				 if($v['status']==0)
				 	$list[$k]['status_format'] = "未审核";
				 elseif($v['status']==1)
				 	$list[$k]['status_format'] = "已通过";
				 elseif($v['status']==2)
					$list[$k]['status_format'] = "未通过";
			}
		}
		
		return array("list"=>$list,'count'=>$count);
	}
	//查询信用值申请列表
	function get_quota_list($limit,$user_id,$condition)
	{	
		$user_id = intval($user_id);
		$sql = "select * from ".DB_PREFIX."quota_submit  where user_id = ".$user_id." $condition order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."quota_submit  where user_id = ".$user_id." $condition ";
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0){
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v){
				$list[$k]['create_time_format'] = to_date($v['create_time'],'Y-m-d H:i');
				$list[$k]['op_time_format'] = to_date($v['op_time'],'Y-m-d H:i');
				if($v['status']==0)
				 	$list[$k]['status_format'] = "未审核";
				elseif($v['status']==1)
				 	$list[$k]['status_format'] = "已通过";
				elseif($v['status']==2)
					$list[$k]['status_format'] = "未通过";
			}
		}
			
		
		return array("list"=>$list,'count'=>$count);
	}

// 查询投资可用加息券列表  zhao
function get_interest_card_list($limit,$user_id,$deal_id){
    $deal_repay=$GLOBALS['db']->getRow("select repay_time,repay_time_type from ".DB_PREFIX."deal where id=".$deal_id);
    if($deal_repay['repay_time']==1){
        $repay_time="one_month";
    }elseif($deal_repay['repay_time']==3){
        $repay_time="three_month";
    }elseif($deal_repay['repay_time']==6){
        $repay_time="six_month";
    }elseif($deal_repay['repay_time']==12){
        $repay_time="twelve_month";
    }
    $time=time();
    $user_id = intval($user_id);
    $sql = "select c.use_condition,ic.id,ic.begin_time,ic.end_time,c.interest_time,c.interest_time_type,ic.content,c.use_condition,c.card_name,ic.rate,ic.use_time,ic.status from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.user_id=".$user_id." and c.".$repay_time."=1 and ic.status=0 and ic.end_time>".$time." order by ic.end_time asc limit ".$limit;

    $sql_count = "select count(*) from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.user_id=".$user_id." and c.".$repay_time."=1 and ic.status=0 and ic.end_time>".$time;
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);

    return array("list"=>$list,'count'=>$count);
}

// 查询理财计划投资可用加息券列表  zhao
function get_lcinterest_card_list($limit,$user_id,$plan_id){
    $deal_repay=$GLOBALS['db']->getRow("select repay_time from ".DB_PREFIX."plan where id=".$plan_id);
	$repay_time="plan_day";
    $time=time();
    $user_id = intval($user_id);
    $sql = "select c.use_condition,ic.id,ic.begin_time,ic.end_time,c.interest_time,c.interest_time_type,ic.content,c.use_condition,c.card_name,ic.rate,ic.status from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.user_id=".$user_id." and c.".$repay_time."=1 and ic.status=0 and ic.end_time>".$time." order by ic.end_time asc limit ".$limit;
    $sql_count = "select count(*) from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.user_id=".$user_id." and c.".$repay_time."=1 and ic.status=0 and ic.end_time>".$time;
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();

    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);


    return array("list"=>$list,'count'=>$count);
}





// 查询用户中心加息券列表  zhao
function get_uc_interest_card_list($limit,$user_id,$condition){
    $time=time();
    $user_id = intval($user_id);
    $sql = "select c.use_condition,ic.id,ic.begin_time,ic.end_time,c.interest_time,c.interest_time_type,ic.content,c.use_condition,c.card_name,ic.rate,ic.status from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.user_id=".$user_id.$condition." order by ic.end_time desc,status asc limit ".$limit;
    $sql_count = "select count(*) from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.user_id = ".$user_id.$condition;
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);

    return array("list"=>$list,'count'=>$count);
}
// 计算加息收益
function get_interestrate_money($interestrate_id,$total_money,$deal_id,$plan_id=''){
	if (!empty($plan_id)) {
		$repay_time=$GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."plan where id=".$plan_id);
		//是否是天标
		$repay_time_type =$GLOBALS['db']->getOne("select repay_time_type from ".DB_PREFIX."plan where id=".$plan_id);
	}else{
		$repay_time=$GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."deal where id=".$deal_id);
	}
    $sql="select ic.use_time,ic.rate,c.interest_time,c.interest_time_type from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.id=".$interestrate_id;
    $interest=$GLOBALS['db']->getRow($sql);
    if($interest['use_time']!=0&&$interest['interest_time_type']==0){
		if($repay_time_type==0&&$repay_time<=$interest['use_time']){
			 $interestrate_money=($interest['rate']/100/365) * $total_money*$interest['use_time'];
		}elseif($repay_time_type==0&&$repay_time>$interest['use_time']){
			$interestrate_money=($interest['rate']/100/365) * $total_money*$repay_time;
		}else{
			$interestrate_money=($interest['rate']/100/365) * $total_money*$interest['use_time'];
		}
        //$interestrate_money=($interest['rate']/100/365) * $total_money*$interest['use_time'];
    }elseif($interest['use_time']==0){
		if($repay_time_type==0){
			$interestrate_money=($interest['rate']/100/365) * $total_money*$repay_time;
		}else{
			$interestrate_money=($interest['rate']/100/12) * $total_money*$repay_time;
		}
        //$interestrate_money=($interest['rate']/100/12) * $total_money*$repay_time;
    }elseif($interest['use_time']!=0&&$interest['interest_time_type']==1){
        $interestrate_money=($interest['rate']/100/12) * $total_money*$interest['use_time'];
    }
    return $interestrate_money;
}
// 查询投资可用红包列表
function get_red_list($limit,$user_id,$deal_id,$is_plan=false)
{

	$deal_repay=$GLOBALS['db']->getRow("select repay_time,repay_time_type,old_deal_id,debts from ".DB_PREFIX."deal where id=".$deal_id);

	if(!$is_plan){
		if(!$deal_repay['repay_time_type']){//如果repay_time_type为0
			$deal_repay['repay_time'] = ceil($deal_repay['repay_time']/31);
		}
		if($deal_repay['repay_time']==1){
		   $repay_time="one_month";
		}elseif($deal_repay['repay_time']<=3){
			$repay_time="three_month";
		}elseif($deal_repay['repay_time']<=6){
			$repay_time="six_month";
		}elseif($deal_repay['repay_time']<=12){
			$repay_time="twelve_month";
		}
	}else{
		$repay_time="plan_day";
	}
	 
	
    
    $time=time();

    $user_id = intval($user_id);
    $sql = "select rp.id,rp.begin_time,rp.end_time,rp.content,rp.status,rp.money,rpn.use_condition,rpn.amount,rpn.ratio,rpn.is_increase,rpn.red_type from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id=".$user_id." and rpn.red_type=1 and rpn.".$repay_time."=1 and rp.status=0 and rp.end_time>".$time." order by rp.end_time asc limit ".$limit;

    $sql_count = "select count(*) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id=".$user_id." and rpn.red_type=1 and rpn.".$repay_time."=1 and rp.status=0 and rp.end_time>".$time;
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);

    return array("list"=>$list,'count'=>$count);
}
/*
 * 查询用户中心红包列表
 * $red_type_id    1
 */
function get_uc_red_list($limit,$user_id,$condition)
{
    $time=time();
    $user_id = intval($user_id);
    $sql = "select rp.id,rp.begin_time,rp.end_time,rp.content,rp.status,rpn.use_condition,rpn.amount,rp.money,rpn.ratio,rpn.is_increase,rpn.red_type from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id=".$user_id." $condition order by rp.status asc,rp.end_time desc limit ".$limit;
    $sql_count = "select count(*) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id  where user_id = ".$user_id." $condition";
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);

    return array("list"=>$list,'count'=>$count);
}
//查询代金券列表
function get_voucher_list($limit,$user_id)
{
    $user_id = intval($user_id);
    $sql = "select e.*,et.name,et.gen_count,et.send_type,et.exchange_score,et.exchange_limit,et.exchange_sn from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.cunguan_tag=1 and e.user_id = ".$user_id." order by e.id desc limit ".$limit;
    $sql_count = "select count(*) from ".DB_PREFIX."ecv where cunguan_tag=1 and user_id = ".$user_id;
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);
    foreach($list as $k=>$v){
        if($v['end_time']>1498924800){
            $list[$k]['end_time']=1498924800;
        }
    }

    return array("list"=>$list,'count'=>$count);
}
//查询红包列表
function get_red_packet_list($limit,$user_id,$condition)
{
    $user_id = intval($user_id);
    $sql = "select r.id,r.money,r.begin_time,r.end_time,r.content,rpe.ratio,rpe.use_condition from ".DB_PREFIX."red_packet r left join ".DB_PREFIX."red_packet_newconfig rpe on r.red_type_id = rpe.id where user_id = ".$user_id." $condition order by money asc limit ".$limit;
    $sql_count = "select count(*) from ".DB_PREFIX."red_packet r where r.user_id = ".$user_id." $condition";
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);

    return array("list"=>$list,'count'=>$count);
}

//查询加息卡列表
function get_interest_increase_list($limit,$user_id,$condition)
{
    $user_id = intval($user_id);
    $sql = "select ic.*,c.interest_time,c.use_condition from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on c.id=ic.coupon_id  where ic.user_id = ".$user_id." $condition order by ic.rate asc limit ".$limit;
    $sql_count = "select count(*) from ".DB_PREFIX."interest_card as ic where user_id = ".$user_id." $condition";
    $count = $GLOBALS['db']->getOne($sql_count);
    $list = array();
    if($count > 0)
        $list = $GLOBALS['db']->getAll($sql);

    return array("list"=>$list,'count'=>$count);
}
	//查询可兑换代金券列表
	function get_exchange_voucher_list($limit)
	{
		$sql = "select * from ".DB_PREFIX."ecv_type where send_type = 1 order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."ecv_type where send_type = 1";
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0)
			$list = $GLOBALS['db']->getAll($sql);
		
		return array("list"=>$list,'count'=>$count);
	}


    function voucher_list($limit,$user_id,$condition){
        $user_id = intval($user_id);
        //$sql = "select end_time,money,status,ecv_type_id,activity_id,content from ".DB_PREFIX."taste_cash where user_id=$user_id $condition order by status asc,(end_time-now()) desc,id desc";
        $sql = "select money,user_id,id,create_time,end_time,use_status,cunguan_tag from ".DB_PREFIX."taste_cash where user_id=$user_id $condition order by id desc";
        $sql_count = "select count(*) from ".DB_PREFIX."taste_cash where user_id =$user_id $condition";
        $count = $GLOBALS['db']->getOne($sql_count);

        if($limit !=""){
            $sql .=" limit ".$limit;
        }

        $list = array();
        $list = $GLOBALS['db']->getAll($sql);

        return array("list"=>$list,'count'=>$count);
    }


    function red_packets($limit,$user_id){
        
        /*
        $red_money = $GLOBALS['db']->getOne("select red_money from ".DB_PREFIX."user  where id = ".$GLOBALS['user_info']['id']);
        $sql = "select e.type,e.create_time,e.money,e.account_money,e.memo,et.red_money from ".DB_PREFIX."user_red_money_log as e left join ".DB_PREFIX."user as et on e.user_id = et.id where e.user_id= ".$GLOBALS['user_info']['id'];
        $red_money_list = $GLOBALS['db']->getAll($sql);
        */
        $user_id = intval($user_id);
        //$sql ="select money,user_id,memo,create_time_ymd,account_money,type,create_time from jctp2p_user_money_log where user_id=$user_id and  type=56 order by id desc";
        $sql_count="select count(*) from jctp2p_user_red_money_log where user_id=$user_id and type in(56,0) order by id asc";
        $sql = "select money,account_money,memo,create_time from jctp2p_user_red_money_log where user_id=$user_id and type in(56,0) order by id desc";
        $count = $GLOBALS['db']->getOne($sql_count);
        if($limit !=""){
            $sql .=" limit ".$limit;
        }
        $list = array();
        $list = $GLOBALS['db']->getAll($sql);

        return array("list"=>$list,'count'=>$count);
    }
	
	//查询加息券列表
	function get_interestrate_list($limit,$user_id,$type=0)
	{
		$user_id = intval($user_id);
		$condition = "";
		if($type==1)
		{
			$condition = " and (et.use_type = 1 or et.use_type = 2) ";
		}
		else
		{
			//$condition = " and use_type = 0 ";
		}
		$sql = "select *,e.id as i_id from ".DB_PREFIX."interestrate as e left join ".DB_PREFIX."interestrate_type as et on e.ecv_type_id = et.id where ((e.user_id = ".$user_id." and e.to_user_id = 0) or e.to_user_id = ".$user_id.") ".$condition." order by e.id desc limit ".$limit;
		
		$sql_count = "select count(*) from ".DB_PREFIX."interestrate e left join ".DB_PREFIX."interestrate_type as et on e.ecv_type_id = et.id where ((e.user_id = ".$user_id." and e.to_user_id = 0) or e.to_user_id = ".$user_id.")".$condition;
		
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0)
			$list = $GLOBALS['db']->getAll($sql);
		
		foreach($list as $k=>$v)
		{
			$list[$k]["rate_format"] = number_format($v["rate"],2)."%"; 
			if($v["use_type"] == 0)
			{
				$list[$k]["use_type_name"] = "PC端";
			}
			elseif($v["use_type"] == 1)
			{
				$list[$k]["use_type_name"] = "手机端";
			}
			elseif($v["use_type"] == 2)
			{
				$list[$k]["use_type_name"] = "通用";
			}			
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询可兑换加息券列表
	function get_exchange_interestrate_list($limit)
	{
		$sql = "select * from ".DB_PREFIX."interestrate_type where send_type = 1 and (end_time = 0 or end_time >= ".to_timespan(to_date(TIME_UTC,"Y-m-d")).") order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."interestrate_type where send_type = 1 and (begin_time = 0 or begin_time <= ".to_timespan(to_date(TIME_UTC,"Y-m-d")).") and (end_time = 0 or end_time >= ".to_timespan(to_date(TIME_UTC,"Y-m-d")).")";
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0)
			$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v)
		{
			$list[$k]["rate_format"] = number_format($v["rate"],2)."%"; 
			if($v["use_type"] == 0)
			{
				$list[$k]["use_type_name"] = "PC端";
			}
			elseif($v["use_type"] == 1)
			{
				$list[$k]["use_type_name"] = "手机端";
			}
			elseif($v["use_type"] == 2)
			{
				$list[$k]["use_type_name"] = "通用";
			}
			
			if($v['begin_time'] > 0)
			{
				$list[$k]['begin_date'] = to_date($v['begin_time'],"Y-m-d");
			}
			else
			{
				$list[$k]['begin_date'] = "即时生效";
			}
			
			if($v['end_time'] > 0)
			{
				$list[$k]['end_date'] = to_date($v['end_time'],"Y-m-d");
			}
			else
			{
				$list[$k]['end_date'] = "永不过期";
			}
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	function get_collect_list($limit,$user_id)
	{
		$user_id = intval($user_id);
		$sql_count = "select count(*) from ".DB_PREFIX."deal_collect where user_id = ".$user_id;
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0){
			$sql = "select d.*,c.create_time as add_time ,c.id as cid,u.user_name,u.level_id,u.province_id,u.city_id from ".DB_PREFIX."deal_collect as c left join ".DB_PREFIX."deal as d on d.id = c.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id= d.user_id where d.is_delete=0 and d.publish_wait = 0 and c.user_id = ".$user_id." order by c.create_time desc limit ".$limit;
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v){
				$list[$k]['is_wait'] = 0;
				if($list[$k]['start_time'] > TIME_UTC){
					$list[$k]['is_wait'] = 1;
					$list[$k]['remain_time'] = $list[$k]['start_time'] - TIME_UTC;
				}
				else{
					$list[$k]['remain_time'] = $list[$k]['start_time'] + $list[$k]['enddate']*24*3600 - TIME_UTC;
				}
				$list[$k]['borrow_amount_format'] = format_price($v['borrow_amount']);
				$list[$k]['borrow_amount_format'] = format_price($v['borrow_amount']/10000)."万";//format_price($deal['borrow_amount']);
				$list[$k]['rate_foramt_w'] = number_format($v['rate'],2)."%";
				
				$list[$k]['rate_foramt'] = number_format($v['rate'],2);
				//本息还款金额
				$list[$k]['month_repay_money'] = format_price(pl_it_formula($v['borrow_amount'],$v['rate']/12/100,$v['repay_time']));
				//还需多少钱
				$list[$k]['need_money'] = format_price($v['borrow_amount'] - $v['load_money']);
				//百分比
				if($v['deal_status']==4){
					$list[$k]['month_repay_money'] = pl_it_formula($v['borrow_amount'],$v['rate']/12/100,$v['repay_time']);
					$list[$k]['remain_repay_money'] = $list[$k]['month_repay_money'] * $v['repay_time'];
					$list[$k]['progress_point'] =  round($v['repay_money']/$list[$k]['remain_repay_money']*100,2);
				}else{
					$list[$k]['progress_point'] = $v['load_money']/$v['borrow_amount']*100;
				}
				$user_location = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($v['city_id']),false);
				if($user_location=='')
					$user_location = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($v['province_id']),false);
				$list[$k]['user_location'] = $user_location;
				$list[$k]['point_level'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_level where id = ".intval($v['level_id']));
				$durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=".$v['id'];
				$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
			}
		}
		
		return array("list"=>$list,'count'=>$count);
	}
	//理财计划出借列表
	function getPlanInvestList($mode = "index", $user_id = 0, $page = 0,$lending="",$user_pwd='',$user_name=''){
		if ($user_id > 0){	
			$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";            
			switch($mode){
				case "index" :
					$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";
					break;
				case "invite" :
					$condtion = "   AND d.deal_status in(1,2,4)  ";
					break;				
				case "over" :
					$condtion = "   AND d.deal_status = 5  ";
					break;
			}
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			$sql = "select d.*,pl.money as u_load_money,pl.load_date as create_date,pl.id as load_id from ".DB_PREFIX."plan_load pl left join ".DB_PREFIX."plan d on d.id = pl.plan_id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and pl.user_id = ".$user_id." $condtion $lending order by pl.load_time desc limit ".$limit;

			$sql_count = "select count(DISTINCT pl.id) from ".DB_PREFIX."plan_load pl left join ".DB_PREFIX."plan d on d.id = pl.plan_id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and pl.user_id = ".$user_id." $condtion $lending";
			$count = $GLOBALS['db']->getOne($sql_count);
			$list = array();
			if($count >0){
				$list = $GLOBALS['db']->getAll($sql);
				foreach ($list as $k => $v) {
					$list[$k]['deal_type'] = 1; //1 理财计划 
				}				
			}		
			return array('list'=>$list,'count'=>$count);
		}else{
			return array();
		}
	}
	function getInvestList($mode = "index", $user_id = 0, $page = 0,$lending="",$user_pwd='',$user_name='') {
		
		if ($user_id > 0){	
			$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";
            /*switch($mode){
                case "index":
                    $condtion=$condtion;
                    break;
                case "over":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)<='".date('Y-m-d')."'";
                    break;
                case "invite":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."'";
                    break;
            }*/
			switch($mode){
				case "index" :
					$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";
					break;
				case "invite" :
					$condtion = "   AND d.deal_status in(1,2,4)  ";
					break;
				case "in" :
					$condtion = "   AND d.deal_status =1  ";
					break;
				case "full" :
					$condtion = "   AND d.deal_status =2  ";
					break;
				case "flow" :
					$condtion = "   AND d.deal_status =3  ";
					break;
				case "ing" :
					$condtion = "   AND d.deal_status =4  ";
					break;
				case "over" :
					$condtion = "   AND d.deal_status =5  ";
					break;
				case "conduct" :
					$condtion = "   AND d.deal_status in(1,2,4)";
					break;
				case "bad" :
					$condtion = "   AND d.deal_status = 4 AND (".TIME_UTC." - d.next_repay_time)/24/3600 >=".trim(app_conf("YZ_IMPSE_DAY"))." and d.last_repay_time > 0 ";
					break;
			}
			$condtion.= " and dl.plan_load_id = 0";
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			//$sql = "select d.*,u.user_name,dl.money as u_load_money,u.level_id,u.province_id,u.city_id,dl.id as load_id from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=d.user_id where dl.user_id = ".$user_id." $condtion group by dl.id order by dl.create_time desc limit ".$limit;
			//$sql_count = "select count(DISTINCT dl.id) from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id where d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." $condtion ";
			
			$sql = "select d.id,d.debts as deal_debts,d.old_deal_id as deal_old_deal_id,d.repay_start_time,d.interest_rate,dl.total_money,d.rate,d.name,d.repay_time,d.repay_time_type,d.deal_status,dl.money as u_load_money,dl.id as bid,dl.create_date,dl.id as load_id,dl.rebate_money,dl.create_time,dl.debts,dl.cunguan_tag from ".DB_PREFIX."deal_load as dl left join  ".DB_PREFIX."deal d on d.id = dl.deal_id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." $condtion $lending order by dl.create_time desc limit ".$limit;
			//var_dump($sql);
			$sql_count = "select count(DISTINCT dl.id) from ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt on dlt.load_id = dl.id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and (dl.user_id = ".$user_id." or dlt.t_user_id =".$user_id.")  $condtion $lending";
			//var_dump($sql_count);
			$count = $GLOBALS['db']->getOne($sql_count);
			$list = array();
			if($count >0){
				$list = $GLOBALS['db']->getAll($sql);
				$load_ids = array();
				foreach($list as $k=>$v){
					$list[$k]['borrow_amount_format'] = format_price($v['borrow_amount']/10000)."万";//format_price($deal['borrow_amount']);
					$list[$k]['rate_foramt_w'] = number_format($v['rate'],2)."%";
					$list[$k]['red_ecv']=$v['total_money']-$v['u_load_money'];
					//$list[$k]['borrow_amount_format'] = format_price($v['borrow_amount']);					
					$list[$k]['rate_foramt'] = number_format($v['rate'],2);
					
					$list[$k]['u_load_money_format'] =  app_conf("CURRENCY_UNIT")."".number_format($list[$k]['u_load_money']/10000,3)."万";
					
					//本息还款金额
					$list[$k]['month_repay_money'] = pl_it_formula($v['borrow_amount'],$v['rate']/12/100,$v['repay_time']);
					$list[$k]['month_repay_money_format'] =  format_price($list[$k]['month_repay_money']);
                    // 月收利息
                    $list[$k]['month_rebate_money']=number_format($list[$k]['u_load_money']*$list[$k]['rate']/12/100);
					if($list[$k]['create_time'] !=""){
						$list[$k]['create_time_format'] =  to_date($list[$k]['create_time'],"Y-m-d");
					}
					
					if($list[$k]['start_time'] !=""){
						$list[$k]['start_time_format'] =  to_date($list[$k]['start_time'],"Y-m-d");
					}
						
					if($v['deal_status'] == 1){
						//还需多少钱
						$list[$k]['need_money'] = format_price($v['borrow_amount'] - $v['load_money']);
			
						//百分比
						$list[$k]['progress_point'] = $v['load_money']/$v['borrow_amount']*100;
			
					}
					elseif($v['deal_status'] == 2 || $v['deal_status'] == 5)
					{
						$list[$k]['progress_point'] = 100;
					}
					elseif($v['deal_status'] == 4){
						//百分比
						$list[$k]['remain_repay_money'] = $list[$k]['month_repay_money'] * $v['repay_time'];
						//还有多少需要还
						$list[$k]['need_remain_repay_money'] = $list[$k]['remain_repay_money'] - $v['repay_money'];
						//还款进度条
						$list[$k]['progress_point'] =  round($v['repay_money']/$list[$k]['remain_repay_money']*100,2);
					}
						
//					$user_location = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($v['city_id']),false);
//					if($user_location=='')
//						$user_location = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($v['province_id']),false);
//
//					$list[$k]['user_location'] = $user_location;
//					$list[$k]['point_level'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_level where id = ".intval($v['level_id']));
					
					
					//$durl = url("index","deal",array("id"=>$list[$k]['id']));
					//$deal['url'] = $durl;
//					if($v['deal_status'] == 4 || $v['deal_status'] == 5){
//						$durl = "/index.php?ctl=uc_invest&act=mrefdetail&is_sj=1&id=".$v['id']."&load_id=".$v['load_id']."&user_name=".$user_name."&user_pwd=".$user_pwd;					
//						$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
//					}else{
						$durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=".$v['id'];
						$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
//					}
					$load_ids[] = $v['load_id'];
				}
				//判断是否已经转让
//				if(count($load_ids) > 0){
//					$tmptransfer_list  = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load_transfer where load_id in(".implode(",",$load_ids).") and t_user_id > 0 and user_id=".$user_id);
//					$transfer_list = array();
//					foreach($tmptransfer_list as $k=>$v){
//						$transfer_list[$v['load_id']] = $v;
//					}
//					unset($tmptransfer_list);
//					foreach($list as $k=>$v){
//						if(isset($transfer_list[$v['load_id']])){
//							$list[$k]['has_transfer'] = 1;
//						}
//					}
//				}
				
			}
		
		//var_dump($list);die;
			return array('list'=>$list,'count'=>$count);
		}else{
			return array();
		}
	
	}
	function getInvestCunguanList($condtion, $user_id = 0, $page = 0,$user_name='',$user_pwd='') {
				
		if ($user_id > 0){
			/*$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";*/
            /*switch($mode){
                case "index":
                    $condtion=$condtion;
                    break;
                case "over":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)<='".date('Y-m-d')."'";
                    break;
                case "invite":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."'";
                    break;
            }*/
			/*switch($mode){
				case "index" :
					$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";
					break;
				case "invite" :
					$condtion = "   AND d.deal_status in(1,2,4)  ";
					break;
				case "in" :
					$condtion = "   AND d.deal_status =1  ";
					break;
				case "full" :
					$condtion = "   AND d.deal_status =2  ";
					break;
				case "flow" :
					$condtion = "   AND d.deal_status =3  ";
					break;
				case "ing" :
					$condtion = "   AND d.deal_status =4  ";
					break;
				case "over" :
					$condtion = "   AND d.deal_status =5  ";
					break;
				case "conduct" :
					$condtion = "   AND d.deal_status in(1,2,4)";
					break;
				case "bad" :
					$condtion = "   AND d.deal_status = 4 AND (".TIME_UTC." - d.next_repay_time)/24/3600 >=".trim(app_conf("YZ_IMPSE_DAY"))." and d.last_repay_time > 0 ";
					break;
			}*/
		
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			//$sql = "select d.*,u.user_name,dl.money as u_load_money,u.level_id,u.province_id,u.city_id,dl.id as load_id from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=d.user_id where dl.user_id = ".$user_id." $condtion group by dl.id order by dl.create_time desc limit ".$limit;
			//$sql_count = "select count(DISTINCT dl.id) from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id where d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." $condtion ";
			
			$sql = "select d.id,dl.total_money,d.rate,d.name,d.repay_time,d.repay_time_type,dl.money as u_load_money,dl.id as bid,dl.create_date,dl.id as load_id,dl.rebate_money,dl.create_time from ".DB_PREFIX."deal_load as dl left join  ".DB_PREFIX."deal d on d.id = dl.deal_id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." $condtion  order by dl.create_time desc limit ".$limit;

			$sql_count = "select count(DISTINCT dl.id) from ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt on dlt.load_id = dl.id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and (dl.user_id = ".$user_id." or dlt.t_user_id =".$user_id.")  $condtion ";
			
			$count = $GLOBALS['db']->getOne($sql_count);
			$list = array();
			if($count >0){
				$list = $GLOBALS['db']->getAll($sql);
				$load_ids = array();
				foreach($list as $k=>$v){
					$list[$k]['borrow_amount_format'] = format_price($v['borrow_amount']/10000)."万";//format_price($deal['borrow_amount']);
					$list[$k]['rate_foramt_w'] = number_format($v['rate'],2)."%";
					$list[$k]['red_ecv']=$v['total_money']-$v['u_load_money'];
					//$list[$k]['borrow_amount_format'] = format_price($v['borrow_amount']);					
					$list[$k]['rate_foramt'] = number_format($v['rate'],2);
					
					$list[$k]['u_load_money_format'] =  app_conf("CURRENCY_UNIT")."".number_format($list[$k]['u_load_money']/10000,3)."万";
					
					//本息还款金额
					$list[$k]['month_repay_money'] = pl_it_formula($v['borrow_amount'],$v['rate']/12/100,$v['repay_time']);
					$list[$k]['month_repay_money_format'] =  format_price($list[$k]['month_repay_money']);
                    // 月收利息
                    $list[$k]['month_rebate_money']=number_format($list[$k]['u_load_money']*$list[$k]['rate']/12/100);
					if($list[$k]['create_time'] !=""){
						$list[$k]['create_time_format'] =  to_date($list[$k]['create_time'],"Y-m-d");
					}
					
					if($list[$k]['start_time'] !=""){
						$list[$k]['start_time_format'] =  to_date($list[$k]['start_time'],"Y-m-d");
					}
						
					if($v['deal_status'] == 1){
						//还需多少钱
						$list[$k]['need_money'] = format_price($v['borrow_amount'] - $v['load_money']);
			
						//百分比
						$list[$k]['progress_point'] = $v['load_money']/$v['borrow_amount']*100;
			
					}
					elseif($v['deal_status'] == 2 || $v['deal_status'] == 5)
					{
						$list[$k]['progress_point'] = 100;
					}
					elseif($v['deal_status'] == 4){
						//百分比
						$list[$k]['remain_repay_money'] = $list[$k]['month_repay_money'] * $v['repay_time'];
						//还有多少需要还
						$list[$k]['need_remain_repay_money'] = $list[$k]['remain_repay_money'] - $v['repay_money'];
						//还款进度条
						$list[$k]['progress_point'] =  round($v['repay_money']/$list[$k]['remain_repay_money']*100,2);
					}
						
//					$user_location = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($v['city_id']),false);
//					if($user_location=='')
//						$user_location = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($v['province_id']),false);
//
//					$list[$k]['user_location'] = $user_location;
//					$list[$k]['point_level'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_level where id = ".intval($v['level_id']));
					
					
					//$durl = url("index","deal",array("id"=>$list[$k]['id']));
					//$deal['url'] = $durl;
//					if($v['deal_status'] == 4 || $v['deal_status'] == 5){
//						$durl = "/index.php?ctl=uc_invest&act=mrefdetail&is_sj=1&id=".$v['id']."&load_id=".$v['load_id']."&user_name=".$user_name."&user_pwd=".$user_pwd;					
//						$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
//					}else{
						$durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=".$v['id'];
						$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
//					}
					$load_ids[] = $v['load_id'];
				}
				//判断是否已经转让
//				if(count($load_ids) > 0){
//					$tmptransfer_list  = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load_transfer where load_id in(".implode(",",$load_ids).") and t_user_id > 0 and user_id=".$user_id);
//					$transfer_list = array();
//					foreach($tmptransfer_list as $k=>$v){
//						$transfer_list[$v['load_id']] = $v;
//					}
//					unset($tmptransfer_list);
//					foreach($list as $k=>$v){
//						if(isset($transfer_list[$v['load_id']])){
//							$list[$k]['has_transfer'] = 1;
//						}
//					}
//				}
				
			}
		
		
			return array('list'=>$list,'count'=>$count);
		}else{
			return array();
		}
	
	}
	//存管请求
	
	function get_invest_list($mode = "index", $user_id = 0, $limit = 0,$order) {
				
		if ($user_id > 0){
			$condtion = "   AND d.deal_status in(1,2,3,4,5)  ";
            switch($mode){
                case "index":
                    $condtion=$condtion;
                    break;
                case "over":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)<='".date('Y-m-d')."'";
                    break;
                case "invite":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."'";
                    break;
            }			
			$sql = "select d.id as bid,dl.total_money as total_money,dl.money as money,dl.create_time,dl.id as id,dl.deal_id as deal_id,d.sub_name as sub_name,d.repay_time as repay_time,d.rate as rate from ".DB_PREFIX."deal_load as dl left join  ".DB_PREFIX."deal d on d.id = dl.deal_id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." $condtion  $order limit ".$limit;
			$sql_count = "select count(DISTINCT dl.id) from ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt on dlt.load_id = dl.id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and (dl.user_id = ".$user_id." or dlt.t_user_id =".$user_id.")  $condtion ";
			
			$count = $GLOBALS['db']->getOne($sql_count);
			if($count >0){
				$list = $GLOBALS['db']->getAll($sql);
			}
			return array('list'=>$list,'count'=>$count);
		}else{
			return array();
		}
	}
	function get_app_invest_list($mode = "index", $user_id = 0, $page = 0,$order) {
				
		if ($user_id > 0){
			$order = " order by dl.id desc";
			$condtion = "   AND d.deal_status in(1,2,3,4,5) AND d.cunguan_tag=0 ";
            switch($mode){
                case "index":
                    $condtion=$condtion;
                    break;
                case "over":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)<='".date('Y-m-d')."' and d.cunguan_tag=0";
                    break;
                case "invite":
                    $condtion=" AND date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."' and d.cunguan_tag=0";
                    break;
            }
            if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");			
			$sql = "select d.id as bid,dl.total_money as total_money,dl.money as money,dl.create_time,dl.id as load_id,dl.deal_id as deal_id,d.sub_name as sub_name,d.repay_time as repay_time,d.rate as rate from ".DB_PREFIX."deal_load as dl left join  ".DB_PREFIX."deal d on d.id = dl.deal_id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." $condtion  $order limit ".$limit;
			$sql_count = "select count(DISTINCT dl.id) from ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt on dlt.load_id = dl.id where d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and (dl.user_id = ".$user_id." or dlt.t_user_id =".$user_id.")  $condtion ";
			
			$count = $GLOBALS['db']->getOne($sql_count);
			if($count >0){
				$list = $GLOBALS['db']->getAll($sql);
			}
			return array('list'=>$list,'count'=>$count);
		}else{
			return array();
		}
	}	
	
	function getUcTransferList($page,$status){
		if($page==0)
			$page = 1;
			
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	
		$condition = ' AND if(d.transfer_day > 0 ,(d.repay_start_time + d.transfer_day*24*3600 + 24*3600 )< '.TIME_UTC.'  ,"1=1") and d.is_effect=1 and d.is_delete=0  and d.repay_time_type =1 and  d.publish_wait=0 and dl.user_id='.$GLOBALS['user_info']['id']."  ";
		$union_sql = " LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.deal_id = dl.deal_id and dlt.load_id=dl.id ";
		switch($status){
			case 1://可转让
				$condition.=" AND d.next_repay_time - ".TIME_UTC." + 24*3600 - 1 > 0 AND d.deal_status = 4 and (isnull(dlt.id) or (dlt.t_user_id =0 and dlt.status = 0) ) ";
				break;
			case 2://转让中
				$condition.=" AND d.deal_status = 4 AND dlt.status = 1 and dlt.user_id >0 and dlt.t_user_id=0 ";
				break;
			case 3://已转让
				$condition.=" AND dlt.t_user_id > 0 ";
				break;
			case 4://已撤销
				$condition.=" AND dlt.status = 0 ";
				break;
			default ://默认
				$condition.=" AND ((d.deal_status = 4 and dlt.id > 0) or (d.deal_status = 4 and isnull(dlt.id) AND d.next_repay_time - ".TIME_UTC." + 24*3600 - 1 > 0)  or (d.deal_status = 5 and dlt.id >0))";
				break;
		}
	
		$count_sql = 'SELECT count(dl.id) FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;
	
		$rs_count = $GLOBALS['db']->getOne($count_sql);
		if($rs_count > 0){
			$list_sql = 'SELECT dl.id as dlid,d.*,dlt.near_repay_time,dl.money as load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time,dl.learn_id,dl.learn_money FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 and dl.learn_id = 0 '.$condition.' ORDER BY d.id DESC'; 
			
			$list = $GLOBALS['db']->getAll($list_sql." LIMIT $limit ");
			foreach($list as $k => $v){
				//最后还款日
				$list[$k]['final_repay_time'] = next_replay_month($v['repay_start_time'],$v['repay_time']);
					
				$list[$k]['final_repay_time_format'] = to_date($list[$k]['final_repay_time'],"Y-m-d");
				//剩余期数
				if(intval($v['t_user_id']) > 0){
					$list[$k]['how_much_month'] = how_much_month($v['near_repay_time'],$list[$k]['final_repay_time']);
					if(is_last_repay($v['loantype'])==2){
						$list[$k]['how_much_month'] =  $list[$k]['how_much_month'] / 3;
					}
				}
				elseif($v['deal_status']==4)
				{
					if($v['last_repay_time'] > 0)
						$list[$k]['how_much_month'] = how_much_month($v['last_repay_time'],$list[$k]['final_repay_time']);
					else
						$list[$k]['how_much_month'] = how_much_month($v['repay_start_time'],$list[$k]['final_repay_time']);
					
					if(is_last_repay($v['loantype'])==2){
						$list[$k]['how_much_month'] =  $list[$k]['how_much_month'] / 3;
					}
				}
				else{
					$list[$k]['how_much_month'] = 0;
				}
				
				$transfer_rs = deal_transfer($list[$k]);
				$list[$k]['month_repay_money'] = $transfer_rs['month_repay_money'];
				$list[$k]['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
				$list[$k]['left_benjin'] = round($transfer_rs['left_benjin'],2);
				
				$list[$k]['learn_id'] = $v['learn_id'];
					
				$list[$k]['left_benjin_format'] = format_price($list[$k]['left_benjin']);
				//剩多少利息
				$list[$k]['left_lixi'] = $list[$k]['all_must_repay_money'] - $list[$k]['left_benjin'];
				$list[$k]['left_lixi_format'] = format_price($list[$k]['left_lixi']);
	
				//转让价格
				$list[$k]['transfer_amount_format'] =  format_price($v['transfer_amount']/10000)."万";
	
				if($v['tras_create_time'] !=""){
					$list[$k]['tras_create_time_format'] =  to_date($v['tras_create_time'],"Y-m-d");
				}
				
				$list[$k]['near_repay_time_format'] =  to_date($v['near_repay_time'],"Y-m-d");
				
				if ($list[$k]['tras_status'] == ''){
					if($list[$k]['learn_id']==0){
						$list[$k]['tras_status_format'] = '可转让';
					}else{
						$list[$k]['tras_status_format'] = '不可转让';
					}
				}
				else if ($list[$k]['tras_status'] == 0)
					$list[$k]['tras_status_format'] = '已撤销';
				else if ($list[$k]['tras_status'] == 1){
					if ($list[$k]['t_user_id'] > 0){
						$list[$k]['tras_status_format'] = '已转让';
					}else{
						$list[$k]['tras_status_format'] = '转让中';
					}					
				}
				
				$list[$k]['tras_status_op'] = 0;
				
				if ($list[$k]['tras_status'] == '')
					$list[$k]['tras_status_op'] = 1;//'转让';//<a href="javascript:void(0);" class="J_do_transfer" dataid="{$item.dlid}">转让</a>
				else if ($list[$k]['tras_status'] == 0){
					if ($list[$k]['how_much_month'] == 0)
						$list[$k]['tras_status_op'] = 2;//'还款完毕,无法转让';
					else{
						if ($list[$k]['next_repay_time'] +24*3600-1 - TIME_UTC < 0)
							$list[$k]['tras_status_op'] = 3;//'逾期还款,无法转让';
						else
							$list[$k]['tras_status_op'] = 4;//'重转让';//<a href="javascript:void(0);" class="J_do_transfer" dataid="{$item.dlid}">重转让</a>
					}
				}
				else if ($list[$k]['tras_status'] == 1){
					if ($list[$k]['t_user_id'] > 0){
						$list[$k]['tras_status_op'] = 5;//'查看详情<br>转让协议';
						//<a href="{url x="index" r="transfer#detail" p="id=$item.dltid"}">查看详情</a><br>
						//<a href="javascript:void(0);" onclick="javascript:window.showModalDialog('{url x="index" r="uc_transfer#contact" p="id=$item.dltid"}');">转让协议</a>
					}else
						$list[$k]['tras_status_op'] = 6;//'撤销';//<a href="javascript:void(0);"  class="J_do_reback" dataid="{$item.dltid}">撤销</a>					
				}
				
				if(is_last_repay($v['loantype'])==2){
					$list[$k]['repay_time'] =  $list[$k]['repay_time'] / 3;
				}
				
				$durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=".$v['id'];				
				$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
			}
				
			return array('list'=>$list,'count'=>$rs_count);
		}else{
			return array('list'=>null,'count'=>0);
		}
	}	
	
	//转让;
	function getUcToTransfer($id,$tid){	
			
		$status = array('status'=>0,'show_err'=>'','transfer');
		if($id==0){			
			$status['status'] = 0;
			$status['show_err'] = "不存在的债权";
			return $status;
		}
		
		$learn_id = $GLOBALS['db']->getOne("SELECT learn_id FROM ".DB_PREFIX."deal_load WHERE id=".$id);
		if($learn_id > 0){
			$status['status'] = 0;
			$status['show_err'] = "体验金出借，不可转让";
			return $status;
		}
		
		//先执行更新借贷信息
		$deal_id = $GLOBALS['db']->getOne("SELECT deal_id FROM ".DB_PREFIX."deal_load WHERE id=".$id);
		if($deal_id==0){
			$status['status'] = 0;
			$status['show_err'] = "不存在的债权";
			return $status;
		}
		else{
			syn_deal_status($deal_id);
		}
		
		$condition = ' AND if(d.transfer_day > 0 ,(d.repay_start_time + d.transfer_day*24*3600 + 24*3600 )< '.TIME_UTC.'  ,"1=1") AND dl.id='.$id.' AND d.deal_status = 4 and d.is_effect=1 and d.is_delete=0  and d.repay_time_type =1 and  d.publish_wait=0 and dl.user_id='.$GLOBALS['user_info']['id']."  and d.next_repay_time - ".TIME_UTC." + 24*3600 - 1 > 0  ";
		if($tid > 0)
		{
			$condition.=" and dlt.id=$tid";
		}
		
		$union_sql = " LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.deal_id = dl.deal_id and dlt.load_id=dl.id ";
	
		$sql = 'SELECT dl.id as dlid,d.*,dlt.near_repay_time,dl.money as load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;
	
		$transfer = $GLOBALS['db']->getRow($sql);
	
		if($transfer){
			//下个还款日
			if(intval($transfer['next_repay_time']) > 0){
				$transfer['next_repay_time_format'] = to_date($transfer['next_repay_time'],"Y-m-d");
			}
			else{
				$transfer['next_repay_time_format'] = to_date(next_replay_month($transfer['repay_start_time']),"Y-m-d");
			}

			//还款日
			$transfer['final_repay_time'] = next_replay_month($transfer['repay_start_time'],$transfer['repay_time']);
			$transfer['final_repay_time_format'] = to_date($transfer['final_repay_time'],"Y-m-d");
			//剩余期数
			if(intval($transfer['t_user_id']) > 0){
				$transfer['how_much_month'] = how_much_month($transfer['near_repay_time'],$transfer['final_repay_time']);
				if(is_last_repay($transfer['loantype'])==2){
					$transfer['how_much_month'] =  $transfer['how_much_month'] / 3;
				}
			}
			elseif($transfer['deal_status']==4){
				if($transfer['last_repay_time'] > 0)
					$transfer['how_much_month'] = how_much_month($transfer['last_repay_time'],$transfer['final_repay_time']);
				else
					$transfer['how_much_month'] = how_much_month($transfer['repay_start_time'],$transfer['final_repay_time']);
				
				if(is_last_repay($transfer['loantype'])==2){
					$transfer['how_much_month'] =  $transfer['how_much_month'] / 3;
				}
			}
			else{
				$transfer['how_much_month'] = 0;
			}
            
			$transfer_rs = deal_transfer($transfer);
			$transfer['month_repay_money'] = $transfer_rs['month_repay_money'];
			$transfer['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
			$transfer['left_benjin'] = $transfer_rs['left_benjin'];
			
			$transfer['left_benjin_format'] = format_price($transfer['left_benjin']);
			//剩多少利息
			$transfer['left_lixi'] = $transfer['all_must_repay_money'] - $transfer['left_benjin'];
			$transfer['left_lixi_format'] = format_price($transfer['left_lixi']);
				
			//转让价格
			$transfer['transfer_amount_format'] =  format_price($transfer['all_must_repay_money']);
				
			if($transfer['tras_create_time'] !=""){
				$transfer['tras_create_time_format'] =  to_date($transfer['tras_create_time'],"Y-m-d");
			}
			
			if(is_last_repay($transfer['loantype'])==2){
				$transfer['repay_time'] =  $transfer['repay_time'] / 3;
			}
				
			$status['status'] = 1;
			$status['transfer'] = $transfer;
			$status['show_err'] = "";
			return $status;
		}
		else{			
			$status['status'] = 0;
			$status['show_err'] = "不存在的债权转让";
			return $status;
		}
	}
		
	/**
	 * 执行转让
	 */
	function getUcDoTransfer($id,$tid,$paypassword,$transfer_money){
		$paypassword = strim($paypassword);
		$id = intval($id);
		$tid = intval($tid);
		$transfer_money = floatval($transfer_money);
		
		$status = array('status'=>0,'show_err'=>'');
		if($id==0){
			$status['status'] = 0;
			$status['show_err'] = "不存在的债权";
			return $status;
		}
		
		if($transfer_money <= 0){
			$status['status'] = 0;
			$status['show_err'] = "转让金额必须大于0";
			return $status;
		}
				
		$deal_id = $GLOBALS['db']->getOne("SELECT deal_id FROM ".DB_PREFIX."deal_load WHERE id=".$id);
		if($deal_id==0){
			$status['status'] = 0;
			$status['show_err'] = "不存在的债权";
			return $status;
		}
		else{
			syn_deal_status($deal_id);
		}
	
		//判断支付密码是否正确
		if($paypassword ==""){			
			$status['status'] = 0;
			$status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
			return $status;
		}
	
		if(md5($paypassword) != $GLOBALS['user_info']['paypassword']){			
			$status['status'] = 0;
			$status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
			return $status;
		}
	
		$condition = ' AND if(d.transfer_day > 0 ,(d.repay_start_time + d.transfer_day*24*3600 + 24*3600 )< '.TIME_UTC.'  ,"1=1") AND dl.id='.$id.' AND d.deal_status = 4 and d.is_effect=1 and d.is_delete=0 and d.repay_time_type =1 and  d.publish_wait=0 and dl.user_id='.$GLOBALS['user_info']['id']." and d.next_repay_time - ".TIME_UTC." + 24*3600 - 1 > 0  ";
		$union_sql = " LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.deal_id = dl.deal_id and dlt.load_id=dl.id ";
	
		$sql = 'SELECT dl.id as dlid,d.*,dl.money as load_money,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;
	
		$transfer = $GLOBALS['db']->getRow($sql);
	
		if($transfer){
	
			//下个还款日
			if(intval($transfer['next_repay_time']) == 0){
				$transfer['next_repay_time'] = next_replay_month($transfer['repay_start_time']);
			}
				
			if($transfer['next_repay_time'] - TIME_UTC + 24*3600 -1 < 0){				
				$status['status'] = 0;
				$status['show_err'] = "转让操作失败，有逾期未还款存在！";
				return $status;
			}
				
			//还款日
			$transfer['final_repay_time'] = next_replay_month($transfer['repay_start_time'],$transfer['repay_time']);
				
			//剩余期数
			if(intval($transfer['last_repay_time']) > 0)
				$transfer['how_much_month'] = how_much_month($transfer['last_repay_time'],$transfer['final_repay_time']);
			else{
				$transfer['how_much_month'] = how_much_month($transfer['repay_start_time'],$transfer['final_repay_time']);
			}

			if(is_last_repay($transfer['loantype'])==2){
				$transfer['how_much_month'] =  $transfer['how_much_month'] / 3;
			}
			
			$transfer_rs = deal_transfer($transfer);
			$transfer['month_repay_money'] = $transfer_rs['month_repay_money'];
			$transfer['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
			$transfer['left_benjin'] = $transfer_rs['left_benjin'];
				
			//剩多少利息
			$transfer['left_lixi'] = $transfer['all_must_repay_money'] - $transfer['left_benjin'];
	
			//判断转让金额是否超出了可转让的界限
			if(round($transfer_money,2) > round(floatval($transfer['all_must_repay_money']),2)){				
				$status['status'] = 0;
				$status['show_err'] = "转让金额不得大于最大转让金额";
				return $status;				
			}
			$transfer_data['create_time'] = TIME_UTC;
			$transfer_data['create_date'] = to_date(TIME_UTC);
			$transfer_data['deal_id'] = $transfer['id'];
			$transfer_data['load_id'] = $transfer['dlid'];
			$transfer_data['user_id'] = $GLOBALS['user_info']['id'];
			$transfer_data['transfer_number'] = $transfer['how_much_month'];
			$transfer_data['last_repay_time'] = $transfer['final_repay_time'];
			$transfer_data['load_money'] = $transfer['load_money'];
			$transfer_data['status'] = 1;
			$transfer_data['transfer_amount'] = $transfer_money;
			$transfer_data['near_repay_time'] = is_last_repay($transfer['loantype']) == 1 ? $transfer['next_repay_time'] : ($transfer['last_repay_time'] > 0 ? $transfer['last_repay_time'] : $transfer['repay_start_time']);
				
			if($tid > 0){
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_transfer",$transfer_data,"UPDATE","id=".$tid);
			}
			else{
				$tid =  $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."deal_load_transfer  WHERE deal_id='".$transfer['id']."' AND  load_id= '".$transfer['dlid']."' and user_id='".$GLOBALS['user_info']['id']."'  ");
                if($tid){
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_transfer",$transfer_data,"UPDATE","id=".$tid);
                }
                else{
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_transfer",$transfer_data);
                }
			}
				
			if($GLOBALS['db']->affected_rows()){				
				$status['status'] = 1;
				$status['show_err'] = "转让操作成功";
				return $status;
			}
			else{				
				$status['status'] = 0;
				$status['show_err'] = "转让操作失败";
				return $status;
			}
		}
		else{			
			$status['status'] = 0;
			$status['show_err'] = "不存在的债权";
			return $status;			
		}
	}
		
	function getUcTransferBuys($page,$status){
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$page_args= array();
		$condition = ' and d.is_effect=1 and d.is_delete=0  and d.repay_time_type =1 and  d.publish_wait=0 and dlt.t_user_id='.$GLOBALS['user_info']['id']."  ";
		$union_sql = " LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.deal_id = dl.deal_id  and dlt.load_id=dl.id ";
		switch($status){
			case 1://回收中
				$condition.= " AND d.deal_status = 4 ";
				break;
			case 2://已回收
				$condition.=" AND d.deal_status = 5 ";
				break;
			default ://默认
				$condition.=" AND d.deal_status >= 4 ";
				break;
		}
		$count_sql = 'SELECT count(dl.id) FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;
		$rs_count = $GLOBALS['db']->getOne($count_sql." LIMIT $limit ");
		$list = array();
		if($rs_count > 0){
			$list_sql = 'SELECT dl.id as dlid,d.*,dl.money as load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time,dlt.transfer_time FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition." ORDER BY dlid DESC";
			$list = $GLOBALS['db']->getAll($list_sql);
			foreach($list as $k => $v){
				//最后还款日
				$list[$k]['final_repay_time'] = next_replay_month($v['repay_start_time'],$v['repay_time']);
				$list[$k]['final_repay_time_format'] = to_date($list[$k]['final_repay_time'],"Y-m-d");
				//剩余期数
				if($v['deal_status']==4){
					if(intval($v['last_repay_time']) > 0)
						$list[$k]['how_much_month'] = how_much_month($v['last_repay_time'],$list[$k]['final_repay_time']);
					else{
						$list[$k]['how_much_month'] = how_much_month($v['repay_start_time'],$list[$k]['final_repay_time']);
					}
					if(is_last_repay($v['loantype'])==2){
						$list[$k]['how_much_month'] = $list[$k]['how_much_month'] / 3;
					}
				}
				else{
					$list[$k]['how_much_month'] = 0;
				}
				$transfer_rs = deal_transfer($list[$k]);
				$list[$k]['month_repay_money'] = $transfer_rs['month_repay_money'];
				if($v['deal_status']==4){
					$transfer_rs = deal_transfer($list[$k]);
					//剩余多少钱未回
					$list[$k]['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
					$list[$k]['left_benjin'] = $transfer_rs['left_benjin'];
					$list[$k]['left_benjin_format'] = format_price($list[$k]['left_benjin']/10000)."万";
					//剩多少利息
					$list[$k]['left_lixi'] = $list[$k]['all_must_repay_money'] - $list[$k]['left_benjin'];
					$list[$k]['left_lixi_format'] = format_price($list[$k]['left_lixi']);
				} else{
					$list[$k]['left_benjin_format'] = format_price(0);
					$list[$k]['left_lixi_format'] = format_price(0);
				}
	
				//转让价格
				$list[$k]['transfer_amount_format'] =  format_price($v['transfer_amount']/10000,3)."万";
	
				if($v['tras_create_time'] !=""){
					$list[$k]['tras_create_time_format'] =  to_date($v['tras_create_time'],"Y-m-d");
				}
	
				if(intval($v['transfer_time'])>0){
					$list[$k]['transfer_time_format'] =  to_date($v['transfer_time'],"Y-m-d");
				}
				
				if(is_last_repay($v['loantype'])==2){
					$list[$k]['repay_time'] = $list[$k]['repay_time'] / 3;
				}

				$list[$k]['tras_status_op'] = 5;
				if($v['deal_status']==4)
					$list[$k]['tras_status_format'] = '回收中';
				elseif($v['deal_status']==5)
					$list[$k]['tras_status_format'] = '已回收';
				
				$durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=".$v['id'];							
				$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
			}
			return array('list'=>$list,'count'=>$rs_count);
		}else{
			return array('list'=>null,'count'=>0);
		}
	}	
	
	
	function getInchargeDone($payment_id,$money,$bank_id,$memo,$pingzheng,$debit_id = 0,$debit_type = 0)
	{
		$status = array('status'=>0,'show_err'=>'');
		if($money<=0)
		{
			$status['status'] = 0;
			$status['show_err'] = $GLOBALS['lang']['PLEASE_INPUT_CORRECT_INCHARGE'];
			return $status;
		}
	
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_id);
		if(!$payment_info)
		{
			$status['status'] = 0;
			$status['show_err'] = $GLOBALS['lang']['PLEASE_SELECT_PAYMENT'];
			return $status;
		}
		$order = array();
		$order['payment_id'] = $payment_id;
		$order['bank_id'] = $bank_id;
		$order['memo'] = $memo;
				
		//开始生成订单
		$now = TIME_UTC;
		$order['user_type'] = 0;
		$order['user_id'] = $GLOBALS['user_info']['id'];
		$order['create_time'] = $now;
		$order['create_date'] = to_date(TIME_UTC,"Y-m-d");
		
		//VIP 会员则按照VIP充值手续费配置
		$vip_id = $GLOBALS['user_info']['vip_id'];
		if($vip_id>0){
			$interface_class = $payment_info['class_name'];
			$recharge_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_recharge_config WHERE interface_class='$interface_class' and vip_id = ".$vip_id);
			if($recharge_info){
				$payment_info['fee_type'] = $recharge_info['fee_type'];
				$payment_info['fee_amount'] = $recharge_info['fee'];
			}
		}
		
		if($payment_info['fee_type'] == 0)
			$order['money'] = $money + $payment_info['fee_amount'];
		else
			$order['money'] = $money + $payment_info['fee_amount']*$money;
			
		//收用户手续费
		if($payment_info['fee_type'] == 0)
			$order['fee_amount'] = $payment_info['fee_amount'];
		else
			$order['fee_amount'] = $payment_info['fee_amount']*$money;

		/*支付手续费
		if($payment_info['pay_fee_type'] == 0)
			$order['pay_fee_amount'] = $payment_info['pay_fee_amount'];
		else
			$order['pay_fee_amount'] = $payment_info['pay_fee_amount']*$money;
		*/				

		if($payment_info['class_name']=='Otherpay' && $order['memo']!=""){
			$payment_info['config'] = unserialize($payment_info['config']);
			if($order['memo']==""){
				$status['status'] = 0;
				$status['show_err'] = "请输入银行流水单号";
				return $status;
			}
			
			if($order['bank_id']==""){
				$status['status'] = 0;
				$status['show_err'] = "请选择开户行";
				return $status;
			}

			$order['outer_notice_sn'] =  $order['memo'];//银行流水号
			$order['memo'] = "银行流水单号:".$order['memo'];
			$order['memo'] .= "<br>开户行：".$payment_info['config']['pay_bank'][$order['bank_id']];
			$order['memo'] .= "<br>充值银行：".$payment_info['config']['pay_name'][$order['bank_id']];
			$order['memo'] .= "<br>帐号：".$payment_info['config']['pay_account'][$order['bank_id']];
			$order['memo'] .= "<br>用户：".$payment_info['config']['pay_account_name'][$order['bank_id']];
			if($pingzheng!="")
				$order['memo'] .= "<br>凭证：<a href='".$pingzheng."' target='_blank'>查看</a>";
			
			//$order['bank_id'] = $payment_info['config']['pay_account'][$order['bank_id']];//银行帐户
		}
		if($debit_id > 0)
		{
			$order['order_id'] = intval($debit_id);
		}
		if($debit_type > 0)
		{
			$order['debit_type'] = intval($debit_type);
		}
		
		do
		{
			$order['notice_sn'] = to_date(TIME_UTC,"Ymdhis").rand(100,999);
			$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$order,'INSERT','','SILENT');
			$order_id = intval($GLOBALS['db']->insert_id());
		}

		while($order_id==0);
		$status['payment_info'] = $payment_info;
		$status['status'] = 1;
		$status['payment_notice_id'] = $order_id;
		$status['order_id'] = $order_id;
		$status['pay_status'] = 0;
	
		return $status;
	}	
	
	//用户提现;
	function getUcSaveCarry($amount,$paypassword,$bid,$withdraw_acc){

		$status = array('status'=>0,'show_err'=>'');
		$last_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_carry where user_id =".$GLOBALS['user_info']['id']." order by id desc limit 1");
		if($last_time + 10 > time()){
		    $status['info'] = "操作频繁！";
		    $status['status'] = 0;
		    ajax_return($status);
		}
		
		if($GLOBALS['user_info']['id'] > 0){
		    $pattern = "/^(?!0(\.0{1,2})?$)(?:[1-9][0-9]*|0)(?:\.[0-9]{1,2})?$/";
			$paypassword = strim($paypassword);
			$amount = floatval($amount);
			$bid = intval($bid);
			$withdraw_acc = intval($withdraw_acc);

			if(!preg_match($pattern, $amount)){
			    $status['status'] = 0;
			    $status['show_err'] = "请输入正确的金额";
			    return $status;
			}
			//判断提现限额
// 			if($amount < 2)
// 			{
// 			    $status['status'] = 0;
// 			    $status['show_err'] = $GLOBALS['lang']['CARRY_MONEY_NOT_TRUE'];
// 			    return $status;
// 			}

            $user_type=$GLOBALS['user_info']['user_type'];

            if($user_type==0){
                if($bid <= 0)//bid=user_bank 的id
                {
                    $status['status'] = 0;
                    $status['show_err'] = $GLOBALS['lang']['PLASE_ENTER_CARRY_BANK'];
                    return $status;
                }
            }

			//2.0余额
			$old_moeny = $GLOBALS['user_info']['money'];
			//存管可提余额
			$custody_money = $GLOBALS['user_info']['cunguan_money'];
			//判断提现账户 1普通账户 2存管账户
			if(empty($withdraw_acc) || !in_array($withdraw_acc, array(1,2))){
			    $status['status'] = 0;
			    $status['show_err'] = $GLOBALS['lang']['CARRY_ACC_NOT_ENOUGHT'];
			    return $status;
			}
			require_once APP_ROOT_PATH.'system/libs/user.php';
			$data['user_id'] = intval($GLOBALS['user_info']['id']);
			$data['money'] = $amount;
			//普通账户提现 校验交易密码是否正确
			if($withdraw_acc == 1){
			    if(empty($GLOBALS['user_info']['paypassword'])){
			        $status['status'] = 0;
			        $status['show_err'] = "请您设置交易密码";
			        return $status;
			    }
			    if($paypassword==""){
			        $status['status'] = 0;
			        $status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
			        return $status;
			    }
			    if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
			        $status['status'] = 0;
			        $status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
			        return $status;
			    }
			    if($amount > $old_moeny){
			        $status['status'] = 0;
			        $status['show_err'] = $GLOBALS['lang']['CARRY_MONEY_NOT_ENOUGHT'];
			        return $status;
			    }
			    //更新会员账户信息
		        $user_bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_bank where user_id=".intval($GLOBALS['user_info']['id'])." AND id=".$bid);
		        $data['bank_id'] = $user_bank['bank_id'];
		        $data['real_name'] = $user_bank['real_name'];
		        $data['region_lv1'] = intval($user_bank['region_lv1']);
		        $data['region_lv2'] = intval($user_bank['region_lv2']);
		        $data['region_lv3'] = intval($user_bank['region_lv3']);
		        $data['region_lv4'] = intval($user_bank['region_lv4']);
		        $data['bankzone'] = trim($user_bank['bankzone']);
		        $data['bankcard'] = trim($user_bank['bankcard']);
		        $data['create_time'] = TIME_UTC;
		        $data['create_date'] = to_date(TIME_UTC,"Y-m-d");
		        $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$data,"INSERT");
		        $order_id = intval($GLOBALS['db']->insert_id());
			    
		        $fee = 0;//暂定后台填写手续费
		        modify_account(array('money'=>-$data['money'],'lock_money'=>$data['money']),$data['user_id'],"提现申请",8,"提现申请");
                //modify_account(array('money'=>-$fee,'lock_money'=>$fee),$data['user_id'],"提现手续费",9,"提现手续费".$order_id);
			    
		        //$content = "您于".to_date($data['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($data['money'])."提现申请我们正在处理，如您填写的账户信息正确无误，您的资金将会于3个工作日内到达您的银行账户.";
		        $notices['site_name'] = app_conf("SHOP_TITLE");
		        $notices['user_name'] = $GLOBALS['user_info']['real_name'];
		        $notice['time']=to_date($data['create_time']);
		        $notice['money']=format_price($data['money']);
		        	
		        $tmpl_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_WITHDRAWS_CASH'",false);
		        $GLOBALS['tmpl']->assign("notice",$notice);
		        $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content['content']);
		        	
		        send_user_msg("我们正在处理您的提现申请，请耐心等待!",$content,0,$data['user_id'],TIME_UTC,0,true,5);
		        $status['id'] = $order_id;
		        $status['status'] = 1;
		        $status['show_err'] = $GLOBALS['lang']['CARRY_SUBMIT_SUCCESS'];
		    
		        return $status;
			    
			}elseif ($withdraw_acc == 2){
			    //用户存管信息 判断可提现金额

			    $user_cg_info = get_cg_user_info($GLOBALS['user_info']['id'],$user_type);
			    $cg_bank = $GLOBALS['db']->getRow("SELECT username,corpacc FROM ".DB_PREFIX."company_reginfo where user_id='".intval($GLOBALS['user_info']['id'])."'");
	

			    if($data['money'] > $user_cg_info['withdrawalamount']){
			        $status['status'] = 0;
			        $status['show_err'] = "可提现金额不足";
			        return $status;
			    }

                if($user_type=="1"){

                    $data['user_type'] ="2";

                }else{
                    //判断是否开通存管
                    if(!$GLOBALS['user_info']['cunguan_tag']){
                        $status['status'] = 0;
                        $status['show_err'] = "请您先开通银行存管账户";
                        return $status;
                    }
                    //判断是否设置存管交易密码
                    if(!$GLOBALS['user_info']['cunguan_pwd']){
                        $status['status'] = 0;
                        $status['show_err'] = "请您先设置存管系统交易密码";
                        return $status;
                    }
                    //判断是否绑卡
                    $user_bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_bank where  id=".$bid." and user_id=".intval($GLOBALS['user_info']['id'])." AND cunguan_tag=1");
                    if(empty($user_bank)){
                        $status['status'] = 0;
                        $status['show_err'] = "请您先绑定存管系统的银行卡";
                        return $status;
                    }
                    //判断提现金额+手续费是否大于存管余额
                    $fee = 0;//暂定后台填写手续费
// 			    $fee = getCarryFee($data['money'], $GLOBALS['user_info']);
                    if($data['money'] + $fee > $custody_money) {
                        $status['status'] = 0;
                        $status['show_err'] = $GLOBALS['lang']['CG_CARRY_MONEY_NOT_ENOUGHT'];
                        return $status;
                    }
                }

			    $data['bank_id'] = ($user_type == 1) ? "" : $user_bank['bank_id'];
			    $data['real_name'] = ($user_type == 1) ? "" : $user_bank['real_name'];
			    $data['region_lv1'] = ($user_type == 1) ? "" : intval($user_bank['region_lv1']);
			    $data['region_lv2'] = ($user_type == 1) ? "" : intval($user_bank['region_lv2']);
			    $data['region_lv3'] = ($user_type == 1) ? "" : intval($user_bank['region_lv3']);
			    $data['region_lv4'] = ($user_type == 1) ? "" : intval($user_bank['region_lv4']);
			    $data['bankzone'] = ($user_type == 1) ? "" : trim($user_bank['bankzone']);
			    $data['bankcard'] = ($user_type == 1) ? $cg_bank['corpacc'] : trim($user_bank['bankcard']);




			    $data['fee'] = $fee;
		        $data['create_time'] = TIME_UTC;
		        $data['create_date'] = to_date(TIME_UTC,"Y-m-d");
		        $data['cunguan_tag'] = 1;
		        $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$data,"INSERT");
		        $order_id = intval($GLOBALS['db']->insert_id());
		        
		        $status['status'] = 2;
                if($user_type==0){
                    $status['jump'] = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=uc_money&act=check_cg_password&id=".$order_id;
                }else{ 
                	$status['jump'] = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=uc_money&act=Enterprise_withdrawal&id=".$order_id;
                }
		        return $status;
			    
			}
				
// 			$fee = getWithdrawFee($data['money'],$GLOBALS['user_info']['id']);
            /*
             * 区分1.0的钱和2.0的钱、
             * $GLOBALS['user_info']['money']  账户余额
             * $fir_money   1.0原来的钱
             * $GLOBALS['user_info']['recharge_money']   2.0的新充值的钱
             * $sec_money   1.0的钱不够，还需要多少钱.
            */
            /*
                require_once APP_ROOT_PATH.'system/libs/user.php';
                $fir_money = $GLOBALS['user_info']['money']-$GLOBALS['user_info']['recharge_money'];
                if($amount>$fir_money) {
                $sec_money = $amount - $fir_money;
                }else{
                $sec_money=0;
                }
            */
		}else{
			$status['show_err'] ="未登录";
		}
		return $status;
	}

	
	//用户提现;
	function getAuthorizedSaveCarry($amount,$paypassword,$bid){
		
		$status = array('status'=>0,'show_err'=>'');
	
		if($GLOBALS['authorized_info']['id'] > 0){
			$paypassword = strim($paypassword);
			$amount = floatval($amount);
			$bid = intval($bid);
			if($paypassword==""){
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
				return $status;
			}
			if(md5($paypassword)!=$GLOBALS['authorized_info']['paypassword']){
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
				return $status;
			}
				
			$data['user_id'] = intval($GLOBALS['authorized_info']['id']);
			$data['money'] = $amount;
			
			if($data['money'] <=0)
			{
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['CARRY_MONEY_NOT_TRUE'];
				return $status;
			}
			$fee = 0;
			$feel_type = 0;
			//获取手续费配置表
			$fee_config = load_auto_cache("user_carry_config");
			//如果手续费大于最大的配置那么取这个手续费
			if($data['money'] >=$fee_config[count($fee_config)-1]['max_price']){
				$fee = $fee_config[count($fee_config)-1]['fee'];
				$feel_type = $fee_config[count($fee_config)-1]['fee_type'];
			}
			else{
				foreach($fee_config as $k=>$v){
					if($data['money'] >= $v['min_price'] &&$data['money'] <= $v['max_price']){
						$fee =  floatval($v['fee']);
						$feel_type = $v['fee_type'];
					}
				}
			}
			
			if($feel_type == 1){
				$fee = $data['money'] * $fee * 0.01;
			}	
			
			//判断提现金额限制	
			if(($data['money'] + $fee + floatval($GLOBALS['user_info']['nmc_amount'])) > floatval($GLOBALS['authorized_info']['money'])){
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['CARRY_MONEY_NOT_ENOUGHT'];
				return $status;
			}
			$data['fee'] = $fee;
				
			
				
			if($bid == 0)
			{
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['PLASE_ENTER_CARRY_BANK'];
				return $status;
			}
				
			$user_bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_bank where user_id=".intval($GLOBALS['authorized_info']['id'])." AND id=$bid ");
				
			$data['bank_id'] = $user_bank['bank_id'];
			$data['real_name'] = $user_bank['real_name'];
			$data['region_lv1'] = intval($user_bank['region_lv1']);
			$data['region_lv2'] = intval($user_bank['region_lv2']);
			$data['region_lv3'] = intval($user_bank['region_lv3']);
			$data['region_lv4'] = intval($user_bank['region_lv4']);
			$data['bankzone'] = trim($user_bank['bankzone']);
			$data['bankcard'] = trim($user_bank['bankcard']);
				
				
			$data['create_time'] = TIME_UTC;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$data,"INSERT");
				
			//更新会员账户信息
			require_once APP_ROOT_PATH.'system/libs/user.php';
			modify_account(array('money'=>-$data['money'],'lock_money'=>$data['money']),$data['user_id'],"提现申请",8);
			modify_account(array('money'=>-$fee,'lock_money'=>$fee),$data['user_id'],"提现手续费",9);
				
			//$content = "您于".to_date($data['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($data['money'])."提现申请我们正在处理，如您填写的账户信息正确无误，您的资金将会于3个工作日内到达您的银行账户.";
				
				$notice['time']=to_date($data['create_time'],"Y年m月d日 H:i:s");
				$notice['money']=format_price($data['money']);
					
				$tmpl_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_WITHDRAWS_CASH'",false);
				$GLOBALS['tmpl']->assign("notice",$notice);
				$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content['content']);
					
				send_user_msg("",$content,0,$data['user_id'],TIME_UTC,0,true,5);
				
			$status['status'] = 1;
			$status['show_err'] = $GLOBALS['lang']['CARRY_SUBMIT_SUCCESS'];
		}else{
			$status['show_err'] ="未登录";
		}
		return $status;
	}	
	
	function getUcRepayPlan($user_id,$status,$limit,$condition=""){
		$result = array("rs_count"=>0,"list"=>array());
		$extWhere =" 1=1 ";
		if($user_id > 0){
			$extWhere .=" and ((dlr.user_id = $user_id and dlr.t_user_id=0) or dlr.t_user_id=$user_id)  and dlr.plan_id=0 ";
		}
		
		switch($status){
			case "1": //待还款
				$extWhere .=" and dlr.has_repay=0 ";
				break;
			case "2": //已还款
				$extWhere .=" and dlr.has_repay=1 ";
				break;
			case "3": //近期待还款
				//$extWhere .=" and dlr.has_repay=0 and dlr.repay_time <=".next_replay_month(TIME_UTC,1)." ";
				$extWhere .="and dlr.repay_time<".strtotime('+3 months')." and dlr.has_repay=0 ";
				break;
			case "5": //逾期还款
				$extWhere .=" and ((dlr.has_repay=1 and dlr.true_repay_time >dlr.repay_time) OR (dlr.has_repay=0  and dlr.repay_time < ".TIME_UTC.")) ";
				break;
            case "6":// 所有还款
                $extWhere .=" and dlr.has_repay in (0,1)";
		}
		
		$sql_count = "SELECT count(*) FROM ".DB_PREFIX."deal_load_repay dlr LEFT JOIN ".DB_PREFIX."deal d On d.id = dlr.deal_id where $extWhere $condition ";
		$result['rs_count'] = $GLOBALS['db']->getOne($sql_count);
		if($result['rs_count'] > 0){
			$sql_list = "SELECT dlr.interestrate_money,dlr.increase_interest,d.repay_time_type,d.repay_time as month_time,dlr.*,dlr.l_key +1 as l_key_index ,d.name FROM ".DB_PREFIX."deal_load_repay dlr LEFT JOIN ".DB_PREFIX."deal d On d.id = dlr.deal_id left join ".DB_PREFIX."deal_load dl on dlr.load_id=dl.id where $extWhere $condition ORDER BY dlr.repay_time asc LIMIT ".$limit;
			$result['list'] = $GLOBALS['db']->getAll($sql_list);
			foreach($result['list'] as $k=>$v){
				if($v['repay_time_type'] == 0){
					$result['list'][$k]['month_time'] = 1;
				}
				//$result['list'][$k]['l_key_index'] = "第 ".$v['l_key_index']." 期";
				$result['list'][$k]['l_key_num'] = $v['l_key_index'];
				//状态
				if($v['has_repay'] == 0){
					$result['list'][$k]['status_format'] = '待还';
				}elseif($v['status'] == 0){
					$result['list'][$k]['status_format'] = '提前还款';
				}elseif($v['status'] == 1){
					$result['list'][$k]['status_format'] = '准时还款';
				}elseif($v['status'] == 2){
					$result['list'][$k]['status_format'] = '逾期还款';
				}elseif($v['status'] == 3){
					$result['list'][$k]['status_format'] = '严重逾期';
				}
				$result['list'][$k]['interest_money_format'] = format_price($v['interest_money'] +$v['increase_interest']+$v['interestrate_money']);// - $v['manage_money'] - $v['manage_interest_money']);
				$result['list'][$k]['shiji_money'] = format_price($v['true_interest_money'] + $v['impose_money'] + $v['true_reward_money']);// - $v['true_manage_money'] - $v['true_manage_interest_money']);
				$result['list'][$k]['repay_money_format'] = format_price($v['repay_money']+$v['increase_interest']+$v['interestrate_money']);
				$result['list'][$k]['manage_interest_money_format'] = format_price($v['manage_interest_money']);
			}
		}
		return $result;
	}
	
	/*
	  理财计划回款计划	
	*/
	function getUcPlanReay($user_id,$plan_load_id){
		$result = array("rs_count"=>0,"list"=>array());

		//获取理财计划所包含标的信息
		$plan_load= $GLOBALS['db']->getRow('select p.repay_time,p.repay_time_type,p.deal_id from '.DB_PREFIX.'plan p left join '.DB_PREFIX.'plan_load pl on pl.plan_id=p.id where pl.id='.$plan_load_id.' and p.deal_status=4');
		if(!$plan_load){
			return false;
		}
			$deal_ids = unserialize($plan_load['deal_id']);
			if(!$plan_load['repay_time_type']){//如果为天标，repay_time改为1
				$plan_load['repay_time']=1;
			}
			for($i=0;$i<$plan_load['repay_time'];$i++){
				//当期本金
				$self_money= $GLOBALS['db']->getOne('select sum(self_money) from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i);
				//当期应还利息
				$repay_money = $GLOBALS['db']->getOne('select sum(repay_money) from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i);
				//当期利息
				$interest_money = $GLOBALS['db']->getOne('select sum(interest_money) from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i);
				//当期奖励加息收益
				$increase_interest = $GLOBALS['db']->getOne('select sum(increase_interest) from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i);
				//当期加息卡收益
				$interestrate_money = $GLOBALS['db']->getOne('select sum(interestrate_money) from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i);
				//当期还款时间
				$repay_time = $GLOBALS['db']->getRow('select repay_time,l_key from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i.' order by id desc limit 1');
				if($i+1==$plan_load['repay_time']){
					$result['list'][$i]['l_key_num'] =$repay_time['l_key']+1;
					$result['list'][$i]['repay_money']=$repay_money;
					$result['list'][$i]['interest_money']=$interest_money;
					$result['list'][$i]['self_money']=$self_money;
					$result['list'][$i]['repay_time']=$repay_time['repay_time'];
					$result['list'][$i]['interest_money_format'] = format_price($interest_money +$increase_interest+$interestrate_money);
					$result['list'][$i]['repay_money_format'] = format_price($repay_money+$increase_interest+$interestrate_money);
				}else{
					$result['list'][$i]['l_key_num'] =$repay_time['l_key']+1;
					$result['list'][$i]['repay_money']=$repay_money;
					$result['list'][$i]['interest_money']=$interest_money;
					$result['list'][$i]['self_money']=0;
					$result['list'][$i]['repay_time']=$repay_time['repay_time'];
					$result['list'][$i]['interest_money_format'] = format_price($interest_money +$increase_interest+$interestrate_money);
					$result['list'][$i]['repay_money_format'] = format_price($repay_money+$increase_interest+$interestrate_money);
					
				}
				//判断当期标的是否全部还款
					$has_repay = $GLOBALS['db']->getOne('select count(*) from '.DB_PREFIX.'deal_load_repay where plan_load_id='.$plan_load_id.' and l_key='.$i.' and has_repay=0');
				if($has_repay){
					$result['list'][$i]['has_repay']=0;
				}else{
					$result['list'][$i]['has_repay']=1;
				}
				
			}
		
		return $result;
	}
	
	
	
	
	
	function getUcDealRepay($user_id,$limit,$condition=""){
		
		$result = array("rs_count"=>0,"list"=>array());
		$extWhere =" 1=1 ";
		$extWhere .=" and   has_repay=0 and user_id = ".$user_id ." and repay_time <=".next_replay_month(TIME_UTC,1)." ";
		
		$sql_count = "SELECT count(*) FROM ".DB_PREFIX."deal_repay where  $extWhere $condition  order by deal_id";

		$result['rs_count'] = $GLOBALS['db']->getOne($sql_count);
		if($result['rs_count'] > 0){
			$result['list']=$GLOBALS['db']->getAll("select *,l_key+1 as l_key_index from ".DB_PREFIX."deal_repay where  $extWhere $condition order by deal_id limit ".$limit);
			foreach($result['list'] as $k=>$v){
				$result['list'][$k]['name']= $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal where id = ".$result['list'][$k]['deal_id']);//贷款名称
				$result['list'][$k]['l_key_index'] = "第 ".$v['l_key_index']." 期";
				if($v['has_repay'] == 0){
					$result['list'][$k]['status_format'] = '待还';
				}elseif($v['status'] == 0){
					$result['list'][$k]['status_format'] = '提前还款';
				}elseif($v['status'] == 1){
					$result['list'][$k]['status_format'] = '准时还款';
				}elseif($v['status'] == 2){
					$result['list'][$k]['status_format'] = '逾期还款';
				}elseif($v['status'] == 3){
					$result['list'][$k]['status_format'] = '严重逾期';
				}
				$result['list'][$k]['repay_money_format'] = format_price($v['repay_money']);
				$result['list'][$k]['self_money_format'] = format_price($v['self_money']);
				$result['list'][$k]['interest_money_format'] = format_price($v['interest_money']);
			}
		}
		return $result;
	}
	

	//VIP购买 ;
	function getUcSaveVipBuy($amount,$paypassword,$vip_id){
		$status = array('status'=>0,'show_err'=>'');
	
		if($GLOBALS['user_info']['id'] > 0){
			$paypassword = strim($paypassword);
			$amount = floatval($amount);
			$vip_id = intval($vip_id);
				
			if($paypassword==""){
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
				return $status;
			}
				
			if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
				$status['status'] = 0;
				$status['show_err'] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
				return $status;
			}
			
			
			
			$now_vip_grade = $GLOBALS['db']->getRow("select v.*,vt.vip_grade from ".DB_PREFIX."vip_setting v LEFT JOIN ".DB_PREFIX."vip_type vt ON v.vip_id=vt.id  where vt.id = '$vip_id'  ");
			
			if(!$now_vip_grade){
				$status['status'] = 0;
				$status['show_err'] = "购买的等级不存在";
				return $status;
			}
			if($vip_id == $GLOBALS['user_info']['vip_id']){
				$status['status'] = 0;
				$status['show_err'] = "您已经是".$now_vip_grade['vip_grade'];
				return $status;
			}
			
			$data['user_id'] = intval($GLOBALS['user_info']['id']);	
			$data['money'] = $amount * $now_vip_grade['site_pirce'];
			$buy_fee = $data['money'] ;
			
			if($data['money'] <=0)
			{
				$status['status'] = 0;
				$status['show_err'] = "购买金额不对";
				return $status;
			}
			
			if((floatval($data['money'])) <= $GLOBALS['user_info']['money']){
				
				$user_info = get_user_info("*","id='".$data['user_id']."' and vip_id = '".$vip_id."' ");
				
				$vip_buy_data['user_id'] =  $data['user_id'];
				$vip_buy_data['vip_id'] =  $vip_id;
				$vip_buy_data['vip_buytime'] = TIME_UTC;
				$vip_buy_data['buy_limit'] =  $amount;
				$vip_buy_data['buy_fee'] =  $buy_fee;
				if(TIME_UTC < $user_info['vip_end_time']){
					$vip_end_time = $user_info['vip_end_time'] + $amount * 365 * 24 * 3600 ;
				}else{
					$vip_end_time = TIME_UTC + $amount * 365 * 24 * 3600 ;
				}
				
				$vip_buy_data['vip_end_time'] =  $vip_end_time;
					
				$GLOBALS['db']->autoExecute(DB_PREFIX."vip_buy_log",$vip_buy_data,"INSERT");
				
				$userdata['vip_end_time'] = $vip_end_time;
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$userdata,"UPDATE","id=".$data['user_id']);
				
				//VIP 购买升级
				$type = 1;
				$type_info = 8;
				$resultdate = syn_user_vip($data['user_id'],$type,$type_info,$vip_id);
				
				require_once APP_ROOT_PATH.'system/libs/user.php';
				modify_account(array('money'=>"-".$buy_fee),$data['user_id'],"VIP购买",27);
		
				$status['status'] = 1;
				$status['show_err'] = "用户购买VIP成功";
				return $status;
				
			}
			else{
				$status['status'] = 0;
				$status['show_err'] = "用户购买VIP失败";
				return $status;
			}
			
		}else{
			$status['show_err'] ="未登录";
		}
		return $status;
	}
	//阿拉数字转换成日期
	function week($number){
		$number=substr($number,0,2);
		$arr=array("零","一","二","三","四","五","六","日");
		if(strlen($number)==1){
			$result=$arr[$number];
		}
		return "周".$result;
	}
	//用户资金详情
	function get_user_money_info($user_id){
		//红包余额-2.0
		//$user_statics['red_money'] = floatval($GLOBALS['db']->getOne("SELECT red_money FROM ".DB_PREFIX."user WHERE id=".$user_id));
		//红包余额-存管
		//$user_statics['cunguan_red_money'] = floatval($GLOBALS['db']->getOne("SELECT cunguan_red_money FROM ".DB_PREFIX."user WHERE id=".$user_id));	
		$user_statics['red_money'] = floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."red_packet WHERE user_id=$user_id and packet_type in (1,3) and end_time>".time()." and status=0 "));		
        // 可用加息卡张数
        $user_statics['interest_card_count'] = intval($GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."interest_card WHERE id=".$user_id));
		//可用代金券-2.0
		$user_statics["ecv_money"]=sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."ecv where user_id=".$user_id." AND end_time>".time()."  AND status=0 and cunguan_tag=0"));
		//可用代金券-存管
		$user_statics["cunguan_ecv_money"]=sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."ecv where user_id=".$user_id." AND end_time>".time()."  AND status=0 and cunguan_tag=1"));
		//已收收益总计-2.0
		$user_statics["load_repay_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay = 1 and cunguan_tag=0"));
		//已收收益总计-存管
		$user_statics["cunguan_load_repay_money_one"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay = 1 and status!=4 and cunguan_tag=1"));
		$user_statics["cunguan_load_repay_money_two"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(true_interest_money+increase_interest+interestrate_money+raise_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay = 1 and status=4 and cunguan_tag=1"));
		$user_statics["cunguan_load_repay_money"] = sprintf('%.2f', $user_statics["cunguan_load_repay_money_one"]+$user_statics["cunguan_load_repay_money_two"]);
		//待收收益总计-2.0
		$user_statics["load_wait_earnings"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay = 0 and cunguan_tag=0"));
		//待收收益总计-存管
		$user_statics["cunguan_load_wait_earnings"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay = 0 and cunguan_tag=1"));
		//累计出借总计-2.0
		$user_statics["invest_total_money"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(total_money) FROM ".DB_PREFIX."deal_load WHERE is_repay=0 and cunguan_tag=0 and  user_id=".$user_id)));
		//累计出借总计-存管
		$user_statics["cunguan_invest_total_money"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(total_money) FROM ".DB_PREFIX."deal_load WHERE is_repay=0 and user_id=".$user_id." and cunguan_tag=1")));
		//累计收益总计		
		//$user_statics["invest_total_interest"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE has_repay=1 and user_id=".$user_id)));
		//--------------------------------------------------------------------
//		$invest_sql = "SELECT sum(total_money) as invest_total_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$user_id." and d.deal_status in(1,2,4)";
		$invest_sql = "SELECT sum(total_money) as invest_total_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$user_id." and date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."' and dl.cunguan_tag=0";
        $invest = $GLOBALS['db']->getRow($invest_sql);
		// 将再投的代金券ecv_id转化成数组 去空
		// $arr_ecv_id=array_filter(explode(',',$invest['ecv_id']));
		 // 在投中的代金券金额
		 //$invest_ecv_money=get_ecv_money($arr_ecv_id);

		//在投金额-2.0
		// $user_statics["invest_money"] = sprintf('%.2f',floatval($invest["l_money"]) - $user_statics["load_repay_money"]);
//		$user_statics["invest_money"] = sprintf('%.2f',floatval($invest["l_money"]))+sprintf('%.2f',floatval($invest["red"]))+$invest_ecv_money;
        $user_statics["invest_money"] =sprintf('%.2f',floatval($invest["invest_total_money"]));
		//在投金额-存管
		//$cunguan_invest_sql = "SELECT sum(total_money) as invest_total_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$user_id." and d.deal_status in(1,2,4) and date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."' and dl.cunguan_tag=1";
		$cunguan_invest_sql = "select sum(dlr.self_money) from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."deal d on d.id =dlr.deal_id  where dlr.user_id =$user_id and dlr.cunguan_tag=1 and dlr.has_repay=0";
        $user_statics["cunguan_invest_money"] =sprintf('%.2f',floatval($GLOBALS['db']->getOne($cunguan_invest_sql)));
		// 提现中的金额-2.0
		$user_statics['cash_money'] = sprintf('%.2f', floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id= ".$user_id." and status in (0,3) and cunguan_tag=0")));
		// 提现中的金额-存管
		$user_statics['cunguan_cash_money'] = sprintf('%.2f', floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id= ".$user_id." and status in (0,3) and cunguan_tag=1 and cunguan_pwd=1")));
		// 募集期的出借金额
		//----------------------------------------------------------------------------
		// $collect_money =$GLOBALS['db']->getRow("SELECT GROUP_CONCAT(ecv_id) AS ecv_id,sum(red) as red,sum(money) as l_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$user_id." and d.deal_status=1 group by dl.user_id");
		// $user_statics['collect_money']=$collect_money['l_money'];
		// 将募集期使用的代金券ecv_id转化成数组 去空
		 //$collect_ecv_id=array_filter(explode(',',$collect_money['ecv_id']));
		 // 在募集期中的代金券金额
		 //$collect_ecv_money=get_ecv_money($collect_ecv_id);
		 // 冻结余额--2.0
        //$user_statics['lock_money']= sprintf('%.2f', floatval($user_statics['cash_money']+$user_statics['collect_money']))+sprintf('%.2f',floatval($collect_money["red"]))+$collect_ecv_money;;
        $user_statics['lock_money']= sprintf('%.2f', floatval($GLOBALS['db']->getOne("select lock_money from ".DB_PREFIX."user where id= ".$user_id)));
		// 冻结余额--存管
		$user_statics['cunguan_lock_money']= sprintf('%.2f', floatval($GLOBALS['db']->getOne("select cunguan_lock_money from ".DB_PREFIX."user where id= ".$user_id)));
		//账户总资产-2.0
		// $user_statics["total_money"] = sprintf('%.2f',floatval(round($invest["money"],2)+ round($user_statics["load_wait_repay_money"],2)+round($user_statics["load_repay_money"],2) + floatval($GLOBALS['user_info']['money']) + $user_statics["invest_money"]+$user_statics["lock_money"]));
		$user_statics["total_money"] = sprintf('%.2f',floatval(($GLOBALS['user_info']['money'])+$user_statics["lock_money"]));
		//账户总资产-存管
		$user_statics["cunguan_total_money"] = sprintf('%.2f',floatval(($GLOBALS['user_info']['cunguan_money'])+$user_statics["cunguan_lock_money"]));
		//代金券-2.0
		$user_statics["voucher_count"] = floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."ecv where user_id=".$user_id." AND end_time > ".time()." and status= 0 and cunguan_tag=0"));
		//代金券-存管
		$user_statics["cunguan_voucher_count"] = floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."ecv where user_id=".$user_id." AND end_time > ".time()." and status= 0 and cunguan_tag=1"));
		$t = time() - (24*3600);
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
		$end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
		$user_statics["yesterday_invert"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and status = 1 and true_repay_time between ".$begin_time." and ".$end_time)));
		$user_statics["cunguan_yesterday_invert"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and status = 1 and cunguan_tag=1 and true_repay_time between ".$begin_time." and ".$end_time)));

		//体验金收益-2.0
		$user_statics["taste"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(interest) FROM ".DB_PREFIX."taste_cash where get_interest_status =1 and  user_id =".$user_id)));
		//体验金收益-存管		
		//$user_statics["cunguan_taste"] = sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."taste_cash where user_id =".$user_id." and use_status =0 ")));
	
		//体验经已收收益
		/*
        $user_statics['cunguan_taste'] = $GLOBALS['db']->getOne("select SUM(experience_money) from ".DB_PREFIX."experience_deal_load where user_id=".$user_id." and has_repay=1 ");
        $user_statics['cunguan_taste']= $user_statics['cunguan_taste']? $user_statics['cunguan_taste']:"0.00";
		*/
        //体验经已收收益
		$user_statics['cunguan_taste'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."red_packet where user_id=".$user_id." and status=1 and packet_type=3 and publish_wait=1 ");
        $user_statics['cunguan_taste']= $user_statics['cunguan_taste']? $user_statics['cunguan_taste']:"0.00";

		//存管加息卡 
		$user_statics["Plus_card"] = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."interest_card where user_id =".$user_id." and status=0 and end_time>".time()."");
		return $user_statics;
	}

	function get_invest_log($user_id,$condition,$order,$limit){
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal de on de.id = dl.deal_id WHERE dl.plan_id='' and dl.user_id=".$user_id." $condition $order" );
		$list = array();
		if($count > 0){			
			$list = $GLOBALS['db']->getAll("SELECT de.id,de.name,de.repay_time,de.bad_date,de.deal_status,de.repay_start_time,de.repay_time_type,dl.money,dl.rebate_money,dl.id as bid,dl.red as red,dl.ecv_money as ecv_money,dl.create_time,dl.total_money,de.old_deal_id,dl.debts FROM ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal de on de.id = dl.deal_id WHERE de.is_effect=1 and de.is_delete=0 and de.publish_wait = 0 and dl.plan_id='' and dl.user_id=".$user_id." $condition $order limit $limit");
		}
		return array("list"=>$list,'count'=>$count);
	}
	function get_plan_invest_log($user_id,$condition,$order,$limit){
		$user_id = intval($user_id);
		$count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."plan_load dl left join ".DB_PREFIX."plan de on de.id = dl.plan_id WHERE dl.user_id=".$user_id." $condition $order" );
		$list = array();
		if($count > 0){			
			$list = $GLOBALS['db']->getAll("SELECT de.id,de.name,de.repay_time,de.deal_status,de.repay_start_time,de.repay_time_type,dl.money,dl.id as bid,dl.red as red,dl.ecv_money as ecv_money,dl.load_time as create_time,dl.total_money FROM ".DB_PREFIX."plan_load dl left join ".DB_PREFIX."plan de on de.id = dl.plan_id WHERE de.is_effect=1 and de.is_delete=0 and de.publish_wait = 0 and dl.user_id=".$user_id." $condition $order limit $limit");
		}
		return array("list"=>$list,'count'=>$count);
	}		
	/**
	 * 获取使用的代金券
	 */
	function get_ecv_money($arr_ecv_id){
		if(empty($arr_ecv_id)){
		 	$invest_ecv_money=0;
		 }else{
		 	$str_ecv_id=implode(',',$arr_ecv_id);
		 	$invest_ecv_money=sprintf('%.2f',floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."ecv WHERE id in ($str_ecv_id)")));
		 }
		 return $invest_ecv_money;
	}

	/**
	 * 会员红包日志
	 * $limit 数量
	 * $user_id 用户id
	 * $type -1全部
	 * $condition 其他条件
	 */
	function get_user_red_money_log($limit,$user_id,$type=-1,$condition=""){
	    $extWhere = "";
	    if($type >= 0){
	        $extWhere.=" AND `type`=".$type;
	    }
	    $user_id = intval($user_id);
	    $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_red_money_log where cunguan_tag=1 and user_id =".$user_id." $extWhere $condition");
	    $list = array();
	    if($count > 0){
	
	        $list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_red_money_log where cunguan_tag=1 and  user_id =".$user_id." $extWhere $condition order by id desc limit ".$limit);
	    }
	    return array("list"=>$list,'count'=>$count);
	}

    function voucher_list_log($limit,$user_id,$condition){
        $user_id = intval($user_id);
        $sql="select et.name,dl.create_time,e.money,e.status,e.begin_time,d.sub_name from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."deal_load as dl on e.deal_load_id=dl.id left join ".DB_PREFIX."deal as d on d.id=dl.deal_id left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id=et.id where e.user_id=$user_id $condition order by e.id asc";
        $sql_count ="select count(*) from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."deal_load as dl on e.deal_load_id=dl.id left join ".DB_PREFIX."deal as d on d.id=dl.deal_id left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id=et.id where e.user_id=$user_id $condition order by e.id asc";
        $count = $GLOBALS['db']->getOne($sql_count);
        if($limit !=""){
            $sql .=" limit ".$limit;
        }
        $list = array();
        $list = $GLOBALS['db']->getAll($sql);
        return array("list"=>$list,'count'=>$count);
    }
    /**
	 * 查询会员邀请及返利列表
	 * $type 0有效推荐 1无效推荐 2已实名认证的邀请 3所有邀请
	 */
	function get_invite_list($limit,$user_id,$type=0)
	{
		$user_id = intval($user_id);
		
		$condition = " AND dl.is_has_loans = 1 AND u.user_type in(0,1) ";
		if($type==0){
			if(intval(app_conf("INVITE_REFERRALS_DATE")) > 0){
				$after_year =  next_replay_month(to_timespan(to_date(TIME_UTC,"Y-m-d")),-intval(app_conf("INVITE_REFERRALS_DATE")));
				$condition =" AND u.create_time >= ".$after_year." and dl.create_time  > ".$after_year."  AND dl.user_id > 0 and dl.id > 0";
			}
			else{
				$condition =" AND dl.user_id > 0 and dl.id > 0";
			}
		}
		elseif($type==1)
		{
			if(intval(app_conf("INVITE_REFERRALS_DATE")) > 0){
				$after_year =  next_replay_month(to_timespan(to_date(TIME_UTC,"Y-m-d")),-intval(app_conf("INVITE_REFERRALS_DATE")));
				$condition =" AND (u.create_time < ".$after_year." OR dlr.user_id is null ) and (dlr.id is null OR dlr.id = 0) ";
			}
			else{
				$condition =" AND (dlr.user_id is null ) and (dlr.id is null OR dlr.id = 0)";
			}
		}elseif($type==2){
            if(intval(app_conf("INVITE_REFERRALS_DATE")) > 0){
                $after_year =  next_replay_month(to_timespan(to_date(TIME_UTC,"Y-m-d")),-intval(app_conf("INVITE_REFERRALS_DATE")));
                $condition =" AND u.create_time >= ".$after_year." and u.idcardpassed=1";
            }
            else{
                $condition =" AND u.idcardpassed=1";
            }
        }else{
        	if(intval(app_conf("INVITE_REFERRALS_DATE")) > 0){
                $after_year =  next_replay_month(to_timespan(to_date(TIME_UTC,"Y-m-d")),-intval(app_conf("INVITE_REFERRALS_DATE")));
                $condition =" AND u.create_time >= ".$after_year;
            }
            else{
                $condition =" ";
            }
        }

		$sql_count = "select count(DISTINCT u.id) from ".DB_PREFIX."user u " .
				"LEFT JOIN ".DB_PREFIX."deal_load_repay dlr ON  dlr.t_user_id = 0 AND dlr.user_id =u.id  " .
				"LEFT JOIN ".DB_PREFIX."deal_load_repay dlrr ON dlrr.id=dlr.id AND dlr.t_user_id > 0 AND dlr.t_user_id=u.id  " .
				"LEFT JOIN ".DB_PREFIX."deal_load dl ON dl.id=dlr.load_id  " .
				"where u.pid = ".$user_id." $condition ";
		$count = $GLOBALS['db']->getOne($sql_count);
		$list = array();
		if($count > 0){
			$sql = "select AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as friend_real_name,u.*,dl.user_id as r_user_id,dl.create_time as r_create_time FROM ".DB_PREFIX."user u " .
					"LEFT JOIN ".DB_PREFIX."deal_load_repay dlr ON  dlr.t_user_id = 0 AND dlr.user_id =u.id  " .
					"LEFT JOIN ".DB_PREFIX."deal_load_repay dlrr ON dlrr.id=dlr.id AND dlr.t_user_id > 0 AND dlr.t_user_id=u.id  " .
					"LEFT JOIN ".DB_PREFIX."deal_load dl ON dl.id=dlr.load_id  " .
					"where u.pid = ".$user_id." $condition group by u.id order by id DESC limit ".$limit;
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v){
				if(intval($v['r_user_id'])==0){
					$list[$k]['result'] = "未出借";
				}
				elseif(intval($v['create_time'])<$after_year){
					$list[$k]['result'] = "过期";
				}
			}
		}

		return array("list"=>$list,'count'=>$count);
	}
    
	function getLoansList($mode = "index", $user_id = 0, $page = 0,$user_name='',$user_pwd=''){
		if ($user_id > 0){
			$condtion = "   AND deal_status in(4,5)  ";
            switch($mode){
                case "borrowed":
                    $condtion=$condtion;
                    break;
                case "over":
                    $condtion=" AND deal_status =5 " ;
                    break;
                case "loans":
                    $condtion=" AND deal_status =4 ";
                    break;
            }
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			$sql ="select id,name,borrow_amount,repay_time,rate,repay_start_time,create_time,deal_status,debts from ".DB_PREFIX."deal where is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and cunguan_tag=1 and user_id = ".$user_id." $condtion order by create_time desc limit ".$limit;
			$sql_count = "select count(DISTINCT id) from ".DB_PREFIX."deal where cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and user_id = ".$user_id." $condtion order by id desc";
			
			$count = $GLOBALS['db']->getOne($sql_count);
			$list = array();
			if($count >0){
				$list = $GLOBALS['db']->getAll($sql);
				$load_ids = array();
				foreach($list as $k=>$v){
					$deal_has_repay = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where has_repay=3 and deal_id=".$v['id']);
					if($deal_has_repay>0){
						$list[$k]['deal_status'] = 4;
					}

					//$list[$k]['borrow_amount'] = format_price($v['borrow_amount']/10000)."万";//format_price($deal['borrow_amount']);
					$list[$k]['rate'] = number_format($v['rate'],1)."%";
					if($list[$k]['create_time'] !=""){
						$list[$k]['create_time'] =  to_date($v['create_time'],"Y-m-d");
					}
					
					if($list[$k]['repay_start_time'] !=""){
						$list[$k]['repay_start_time'] =  to_date($v['repay_start_time'],"Y-m-d");
					}
				}
				
				
			}
		
		
			return array('list'=>$list,'count'=>$count);
		}else{
			return array();
		}
	}
	/**
	 * 查询会员存管信息
	 */
	function get_cg_user_info($userid,$user_type){
	    if(empty($userid)) return false;
	    $Publics = new Publics();
	    $map['reqHeader'] = $Publics->reqheader("C00002");
	    $map['inBody']['checkType'] = "01";//用户信息查询
	    $map['inBody']['customerId'] = strval($userid);//会员编号
	    $map['inBody']['accountNo'] = '';//台帐帐号
	    $map['inBody']['beginDate'] = '';//开始日期
	    $map['inBody']['endDate'] = '';//结束日期
	    $map['inBody']['beginPage'] = "";//起始页码
	    $map['inBody']['endPage'] = "";//截止页码
	    $map['inBody']['showNum'] = "10";//每页显示条数
	    $map['inBody']['note'] = "";//备注
	    $dep = $Publics->sign($map);
	    $map['reqHeader']['signTime'] = $dep['signTime'];
	    $map['reqHeader']['signature'] = $dep['signature'];
	    if($map['inBody']['accountNo']){
	        $map['inBody']['accountNo'] = $Publics->encrypt($map['inBody']['accountNo']);
	    }
	    if($map['inBody']['customerId']){
	        $map['inBody']['customerId'] = $Publics->encrypt($map['inBody']['customerId']);
	    }
	     
	    $DepSdk = new DepSdk();
	    $result=$DepSdk->dataQuery(json_encode($map));
	    if($result['outBody']){
// 	        foreach ($result['outBody']['cardList'] as $k => $v) {
// 	            $result['outBody']['tiedAccno'] = $v['tiedAccno'];
// 	            $result['outBody']['tiedAcctelno'] = $v['tiedAcctelno'];
// 	            $result['outBody']['oderNo'] = $v['oderNo'];
// 	            $result['outBody']['tiedAccStatus'] = $v['tiedAccStatus'];
// 	        }
	        $list['withdrawalamount'] = $result['outBody']['withdrawalamount'];
	        $list = $result['outBody'];
	    }else{
	        $list = array();
	    }
	    
        if($user_type == "0"){
            //减去自动投标冻结金额
            $lock_invest_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."auto_invest_config where user_id=".$userid." and status=1 and is_delete=0");
            $list['withdrawalamount'] = $list['withdrawalamount'] - $lock_invest_money;
        }
	    return $list;
	}

	/**
	 * 发放封标奖励
	 */
	function SendSealedReward($user_id,$money){
	    
	$sealed_config = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sealed_config");
	    $time = time();
	    foreach ($sealed_config as $k=>$v){
	        //判断 是否开启 金额 过期时间
	        if($v['is_switch'] == 1 && ($money >= $v['min_money'] && $money <= $v['max_money']) && ($time >= $v['begin_time'] && $time < $v['end_time'])){
	            $rpc = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_newconfig where id=".$v['red_config_id']);
	            $sn = unpack('H12',str_shuffle(md5(uniqid())));
	            $data['sn'] = $sn[1];
	            $data['user_id'] = $user_id;
	            $data['use_limit'] = $rpc['use_limit'];
	            $data['money'] = $rpc['amount'];
	            $data['begin_time'] = strtotime(date("Y-m-d",time()));
	            $data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime('+'.($rpc['use_limit']-1).' day')));
	            $data['red_type_id'] = $rpc['id'];
	            $data['status'] = 0;
	            $data['content'] = "尾标奖励";
	            $data['packet_type'] = 1;
	            $data['create_time'] = time();
	            $result = $GLOBALS['db']->autoExecute(DB_PREFIX . "red_packet", $data, "INSERT");
	            break;
	        }else{
	            continue;
	        }
	    
	    }
	    if($result){
	        return true;
	    }else{
	        return false;
	    }
	}
	
	/**
	 * 沐金农异步通知
	 */
	function insert_mjn_async($serial_number,$seqno,$user_id,$status,$msg){
	    $data['user_id'] = $user_id;
	    $data['create_time'] = time();
	    $data['status'] = $status;
	    $data['reson_text'] = $msg;
	    $data['serial_number'] = $serial_number;
	    $data['seqno'] = $seqno;
	    
	    
	    $GLOBALS['db']->autoExecute(DB_PREFIX."mjn_async",$data,"INSERT");
	}
	
	
?>
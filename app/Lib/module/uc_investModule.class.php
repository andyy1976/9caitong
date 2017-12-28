<?php

require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_investModule extends SiteBaseModule
{
	public function index(){
		$this-> getlist("index");
	}
	public function invite(){

		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		$this->getlist("invite");
	}
	public function flow(){
		$this->getlist("flow");
	}
	public function ing(){
		$this->getlist("ing");
	}
	public function over(){
		$this->getlist("over");
	}
	public function bad(){
		$this->getlist("bad");
		
	}
	
    private function getlist($mode = "index") {

    	$standard_account = intval($_REQUEST['standard_account']);
        $GLOBALS['tmpl']->assign("standard_account",$standard_account);

        $condition_paging = intval($_REQUEST['condition_paging']);
        $GLOBALS['tmpl']->assign("condition_paging",$condition_paging);

        $deal_type = intval($_REQUEST['loan_type']);
        $GLOBALS['tmpl']->assign("deal_type",$deal_type);
        if($condition_paging==0)
        	$mode='index';
        else if($condition_paging==1)
        	$mode='invite';
        else if($condition_paging==2)
        	$mode='over';
        if($standard_account==1){
			if($mode=="invite"){
				$lending  = " AND date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."' AND d.cunguan_tag=0";
			}elseif($mode=="over"){
				$lending = " AND date_sub(dl.create_date,interval -d.repay_time month)<='".date('Y-m-d')."' AND d.cunguan_tag=0 ";
			}else{
				$lending  = " AND d.cunguan_tag=0";
			}
			
		}else if($standard_account==0){
			$lending  = " AND d.cunguan_tag=1";
		}
		
		$page_args['loan_type'] = $deal_type;
        $page_args['standard_account'] = $standard_account;
        $page_args['condition_paging'] = $condition_paging;


    	$page_pram = "";
			foreach($page_args as $k=>$v){
				$page_pram .="&".$k."=".$v;
		}
		
//项目类型
        /*$loan_type=$GLOBALS['db']->getAll("SELECT id,name,information_status  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0");
        foreach($loan_type as $k=>$v){
            $tmp_args = $page_args;
            if($v['information_status']==1){
                $tmp_args['loan_type'] = $v['information_status'];
                $loan_types[$k]['name'] = '短贷宝';
                $loan_types[$k]['url'] = url("index","uc_invest#invite",$tmp_args);
            }else{
                $tmp_args['loan_type'] = $v['information_status'];
                $loan_types[$k]['name'] = '普通标';
                $loan_types[$k]['url'] = url("index","uc_invest#invite",$tmp_args);
            }

            //$tmp_args['loan_type'] = $v['information_status'];

        }*/
        $loan_types = array(
            array(
                "name" => "普通标",
                "information_status"=>0,
            ),
            array(
                "name" => "短贷宝",
                "information_status"=>1,
            ),
        );
        foreach($loan_types as $k=>$v){
            $tmp_args = $page_args;
            $tmp_args['loan_type'] = $v['information_status'];
            $loan_types[$k]['url'] = url("index","uc_invest#invite",$tmp_args);
        }
        //var_dump($loan_types);die;
        $GLOBALS['tmpl']->assign('loan_type',$loan_types);
        $deal_type_id=$GLOBALS['db']->getOne("SELECT id  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0 and information_status=".$deal_type);
      /*  if($deal_type != 1){
        	$lending.= " AND d.type_id=".$deal_type_id;
        }*/
		$account_url = array(
			array(
				"name" => "存管账户",
			),
			array(
				"name" => "普通账户",
			),
		);
		foreach($account_url as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['standard_account'] = $k;		
			$account_url[$k]['url'] = url("index","uc_invest#invite",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('account_url',$account_url);



		$state_url = array(
			array(
				"condition_paging"=>"index",
				"name" => "全部",
				
			),
			array(
				"condition_paging"=>"invite",
				"name" => "持有中",
			),
			array(
				"condition_paging"=>"over",
				"name" => "已完成",
			),
		);
		foreach($state_url as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['condition_paging'] =$k;
			$state_url[$k]['url'] = url("index","uc_invest#invite",$tmp_args);
		}
		if($deal_type != 1){
			$result = getInvestList($mode,intval($GLOBALS['user_info']['id']),intval($_REQUEST['p']),$lending);			
		}else{
           /* $lending.= " AND d.type_id <>12";*/
			$result = getPlanInvestList($mode,intval($GLOBALS['user_info']['id']),intval($_REQUEST['p']),$lending);
		}
    	
   		//var_dump($result['list']);exit;
    	// $money_log= get_user_money_info($GLOBALS['user_info']['id']);

    	//存管
    	$invest_loan_sql = "SELECT count(money) as l_count,sum(money) as l_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$GLOBALS['user_info']['id']." and dl.plan_id = '' and dl.cunguan_tag=1  group by dl.user_id";
		$invest_load_deal = $GLOBALS['db']->getRow($invest_loan_sql);

		$invest_load_plan = $GLOBALS['db']->getRow("SELECT count(money) as l_count,sum(money) as l_money FROM ".DB_PREFIX."plan_load WHERE user_id=".$GLOBALS['user_info']['id']);
		//2.0
		$invest_sql = "SELECT count(money) as l_count,sum(money) as l_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$GLOBALS['user_info']['id']."  and dl.cunguan_tag=0 group by dl.user_id";
		$invest_deal = $GLOBALS['db']->getRow($invest_sql);

		//2.0-出借笔数
		$money_log["load_count"] = $invest_deal["l_count"];
		//存管-笔数
		$money_log["load_count_invest"] =$invest_load_deal["l_count"]+intval($invest_load_plan['l_count']);
		//总笔数
		$money_log['count_load'] = $invest_deal["l_count"]+$invest_load_deal["l_count"]+intval($invest_load_plan['l_count']);

		//累计已收收益-2.0
		$money_log["load_repay_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 1 and cunguan_tag=0"));		
		//存管-累计已收收益
		$money_log["lend_repay_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 1 and cunguan_tag=1"));
		//总收益
		$money_log['count_lend'] = $money_log["load_repay_money"]+$money_log["lend_repay_money"];

		//在投金额-2.0
		$money_log["invest_money"]=sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(total_money) FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$GLOBALS['user_info']['id']." and date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."' and dl.cunguan_tag=0"));
    	//存管-再投金额
		//$money_log["lend_invest_money"]=sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(total_money) FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$GLOBALS['user_info']['id']." and d.deal_status in(1,2,4) and date_sub(dl.create_date,interval -d.repay_time month)>'".date('Y-m-d')."' and dl.cunguan_tag=1"));
		
		$money_log["lend_invest_money"]=sprintf('%.2f', $GLOBALS['db']->getOne("select sum(dlr.self_money) from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."deal d on d.id =dlr.deal_id  where dlr.user_id =".$GLOBALS['user_info']['id']." and dlr.cunguan_tag=1 and dlr.has_repay=0"));
    	//总再投金额
		$money_log['count_lend_money'] = $money_log["invest_money"]+$money_log["lend_invest_money"];

    	$GLOBALS['tmpl']->assign('money_log',$money_log);

    	$list = $result['list'];
		
		/*
    	foreach($list as $k=>$v){
    		//当为天的时候
			if($v['repay_time_type'] == 0){
				$true_repay_time = 1;
			}
			else{
				$true_repay_time = $v['repay_time'];
			}

			$deal['borrow_amount'] = $v['u_load_money']+$v['red']+$v['ecv_money'];
			$deal['rate'] = $v['rate'];
			$deal['user_bid_rebate'] = $v['user_bid_rebate'];
			$deal['loantype'] = $v['loantype'];
			$deal['repay_time'] = $v['repay_time'];
	    	$deal['repay_time_type'] = $v['repay_time_type'];
	    	$deal['repay_start_time'] = $v['repay_start_time'];
			$deal_repay_rs = deal_repay_money($deal);
			
    		$v['interest_amount'] = $deal_repay_rs['month_repay_money'];
    		if($v['success_time']!=0){
				$v['success_date']=date("Y-m-d",strtotime('+'.$v['repay_time'].' months',$v['success_time']+24*3600));
    		}
    		$list[$k] = $v;
			if($v['deal_status']==4||$v['deal_status']==5){
				$sql = "select repay_date from ".DB_PREFIX."deal_load_repay where load_id='".$v['load_id']."' order by id desc limit 1 ";
				$list[$k]['repay_date'] = $GLOBALS['db']->getOne($sql);
			}else{
				$list[$k]['repay_date'] = '';
			}

    	}
		*/
		foreach($list as $k=>$v){
            if($v['deal_debts']==1){
                $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$v['deal_old_deal_id']." order by repay_time desc limit 1");
//                 $list[$k]['debts_repay_time']= ceil(($last_repay_time-time())/3600/24)+1;
                $invest_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where id=".$v['bid']);
                $list[$k]['debts_repay_time']= ceil(($last_repay_time-$invest_time)/3600/24)+1;
				if($v['deal_status']!=5){
					$list[$k]['deal_status']=4;
				}
				
            }
            if($v['interest_rate']){
                $list[$k]['rate']=$v['rate']+$v['interest_rate'];
            }

			if($v['create_time']) {
                if($v['cunguan_tag']==1){
					if($v['repay_start_time']){
						if($v['repay_time_type'] == 1){
							// 月标
							$list[$k]['last_repay_date'] = date("Y-m-d",(strtotime('+'.$v['repay_time'].' months',$v['repay_start_time'])-24*60*60));
						}else{
							if($deal_type==1){
								//天标
								$list[$k]['last_repay_date'] = date("Y-m-d",(strtotime('+'.$v['repay_time'].' day',$v['repay_start_time'])));
							}else{
								//天标
								$list[$k]['last_repay_date'] = date("Y-m-d",(strtotime('+'.$v['repay_time'].' day',$v['repay_start_time'])-24*60*60));
							}
						}
						
					}
                }else{
                    $list[$k]['last_repay_date'] = date("Y-m-d",strtotime('+'.$v['repay_time'].' months',$v['create_time']));
					if($list[$k]['last_repay_date']>=date('Y-m-d')){
						$list[$k]['deal_load_status']="还款中";
						$list[$k]['deal_status']=4;
					}else{
						$list[$k]['deal_load_status']="已完成";
						$list[$k]['deal_status']=5;
					}
                }
				
			}
		}
    	$count = $result['count'];
    	$GLOBALS['tmpl']->assign("list",$list);
    	$page = new Page($count,app_conf("PAGE_SIZE"));   //初始化分页对象
    	$p  =  $page->show();
    	$GLOBALS['tmpl']->assign('pages',$p);

    	$GLOBALS['tmpl']->assign('state_url',$state_url);
		$GLOBALS['tmpl']->assign("condition_paging",$condition_paging);
    	
		$GLOBALS['tmpl']->assign('user_id', $GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign('debts', $GLOBALS['user_info']['debts']);
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_INVEST']);
  
    	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invest.html");
    	$GLOBALS['tmpl']->display("page/uc.html");
    }

    public function lendlist() {
    	/*wap出借记录*/
    	$invest_status = intval($_REQUEST['invest_status']);
    	$GLOBALS['tmpl']->assign("invest_status",$invest_status);
    	$page_args['invest_status'] =  $invest_status;

    	$deal_type = intval($_REQUEST['loan_type']);
    	$GLOBALS['tmpl']->assign("deal_type",$deal_type);
        $page_args['loan_type'] =  $deal_type;

    	$invest_type = intval($_REQUEST['invest_type']);
    	$GLOBALS['tmpl']->assign("invest_type",$invest_type);
		$page_args['invest_type'] =  $invest_type;
		
		
    	$invest_cg = array(
			array(
				"name" => "存管版",
			),
			array(
				"name" => "普通版",
			),
		);
		foreach($invest_cg as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_status'] = $k;
			$invest_cg[$k]['url'] = url("index","uc_invest#lendlist",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest_cg',$invest_cg);
		
    	$invest = array(
			array(
				"name" => "进行中",
			),
			array(
				"name" => "已完成",
			),
		);
		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_type'] = $k;
			$invest[$k]['url'] = url("index","uc_invest#lendlist",$tmp_args);
		}		
		$GLOBALS['tmpl']->assign('invest',$invest);
		//项目类型
		
        
        //$loan_types=$GLOBALS['db']->getAll("SELECT id,name,information_status  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0");
        $loan_types = array(
            array(
                "name" => "普通标",
                "information_status"=>0,
            ),
            array(
                "name" => "短贷宝",
                "information_status"=>1,
            ),
        );
        foreach($loan_types as $k=>$v){
            $tmp_args = $page_args;
            $tmp_args['loan_type'] = $v['information_status'];
            $loan_types[$k]['url'] = url("index","uc_invest#lendlist",$tmp_args);
        }
        $GLOBALS['tmpl']->assign('loan_type',$loan_types);
		$order = " order by dl.id desc";
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		if($invest_status == 1){
			if ($invest_type == 1){
			$condition = " AND date_sub(dl.create_date,interval  -de.repay_time month)<='".date('Y-m-d')."' and de.cunguan_tag =0";
			}else{
				$condition = " AND date_sub(dl.create_date,interval -de.repay_time month)>='".date('Y-m-d')."' and de.cunguan_tag =0";
			}
		}else{
			if($invest_type == 1){
				$condition = " and de.deal_status = 5 and de.cunguan_tag =1";
			}else{
				$condition = " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =1";
			}
		}
    	/*********wap 进行中的出借********/
    	$user_id = $GLOBALS['user_info']['id'];
   		//获取投资记录 wap
   		$loan_type_id=$GLOBALS['db']->getOne("SELECT id  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0 and information_status=".$deal_type);
   		if($deal_type != 1){
   			//$condition.=" and de.type_id=".$loan_type_id;
            $condition.=" and de.type_id <>12";
   		}
		if($deal_type == 1){
			$result = get_plan_invest_log($user_id,$condition,$order,$limit);
		}else{

			$result = get_invest_log($user_id,$condition,$order,$limit);

		}
    	foreach ($result['list'] as $k => $v) {  
			
    		$i = $v['repay_time'];
    		if($v['old_deal_id']>0){
				$v['repay_date'] = date("Y-m-d",(strtotime("+$i month",$v['create_time'])-24*60*60));
			}else{
				if($v['repay_time_type']==1){
					$v['repay_date'] = date("Y-m-d",(strtotime("+$i month",$v['create_time'])-24*60*60));
				}else{
					$v['repay_date'] = date("Y-m-d",(strtotime("+$i day",$v['create_time'])));
				}
			}
    		
			if($invest_status==1){
				$v['interest_money'] = $GLOBALS['db']->getOne("select sum(interest_money) as interest_money from ".DB_PREFIX."deal_load_repay  where load_id =".$v['bid']);
				if($v['repay_date']>date('Y-m-d')){
					$v['deal_status']=4;
				}else{
					$v['deal_status']=5;
				}
			}else{
				if($deal_type == 1){
					$v['interest_money']= $GLOBALS['db']->getOne("SELECT sum(interest_money+raise_money+increase_interest+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$v['id']." and plan_load_id=".$v['bid']);
				}else{
					if($v['old_deal_id']>0){
						$v['deal_status'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$v['old_deal_id']);
					}
					$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$v['bid']." and status!=4 and cunguan_tag=1");
					$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$v['bid']." and status=4 and cunguan_tag=1 order by id asc limit 1");
					$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$v['bid']." and status=4 and cunguan_tag=1 order by id desc limit 1");
					$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
					$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
					$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
					$v['interest_money'] = ($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
					$v['deal_type'] = $deal_type;
				}
				
			}
			
    		$v['money'] = intval($v['money']+$v['red']+$v['ecv_money']);
    		$temp_user_load[] = $v;
    	}
    	$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
    	$GLOBALS['tmpl']->assign("temp_user_load",$temp_user_load);
    	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invest.html");
    	$GLOBALS['tmpl']->display("page/uc.html");
    }
	public function details(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$load_id = intval($_REQUEST['load_id']);
		$GLOBALS['tmpl']->assign("load_id",$load_id);
		$deal_type = intval($_REQUEST['deal_type']);
		$GLOBALS['tmpl']->assign("deal_type",$deal_type);
		if($deal_type == 1){
			$temp_user_load = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."plan_load dl left join ".DB_PREFIX."plan de on de.id = dl.plan_id WHERE dl.id=".$load_id." and dl.user_id=".$user_id." and de.id=".$id);
			$date_day = strtotime(date("Y-m-d",strtotime('+1 day',$temp_user_load['load_time'])));
			if(time() > $date_day)
				$temp_user_load['check_debt'] =1;
			else
				$temp_user_load['check_debt'] =0;
		}else{
			$temp_user_load = $GLOBALS['db']->getRow("SELECT de.id,de.name,de.loantype,de.repay_start_time,de.cunguan_tag,dl.id,de.deal_status,dl.total_money,dl.money,dl.rebate_money,de.rate,de.borrow_amount, dl.interestrate_id,dl.increase_interest,de.repay_time,de.repay_time_type,dl.red,dl.ecv_id,dl.create_time,de.interest_rate,de.old_deal_id FROM ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal de on de.id = dl.deal_id WHERE dl.id=".$load_id." and dl.user_id=".$user_id." and de.id=".$id);
		}
		//使用代金券金额
		//$ecv_id = rtrim($temp_user_load['ecv_id'],",");
		//$temp_user_load['ecv_money'] = intval($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."ecv  where id in(".$ecv_id.")"));

		if($temp_user_load['interestrate_id']){
			$interest_card=$GLOBALS['db']->getRow("select rate,use_time from ".DB_PREFIX."interest_card  where user_id = ".$user_id." and id=".$temp_user_load['interestrate_id']);
			$temp_user_load['coupon_rate'] = $interest_card['rate'].'%';
			$temp_user_load['coupon_day'] = ($interest_card['use_time']==0) ? '全程加息' : $interest_card['use_time'].'天';
		}
		if($temp_user_load['cunguan_tag']==0){
			$temp_user_load['deal_status']=4;
		}
		if($deal_type == 1){
			//预期收益 理财计划
			$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money+raise_money+increase_interest+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$id." and plan_load_id=".$load_id);
			if($temp_user_load['repay_time_type'] == 1){
				$temp_user_load['interest_money'] = $intterest_money?format_price($intterest_money):format_price(sprintf('%.2f',$temp_user_load['total_money']*$temp_user_load['rate']/12/100*$temp_user_load['repay_time']));
			}else{
				$temp_user_load['interest_money'] = $intterest_money?format_price($intterest_money):format_price(sprintf('%.2f',$temp_user_load['total_money']*$temp_user_load['rate']/365/100*$temp_user_load['repay_time']));
			}
			//已收收益
			$temp_user_load['end_interest_money'] = floatval($GLOBALS['db']->getOne("select sum(interest_money) from ".DB_PREFIX."deal_load_repay  where plan_load_id =".$load_id." and has_repay= 1"));
			$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
			$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
			$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
			$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
			$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
			$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
			$temp_user_load['end_interest_money'] = floatval($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
			
		}else{
			
			$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money+raise_money+increase_interest+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$id." and load_id=".$load_id);
			$temp_user_load['interest_money'] = $intterest_money?format_price($intterest_money):format_price(sprintf('%.2f',$temp_user_load['total_money']*$temp_user_load['rate']/12/100*$temp_user_load['repay_time']));
			//已收收益
			$temp_user_load['end_interest_money'] = floatval($GLOBALS['db']->getOne("select sum(interest_money) from ".DB_PREFIX."deal_load_repay  where load_id =".$load_id." and has_repay= 1"));
			$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
			$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
			$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
			$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
			$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
			$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
			$temp_user_load['end_interest_money'] = floatval($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
		}
		

		
		//结息日期
		if($temp_user_load['old_deal_id']>0){
			$temp_user_load['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$temp_user_load['old_deal_id']." and load_id=".$load_id." order by id desc limit 1");
			$temp_user_load['deal_status'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$temp_user_load['old_deal_id']);
			$last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$temp_user_load['old_deal_id']." order by repay_time desc limit 1");
// 			$temp_user_load['repay_time'] = ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
			$temp_user_load['repay_time'] = ceil(($last_repay_time-$temp_user_load['create_time'])/3600/24)+1;
		}else{
			if($deal_type == 1){
				$temp_user_load['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$id." and plan_load_id=".$load_id." order by id desc limit 1");
			}else{
				$temp_user_load['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$id." and load_id=".$load_id." order by id desc limit 1");
			}
			
		}
		$temp_user_load['money'] = intval($temp_user_load['money']);
		$temp_user_load['red'] = intval($temp_user_load['red']);
		if($deal_type == 1){
			$create_date = $temp_user_load['load_time'];
		}else{
			$create_date = $temp_user_load['create_time'];
		}
		$temp_user_load['create_date'] = date("Y-m-d",$create_date);
		$temp_user_load['rate'] = ($temp_user_load['interest_rate']!=0) ? sprintf("%.1f",$temp_user_load["rate"])."+".sprintf("%.1f",$temp_user_load["interest_rate"]) : sprintf("%.1f",$temp_user_load["rate"]);
		$temp_user_load['pid'] = $id;//合同下载需要的项目ID
		/*$temp_user_load['count_money'] = $temp_user_load['money']+$temp_user_load['red']+$temp_user_load['ecv_money'];*/
		$temp_user_load['count_money'] = $temp_user_load['money']+$temp_user_load['red'];
		$temp_user_load['loantype_format'] = loantypename($temp_user_load['loantype'],1);
		$GLOBALS['tmpl']->assign("details",$temp_user_load);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invest_details.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
public function ajaxInvest(){
	$invest_type = intval($_REQUEST['invest_type']);
	$invest_status = intval($_REQUEST['invest_status']);
	$deal_type = intval($_REQUEST['deal_type']);
    $order = " order by dl.id desc";
	if($page==0)
	$page = 1;
	if($invest_status == 1){
		if ($invest_type == 1){
			//$condition = " and de.deal_status = 2 and de.cunguan_tag =0";
			$condition ="AND date_sub(dl.create_date,interval  -de.repay_time month)<='".date('Y-m-d')."' and de.cunguan_tag =0";
		}else{
			//$condition = " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =0";
			$condition ="AND date_sub(dl.create_date,interval  -de.repay_time month)>'".date('Y-m-d')."' and de.cunguan_tag =0";
		}
	}else{
		if($invest_type == 1){
			$condition = " and de.deal_status = 5 and de.cunguan_tag =1";
		}else{
			$condition = " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =1";
		}
	}
	/*********wap 进行中的出借********/
	$user_id = $GLOBALS['user_info']['id'];
		//获取出借记录 wap
	if($deal_type == 1){
		$result = get_plan_invest_log($user_id,$condition,$order,$limit);
	}else{
        //$condition.="de.type_id <> 12";
		$result = get_invest_log($user_id,$condition,$order,$limit);
	}
	
	echo $result['count'];
}
public function investList(){
	$page = intval($_REQUEST['page']);
	$invest_type = intval($_REQUEST['invest_type']);
	$invest_status = intval($_REQUEST['invest_status']);
	$deal_type = intval($_REQUEST['deal_type']);

    $order = " order by dl.id desc";
	if($page==0)
	$page = 1;
	if($invest_status == 1){
		if ($invest_type == 1){
			//$condition = " and de.deal_status = 5 and de.cunguan_tag =0";
			$condition ="AND date_sub(dl.create_date,interval  -de.repay_time month)<='".date('Y-m-d')."' and de.cunguan_tag =0";
		}else{
			//$condition = " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =0";
			$condition ="AND date_sub(dl.create_date,interval  -de.repay_time month)>'".date('Y-m-d')."' and de.cunguan_tag =0";
		}
	}else{
		if($invest_type == 1){
			$condition = " and de.deal_status = 5 and de.cunguan_tag =1";
		}else{
			$condition = " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =1";
		}
	}
	/*********wap 进行中的出借********/
	$user_id = $GLOBALS['user_info']['id'];
		//获取投资记录 wap
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	if($deal_type == 1){
		$result = get_plan_invest_log($user_id,$condition,$order,$limit);
	}else{
		$result = get_invest_log($user_id,$condition,$order,$limit);
	}	
	foreach ($result['list'] as $k => $v) {    		
		$i = $v['repay_time'];
		if($v['repay_start_time']==1){
			$v['repay_date'] = date("Y-m-d",(strtotime("+$i month",$v['create_time'])-24*60*60));
		}else{
			$v['repay_date'] = date("Y-m-d",(strtotime("+$i day",$v['create_time'])-24*60*60));
		}
		if($invest_status==1){
			$v['interest_money'] = $GLOBALS['db']->getOne("select sum(interest_money) as interest_money from ".DB_PREFIX."deal_load_repay  where load_id =".$v['bid']);
			if($v['repay_date']>date('Y-m-d')){
				$v['deal_status']=4;
			}else{
				$v['deal_status']=5;
			}
		}else{
			if($deal_type == 1){
				$v['interest_money']= $GLOBALS['db']->getOne("SELECT sum(interest_money+raise_money+increase_interest+interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$v['id']." and plan_load_id=".$v['bid']);
			}else{
				if($v['old_deal_id']>0){
					$v['deal_status'] = $GLOBALS['db']->getOne("select deal_status from ".DB_PREFIX."deal where id=".$v['old_deal_id']);
				}
				$v['interest_money'] = $GLOBALS['db']->getOne("select sum(interest_money+raise_money+increase_interest+interestrate_money) as interest_money from ".DB_PREFIX."deal_load_repay  where load_id =".$v['bid']);
			}
			
		}
		$v['money'] = intval($v['money']+$v['red']+$v['ecv_money']);
		$v['deal_type'] = $deal_type;
		$temp_user_load[] = $v;
	}
	if (empty($result['list'])) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('temp_user_load',$temp_user_load);
            $info = $GLOBALS['tmpl']->fetch("inc/uc/investList.html");
            echo $info;
        }
	}
	public function refdetail(){
    	$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']) ;
		$load_id = intval($_REQUEST['load_id']);
		$deal_type = intval($_REQUEST['deal_type']);
		require APP_ROOT_PATH."app/Lib/deal.php";
		$deal = get_deal($id);
		$GLOBALS['tmpl']->assign('deal',$deal);
		if($deal_type==1){
			//获取本期的投标记录
			$temp_user_loads = $GLOBALS['db']->getAll("SELECT self_money,(interest_money+raise_money+increase_interest+interestrate_money) as interest_money,repay_date,(self_money+interest_money+raise_money+increase_interest+interestrate_money) as money FROM ".DB_PREFIX."deal_load_repay  WHERE plan_id=".$id." and user_id=".$user_id." and plan_load_id=".$load_id." order by repay_time asc");
			$day_repay_list=getUcPlanReay($user_id,$load_id);
			foreach($day_repay_list['list'] as $key =>$value){
					$data['money'] = sprintf("%.2f",$value['self_money']+$value['interest_money']);
					$data['interest_money'] = sprintf("%.2f",$value['interest_money']);
					$data['self_money'] = sprintf("%.2f",$value['self_money']);
					$data['repay_date'] = date("Y-m-d",$value['repay_time']);
					$user_load['self_money'] += $value['self_money'];
					$user_load['raise_money'] += 0;
					$user_load['interest_money'] += $value['interest_money'];
					$user_load['count_money'] +=  $value['self_money']+$value['interest_money'];
					$temp_user_load[]=$data;
				} 
		}else{
			//获取本期的投标记录
			$temp_user_load = $GLOBALS['db']->getAll("SELECT self_money,(interest_money+raise_money+increase_interest+interestrate_money) as interest_money,repay_date,(self_money+interest_money+raise_money+increase_interest+interestrate_money) as money FROM ".DB_PREFIX."deal_load_repay  WHERE load_id=".$id." and user_id=".$user_id." order by repay_time asc");
			//最后一期
			$user_load = $GLOBALS['db']->getRow("SELECT self_money,raise_money,(interest_money+raise_money+increase_interest+interestrate_money) as interest_money,(self_money+interest_money+raise_money+increase_interest+interestrate_money) as count_money FROM  ".DB_PREFIX."deal_load_repay WHERE load_id=".$id." and user_id=".$user_id." order by repay_time desc limit 1");
		}		
		
		$GLOBALS['tmpl']->assign('user_load',$user_load);
		$GLOBALS['tmpl']->assign('temp_user_load',$temp_user_load);

		$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
		$GLOBALS['tmpl']->assign("inrepay_info",$inrepay_info);
		
		$GLOBALS['tmpl']->assign("load_id",$load_id);
		$GLOBALS['tmpl']->assign("deal_type",$deal_type);
		$GLOBALS['tmpl']->assign("page_title","我的回款");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invest_refdetail.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
    }
    
    public function mrefdetail(){
    	$user_id = $GLOBALS['user_info']['id'];
    	$id = intval($_REQUEST['id']);
    	$load_id = intval($_REQUEST['load_id']);
    	require APP_ROOT_PATH."app/Lib/deal.php";
    	$deal = get_deal($id);
    	if(!$deal || $deal['deal_status']<4){
    		showErr("无法查看，可能有以下原因！<br>1。借款不存在<br>2。借款被删除<br>3。借款未成功");
    	}
    	$GLOBALS['tmpl']->assign('deal',$deal);
    
    	$deal_load_list = get_deal_load_list($deal);
    
    	//获取本期的投标记录
		$temp_user_load = $GLOBALS['db']->getRow("SELECT id,deal_id,user_id,money FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." and id=".$load_id." and user_id=".$user_id);
		
		$user_load_ids = array();
		if($temp_user_load){
			$u_key = $GLOBALS['db']->getOne("SELECT u_key FROM ".DB_PREFIX."deal_load_repay WHERE load_id=".$load_id." and user_id=".$user_id);
			if($temp_user_load['user_id'] == $user_id){
				$temp_user_load['repay_start_time'] = $deal['repay_start_time'];
				$temp_user_load['repay_time'] = $deal['repay_time'];
				$temp_user_load['rate'] = $deal['rate'];
				$temp_user_load['u_key'] = $u_key;
				$temp_user_load['load'] = get_deal_user_load_list($deal, $user_id, -1 ,$u_key);
				$temp_user_load['impose_money'] =0;
				$temp_user_load['manage_fee'] = 0;
				$temp_user_load['repay_money'] = 0;
				$temp_user_load['manage_interest_money'] = 0;
				foreach($temp_user_load['load'] as $kk=>$vv){
					$temp_user_load['impose_money'] += $vv['impose_money'];
					$temp_user_load['manage_fee'] += $vv['manage_money'];
					$temp_user_load['repay_money'] += $vv['month_has_repay_money'];
					$temp_user_load['manage_interest_money'] += $vv['manage_interest_money'];
					
					//预期收益
					$temp_user_load['load'][$kk]['yuqi_money']=format_price($vv['month_repay_money']-$vv['self_money'] - $vv['manage_money'] - $vv['manage_interest_money']);
					//实际收益
					if($vv['has_repay']==1){
						$temp_user_load['load'][$kk]['real_money']=format_price($vv['month_repay_money']- $vv['self_money']+$vv['impose_money'] - $vv['manage_money']- $vv['manage_interest_money']);
						
					}
				}
				$user_load_ids[] = $temp_user_load;
			}
		}
    
    	$GLOBALS['tmpl']->assign('user_load_ids',$user_load_ids);
    
    	$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
    	$GLOBALS['tmpl']->assign("inrepay_info",$inrepay_info);
    	
    	$GLOBALS['tmpl']->assign("load_id",$load_id);
    	$GLOBALS['tmpl']->assign("page_title","我的回款");
    	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invest_refdetail.html");
    	$GLOBALS['tmpl']->display("uc_invest_mrefdetail.html");
    }
    
    public function export_csv($page = 1)
    {
    	set_time_limit(0);
    	$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
    
    	$ac= strim($_REQUEST['ac']);  //"index".我的出借 "invite".招标的借款 "flow".流标的借款 "ing".回收中借款 "over".已回收借款 "bad".我的坏账
    	if(!in_array($ac,array("index","invite","flow","ing","over","bad"))){
    		showErr("无导出信息");
    	}
    	$result = getInvestList($ac,intval($GLOBALS['user_info']['id']),intval($_REQUEST['p']));
    	
    	$list = $result['list'];
    	//定义条件
    	if(!$list)
    	{
    		showErr("无导出信息");
    	}
    	foreach($list as $k=>$v){
    		
			$deal['borrow_amount'] = $v['u_load_money'];
			$deal['rate'] = $v['rate'];
			$deal['loantype'] = $v['loantype'];
			$deal['repay_time'] = $v['repay_time'];
	    	$deal['repay_time_type'] = $v['repay_time_type'];
	    	$deal['repay_start_time'] = $v['repay_start_time'];
			
			$deal_repay_rs = deal_repay_money($deal);
			
    		$v['interest_amount'] = $deal_repay_rs['month_repay_money'];
				
    		$list[$k] = $v;
    	}
    	
    	if($list)
    	{
    		register_shutdown_function(array(&$this, 'export_csv_1'), $page+1);
    		$repay_value = array('name'=>'""','user_name'=>'""','u_load_money'=>'""','repay_time'=>'""','rate'=>'""','rebate_money'=>'""','point_level'=>'""','progress_point'=>'""','deal_stauts'=>'""','transfer'=>'""');
    	
    		$content = "";
    		$contentss = iconv("utf-8","gbk","标题,借款人,投标金额,期限,利率,奖励,信用等级,进度,状态,是否转让");
    		$content  .= $contentss . "\n";
    		foreach($list as $k=>$v)
    		{
    			$deal_status = array("1"=>"筹标中","2"=>"已满标","3"=>"已流标","4"=>"回收中","5"=>"已回收");
    			$repay_value = array();
    			$repay_value['name'] = iconv('utf-8','gbk','" 第' . $v['name'] . '期"');
    			$repay_value['user_name'] = iconv('utf-8','gbk','"' . $v['user_name'] . '"');
    			$repay_value['u_load_money'] = iconv('utf-8','gbk','"' . format_price($v['u_load_money']) . '"');
    			$repay_value['repay_time'] = iconv('utf-8','gbk','"' . $v['repay_time'].($v['repay_time_type'] == 0? "天" : "个月") . '"');
    			$repay_value['rate'] = iconv('utf-8','gbk','"' . $v['rate']."%" . '"');
    			$repay_value['rebate_money'] = iconv('utf-8','gbk','"' . format_price($v['rebate_money']) . '"');
    			$repay_value['point_level'] = iconv('utf-8','gbk','"' . $v['point_level'] . '"');
    			$repay_value['progress_point'] = iconv('utf-8','gbk','"' . $v['progress_point']."%" . '"');
    			$repay_value['deal_stauts'] = iconv('utf-8','gbk','"' . $deal_status[$v['deal_status']]. '"');
    			if($v['has_transfer'] > 0 && $v['t_user_id'] <> $GLOBALS['user_info']['id']){
    				$repay_value['transfer'] = iconv('utf-8','gbk','"是"');
    			}
    			else{
    				$repay_value['transfer'] = iconv('utf-8','gbk','"否"');
    			}
    			$content .= implode(",", $repay_value) . "\n";
    		}
    			
    		header("Content-Disposition: attachment; filename=repay_list.csv");
    		echo $content;
    	}
    	else
    	{
    		if($page==1)
    			$this->error(L("NO_RESULT"));
    	}
    
    }
    
    function export_detail_csv(){
    	//277
    	$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$load_id = intval($_REQUEST['load_id']);
		require APP_ROOT_PATH."app/Lib/deal.php";
		$deal = get_deal($id);
		if(!$deal || $deal['deal_status']<4){
			showErr("无法查看，可能有以下原因！<br>1。借款不存在<br>2。借款被删除<br>3。借款未成功");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
				
		//获取本期的投标记录
		$temp_user_load = $GLOBALS['db']->getRow("SELECT dl.id,dl.deal_id,dl.user_id,dl.money,dlt.t_user_id FROM ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal_load_transfer dlt on dlt.load_id = dl.id WHERE dl.deal_id=".$id." and dl.id=".$load_id);
		
		//print_r("SELECT id,deal_id,user_id,money FROM ".DB_PREFIX."deal_load  WHERE deal_id=".$id." and id=".$load_id);die;
		
		$content = iconv("utf-8","gbk",'"'.$deal['name']." 投标的回款记录!".'"')."\n";
		$content .= iconv("utf-8","gbk","借款金额,年利率,期限,已还本息,管理费,利息管理费,逾期/违约,还款方式")."\n";
		$user_load_ids = array();
		if($temp_user_load){
			$u_key = $GLOBALS['db']->getOne("SELECT u_key FROM ".DB_PREFIX."deal_load_repay WHERE load_id=".$load_id." and (user_id=".$user_id." or t_user_id = ".$user_id.")");
			if(($temp_user_load["user_id"] == $user_id && intval($temp_user_load['t_user_id']) == 0 )|| $temp_user_load['t_user_id'] == $user_id){
				$temp_user_load['repay_start_time'] = $deal['repay_start_time'];
				$temp_user_load['repay_time'] = $deal['repay_time'];
				$temp_user_load['rate'] = $deal['rate'];
				$temp_user_load['u_key'] = $u_key;
				$temp_user_load['load'] = get_deal_user_load_list($deal, $user_id, -1 ,$u_key);
				$temp_user_load['impose_money'] =0;
				$temp_user_load['manage_fee'] = 0;
				$temp_user_load['repay_money'] = 0;
				$temp_user_load['manage_interest_money'] = 0;
				
				$list_content = "";
				foreach($temp_user_load['load'] as $kk=>$vv){
					$temp_user_load['impose_money'] += $vv['impose_money'];
					$temp_user_load['manage_fee'] += $vv['manage_money'];
					$temp_user_load['repay_money'] += $vv['month_has_repay_money'];
					$temp_user_load['manage_interest_money'] += floatval($vv['manage_interest_money']);
					
					//预期收益
					$temp_user_load['load'][$kk]['yuqi_money']=format_price($vv['month_repay_money']-$vv['self_money'] - $vv['manage_money'] - $vv['manage_interest_money']);
					//实际收益
					if($vv['has_repay']==1){
						$temp_user_load['load'][$kk]['real_money']=format_price($vv['month_repay_money']- $vv['self_money']+$vv['impose_money'] - $vv['manage_money']- $vv['manage_interest_money']);
						
					}
					$repay_value = array();
					$repay_value['repay_day'] = iconv("utf-8","gbk",'"'.to_date($vv['repay_day'],"Y-m-d").'"');
					$repay_value['true_repay_day'] = iconv("utf-8","gbk",'"'.to_date($vv['true_repay_time'],"Y-m-d").'"');
					$repay_value['month_has_repay_money'] = iconv("utf-8","gbk",'"'.format_price($vv['month_has_repay_money']).'"');
					$repay_value['manage_money'] = iconv("utf-8","gbk",'"'.format_price($vv['manage_money']).'"');
					$repay_value['manage_interest_money'] = iconv("utf-8","gbk",'"'.format_price($vv['manage_interest_money']).'"');
					$repay_value['impose_money'] = iconv("utf-8","gbk",'"'.format_price($vv['impose_money']).'"');
					$repay_value['yuqi_money'] = iconv("utf-8","gbk",'"'.format_price($temp_user_load['load'][$kk]['yuqi_money']).'"');
					$repay_value['real_money'] = iconv("utf-8","gbk",'"'.format_price($temp_user_load['load'][$kk]['real_money']).'"');
					$repay_value['status_format'] = iconv("utf-8","gbk",'"'.$vv['status_format'].'"');
					
					$list_content  .= implode(",", $repay_value) . "\n";
				}
				
				
				$content .=iconv("utf-8","gbk",'"'.format_price($temp_user_load['money']).'"').",";//借款金额
				$content .=iconv("utf-8","gbk",'"'.number_format($temp_user_load['rate'],2).'%"').",";//年利率
				$content .=iconv("utf-8","gbk",'"'.$deal['repay_time'].($deal['repay_time_type']==0 ? "天" :"个月").'"').",";//期限
				$content .=iconv("utf-8","gbk",'"'.format_price($temp_user_load['repay_money']).'"').",";//已还本息
				$content .=iconv("utf-8","gbk",'"'.format_price($temp_user_load['manage_fee']).'"').",";//管理费
				$content .=iconv("utf-8","gbk",'"'.format_price($temp_user_load['manage_interest_money']).'"').",";//利息管理费
				$content .=iconv("utf-8","gbk",'"'.format_price($temp_user_load['impose_money']).'"').",";//逾期/违约
				$content .=iconv("utf-8","gbk",'"'.loantypename($deal['loantype']).'"')."\n";//还款方式
				
				$content .="\n";
				$content .= iconv("utf-8","gbk","还款日,实际还款日,已回收本息,管理费,利息管理费,逾期/违约金,预期收益,实际收益,状态")."\n";
				
				$content .=$list_content;
			}
		}
		
		$GLOBALS['tmpl']->assign('user_load_ids',$user_load_ids);
		
		$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
		
		if($inrepay_info){
			$content .="\n";
			$content .= iconv("utf-8","gbk",'"因借款者在'.to_date($inrepay_info['true_repay_time'],"Y-m-d").'提前还款，故计算方式改变。"')."\n";
		}
		
		header("Content-Disposition: attachment; filename=repay_detail.csv");
    	echo $content;
		
    }
	public function invite_info(){
		$user_id = $GLOBALS['user_info']['id'];
		$load_id = intval($_REQUEST['load_id']);
		$deal_type = intval($_REQUEST['deal_type']);
		if(!$deal_type){
			$deal_log = $GLOBALS['db']->getRow("SELECT deal_id,interestrate_id,money,red,ecv_money,create_time,total_money,debts,old_deal_id FROM ".DB_PREFIX."deal_load WHERE id=".$load_id." and user_id=".$user_id);
			$deal = $GLOBALS['db']->getRow("SELECT * from ".DB_PREFIX."deal where id = ".intval($deal_log['deal_id'])."  and is_delete <> 1  and is_effect = 1");
			if($deal_log['interestrate_id']){
	            $interest=$GLOBALS['db']->getRow("select ic.rate,c.interest_time,c.interest_time_type from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.id=".$deal_log['interestrate_id']);
	            $GLOBALS['tmpl']->assign('interest',$interest);
	        }
			if($deal['pid_1_0']){
				$deal['success_time'] = $deal_log['create_time'];
				//项目募集期
				$deal['collect_day'] = 0;
				//个人募集期
				$deal['my_collect_day'] = 0;
				//起息日期
				$deal['repay_start_time'] = $deal_log['create_time'];
				$deal['repay_start_date'] = date("Y-m-d",$deal['repay_start_time']);
				//结息日期
				$deal['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$deal_log['deal_id']." and load_id=".$load_id." order by id desc limit 1");
				// 项目收益
				$deal_log['deal_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
				// 募集期收益
				$deal_log['raise_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(raise_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
				// 加息券收益
				$deal_log['interestrate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
				// 奖励加息收益
				$deal_log['increase_interest']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(increase_interest) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
				// 预期收益
				$deal_log['anticipate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
				// 已收收益
				$deal_log["load_repay_money"] = format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay=1 and deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
			}else {
				// 募集天数
				if ($deal['repay_start_time']) {
					//项目募集期
					$deal['collect_day'] = (strtotime(date("Y-m-d", $deal['repay_start_time'])) - strtotime(date("Y-m-d", $deal['start_time']))) / 86400;
					//个人募集期
					$deal['my_collect_day'] = (strtotime(date("Y-m-d", $deal['repay_start_time'])) - strtotime(date("Y-m-d", $deal_log['create_time']))) / 86400;
					//结息日期
					if($deal['debts']>0){
						$deal['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$deal_log['old_deal_id']." and load_id=".$load_id." order by id desc limit 1");
					}else{
						$deal['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$deal_log['deal_id']." and load_id=".$load_id." order by id desc limit 1");
					}
					if($GLOBALS['user_info']['debts']>0 && $deal_load['old_deal_id']==0){
						$load_money = $GLOBALS['db']->getOne("select load_money from ".DB_PREFIX."deal where old_deal_id=".$deal_log['deal_id']." and old_load_id=".$load_id);
						//持有天数
						$deal_log['holddays'] = (strtotime(date("Y-m-d",$deal_log['create_time'])) - strtotime(date("Y-m-d",$deal['create_time'])))/86400;
						//手续费
						$deal_log['fee_money'] = 0;
						//转让价格
						$deal_log['debts_money'] = $deal_log['total_money'];
						//转让进度
						$deal_log['progress'] =round((($load_money/$deal_log['debts_money'])*100),2);
					}
					// 项目收益
					$deal_log['deal_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					// 募集期收益
					$deal_log['raise_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(raise_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					// 加息券收益
					$deal_log['interestrate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					// 奖励加息收益
					$deal_log['increase_interest']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(increase_interest) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					// 预期收益
					if($deal_log['debts']>0){
						$deal_log['anticipate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT SUM(interest_money+raise_money+interestrate_money+increase_interest) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$deal_log['old_deal_id']." and load_id=".$load_id)));
						
					}else{
						$deal_log['anticipate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT SUM(interest_money+raise_money+interestrate_money+increase_interest) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
						
					}
					// 已收收益
					//$deal_log["load_repay_money"] = format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(true_interest_money+raise_money+interestrate_money+increase_interest) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay=1 and deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
					$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
					$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
					$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
					$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
					$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
					$deal_log["load_repay_money"] = ($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
				} else {
					//项目募集期
					$deal['success_time']=$deal['success_time']?$deal['success_time']:time();
					$deal['collect_day'] = (strtotime(date("Y-m-d",$deal['success_time'])) - strtotime(date("Y-m-d", $deal['start_time']))) / 86400;
					//起息日期
					$deal['repay_start_time'] = $deal_log['create_time'];
					$deal['repay_start_date'] = date("Y-m-d",$deal['repay_start_time']);
					//结息日期
					$deal['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$deal_log['deal_id']." and load_id=".$load_id." order by id desc limit 1");
					//个人募集期
					$deal['my_collect_day'] = (strtotime(date("Y-m-d",$deal['success_time'])) - strtotime(date("Y-m-d", $deal_log['create_time']))) / 86400;
					// 项目收益
					$deal_log['deal_money'] = format_price(sprintf('%.2f',$deal_log['total_money']*$deal['rate']/12/100*$deal['repay_time']));
					// 募集期收益
					$deal_log['raise_money'] = format_price(sprintf('%.2f',$deal_log['total_money']*$deal['rate']/12/365*$deal['my_collect_day']));
					// 加息券收益
					$deal_log['interestrate_money'] = 0;
					// 奖励加息收益
					$deal_log['increase_interest'] = 0;
					// 预期收益
					//$deal_log['anticipate_money'] = format_price(sprintf('%.2f',$deal_log['total_money']*$deal['rate']/12/100*$deal['repay_time'])+sprintf('%.2f',$deal_log['total_money']*$deal['rate']/12/365*$deal['my_collect_day']));
					$deal_log['anticipate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and  deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					
					// 已收收益
					//$deal_log["load_repay_money"] = 0;
					//$deal_log["load_repay_money"] = format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT SUM(true_interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and has_repay=1 and deal_id =".$deal_log['deal_id']." and load_id=".$load_id)));
					$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
					$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
					$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
					$cunguan_load_repay_money_one = $cunguan_load_repay_money_one?$cunguan_load_repay_money_one:0;
					$cunguan_load_repay_money_two = $cunguan_load_repay_money_two?$cunguan_load_repay_money_two:0;
					$cunguan_load_repay_money_thr = $cunguan_load_repay_money_thr?$cunguan_load_repay_money_thr:0;
					$deal_log["load_repay_money"] = ($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
				}
			}
	        if($deal['debts']==1){
	            $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal['old_deal_id']." order by repay_time desc limit 1");
	//             $deal['debts_repay_time']= ceil(($last_repay_time-time())/3600/24)+1;
	            $deal['debts_repay_time']= ceil(($last_repay_time-$deal_log['create_time'])/3600/24)+1;
	        }
	        // 回款计划
			$limit=12;
			$day_repay_list = getUcRepayPlan($user_id,6,$limit," and dlr.load_id=$load_id");
			$deal['borrow_amount']=format_price($deal['borrow_amount']);
			$deal_log['red_ecv']=format_price($deal_log['total_money']-$deal_log['money']);
			$deal_log['money']=format_price($deal_log['money']);
			$deal_log['red']=format_price($deal_log['red']);
			$deal_log['ecv_money']=format_price($deal_log['ecv_money']);
			$deal_log['create_time']=date('Y-m-d',$deal_log['create_time']);
		}else{
			$page = intval($_REQUEST['p']);
			if($page==0){
				$page = 1;
			}
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			$deal_log = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."plan_load WHERE id=".$load_id." and user_id=".$user_id);
			$deal = $GLOBALS['db']->getRow("SELECT * from ".DB_PREFIX."plan where id = ".intval($deal_log['plan_id'])."  and is_delete <> 1  and is_effect = 1");
			$deal['mb_success_time']=$deal['success_time']?$deal['success_time']:time();
			$deal['collect_day'] = (strtotime(date("Y-m-d",$deal['mb_success_time'])) - strtotime(date("Y-m-d", $deal['start_time']))) / 86400;
			$deal['borrow_amount']=format_price($deal['borrow_amount']);
			//起息日期
			$deal_log['create_time'] = strtotime($deal_log['create_date']);
			$deal['repay_start_time'] = $deal_log['load_time'];
			$deal['repay_start_date'] = date("Y-m-d",$deal['repay_start_time']);
			//结息日期
			$deal['repay_end_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_id =".$deal_log['plan_id']." and plan_load_id=".$load_id." order by id desc limit 1");
			$cunguan_load_repay_money_one = $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) as one FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status!=4 and cunguan_tag=1");
			$cunguan_load_repay_money_two = $GLOBALS['db']->getOne("SELECT true_interest_money FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id asc limit 1");
			$cunguan_load_repay_money_thr = $GLOBALS['db']->getOne("SELECT increase_interest+interestrate_money+raise_money as two FROM ".DB_PREFIX."deal_load_repay WHERE plan_load_id =".$load_id." and has_repay=1 and status=4 and cunguan_tag=1 order by id desc limit 1");
			//已收收益
			$deal_log["load_repay_money"] = ($cunguan_load_repay_money_one+$cunguan_load_repay_money_two+$cunguan_load_repay_money_thr);
			$deal_log['anticipate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money+increase_interest+interestrate_money+raise_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_load_id=".$load_id)));
			$deal_log['deal_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_load_id=".$load_id)));
			$deal_log['raise_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(raise_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_load_id=".$load_id)));
			$deal_log['interestrate_money']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(interestrate_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_load_id=".$load_id)));
			$deal_log['increase_interest']=format_price(sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(increase_interest) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and plan_load_id=".$load_id)));
			$deal_log['create_time']= $deal_log['load_date']; //出借日期
			$deal_log['red_ecv']=format_price($deal_log['total_money']-$deal_log['money']);
			$deal_log['money']=format_price($deal_log['money']); //出借金额
			$deals['list'] = $GLOBALS['db']->getAll("SELECT DISTINCT(d.id),d.*,dl.id as load_id from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id where dl.plan_load_id=".$load_id." and dl.user_id=".$user_id." limit ".$limit);
			//$deals['list'] = $GLOBALS['db']->getAll("SELECT DISTINCT(id),*,dl.id as load_id from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id where dl.plan_load_id=".$load_id." and dl.user_id=".$user_id." limit ".$limit);
			$deals['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id = dl.deal_id where dl.plan_load_id=".$load_id." and dl.user_id=".$user_id);
			foreach ($deals['list'] as $k => $v) {
				$deals['list'][$k]['borrow_amount'] =format_price($v['borrow_amount']);
				if($v['repay_time_type'] == 0){
					$deals['list'][$k]['repay_time'] = $v['repay_time']."天"; 
				}else{
					$deals['list'][$k]['repay_time'] = $v['repay_time']."个月"; 
				}
			}
			$day_repay_list=getUcPlanReay($user_id,$load_id);
		}
		$page = new Page($deals['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('deals',$deals['list']);
		$GLOBALS['tmpl']->assign('deal_type',$deal_type);
		$GLOBALS['tmpl']->assign("day_repay_list",$day_repay_list['list']);
		$GLOBALS['tmpl']->assign('deal_log',$deal_log);
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign("page_title","出借详情");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_info.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

    /**
     * 下载合同
     */
    public function down_contract(){
        
        $pid = intval($_REQUEST['id']);
        $load_id = intval($_REQUEST['load_id']) ;
        $user_id = $GLOBALS['user_info']['id'];
        if(!is_numeric($pid) || empty($pid)) showErr("参数错误");
        $pinfo = $GLOBALS['db']->getRow("select type_id,cunguan_tag,debts,old_load_id,old_deal_id,contract_id FROM ".DB_PREFIX."deal WHERE id=".$pid);
        require_once(APP_ROOT_PATH."app/Lib/deal.php");
        $deal = get_deal($pid);
        if(!$deal) showErr("操作失败a！");
        $load_user_id = $GLOBALS['db']->getOne("select user_id FROM ".DB_PREFIX."deal_load WHERE id=".$load_id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
        if($load_user_id == 0  && $deal['user_id']!=$GLOBALS['user_info']['id'] ){
            showErr("操作失败b！");
        }
        $deal['real_name'] = $GLOBALS['user_info']['real_name'];//出借人真实姓名
        $deal['invest_mobile'] = $GLOBALS['user_info']['mobile'];//出借人手机号
        $deal['idno'] = $GLOBALS['user_info']['idno'];//出借人身份证
        $deal['invest_uid'] =  $GLOBALS['user_info']['id'];//出借人ID
        
        $invest_time = $GLOBALS['db']->getOne("select create_time FROM ".DB_PREFIX."deal_load WHERE id=".$load_id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
        $deal['invest_time'] = date("Y年m月d日",$invest_time);
        $deal['maturity_time'] = date("Y年m月d日",strtotime('+'.$deal['repay_time'].' months',$invest_time));
        $deal['invest_money'] = floatval($GLOBALS['db']->getOne("select money FROM ".DB_PREFIX."deal_load WHERE id=".$load_id));
            
        //还款计划
        $repay_list = $GLOBALS['db']->getAll("SELECT self_money,interest_money,repay_time,(self_money+interest_money) money FROM ".DB_PREFIX."deal_load_repay  WHERE user_id=".$user_id." and load_id=".$load_id." order by repay_time asc");
        foreach ($repay_list as $k=>$v){
            $repay_list[$k]['repay_time'] = date("Y年m月d日",$repay_list[$k]['repay_time']);
            $benxi += $repay_list[$k]['money'];
            $benjin += $repay_list[$k]['self_money'];
        }
        //转让期限
        $GLOBALS['tmpl']->assign('relist',$repay_list);
        $GLOBALS['tmpl']->assign('benxi',$benxi);
        $GLOBALS['tmpl']->assign('benjin',$benjin);
        $GLOBALS['tmpl']->assign('deal',$deal);
        //生成合同
        if ($pinfo['contract_id'] == 16){
            //统一信用代码
            $qiye_info = $GLOBALS['db']->getRow("select uniSocCreCode,companyName from ".DB_PREFIX."company_reginfo where user_id=".$deal['user']['id']);
            $contract_id = 16;
            $title = "餐饮贷出借协议";
            $page = "14";
            $ly = "300";
            $GLOBALS['tmpl']->assign('qiye_info',$qiye_info);
        }elseif($pinfo['cunguan_tag'] && $pinfo['debts']){
            $old_load_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where id=".$pinfo['old_load_id']);
            $old_load_time = date(" Y年 m月 d日 ",$old_load_time);
            $old_user_id = $GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."deal where id=".$pinfo['old_deal_id']);
            $debts_borrw_name = $GLOBALS['db']->getOne("select real_name from ".DB_PREFIX."user where id=".$old_user_id);
            $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$pinfo['old_deal_id']." order by repay_time desc limit 1");
            $create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where id=".$load_id);
            $debts_repay_time= ceil(($last_repay_time-$create_time)/3600/24)+1;
            $contract_id = 13;
            $title = "债权转让及受让协议";
            $page = "2";
            $ly = "230";
            $GLOBALS['tmpl']->assign("old_load_time",$old_load_time);
            $GLOBALS['tmpl']->assign("debts_borrw_name",$debts_borrw_name);
            $GLOBALS['tmpl']->assign("debts_repay_time",$debts_repay_time);
        }elseif($pinfo['cunguan_tag'] && $pinfo['type_id'] == 14){//房贷ID 上线需要修改
            $repay_moeny_month = $GLOBALS['db']->getOne("SELECT repay_money FROM ".DB_PREFIX."deal_load_repay  WHERE user_id=".$user_id." and load_id=".$load_id);
            $GLOBALS['tmpl']->assign("repay_moeny_month",$repay_moeny_month);
            $contract_id = 14;
            $title = "房贷出借协议";
//             $page = "9";
//             $ly = "85";
        }elseif($pinfo['cunguan_tag'] && !$pinfo['debts']){
            $contract_id = 11;
            $title = "出借协议";
//             $page = "9";
//             $ly = "170";
        }elseif (!$pinfo['cunguan_tag'] && !$pinfo['debts']){
            $contract_id = 10;
            $title = "债权转让服务协议";
            $page = "6";
            $ly = "300";
        }
        $contract = $GLOBALS['tmpl']->fetch("str:".get_contract($contract_id));
        require APP_ROOT_PATH.'app/Lib/contract.php';
        $pdf = new contract();
        $file_name = $deal['name'] . $title .".pdf";
        $pdfFile = $pdf->contractOutputByHtml($contract,$file_name,'S',$title);
        require APP_ROOT_PATH.'app/Lib/cfca.php';
        $cfca = new cfca();
        
        if($contract_id == 11 || $contract_id == 14 || $contract_id == 16){
            $invest_info['user_id'] = $deal['invest_uid'];
            $invest_info['idno'] = $deal['idno'];
            $invest_info['real_name'] = $deal['real_name'];
            
            $loan_info['user_id'] = $deal['user']['id'];
            $loan_info['idno'] = $deal['user']['idno'];
            $loan_info['real_name'] = $deal['user']['real_name'];
            //查询投资人和借款人是否制章
            $invest_is_cfca = $GLOBALS['db']->getRow("select seqno,create_time from ".DB_PREFIX."contract_cfca where user_id=".$invest_info['user_id']." order by id desc limit 1");
            $loan_is_cfca = $GLOBALS['db']->getRow("select seqno,create_time from ".DB_PREFIX."contract_cfca where user_id=".$loan_info['user_id']." order by id desc limit 1");
            if(empty($invest_is_cfca) || ($invest_is_cfca['create_time']+3600*24) < time() ){
                $invest_info['seqno'] = $this->seqno();
                $cf_result = $cfca->makeNamedSealAutomation($invest_info);
                $GLOBALS['db']->autoExecute(DB_PREFIX."contract_cfca",array("user_id"=>$invest_info['user_id'],"seqno"=>$invest_info['seqno'],"create_time"=>time()),"INSERT");
            }else{
                $invest_info['seqno'] = $GLOBALS['db']->getOne("select seqno from ".DB_PREFIX."contract_cfca where user_id=".$invest_info['user_id']." order by id desc limit 1");
            }
            if(empty($loan_is_cfca) || ($loan_is_cfca['create_time']+3600*24) < time() ){
                $loan_info['seqno'] = $this->seqno();
                //查询借款人是否企业用户
                if($deal['user']['user_type'] == 1){
                    $qiye = $GLOBALS['db']->getRow("select uniSocCreCode,companyName from ".DB_PREFIX."company_reginfo where user_id=".$deal['user']['id']);
                    $loan_info['idno'] = $qiye['uniSocCreCode'];
                    $loan_info['real_name'] = $qiye['companyName'];
                    $cf_result1 = $cfca->makeSealAutomation($loan_info);
                }else{
                    $cf_result1 = $cfca->makeNamedSealAutomation($loan_info);
                }
                $GLOBALS['db']->autoExecute(DB_PREFIX."contract_cfca",array("user_id"=>$loan_info['user_id'],"seqno"=>$loan_info['seqno'],"create_time"=>time()),"INSERT");
            }else{
                $loan_info['seqno'] = $GLOBALS['db']->getOne("select seqno from ".DB_PREFIX."contract_cfca where user_id=".$loan_info['user_id']." order by id desc limit 1");
            }
            
            $strpdf = $cfca->batchSealAutoUncheckedPdf($pdfFile,$contract_id,$loan_info,$invest_info,$title);
        }else{
        
            $data['type'] = "2"; // 签章类型（不能为空），1=空白标签签章,2=坐标签章,3=关键字签章
            $data['pdfURL'] = "";
            $data['sealCode'] = "1001"; //印章编码
            $data['sealPassword'] = "123456"; //印章密码
            $data['page'] = $page; //页数
            $data['sealPerson'] = "北京玖承资产管理有限公司"; //签章人
            $data['sealLocation'] = "北京"; //签章地点
            $data['sealResson'] = $title."签订"; //签章理由
            $data['lX'] = "100"; //左侧的x坐标
            $data['lY'] = $ly; //左侧的y坐标
            $data['keyword'] = ""; //关键字，按关键字签章时不能为空
            $data['locationStyle'] = "C"; // 上:U；下:D；左:L；右:R；中:C；默认：C
            $data['offsetX'] = "0"; //横轴偏移，默认为0
            $data['offsetY'] = "0"; //纵轴偏移，默认为0
            $data['certificationLevel'] = "0"; // 0:Approval signature(NOT_CERTIFIED)2:Author signature, form filling allowed
            
            
            $strpdf = $cfca->sealAutoPdf($pdfFile,$data);
        }
        $length = strlen($strpdf);
        
        //         //浏览器直接打开 下载由客户决定
        header('Content-Type: application/pdf');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Disposition: inline; filename="'.basename($file_name).'"');
        //浏览器直接下载
        //         header('Content-Description: File Transfer');
        //         header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        //         header('Pragma: public');
        //         header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        //         header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        //         header('Content-Type: application/pdf');
        //         header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
        //         header('Content-Transfer-Encoding: binary');
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            header('Content-Length: '.$length);
        }
        echo $strpdf;
        
    }
    //生成CFCA印章编号
    function seqno(){
        $yCode ='YZ';
        $orderSn = $yCode.date('YmdHis',time()).time().rand(0,9999);
        return $orderSn;
    }
    public function deal_list(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		/* $deals=$GLOBALS['db']->getAll("select d.id,d.name,dl.total_money,dl.id as pid from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id=dl.deal_id where dl.plan_load_id=".$id." and dl.user_id=".$user_id." order by d.id asc limit ".$limit); */
		$deals=$GLOBALS['db']->getAll("select d.id,d.name,d.borrow_amount as total_money,dl.id as pid from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id=dl.deal_id where dl.plan_load_id=".$id." and dl.user_id=".$user_id." order by d.id asc limit ".$limit); 
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id=dl.deal_id where dl.plan_load_id=".$id." and dl.user_id=".$user_id);
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$GLOBALS['tmpl']->assign("deals",$deals);
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("page_title","匹配债权");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_deal_list.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function ajaxInvestDeal(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		if($page==0)
		$page = 1;
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id=dl.deal_id where dl.plan_load_id=".$id." and dl.user_id=".$user_id);
	
		echo $count;
	}
	public function investListDeal(){
		$page = intval($_REQUEST['page']);
		$id = intval($_REQUEST['id']);
		if($page==0)
		$page = 1;
		$user_id = $GLOBALS['user_info']['id'];
			//获取投资记录 wap
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$deals=$GLOBALS['db']->getAll("select d.id,d.name,d.borrow_amount as total_money,dl.id as pid from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id=dl.deal_id where dl.plan_load_id=".$id." and dl.user_id=".$user_id." order by d.id asc limit ".$limit); 
		if (empty($deals)) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('deals',$deals);
            $info = $GLOBALS['tmpl']->fetch("inc/uc/investListDeal.html");
            echo $info;
        }
	}
	public function deal_info(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$load_id = intval($_REQUEST['load_id']);
		$GLOBALS['tmpl']->assign("load_id",$load_id);
		$deal_info=$GLOBALS['db']->getRow("select name,borrow_amount,rate,repay_time,repay_time_type,loantype,id as pid from ".DB_PREFIX."deal where id=".$id);
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		$GLOBALS['tmpl']->assign("page_title","债权详情");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_deal_info.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
    public function licai_down_contract(){
		$pid = intval($_REQUEST['id']);
		$load_id = intval($_REQUEST['load_id']) ;
		$user_id = $GLOBALS['user_info']['id'];
		if(!is_numeric($pid) || empty($pid)) showErr("参数错误");
		$load_data = $GLOBALS['db']->getRow("select create_time,total_money FROM ".DB_PREFIX."deal_load WHERE id=".$load_id." and user_id=".$user_id." ORDER BY create_time ASC");
		if(empty($load_data)){
			showErr("操作失败！");
		}
		$deal = $GLOBALS['db']->getRow("SELECT u.real_name,u.idno,d.borrow_amount,d.repay_time,d.loantype,d.rate,d.repay_time_type,d.user_id,d.name FROM ".DB_PREFIX."deal d LEFT JOIN ".DB_PREFIX."user u ON u.id=d.user_id  WHERE d.id=".$pid);
		$deal['r_real_name'] = $GLOBALS['user_info']['real_name'];//出借人真实姓名
		$deal['r_idno'] = $GLOBALS['user_info']['idno'];//出借人身份证
		$deal['invest_time'] = date("Y年m月d日",$load_data['create_time']);
		$deal['self_money'] = $load_data['total_money'];
		$deal['repay_time_con'] = $deal['repay_time_type']==1?$deal['repay_time']."月":$deal['repay_time']."天";
		$deal['loantype_format'] = $deal['loantype']==2?"到期还本息":"按月付息到期还本";
		$deal['rate_foramt_w'] = $deal['rate']."%";


		//还款计划
		$repay_list = $GLOBALS['db']->getAll("SELECT self_money,interest_money,repay_time FROM ".DB_PREFIX."deal_load_repay  WHERE user_id=".$user_id." and load_id=".$load_id." order by repay_time asc");
		foreach ($repay_list as $k=>$v){
			$repay_list[$k]['repay_time'] = date("Y年m月d日",$repay_list[$k]['repay_time']);
		}
		$GLOBALS['tmpl']->assign('relist',$repay_list);
		$GLOBALS['tmpl']->assign('deal',$deal);
		$contract_id = 15;
		$title = "消费分期出借协议";
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($contract_id));
		require APP_ROOT_PATH.'app/Lib/contract.php';
		$pdf = new contract();
		$file_name = $deal['name'] . $title .".pdf";
		$pdfFile = $pdf->contractOutputByHtml($contract,$file_name,'S',$title);
		require APP_ROOT_PATH.'app/Lib/cfca.php';
		$cfca = new cfca();
		$invest_info['user_id'] = $user_id;
		$invest_info['idno'] = $deal['r_idno'];
		$invest_info['real_name'] = $deal['r_real_name'];

		$loan_info['user_id'] = $deal['user_id'];
		$loan_info['idno'] = $deal['idno'];
		$loan_info['real_name'] = $deal['real_name'];
		//查询投资人和借款人是否制章
		$invest_is_cfca = $GLOBALS['db']->getRow("select seqno,create_time from ".DB_PREFIX."contract_cfca where user_id=".$invest_info['user_id']." order by id desc limit 1");
		$loan_is_cfca = $GLOBALS['db']->getRow("select seqno,create_time from ".DB_PREFIX."contract_cfca where user_id=".$loan_info['user_id']." order by id desc limit 1");
		if(empty($invest_is_cfca) || ($invest_is_cfca['create_time']+3600*24) < time() ){
			$invest_info['seqno'] = $this->seqno();
			$cf_result = $cfca->makeNamedSealAutomation($invest_info);
			$GLOBALS['db']->autoExecute(DB_PREFIX."contract_cfca",array("user_id"=>$invest_info['user_id'],"seqno"=>$invest_info['seqno'],"create_time"=>time()),"INSERT");
		}else{
			$invest_info['seqno'] = $GLOBALS['db']->getOne("select seqno from ".DB_PREFIX."contract_cfca where user_id=".$invest_info['user_id']." order by id desc limit 1");
		}
		if(empty($loan_is_cfca) || ($loan_is_cfca['create_time']+3600*24) < time() ){
			$loan_info['seqno'] = $this->seqno();
			$cf_result1 = $cfca->makeNamedSealAutomation($loan_info);
			$GLOBALS['db']->autoExecute(DB_PREFIX."contract_cfca",array("user_id"=>$loan_info['user_id'],"seqno"=>$loan_info['seqno'],"create_time"=>time()),"INSERT");
		}else{
			$loan_info['seqno'] = $GLOBALS['db']->getOne("select seqno from ".DB_PREFIX."contract_cfca where user_id=".$loan_info['user_id']." order by id desc limit 1");
		}
		$strpdf = $cfca->batchSealAutoUncheckedPdf($pdfFile,$contract_id,$loan_info,$invest_info,$title);
		$length = strlen($strpdf);
		//         //浏览器直接打开 下载由客户决定
		header('Content-Type: application/pdf');
		header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Disposition: inline; filename="'.basename($file_name).'"');
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			header('Content-Length: '.$length);
		}
		echo $strpdf;
	}
}
?>
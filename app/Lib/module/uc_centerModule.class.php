<?php

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_centerModule extends SiteBaseModule
{
	private $space_user;
	public function init_main()
	{
//		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
//		require_once APP_ROOT_PATH."system/extend/ip.php";		
//		$iplocation = new iplocate();
//		$address=$iplocation->getaddress($user_info['login_ip']);
//		$user_info['from'] = $address['area1'].$address['area2'];
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
	}
	
	public function init_user(){
		$this->user_data = $GLOBALS['user_info'];
		// 冻结资金
		$lock_money= sprintf('%.2f', $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id= ".$this->user_data['id'])." and status in (0,3)");
		// $this->user_data['lock_money'] = floatval($this->user_data['mortgage_money'])+floatval($this->user_data['lock_money']);
		$this->user_data['lock_money'] = floatval($this->user_data['mortgage_money'])+floatval($lock_money);

		$province_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$this->user_data['province_id']);
		$city_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$this->user_data['city_id']);
		if($province_str.$city_str=='')
			$user_location = $GLOBALS['lang']['LOCATION_NULL'];
		else 
			$user_location = $province_str." ".$city_str;

		$this->user_data['fav_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$this->user_data['id']." and fav_id <> 0");
		$this->user_data['user_location'] = $user_location;
		$this->user_data['group_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_group where id = ".$this->user_data['group_id']." ");
		
		$this->user_data['user_statics'] =sys_user_status($GLOBALS['user_info']['id'],false);

		$GLOBALS['tmpl']->assign('user_statics',$this->user_data['user_statics']);
	}
	
	public function index()
	{
		jumpUrl("jump_url_depository");
		jumpUrl("jump_url_incharge");
		// $this->init_user();
		$user_info = $GLOBALS['user_info'];
		$user_id = $user_info['id'];
		if($user_info['id']>0){
		    //$user_bank = $GLOBALS['db']->getOne("select bankcard from ".DB_PREFIX."user_bank where user_id=".$user_info['id']." and cunguan_tag = 1");
		    if(!$user_info['accno'] ){
                $cunguan_tip = 1;
                $GLOBALS['tmpl']->assign("cunguan_tip",$cunguan_tip);
            }
        }
		$total_assets = intval($_REQUEST['total_assets']);
        $GLOBALS['tmpl']->assign("total_assets",$total_assets);
        $page_args['total_assets'] = $total_assets;

        //var_dump($GLOBALS['user_info']);exit;

		// if(strtolower($className) == "yeepay")
		// {
		// 	$is_yeepay = 1;
		// 	$user_info["ips_status"] = $GLOBALS["db"]->getOne("select activeStatus from ".DB_PREFIX."yeepay_enterprise_register where platformUserNo = ".$user_info["id"]);
		// 	$GLOBALS['tmpl']->assign("is_yeepay",$is_yeepay);
		// }

		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{
			$this->init_main();			
		}
		$user_id = intval($GLOBALS['user_info']['id']);
		// $GLOBALS['tmpl']->assign("header",$GLOBALS['db']->getOne("select header_url from ".DB_PREFIX."user where id=".$user_id));
		// 数据统计（总资产;在投金额;可用红包、代金券）
		$money_total=get_user_money_info($user_id);
		//var_dump($money_total['cunguan_taste']);exit;
		//存管可用余额
       	$Pcrecharge_moneys= $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        $Pcrecharge_money=strval(sprintf("%.2f",$Pcrecharge_moneys));
        //普通可用余额
        $Pcordinary=strval(sprintf("%.2f",$user_info['money']));
        //总的可用余额
        $Pctotal = $Pcrecharge_money+$Pcordinary;

		//存管资产
		$custody_assets=$money_total['cunguan_total_money'];//sprintf('%.2f',floatval(($GLOBALS['user_info']['cunguan_lock_money'])+$Pcrecharge_money));
		//普通资产
		$ordinary=sprintf('%.2f',floatval(($GLOBALS['user_info']['lock_money'])+$Pcordinary));
		//总资产
		$total_general=$custody_assets+$ordinary;
		
		//存管再投金额
		$reinvestment=$money_total['cunguan_invest_money'];
		//普通再投金额
		$user_ordinary=$money_total["invest_money"];
		//总的再投
		$user_amount = $reinvestment+$user_ordinary;
		//总待收
		$total_wait_earnings = $money_total['load_wait_earnings']+$money_total['cunguan_load_wait_earnings'];
		$GLOBALS['tmpl']->assign("total_wait_earnings",$total_wait_earnings);
		$total_repay_money = $money_total['load_repay_money']+$money_total['cunguan_load_repay_money'];
		
		$expload_money = $GLOBALS['db']->getOne("SELECT SUM(money) as money FROM ".DB_PREFIX."taste_cash where user_id=$user_id and use_status=0 and cunguan_tag=1 and end_time>".time()." ");		
		$GLOBALS['tmpl']->assign("expload_money",$expload_money);
		$GLOBALS['tmpl']->assign("total_repay_money",$total_repay_money);
		$GLOBALS['tmpl']->assign("money_total",$money_total);
		$GLOBALS['tmpl']->assign("Pcrecharge_money",$Pcrecharge_money);
        $GLOBALS['tmpl']->assign("Pcordinary",$Pcordinary);
        $GLOBALS['tmpl']->assign("custody_assets",$custody_assets);
        $GLOBALS['tmpl']->assign("ordinary",$ordinary);
        $GLOBALS['tmpl']->assign("total_general",$total_general);
        $GLOBALS['tmpl']->assign("reinvestment",$reinvestment);
        $GLOBALS['tmpl']->assign("user_ordinary",$user_ordinary);
        $GLOBALS['tmpl']->assign("user_amount",$user_amount);
        $GLOBALS['tmpl']->assign("Pctotal",$Pctotal);

		if($total_assets ==1)
        	$condition .= " and dlr.cunguan_tag =0";
       	else 
       		$condition .= "and dlr.cunguan_tag =1";

		//充值的分类
        $uc_centerdeal = array(
            array(
                "name" => "存管账户",
            ),
            array(
                "name" => "普通账户",
            ),
        ); 
        foreach($uc_centerdeal as $k=>$v){
            $tmp_args = $page_args;
            $tmp_args['total_assets']=$k;
            $uc_centerdeal[$k]['url'] = url("index","uc_center#index",$tmp_args);  //是从20出来的   $url =$url.$k."=".urlencode($v)."&";
        }   
        $GLOBALS['tmpl']->assign("uc_centerdeal",$uc_centerdeal);
        $GLOBALS['tmpl']->assign("total_assets",$total_assets);

		//--------------------------------------------------注释掉---------------------------------------------------
		// /***出借统计***/
		// $user_statics = $user_info['user_statics'];
		// //出借收益
		// //$user_statics["load_earnings"] = number_format($user_statics['load_earnings'] + $user_statics['reward_money'] + $user_statics['load_tq_impose'] + $user_statics['load_yq_impose'] + $user_statics['rebate_money'] + $user_statics['referrals_money'] - $user_statics['carry_fee_money']- $user_statics['incharge_fee_money'], 2);

  //       //待还本息
  //       $user_statics["need_repay_money"] = floatval($user_statics["need_repay_amount"]);
  //       // $user_statics["need_repay_money"]= floatval($user_statics["need_repay_money"]);
        
		// //待还本息加管理费
		// $user_statics["need_repay_amount"] = floatval($user_statics["need_repay_amount"])+floatval($user_statics["need_manage_amount"]);
		
		// //待收本金
		// $user_statics["load_wait_self_money"] = floatval($user_statics["load_wait_self_money"]);
		
		// $user_statics["clear_total_money"] = round($user_statics["load_wait_self_money"],2) + round($user_info["money"],2) + round($user_info["lock_money"],2) - round($user_statics["need_repay_amount"],2);
		
		// $user_statics["load_wait_self_money"] = $user_statics["load_wait_self_money"];
		
		// //待收收益
		// $user_statics["load_wait_earnings"] = floatval($user_statics["load_wait_earnings"]);
		
		// $user_statics["ltotal_money"] = floatval($user_statics["load_wait_repay_money"]) + floatval($user_statics["load_repay_money"]);
		
		// $user_info["total_money"] = floatval($user_info["money"]) + floatval($user_info["lock_money"]);
		
		// $user_info["lock_money"] = floatval($user_info["lock_money"]);
		// $user_statics["money"] = floatval($user_info["money"]);
		// $user_statics["need_repay_amount"]= floatval($user_statics["need_repay_amount"]);
		
		// //投标中的
		// $invest_sql = "SELECT count(*) as l_count,sum(money) as l_money FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$user_id." and d.deal_status in(1,2) group by dl.user_id";

		// $invest = $GLOBALS['db']->getRow($invest_sql);
		// $user_statics["invest_count"] = $invest["l_count"];
		// $user_statics["invest_money"] = $invest["l_money"];
		// $user_statics["total_money"] = round($invest_sql["money"],2)+ round($user_statics["load_wait_repay_money"],2)+round($user_statics["load_repay_money"],2);
		
		// //待回收本息
		// $user_statics["load_wait_repay_money"] = floatval($user_statics["load_wait_repay_money"]);
		// //已回收本息
		// $user_statics["load_repay_money"] = floatval($user_statics["load_repay_money"]);
		//--------------------------------------------------注释掉---------------------------------------------------
		//本月
		/*
		$this_wait_deals = $this->get_loadlist($user_id,"  AND DATE_FORMAT(repay_date,'%Y-%m')  = '".to_date(TIME_UTC,"Y-m")."' ");
		$user_statics["this_month_money"] = 0.00;
		$user_statics["this_month_count"] = 0;
		
		foreach($this_wait_deals as $k=>$v)
		{
			$user_statics["this_month_money"] += $v["repay_money"];
			$user_statics["this_month_count"] ++;
		}
		*/
		//下月
		/*
		$next_wait_deals = $this->get_loadlist($user_id," AND DATE_FORMAT(repay_date,'%Y-%m')  = '".to_date(next_replay_month(TIME_UTC),"Y-m")."'");
		$user_statics["next_month_money"] = 0.00;
		$user_statics["next_month_count"] = 0;
		
		foreach($next_wait_deals as $k=>$v)
		{
			$user_statics["next_month_money"] += $v["repay_money"];
			$user_statics["next_month_count"] ++;
		}
		*/
		//本年
		/*
		$year_wait_deals = $this->get_loadlist($user_id," AND DATE_FORMAT(repay_date,'%Y')  =  '".to_date(TIME_UTC,"Y")."' ");
		
		$user_statics["year_money"] = 0.00;
		$user_statics["year_count"] = 0;
		
		foreach($year_wait_deals as $k=>$v)
		{
			$user_statics["year_money"] += $v["repay_money"];
			$user_statics["year_count"] ++;
		}

		$user_statics["year_money"] = number_format(round($user_statics["year_money"],2),2);
		$user_statics["this_month_money"] = number_format(round($user_statics["this_month_money"],2),2);
		$user_statics["next_month_money"] = number_format(round($user_statics["next_month_money"],2),2);
		
		//总计
		$all_wait_deals = $this->get_loadlist($user_id,'');
		$user_statics["total_invest_money"] = 0.00;
		$user_statics["total_invest_count"] = 0;
		
		foreach($all_wait_deals as $k=>$v)
		{
			$user_statics["total_invest_money"] += $v["repay_money"];
			$user_statics["total_invest_count"] ++;
		}
		$user_statics["total_invest_money"] = number_format($user_statics["total_invest_money"],2);
		//$user_statics["total_invest_count"] = $user_statics["this_month_count"]+$user_statics["next_month_count"]+$user_statics["year_count"];
		
		
		$load_list_sql = "SELECT * FROM ".DB_PREFIX."deal_load WHERE user_id = ".$GLOBALS['user_info']['id']." ORDER BY id DESC limit 0,4";
		//最近交易
		$load_list = $GLOBALS['db']->getAll($load_list_sql,false);
		$GLOBALS['tmpl']->assign("load_list",$load_list);
		$user_money = get_user_money_info($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_money",$user_money);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		//最近六个月出借记录
		$month = array();
		
		//select month(FROM_UNIXTIME(time)) from table_name group by month(FROM_UNIXTIME(time))
		$result['lend'] = $GLOBALS['db']->getAll("SELECT count(*) as l_count,sum(money) as l_money,DATE_FORMAT(FROM_UNIXTIME(dl.create_time),'%Y年%m月') as l_month FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.is_repay = 0 AND dl.user_id=".$user_id." and d.deal_status in(1,2,4,5) group by DATE_FORMAT(FROM_UNIXTIME(dl.create_time),'%Y年%m月')",false);
		$months[0]["time"] = to_date(next_replay_month(TIME_UTC,-5),'Y年m月');
		$months[1]["time"] = to_date(next_replay_month(TIME_UTC,-4),'Y年m月');
		$months[2]["time"] = to_date(next_replay_month(TIME_UTC,-3),'Y年m月');
		$months[3]["time"] = to_date(next_replay_month(TIME_UTC,-2),'Y年m月');
		$months[4]["time"] = to_date(next_replay_month(TIME_UTC,-1),'Y年m月');
		$months[5]["time"] = to_date(TIME_UTC,'Y年m月');
		
		$max_money = 100;
		foreach($result['lend']  as $k=>$v)
		{
			if(round($max_money)<round($v["l_money"]))
			{
				$max_money = $v["l_money"];
			}
			foreach($months as $kk => $vv)
			{
				if($vv["time"] == $v["l_month"])
				{
					$months[$kk]["l_money"] = $v["l_money"];
					$months[$kk]["show_money"] = number_format(floatval($v["l_money"]), 2); 
				}
			}
		}
		foreach($months as $k => $v)
		{
			$months[$k]["height"] = $v["l_money"]/$max_money*325;
			$months[$k]["bottom"] = $v["l_money"]/$max_money*325+35;
		}
		$GLOBALS['tmpl']->assign("max_money",$max_money);
		$GLOBALS['tmpl']->assign("months",$months);
		
		/***右侧统计结束***/
		
		

		$GLOBALS['tmpl']->assign("user_data",$user_info);
		if($ajax==0)
		{
			
			$page = intval($_REQUEST['p']);
			if($page==0)
				$page = 1;
		
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			//近期待还款
			// $day_deal_repay = getUcDealRepay($user_id,10,"");
			//近期待收款
			$day_repay_list = getUcRepayPlan($user_id,3,$limit,$condition);

			//推荐的标
			/*
			require APP_ROOT_PATH."app/Lib/deal_func.php";
			$where = " is_recommend = 1 and deal_status in (0,1,2)";
			$deals_list = get_deal_list(10,0,$where);
			
			foreach($deals_list['list'] as $k=>$v){
				$deals_list['list'][$k]['repay_time_format'] = $v['repay_time'].($v['repay_time_type'] == 1 ? "个月" : "天");
				$deals_list['list'][$k]['start_time_format'] = to_date($v['start_time'],"Y-m-d");
				
				if($v['is_delete'] == 2)
					$deals_list['list'][$k]['deal_status_format'] = "待发布";
				elseif($v['is_wait'] == 1)
					$deals_list['list'][$k]['deal_status_format'] = "未开始";
				elseif ($v['deal_status'] == 5)
					$deals_list['list'][$k]['deal_status_format'] = "还款完毕";
				elseif($v['deal_status'] == 4)
					$deals_list['list'][$k]['deal_status_format'] = "还款中";
				elseif($v['deal_status'] == 0)
					$deals_list['list'][$k]['deal_status_format'] = $v['need_credit']==0 ? "等待审核" : "等待材料";
				elseif($v['deal_status'] == 1 && $v['remain_time'] > 0)
					$deals_list['list'][$k]['deal_status_format'] ="筹款中";
				elseif($v['deal_status'] == 2)
					$deals_list['list'][$k]['deal_status_format'] ="满标";
				elseif($v['deal_status'] ==3 || $v['remain_time'] <= 0)
					$deals_list['list'][$k]['deal_status_format'] ="流标";
					
			}
			*/
			$page = new Page($day_repay_list['rs_count'],app_conf("PAGE_SIZE"));   //初始化分页对象
	    	$p  =  $page->show();
	    	$GLOBALS['tmpl']->assign('pages',$p);

			/*充值时对认证状态进行校验*/
			$user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where cunguan_tag = 1 and user_id= ".$user_info['id']);
	
			$user_info = $GLOBALS['user_info'];
			if($user_info['cunguan_tag'] == 0){
				$ajaxdata['url'] = url("index","uc_depository_account"); //判断存管是否开户
				$ajaxdata['code'] = 0;
			}else if($user_info['cunguan_tag'] == 1 && $user_info['cunguan_pwd'] == 0){
				$ajaxdata['url'] = url("index","uc_depository_paypassword#setpaypassword"); //判断存管是否设置交易密码
				$ajaxdata['code'] = 1;
			}else if($user_info['cunguan_tag'] == 1 && $user_info['cunguan_pwd'] == 1 && $user_status <1){
				$ajaxdata['url'] = url("index","uc_depository_addbank#wap_check_pwd"); //判断存管是否设置交易密码
				$ajaxdata['code'] = 1;
			}else{
				$ajaxdata['url'] = url("index","uc_money#incharge");
				$ajaxdata['code'] = 4;
			}
			/*$usinfos = $GLOBALS['db']->getRow("select AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,u.paypassword,b.bankcard,b.status from ".DB_PREFIX."user u left join ".DB_PREFIX."user_bank b on u.id = b.user_id where  u.id= ".$user_info['id']);
			$user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where  user_id= ".$user_info['id']);
			
			if(!$usinfos['bankcard'] && !$usinfos['idno']){
				$ajaxdata['url'] = url("index","uc_center#identity");
				$ajaxdata['code'] = 0;
			}else if(!$usinfos['bankcard'] && $usinfos['idno']){
				$ajaxdata['url'] = url("index","uc_account#bind_bank");
				$ajaxdata['code'] = 0;
			}else if($usinfos['bankcard'] && $user_status == 0){
				$ajaxdata['url'] = url("index","uc_account#bind_bank");
				$ajaxdata['code'] = 0;
			}else if(!$usinfos['paypassword']){
				$ajaxdata['url'] = url("index","uc_account#wappaypassword");
				$ajaxdata['code'] = 0;
			}else{
				$ajaxdata['url'] = url("index","uc_money#incharge");
				$ajaxdata['code'] = 1;
			}*/
			$switch_conf = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=4");
			foreach ($switch_conf as $k => $v) {
				if($v['status'] != 1){
					$ajaxdata['code'] = 2;
				}
			}
			$switch_conf1 = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=5");
			foreach ($switch_conf1 as $k => $v) {
				if($v['status'] != 1){
					$ajaxdata['codes'] = 3;
				}
			}
			$GLOBALS['tmpl']->assign('ajaxdata',$ajaxdata);
			$GLOBALS['tmpl']->assign('day_deal_repay',$day_deal_repay['list']);
			$GLOBALS['tmpl']->assign('day_repay_list',$day_repay_list['list']);
			$GLOBALS['tmpl']->assign('deals_list',$deals_list['list']);
			$GLOBALS['tmpl']->assign("cate_title","我的账户");
			$GLOBALS['tmpl']->assign("page_title","账户总览");
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_INDEX']);
			if(!$GLOBALS['user_info']['id']){
				$ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips where code = 2" );
				$GLOBALS['tmpl']->assign("ips",$ips);
				$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			}else{
				$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_uinfo.html");
			}			
			
			$GLOBALS['tmpl']->display("page/uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo $GLOBALS['tmpl']->fetch("inc/topic_col_list.html");
		}		
	}
	
	public function focustopic()
	{	
		$this->init_user();
		$user_info = $this->user_data;
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();	
		}
		$user_id = intval($GLOBALS['user_info']['id']);
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
					
		//开始输出相关的用户日志
		$uids = $GLOBALS['db']->getOne("select group_concat(focused_user_id) from ".DB_PREFIX."user_focus where focus_user_id = ".$user_info['id']." ");

		if($uids)
		{
			$uids = trim($uids,",");	
			$result = get_topic_list($limit," user_id in (".$uids.") ");
		}
		
		$GLOBALS['tmpl']->assign("topic_list",$result['list']);
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('user_data',$user_info);
		if($ajax==0)
		{	
			$list_html = $GLOBALS['tmpl']->fetch("inc/topic_col_list.html");
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_MYFAV']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_MYFAV']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo $GLOBALS['tmpl']->fetch("inc/topic_col_list.html");
		}
	}
	
	
	public function lend()
	{
		$this->init_user();
		$user_info = $this->user_data;
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();
		}
		$user_id = intval($user_info['id']);	
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$result['total'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load WHERE user_id=".$user_id);
		if($result['total'] >0)
			$result['list'] = $GLOBALS['db']->getAll("SELECT dl.*,d.rate,d.repay_time,d.repay_time_type,d.deal_status,d.name FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON dl.deal_id = d.id WHERE dl.user_id=".$user_id." LIMIT ".$limit);
		
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("lend_list",$result['list']);
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		
		if($ajax==0)
		{	
			$list_html = $GLOBALS['tmpl']->fetch("inc/uc/uc_center_lend.html");
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_LEND']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_LEND']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo $GLOBALS['tmpl']->fetch("inc/uc_center_lend.html");
		}
	}
	
	
	public function deal()
	{	
		$this->init_user();
		$user_info = $this->user_data;	
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();	
		}
		$user_id = intval($user_info['id']);
		
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		require_once (APP_ROOT_PATH."app/Lib/deal.php");
		
		$result = get_deal_list($limit,0,"user_id=".$user_id,"id DESC");

		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('user_data',$user_info);
		if($ajax==0)
		{	
			$list_html = $GLOBALS['tmpl']->fetch("inc/uc/uc_center_deals.html");
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_MYDEAL']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_MYDEAL']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo $GLOBALS['tmpl']->fetch("inc/uc/uc_center_deals.html");
		}
	}
	
	
	
	public function mayfocus()
	{
		$user_info = get_user_info("*","id = ".intval($GLOBALS['user_info']['id']));		
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['YOU_MAY_FOCUS']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_mayfocus.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function fans()
	{
		$user_info = $this->user_data;
				
		$page_size = 24;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = intval($GLOBALS['user_info']['id']);
		
		//输出粉丝
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id);
		$fans_list = array();
		if($total > 0){
			$fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id." order by id desc limit ".$limit);
					
			foreach($fans_list as $k=>$v)
			{			
				$focus_uid = intval($v['id']);
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
				if($focus_data)
				$fans_list[$k]['focused'] = 1;
			}
		}
		$GLOBALS['tmpl']->assign("fans_list",$fans_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['MY_FANS']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_fans.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	
	public function focus()
	{
		$this->init_user();
		$user_info = $this->user_data;
				
		$page_size = 24;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = intval($GLOBALS['user_info']['id']);
		
		//输出粉丝
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id);
		$focus_list = array();
		if($total > 0){
			$focus_list = $GLOBALS['db']->getAll("select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." order by id desc limit ".$limit);
			
			foreach($focus_list as $k=>$v)
			{			
				$focus_uid = intval($v['id']);
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
				if($focus_data)
				$focus_list[$k]['focused'] = 1;
			}
		}
		$GLOBALS['tmpl']->assign("focus_list",$focus_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		$list_html = $GLOBALS['tmpl']->fetch("inc/uc/uc_center_focus.html");
		$GLOBALS['tmpl']->assign("list_html",$list_html);
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['MY_FOCUS']);	
		
			
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	
	public function setweibo()
	{
		$user_info = get_user_info("*","id = ".intval($GLOBALS['user_info']['id']));
				
		$apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login ");
		
		foreach($apis as $k=>$v)
		{
			if(strtolower($v['class_name'])=="qqv2"){
				$v['class_name'] =  "qq";
			}
			if($user_info[strtolower($v['class_name'])."_id"])
			{
				$apis[$k]['is_bind'] = 1;
				if($user_info["is_syn_".strtolower($v['class_name'])]==1)
				{
					$apis[$k]['is_syn'] = 1;
				}
				else
				{
					$apis[$k]['is_syn'] = 0;
				}
			}
			else
			{
				$apis[$k]['is_bind'] = 0;
			}
			
//			if(file_exists(APP_ROOT_PATH."system/api_login/".$v['class_name']."_api.php"))
//			{
//				require_once APP_ROOT_PATH."system/api_login/".$v['class_name']."_api.php";
//				$api_class = $v['class_name']."_api";
//				$api_obj = new $api_class($v);
//				$url = $api_obj->get_bind_api_url();
//				$apis[$k]['url'] = $url;
//			}
		}		
		$GLOBALS['tmpl']->assign("apis",$apis);
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SETWEIBO']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_setweibo.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function identity(){
		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
		/*if($GLOBALS['user_info']['idno'] != ""){
	        app_redirect(url("index"));
	    }*/
	    $user_info = $GLOBALS['db']->getRow("select real_name,idno,paypassword from ".DB_PREFIX."user where id = ".$GLOBALS['user_info']['id']);
	    $bank = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."bank where is_rec = 1");
	    $GLOBALS['tmpl']->assign("bank",$bank);
	    $GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->assign("cate_title","实名认证");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_identity.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	private function get_loadlist($user_id,$where) {
		$condtion = "   AND dlr.has_repay = 0  ".$where." ";
    	$sql = "select dlr.*,u.user_name,u.level_id,u.province_id,u.city_id from ".DB_PREFIX."deal_load_repay dlr LEFT JOIN ".DB_PREFIX."user u ON u.id=dlr.user_id  where ((dlr.user_id = ".$user_id." and dlr.t_user_id = 0) or dlr.t_user_id = ".$user_id.") $condtion order by dlr.repay_time desc ";
		$list = $GLOBALS['db']->getAll($sql);
		
		return $list;
    }
    public function bind_management(){
    	$GLOBALS['tmpl']->display("page/bind_management.html");	
    }
    public function user_set_identity(){
    	require APP_ROOT_PATH."system/utils/Verify.php";
 		require APP_ROOT_PATH."system/utils/BinkCard/Imagebase64.php";
    	$user_data = $_POST;
		if(!$user_data){
			 app_redirect("404.html");
			 exit();
		}
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$info = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."user where idno_encrypt = AES_ENCRYPT('".$user_data["idno"]."','".AES_DECRYPT_KEY."')");
		$credit_data["real_name_encrypt"] = " AES_ENCRYPT('".$user_data["real_name"]."','".AES_DECRYPT_KEY."') ";
		$credit_data["idno_encrypt"] = " AES_ENCRYPT('".$user_data["idno"]."','".AES_DECRYPT_KEY."') ";
		if(!empty($info)){
		 	$json['status'] = 0;
		 	$json['info'] = "认证信息已存在";
		 	ajax_return($json);
		}
		$url = "http://verifyapi.huiyuenet.com/verify/verifyApi.do";	//请求的页面地址
		$name = $user_data["real_name"];  								//真实姓名
		$idnum = $user_data["idno"]; 									//身份证号
		$cpid="jxdrn";   												//企业账号
		$cpserialnum="123456789101231232";    							//订单id  不超过20位
		$md5key="n7mzgu";                  								//MD5密码
		$despwd = "t0tnhfbovuhv0k10lmznjqv0";							//3DES密码
		//实名认证请求
		$realnamearr = array();
		$verifyFun = new VerifyFun($url);
		$result = $verifyFun->RealnameVerify($name,$idnum,$cpid,$cpserialnum,$md5key,$despwd);
		$realnamearr = json_decode($result,true);
		$result = $realnamearr['result'];
		if($result == 'FAIL'){
			$this->errorReturn('验证失败');
		}elseif($result == 'NOLIB'){
			$this->errorReturn('库无（身份证号国家库查询不到）');
		}elseif($result == 'RNINCONSISTENT'){
			$this->errorReturn('不一致');
		}elseif($result == 'RNCONSISTENT'){
			$res = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$credit_data,"UPDATE","id = ".$GLOBALS['user_info']['id']);
			if($res != false){
				$json['status'] = 1;
				$json['info'] = "实名认证成功";
				ajax_return($json);
			}else{
				$json['status'] = 0;
				$json['info'] = "实名认证失败";
				ajax_return($json);
			}
		}
	}
	public  function next_step(){
		$user_data = $_POST;
		if(!$user_data){
			 app_redirect("404.html");
			 exit();
		}
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$info = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."user where idno_encrypt = AES_ENCRYPT('".$user_data["idno"]."','".AES_DECRYPT_KEY."')");
		/*$credit_data["user_name"] = $user_data["user_name"];*/
		$credit_data["real_name"] = $user_data["real_name"];
		$credit_data["idno"] = $user_data["idno"];
		$credit_data["mobile"] = $GLOBALS['user_info']['mobile'];
		$bankList=$GLOBALS['db']->getAll("SELECT id,name FROM  ".DB_PREFIX."bank");
 		$GLOBALS['tmpl']->assign('bankList',$bankList);
 		$GLOBALS['tmpl']->assign("cate_title","绑定银行卡");
		if(!empty($info)){
		 	$json['status'] = 0;
		 	$json['info'] = "认证信息已存在";
		 	ajax_return($json);
		}else{
			$json['status'] = 2;
			$GLOBALS['tmpl']->assign('credit_data', $credit_data);
		 	$json['info'] = $GLOBALS['tmpl']->fetch("inc/uc/uc_bind_bank.html");
		 	ajax_return($json);
		}
	}
}
?>
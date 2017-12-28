<?php

define(MODULE_NAME,"index");
require APP_ROOT_PATH.'app/Lib/deal.php';
class indexModule extends SiteBaseModule
{
	public function index()
	{
		jumpUrl("jump_url_depository");
		jumpUrl("jump_url_invite");
		jumpUrl("jump_url_info");
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 180;  //首页缓存3分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);	
		if (!$GLOBALS['tmpl']->is_cached("page/index.html", $cache_id))
		{	
			change_deal_status();
			
			if((int)app_conf("SHOW_EXPRIE_DEAL") == 0){
				$extW = " AND (if(deal_status = 1, start_time + enddate*24*3600 > ".TIME_UTC .",1=1)) ";
			}
			//广告列表
			$sql = "select * from ".DB_PREFIX."m_adv where status = 1 AND page= 'top' limit 0,5 ";
			$adv_list = $GLOBALS['db']->getAll($sql);
			//var_dump($adv_list);exit;
			$GLOBALS['tmpl']->assign("adv_list",$adv_list);
			//借款预告列表
			$advance_deal_list =  get_deal_list(5,0,"publish_wait =0 AND deal_status =1 AND is_advance=1 AND start_time >".TIME_UTC." and is_hidden = 0 ".$extW," deal_status ASC, is_recommend DESC,sort DESC,id DESC");
			$GLOBALS['tmpl']->assign("advance_deal_list",$advance_deal_list['list']);
			
			//最新借款列表
			$deal_list =  get_deal_list(5,0,"publish_wait =0 AND deal_status in(1,2,4) AND start_time <=".TIME_UTC." and is_hidden = 0 and is_delete = 0 ".$extW," is_new DESC, deal_status ASC, is_recommend DESC,sort DESC,id DESC");
			foreach($deal_list['list'] as $k=>$v){ 
				$url_tmp_args['id'] = $v['id'];
				$deal_list['list'][$k]['url'] = url("index","deal#index",$url_tmp_args); 	//入口 控制器模块 控制器 参数数组
				$deal_list['list'][$k]['rate'] = sprintf("%.1f",$v["rate"]);				//统一预期年化收益格式 
				$deal_list['list'][$k]['need_money'] = intval($v['need_money']);
			}
			$GLOBALS['tmpl']->assign("new_deal_list",$deal_list['list']);

			
			//最新借款列表
			// $deal_list =  get_deal_list(11,0,"publish_wait =0 AND deal_status in(1,2,4) AND start_time <=".TIME_UTC." and is_hidden = 0 ".$extW," deal_status ASC, is_recommend DESC,sort DESC,id DESC");
			// $GLOBALS['tmpl']->assign("deal_list",$deal_list['list']);
			//var_dump($deal_list);exit;
			//输出最新转让
			$transfer_list =  get_transfer_list(11," and d.deal_status >= 4  AND dlt.status=1  ",'',''," d.create_time DESC , dlt.id DESC ");
			$GLOBALS['tmpl']->assign('transfer_list',$transfer_list['list']);

			//输出公告
			//========区分wap和pc访问===========
			if(WAP == 1){
				$notice_list = get_mobile_notice(0);
			}else{
				$notice_list = get_notice(0);
			}
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);
			//========区分wap和pc访问===========
			//输出公司动态
			$art_id =  $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."article_cate_cg where title='公司动态'");
			if($art_id > 0){
				$compnay_active_list  = get_article_list(5,$art_id);
				$GLOBALS['tmpl']->assign("art_id",$art_id);
				$GLOBALS['tmpl']->assign("compnay_active_list",$compnay_active_list['list']);	
			}
			//var_dump($notice_list);exit;
			//输出媒体报道
			$mtbd_id =  $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."article_cate_cg where is_delete=0 and title='媒体报道'");
			if($mtbd_id > 0){
				$mtbd_list  = get_article_list(2,$mtbd_id);
				foreach($mtbd_list['list'] as $k => $v) {
					$mtbd_list['list'][$k]['contents'] = msubstr($mtbd_list['list'][$k]['content'],0,25) ;
				}
				
				$GLOBALS['tmpl']->assign("mtbd_id",$mtbd_id);
				$GLOBALS['tmpl']->assign("mtbd_list",$mtbd_list['list']);

			}

			//出借排行
			//天
			$now_time = to_date(TIME_UTC,"Y-m-d");
			//$day_load_top_list =  $GLOBALS['db']->getAll("SELECT * FROM (SELECT user_name,sum(money) as total_money FROM ".DB_PREFIX."deal_load where create_date = '".$now_time."' and is_repay= 0   group by user_id ORDER BY total_money DESC) as tmp LIMIT 10");
			
			//周
			//$week_time_start =  to_date(TIME_UTC - to_date(TIME_UTC,"w") * 24*3600 ,"Y-m-d");
			//$week_load_top_list =  $GLOBALS['db']->getAll("SELECT * FROM (SELECT user_name,sum(money) as total_money FROM ".DB_PREFIX."deal_load where create_date in (".date_in($week_time_start,to_date(TIME_UTC,"Y-m-d")).") and is_repay= 0   group by user_id ORDER BY total_money DESC) as tmp LIMIT 10 ");
			//月
			//$month_time_start = to_date(TIME_UTC,"Y-m")."-01";
			//$month_load_top_list =  $GLOBALS['db']->getAll("SELECT * FROM (SELECT user_name,sum(money) as total_money FROM ".DB_PREFIX."deal_load where create_date in (".date_in($month_time_start,to_date(TIME_UTC,"Y-m-d")).") and is_repay= 0   group by user_id ORDER BY total_money DESC) as tmp LIMIT 10");
			//总
			//$all_load_top_list =  $GLOBALS['db']->getAll("SELECT * FROM (SELECT user_name,sum(money) as total_money FROM ".DB_PREFIX."deal_load where  is_repay= 0  group by user_id ORDER BY total_money DESC) as tmp LIMIT 10");
			
			
			//$GLOBALS['tmpl']->assign("day_load_top_list",$day_load_top_list);	
			//$GLOBALS['tmpl']->assign("week_load_top_list",$week_load_top_list);	
			//$GLOBALS['tmpl']->assign("month_load_top_list",$month_load_top_list);	
			//$GLOBALS['tmpl']->assign("all_load_top_list",$all_load_top_list);	
			
			//收益排名
			//$load_repay_list = $GLOBALS['db']->getAll("SELECT us.*,u.user_name FROM ".DB_PREFIX."user_sta us LEFT JOIN ".DB_PREFIX."user u ON us.user_id=u.id WHERE u.is_effect =1 and u.is_delete=0 and us.load_earnings > 0  ORDER BY us.load_earnings DESC LIMIT 5");
			//$GLOBALS['tmpl']->assign("load_repay_list",$load_repay_list);	
			
			//使用技巧
			$use_tech_list  = get_article_list(12,6);
			$GLOBALS['tmpl']->assign("use_tech_list",$use_tech_list);	
			
			$now = TIME_UTC;
			//$vote = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote where is_effect = 1 and begin_time < ".$now." and (end_time = 0 or end_time > ".$now.") order by sort desc limit 1");
			//$GLOBALS['tmpl']->assign("vote",$vote);
			
			//$stats = site_statics();
			//$GLOBALS['tmpl']->assign("stats",$stats);
			
			$near_deal_loads = get_near_deal_loads("0,8");
			$GLOBALS['tmpl']->assign("near_deal_loads",$near_deal_loads);
			
			//格式化统计代码-------转字符串
			//$VIRTUAL_MONEY_1_FORMAT =  format_conf_count($stats['total_load']);  //累计出借额
			$decimals = explode('.', $stats['total_load']);
			$length=strlen(floor($stats['total_load']));
			$arr=array();
			for($i=0;$i<$length;$i++){
				$arr[$i]=floor($stats['total_load']/(pow(10,$length-$i-1))%10);
			}
			$GLOBALS['tmpl']->assign("pesent",$decimals[1]);
			$GLOBALS['tmpl']->assign("arr",$arr);
			
			$GLOBALS['tmpl']->assign('user_count',$stats['user_count']); //累计注册用户


			//$VIRTUAL_MONEY_2_FORMAT =  format_conf_count($stats['total_rate']);  ////累计创造收益
			$decimals2 = explode('.', $stats['total_rate']);
			//$length2=strlen(floor($stats['total_rate']));
			//$arr2=array();
			//for($i=0;$i<$length2;$i++){
				//$arr2[$i]=floor($stats['total_rate']/(pow(10,$length2-$i-1))%10);
			//}
			//var_dump($decimals2);exit;
			$GLOBALS['tmpl']->assign("pesent2",$decimals2[1]);
			//$GLOBALS['tmpl']->assign("arr2",$arr2);
			//$VIRTUAL_MONEY_3_FORMAT =  format_conf_count($stats['total_bzh']);		//本息保证金（元）

			//$GLOBALS['tmpl']->assign("VIRTUAL_MONEY_1_FORMAT",$VIRTUAL_MONEY_1_FORMAT);
			//$GLOBALS['tmpl']->assign("VIRTUAL_MONEY_2_FORMAT",$VIRTUAL_MONEY_2_FORMAT);
			$GLOBALS['tmpl']->assign("VIRTUAL_MONEY_3_FORMAT",$VIRTUAL_MONEY_3_FORMAT);

			$GLOBALS['tmpl']->assign("show_site_titile",1);
			//新加首页企业视频
			$limit1 = ("0,4");
			$resultvideo = get_article_list($limit1,38,'ac.type_id = 4','');
			$GLOBALS['tmpl']->assign("listvideo",$resultvideo['list']);
		}
		//===============wap2.0代码于APP数据一致================
		$stats = site_statics();
//		$registered_user = str_split(strip_tags(number_format($stats['user_count'])));//注册用户数;
        $register_count=$GLOBALS['db']->getOne("select fake_data from ".DB_PREFIX."statistics_conf where id=3 and is_effect =1");
        $registered_user = str_split(strip_tags(number_format($register_count)));//注册用户数;
		$wapregistered_user = strip_tags(number_format($stats['user_count']));//注册用户数;
		$GLOBALS['tmpl']->assign('wapregistered_user',$wapregistered_user); //累计注册用户
		$total_load =  strip_tags(number_format(intval($stats['total_load'])));  //累计出借
		$GLOBALS['tmpl']->assign('registered_user',$registered_user); //累计注册用户
		//$GLOBALS['tmpl']->assign('registered_user_num',count(str_split($registered_user))); //累计注册用户
		$GLOBALS['tmpl']->assign('total_load',$total_load); //累计出借
		//首页广告
		$wap_nav = get_wap_nav(index); //index 首页banner 不传 发现页banner
        
        //国庆活动单独设置 活动banner
        $mobile=$GLOBALS['user_info']['mobile'];
	    foreach ($wap_nav as $key => $value) {
	       if($value['id']==32){//正式环境id需改
	             $wap_nav[$key]['url']="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=42&phone=".$mobile."&activity_id=".$value['id'];
	       }
	    }

		$GLOBALS['tmpl']->assign("wap_nav",$wap_nav);
		$ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips_cg where code = 1" );
		$GLOBALS['tmpl']->assign("ips",$ips);
		$todayBegin = strtotime(date("Y-m-d")."00:00:00"); 	//今天开始时间
		$todayEnd = strtotime(date("Y-m-d")."23:59:59"); 	//今天开始结束
		$sign_count_day = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where type = 25 and create_time >=".$todayBegin." and create_time < ".$todayEnd);
		$wap_cloumn = get_wap_cloumn(index);
		foreach ($wap_cloumn as $k => $v) {
			if($v['id'] == 3){
				$hour = date('H');
				if($hour > 3){
					$sign_count_day = "931" + $sign_count_day;
				}				
				$wap_cloumn[$k]['title'] = "当日:".$sign_count_day."人";
			}
		}
		//$code = ltrim($GLOBALS['user_info']['user_name'],"w");
		//$wap_cloumn[0]['url'] = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=29&code=".$code;
		$GLOBALS['tmpl']->assign("wap_cloumn",$wap_cloumn);
		$limit = 1;
		$n_cate_id = 0;		
		$orderby = "";
		$uid = $GLOBALS['user_info']['id'];
		$code = $GLOBALS['user_info']['mobile'];
		//签到
		$wap_cloumn_url = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=33&phone=".$code;
		$GLOBALS['tmpl']->assign("wap_cloumn_url",$wap_cloumn_url);
		//版本切换提示
		if($_POST['version'] == 1){
			es_session::delete('version_switching');
			$result['status'] = 1;
			ajax_return($result);
		}
		if($_REQUEST['PHPSESSID'] && !es_session::get('version_switching')){
			es_session::set('version_switching',1);
		}else if($_REQUEST['PHPSESSID'] && es_session::get('version_switching')){
			es_session::set('version_switching',2);
		}else{
			es_session::set('version_switching',2);
		}
		$GLOBALS['tmpl']->assign("version_switching",es_session::get('version_switching'));	
		/**
		• 未出借用户：推荐新手标
		• 只出借1笔的用户：推荐3月标
		• 多笔出借，出借总额不满10万的用户：推荐6月、12月标
		• 多笔出借，出借总额满10万的用户：推荐秒杀标
		*********/
		if($uid > 0){
			/*用户登录*/
			$is_deal =$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 and deal_status = 1 and type_id !=12");//查询是否有借款中的项目
			$deal = $GLOBALS['db']->getRow("SELECT count(money) as num,sum(money) as money FROM ".DB_PREFIX."deal_load WHERE cunguan_tag=1 and user_id = ".$uid." order by id ASC "); //查询用户出借状态
			if($deal['num'] == 0){
				// 未出借用户：优先推荐新手标 未有新手标 出借期限从小到大
				$is_new =$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and type_id !=12 and is_new = 1 and deal_status = 1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0"); //查询是否存在新手标
				if($is_new){
					$condition = " deal_status=1 and is_new=1";
				}elseif(!$is_deal){
					$orderby = "repay_time asc, id desc,deal_status asc";
					$condition = " (deal_status = 2 or deal_status = 4 or deal_status = 5)";
				}else{
					$orderby = "repay_time asc,id desc";
					//投资期限从小到大
					//$condition = " cunguan_tag=1 and publish_wait = 0 and is_hidden = 0 and deal_status=1  AND (if(deal_status = 1, start_time + enddate*24*3600 > ".TIME_UTC .",1=1))";
					//投资期限从小到大 不包含1个月标
					$condition = " deal_status=1 and is_new = 0";

				}
			}
			/*if($deal['num'] == 1){
				//出借一笔用户
				if(!$is_deal){
					$orderby = "repay_time asc, id desc,deal_status asc";
					$condition = " cunguan_tag=1 and publish_wait = 0 and is_hidden = 0 and (repay_time = 3 or repay_time = 6 or repay_time = 12) and (deal_status = 2 or deal_status = 4 or deal_status = 5)";
				}else{
					$orderby = "repay_time asc,id desc";
					$condition = " cunguan_tag=1 and publish_wait = 0 and is_hidden = 0 and deal_status=1 and (repay_time = 3 or repay_time = 6 or repay_time = 12) AND (if(deal_status = 1, start_time + enddate*24*3600 > ".TIME_UTC .",1=1))";
				}
			}*/
			if($deal['num'] > 0 && $deal['money'] < 100000){
				//出借多笔 出借总额小于10万
				if(!$is_deal){
					$orderby = "repay_time asc, id desc,deal_status asc";
					$condition = " is_new = 0 and (deal_status = 2 or deal_status = 4 or deal_status = 5)";
				}else{
					$orderby = "repay_time asc,id desc";
					$condition = " deal_status=1 and is_new = 0";
				}				
			}
			if($deal['money'] >= 100000){
				$is_hot =$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 and is_hot = 1 and type_id !=12 and deal_status = 1 ");
				if($is_hot){
					$condition = " deal_status=1 and is_hot=1";
				}elseif(!$is_deal){
					$orderby = "repay_time desc, id desc,deal_status asc";
					$condition = " is_new = 0 and (deal_status = 2 or deal_status = 4 or deal_status = 5)";
				}else{
					$orderby = "repay_time desc,id desc";
					$condition = " is_new = 0 and deal_status=1 and (repay_time = 3 or repay_time = 6 or repay_time = 12) ";
				}
			}
		}else{
			/*用户未登录*/
			$is_deal =$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 and deal_status = 1 and type_id !=12");//查询是否有借款中的项目
			$is_new =$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and type_id !=12 and is_new = 1 and deal_status = 1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 "); //查询是否存在新手标
			if($is_new){
				$condition = " deal_status=1 and is_new=1";
			}elseif(!$is_deal){
				$orderby = "repay_time asc,id desc,deal_status asc";
				$condition = " (deal_status = 2 or deal_status = 4 or deal_status = 5)";		
			}else{
				$orderby = "repay_time asc,id desc";
				$condition = " is_new = 0 and deal_status=1";
			}	
		}
		//区分存管版
		$condition.=" and type_id != 12 and cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0";
		$result= get_deal_list($limit,$n_cate_id,$condition,$orderby);
		foreach ($result['list'] as $k => $v) {
			if($v['debts'] == 1){
				$last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$v['old_deal_id']." order by repay_time desc limit 1");
				$remin = strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time()));
				if($remin>0){
					$v['debts_repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
				}else{
					$v['debts_repay_time']=0;
				}
			}
			$v['rate'] = sprintf("%.1f",$v["rate"]);
			$v['information_status'] = $GLOBALS['db']->getOne("select information_status from ".DB_PREFIX."deal_loan_type where id=".$v['type_id']);
			$list = $v;
		}
		//活动推送
		$popup = $GLOBALS['db']->getRow("select id,app_page,img,name,type,url,position from ".DB_PREFIX."app_popup_cg where is_effect =1 and position = 1 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
		if($popup && !es_session::get('pop')){
			es_session::set('pop',1);
		}else if($popup && es_session::get('pop')){
			es_session::set('pop',2);
		}else if(!$popup){
			es_session::set('pop',2);
		}
		$GLOBALS['tmpl']->assign("pop",es_session::get('pop'));	
		$GLOBALS['tmpl']->assign("popup",$popup);	
		$GLOBALS['tmpl']->assign("list",$list);	
		$GLOBALS['tmpl']->assign("uid",$uid);
		$GLOBALS['tmpl']->assign("user_type",$GLOBALS['user_info']['user_type']);//企业用户标识
//		// app下载统计
//		$download_total=str_split(number_format(5397659));
//		$GLOBALS['tmpl']->assign("download_total_num",count($download_total));
//		$GLOBALS['tmpl']->assign("download_total",$download_total);
        // app下载统计
        $app_download=$GLOBALS['db']->getOne("select fake_data from ".DB_PREFIX."statistics_conf where id=4 and is_effect =1");
        $download_total=str_split(number_format($app_download));
        $GLOBALS['tmpl']->assign("download_total_num",count($download_total));
        $GLOBALS['tmpl']->assign("download_total",$download_total);
		// 首页标 3 6 12个月
		$three_deal=$GLOBALS['db']->getRow("select id,name,sub_name,is_new,is_hot,interest_rate,user_id,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".TIME_UTC.") as remain_time from ".DB_PREFIX."deal where repay_time=3 and publish_wait =0 AND deal_status in(1,2,4,5) AND start_time <=".TIME_UTC." and is_hidden = 0 and is_effect=1 and is_delete = 0 and cunguan_tag = 1 and borrow_amount<200000 order by deal_status ASC, is_recommend DESC,sort DESC,id DESC limit 1");
		$six_deal=$GLOBALS['db']->getRow("select id,name,sub_name,is_new,is_hot,interest_rate,user_id,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".TIME_UTC.") as remain_time from ".DB_PREFIX."deal where repay_time=6 and publish_wait =0 AND deal_status in(1,2,4,5) AND start_time <=".TIME_UTC." and is_hidden = 0 and is_effect=1 and is_delete = 0 and cunguan_tag = 1 and borrow_amount<200000  order by deal_status ASC, is_recommend DESC,sort DESC,id DESC limit 1");
		$twelve_deal=$GLOBALS['db']->getRow("select id,name,sub_name,is_new,is_hot,interest_rate,user_id,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".TIME_UTC.") as remain_time from ".DB_PREFIX."deal where repay_time=12 and publish_wait =0 AND deal_status in(1,2,4,5) AND start_time <=".TIME_UTC." and is_hidden = 0 and is_effect=1 and is_delete = 0 and cunguan_tag = 1 and borrow_amount<200000 order by deal_status ASC, is_recommend DESC,sort DESC,id DESC limit 1");
		//$index_deal_list = $GLOBALS['db']->getAll("select id,name,sub_name,is_new,is_hot,interest_rate,user_id,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".TIME_UTC.") as remain_time from ".DB_PREFIX."deal where repay_time in(3,6,12) and publish_wait =0 AND deal_status in(1,2,4,5) AND start_time <=".TIME_UTC." and is_hidden = 0 and is_effect=1 and is_delete = 0 and cunguan_tag = 1 and borrow_amount<200000 order by deal_status ASC, is_recommend DESC,sort DESC,id DESC limit 3");
		 if($three_deal)
		$index_deal_list[]=$three_deal;
		if($six_deal)
		$index_deal_list[]=$six_deal;
		if($twelve_deal)
		$index_deal_list[]=$twelve_deal; 
		// $index_deal_list['list'][]=$three_deal['list'][0];
		// $index_deal_list['list'][]=$six_deal['list'][0];
		// $index_deal_list['list'][]=$twelve_deal['list'][0];
		// $time = TIME_UTC;
		// $index_deal_list=$GLOBALS['db']->getAll("SELECT id,name,sub_name,is_new,is_hot,user_id,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - $time) as remain_time FROM ".DB_PREFIX."deal where is_hidden=0 and is_new=1 and (start_time + enddate*24*3600 - $time)>0 group by repay_time having repay_time in (3,6,12)");
		// if(count($index_deal_list)<3){
		// $index_deal_list=$GLOBALS['db']->getAll("SELECT id,name,sub_name,is_new,is_hot,user_id,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - $time) as remain_time FROM ".DB_PREFIX."deal where is_hidden=0 group by repay_time having repay_time in (3,6,12)");
		// }
		foreach($index_deal_list as $k=>$deal)
			{
				//format_deal_item($deal,$user_name,$user_pwd);
				//$deals[$k] = $deal;
				$index_deal_list[$k]['url'] = url("index","deal",array("id"=>$deal['id']));
				$index_deal_list[$k]['rate'] = sprintf("%.1f",$deal["rate"]);				//统一预期年化收益格式 
				$index_deal_list[$k]['need_money'] = intval($deal['need_money']);
			}
		$statistics = $GLOBALS['db']->getAll("select id,title,fake_data,icon from ".DB_PREFIX."statistics_conf where is_effect =1");
        $GLOBALS['tmpl']->assign("statistics",$statistics);
        $GLOBALS['tmpl']->assign("PHPSESSID",session_id());
        $beginner = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."beginner_welfare where status =1");
        $GLOBALS['tmpl']->assign("beginner",$beginner);	
		$GLOBALS['tmpl']->assign("cate_title","首页");

        //活动广告位
        $hd =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_hpad where is_effect=1" );
        $GLOBALS['tmpl']->assign("hd",$hd);
		$GLOBALS['tmpl']->assign("index_deal_list",$index_deal_list);
		$GLOBALS['tmpl']->display("page/index.html",$cache_id);
	}
}	
?>
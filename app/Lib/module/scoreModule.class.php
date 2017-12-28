<?php

require APP_ROOT_PATH.'app/Lib/deal.php';
require_once APP_ROOT_PATH."system/libs/user.php";
class scoreModule extends SiteBaseModule
{
	public function index(){
		jumpUrl("jump_url_info");
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}	
		
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 60;  //首页缓存10分钟
		$field = es_cookie::get("shop_sort_field"); 
		$field_sort = es_cookie::get("shop_sort_type"); 
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.implode(",",$_REQUEST).$field.$field_sort);	
		if (!$GLOBALS['tmpl']->is_cached("page/score.html", $cache_id))
		{	
			require APP_ROOT_PATH.'app/Lib/page.php';
			
			$cates = intval($_REQUEST['cates']);
			$GLOBALS['tmpl']->assign("cates",$cates);
			
			$integral = intval($_REQUEST['integral']);
			$GLOBALS['tmpl']->assign("integral",$integral);
			
			$sort = intval($_REQUEST['sort']);   //1.最新  2.热门  3.积分
			$GLOBALS['tmpl']->assign("sort",$sort);
			

			//输出投标列表
			$page = intval($_REQUEST['p']);
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("SCORE_PAGE_SIZE")).",".app_conf("SCORE_PAGE_SIZE"); 
			$condition = " 1=1";
			if($sort == 1){
				$condition .= " AND is_new = 1";
			}elseif($sort == 2)
			{
				$condition .= " AND is_hot = 1 ";
			}elseif ($sort == 3)
			{
				$orderby = " score desc";
			}
			
			if($cates>0){
				$cates_id = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."goods_cate where pid = ".$cates);
				$flatmap = array_map("array_pop",$cates_id);
				$cates_ids=implode(',',$flatmap);
				if($cates_ids=="") 
				{
					$condition .= " AND cate_id in (".$cates.") ";
				}else{
					$condition .= " AND cate_id in (".$cates.",".$cates_ids.") ";
				}
				
			}
			
			if($integral==0){
				$condition .= "";
			}elseif ($integral==1){
				$condition .= " AND score  <= 500";
			}elseif ($integral==2){
				$condition .= " AND score  between 500 and 1000";
			}elseif ($integral==3){
				$condition .= " AND score  between 1000 and 3000";
			}elseif ($integral==4){
				$condition .= " AND score  between 3000 and 5000";
			}else{
				$condition .= " AND score  >= 5000";
			}
			
			$result = get_goods_list($limit,$condition,$orderby);
			foreach ($result['list'] as $k => $v) {
				$v['bought'] = $v['max_bought'] - $v['buy_number'];
				$list[] = $v;
			}
			$GLOBALS['tmpl']->assign("goods_list",$list);
			$page_args['cates'] =  $cates;
			$page_args['integral'] =  $integral;
			$page_args['sort'] =  $sort;
			
			$account_score = $GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".$user_id);
			$GLOBALS['tmpl']->assign('account_score',$account_score);
			//商品类别
			$cates_urls =load_auto_cache("score_cates");
			
			//$cates_urls = $GLOBALS['db']->getAll("SELECT id,name FROM ".DB_PREFIX."goods_cate WHERE is_effect=1 and is_delete = 0 and pid= 0");	
			$cates_url = array();
			
			$cates_url[0]['id'] = 0;
			$cates_url[0]['name'] = "不限";
			$tmp_args = $page_args;
			$tmp_args['cates'] = 0;
			$cates_url[0]['url'] = url("index","score#index",$tmp_args);
			
			
			foreach($cates_urls as $k=>$v){
				$cates_url[$k+1]['id'] = $v['id'];
				$cates_url[$k+1]['name'] = $v['name'];
				$tmp_args = $page_args;
				$tmp_args['cates'] = $v['id'];
				$cates_url[$k+1]['url'] = url("index","score#index",$tmp_args);
			}
			$GLOBALS['tmpl']->assign('cates_url',$cates_url);
			
			//积分范围
			$integral_url = array(
				array(
					"name" => "不限",
					),
				array(
					"name" => "500积分以下",
					),
				array(
					"name" => "500-1000积分",
					),
				array(
					"name" => "1000-3000积分",
					),
				array(
					"name" => "3000-5000积分",
					),
				array(
					"name" => "5000积分以上",
					),
				);
			foreach($integral_url as $k=>$v){
				$tmp_args = $page_args;
				$tmp_args['integral'] = $k;
				$integral_url[$k]['url'] = url("index","score#index",$tmp_args);
			}
			$GLOBALS['tmpl']->assign('integral_url',$integral_url);
			
			//排序
			$sort_url = array(
				array(
					"name" => "默认排序",
					),
				array(
					"name" => "最新",
					),
				array(
					"name" => "热门",
					),
				array(
					"name" => "积分",
					),
				);
			foreach($sort_url as $k=>$v){
				$tmp_args = $page_args;
				$tmp_args['sort'] = $k;
				$sort_url[$k]['url'] = url("index","score#index",$tmp_args);
			}
			$GLOBALS['tmpl']->assign('sort_url',$sort_url);
			
			
			$page_pram = "";
			foreach($page_args as $k=>$v){
				$page_pram .="&".$k."=".$v;
			}
			
			$page = new Page($result['count'],app_conf("SCORE_PAGE_SIZE"),$page_pram);   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			
			$GLOBALS['tmpl']->assign("page_args",$page_args);
			
			$GLOBALS['tmpl']->assign("field",$field); //??
			$GLOBALS['tmpl']->assign("field_sort",$field_sort); //??
		}
		$code = $GLOBALS['user_info']['mobile'];
		$wap_cloumn_url = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=28&code=".$code;
		$help_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."app_help_cate  WHERE title='积分商城' and  is_effect = 1 and is_delete=0");
		$GLOBALS['tmpl']->assign('help_id',$help_id);
		$GLOBALS['tmpl']->assign("wap_cloumn_url",$wap_cloumn_url);
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("cate_title",'积分商城');
		$GLOBALS['tmpl']->display("page/score.html",$cache_id);
	}
	public function earn_points(){
		jumpUrl("jump_url_incharge");
		$jumpUrl = es_cookie::get("jump_url_info");
		$GLOBALS['tmpl']->assign('jumpUrl',$jumpUrl);
		$user_id = $GLOBALS['user_info']['id'];
		$t_begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
		$t_end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
		$y_begin_time = $t_begin_time - (24*3600); //昨天开始
		$y_end_time = $t_end_time - (24*3600);  //昨天结束
		$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$t_begin_time." and ".$t_end_time);
		$GLOBALS['tmpl']->assign('t_sign_data',$t_sign_data);
		if(!$GLOBALS['user_info']['id']){
			app_redirect(url("index","user#login")."&jumpUrl=".url("index","find"));
		}
		//检查昨天是否签到 如果未签到 数据置为0		
		$sign_count = $GLOBALS['db']->getRow("select sign_count,sign_score_count from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$y_begin_time." and ".$y_end_time." order by id desc");
		if($sign_count==""){
			$sign_count['sign_count']=0;
			$sign_count['sign_score_count']=0;
		}
		//检查今天是否签到 如果未签到 使用昨天是否签到
		$sign_sum = $GLOBALS['db']->getRow("select sign_count,sign_score_count from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$t_begin_time." and ".$t_end_time." order by id desc");
		
		if($sign_sum['sign_count']==""){
			$sign_sum['sign_count']=$sign_count['sign_count'];
			$sign_sum['sign_score_count']=$sign_count['sign_score_count'];
		}
		$GLOBALS['tmpl']->assign('sign_sum',$sign_sum);
		$GLOBALS['tmpl']->assign('sign_count',$sign_count);
		$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
		//每日分享
		$share_rs = is_share($user_id);
		if($share_rs){
		    $receive_rs = is_receive_share_score($user_id,70,5);
		}
		/************累计出借折标后金额**************/
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
		$end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
		$deal_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id = ".$GLOBALS['user_info']['id']." and create_time between ".$begin_time." and ".$end_time);
		$z_money = 0;
		foreach ($deal_data as $k => $v) {
			$deal = get_deal($v['deal_id']);
			$z_money+= $v['money'] * $deal['repay_time'] /12;//当天出借的折标金额
		}
		/*******************当天充值记录*********************/
		$recharge = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment_notice where user_id = ".$GLOBALS['user_info']['id']." and is_paid = 1 and create_time between ".$begin_time." and ".$end_time.' and cunguan_tag=1');
		$recharge_score = intval($GLOBALS['db']->getOne("select sum(score) as score from ".DB_PREFIX."user_score_log where user_id = ".$GLOBALS['user_info']['id']." and type =1"));
		/*充值时对认证状态进行校验*/
		/*******************当天出借记录*********************/
		$lend = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_load where user_id = ".$GLOBALS['user_info']['id']." and create_time between ".$begin_time." and ".$end_time.' and cunguan_tag=1');
		$recharge_lend_score = intval($GLOBALS['db']->getOne("select sum(score) as score from ".DB_PREFIX."user_score_log where user_id = ".$GLOBALS['user_info']['id']." and type =26"));
		$user_info = $GLOBALS['user_info'];
		/************已领取的累计出借积分**************/
		$inv_sign['sign1'] = checkInvestSign(40,$user_id,100);   //累计出借5000 折标后
		$inv_sign['sign2'] = checkInvestSign(180,$user_id,100);	//累计出借10000 折标后
		$inv_sign['sign3'] = checkInvestSign(330,$user_id,100);	//累计出借50000 折标后
		$inv_sign['sign4'] = checkInvestSign(1500,$user_id,100);	//累计出借100000 折标后
		$inv_sign['recharge'] = checkInvestSign(6,$user_id,1);	//充值任务
		$inv_sign['recharge_lend'] = checkInvestSign(20,$user_id,26);	//充值任务
		$GLOBALS['tmpl']->assign('inv_sign',$inv_sign);
		$user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where cunguan_tag = 1 and user_id= ".$user_info['id']);
		/*判断客户端访问 ios wap 安卓*/
		switch ($MachineInfo[0]) {
			case 'iOS':
				$jump['PopBox'] = 'iosToPopBox';
				$jump['ReCharge'] = 'iosToReCharge';
				$jump['ProductList'] = 'iosToProductList';
				$jump['jumpUrl'] = SITE_DOMAIN;
				break;
			case 'Android':
				$jump['PopBox'] = 'androidToPopBox';
				$jump['ReCharge'] = 'androidToReCharge';
				$jump['ProductList'] = 'androidToProductList';
				break;
			default:
				$jump['PopBox'] = 'popBox';
				$jump['ReCharge'] = 'wapToReCharge';
				$jump['ProductList'] = 'wapToProductList';
				break;
		}
		$help_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."app_help_cate  WHERE title='每日任务' and  is_effect = 1 and is_delete=0");
		$GLOBALS['tmpl']->assign('help_id',$help_id);
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign('mobile',$user_info['mobile']);
		if(time() > 1495727999 && time() < 1496159999){
			$score = 5;
		}else{
			//$score = intval(app_conf("USER_LOGIN_SCORE"));
			$score = 3;
		}
		//存管出借 验证
		if($user_info['cunguan_tag'] == 0){
			$ajax['code'] = 0;
			$ajax['url'] = url("index","uc_depository_account"); //判断存管是否开户
		}else if($user_info['cunguan_tag'] == 1 && $user_info['cunguan_pwd'] == 0){
			$ajax['code'] = 1;
			$ajax['url'] = url("index","uc_depository_paypassword#setpaypassword"); //判断存管是否设置交易密码
		}else if($user_info['cunguan_tag'] == 1 && $user_info['cunguan_pwd'] == 1 && $user_status < 1){
			$ajax['code'] = 1;
			$ajax['url'] = url("index","uc_depository_addbank#wap_check_pwd"); //判断存管是否设置交易密码
		}else{
			$ajax['code'] = 3;
		}
		$GLOBALS['tmpl']->assign('ajax',$ajax);
		//当天签到总数
		$sign_count_day = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where type = 25 and create_time between ".$t_begin_time." and ".$t_end_time);
		$hour = date('H');
//		if($hour > 3){
//			$sign_count_day = "931" + $sign_count_day;
//		}
        // 当日签到基数
        $sign_count_day_base=$GLOBALS['db']->getOne("select fake_data from ".DB_PREFIX."statistics_conf where id=5");
        $sign_count_day = $sign_count_day_base + $sign_count_day;
		$GLOBALS['tmpl']->assign('sign_count_day',$sign_count_day);
		//全部签到总数
		$sign_sum_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where type = 25");
        // 全部签到基数
        $sign_sum_count_base=$GLOBALS['db']->getOne("select fake_data from ".DB_PREFIX."statistics_conf where id=6");
        $sign_sum_count = $sign_sum_count_base + $sign_sum_count;
//		$day = intval((time() - 1499702400)/86400);
//		if($day){
//			$sign_sum_count = 931 * $day+$sign_sum_count;
//		}

		$GLOBALS['tmpl']->assign('sign_sum_count',$sign_sum_count);
		//当天分享数
		$share_count_day = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where type = 70 and create_time between ".$t_begin_time." and ".$t_end_time);
//		if($hour > 3){
//			$share_count_day = "678" + $share_count_day;
//		}
        // 当天分享基数
        $share_count_day_base=$GLOBALS['db']->getOne("select fake_data from ".DB_PREFIX."statistics_conf where id=7");
        $share_count_day = $share_count_day_base + $share_count_day;
		$GLOBALS['tmpl']->assign('share_count_day',$share_count_day);
		//全部分享总数
		$share_sum_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where type = 70");
        $share_sum_count_base=$GLOBALS['db']->getOne("select fake_data from ".DB_PREFIX."statistics_conf where id=8");
        $share_sum_count = $share_sum_count_base + $share_sum_count;
        // 全部分享基数
//		if($day){
//			$share_sum_count = 678 * $day+$share_sum_count;
//		}
		$GLOBALS['tmpl']->assign('share_sum_count',$share_sum_count);
		$GLOBALS['tmpl']->assign('score',$score);
		$GLOBALS['tmpl']->assign('z_money',$z_money);
		$GLOBALS['tmpl']->assign('recharge',$recharge);
		$GLOBALS['tmpl']->assign('recharge_score',$recharge_score);
		$GLOBALS['tmpl']->assign('lend',$lend);
		$GLOBALS['tmpl']->assign('recharge_lend_score',$recharge_lend_score);
		$GLOBALS['tmpl']->assign('share_rs',$share_rs);
		$GLOBALS['tmpl']->assign('receive_rs',$receive_rs);
		$GLOBALS['tmpl']->assign("url",WAP_SITE_DOMAIN);
		/*每日任务广告*/
		$beginner = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."daily_tasks where is_effect =1  and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
        $GLOBALS['tmpl']->assign("beginner",$beginner);

		$GLOBALS['tmpl']->assign('cate_title',"每日任务");
		$GLOBALS['tmpl']->display("page/earn_points.html");
		}
		public function user_sign(){
			$user_id = $GLOBALS['user_info']['id'];
			if(!check_ipop_limit(CLIENT_IP,"user_sign",3,$user_id)){
				$return["status"] = 0;
				$return["info"] = "提交太频繁！";
				ajax_return($return); 
			}
			if(!$user_id){
				$return['status'] = 0;
				$return['info'] = "签到失败，请重新登录";
				ajax_return($return);
			}
			$t_begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
			$t_end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
			$y_begin_time = $t_begin_time - (24*3600); //昨天开始
			$y_end_time = $t_end_time - (24*3600);  //昨天结束
			$GLOBALS['db']->startTrans();
			$GLOBALS['db']->getRow('select score from '.DB_PREFIX.'user where id='.$user_id.' for update');
			$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date >= ".$t_begin_time." and sign_date<=".$t_end_time.' limit 1');
			if($t_sign_data){
				$GLOBALS['db']->rollback();
				$result['status'] = 0;
				$result['info'] = "您已经签到过了";
				ajax_return($result);
			}else{
				//require_once APP_ROOT_PATH."system/libs/user.php";
				if(time() > 1495727999 && time() < 1496159999){
					$score = 5;
				}else{
					//$score = intval(app_conf("USER_LOGIN_SCORE"));
					$score = 3;
				}
				if($score){
					if($score>0)
						$data["score"]=$score;
					//统计是否连续签到
					$sign_count = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." order by id desc");
					$sign_log['user_id'] = $user_id;
					$sign_log['sign_date'] = TIME_UTC;
					if($y_begin_time < $sign_count['sign_date'] && $y_end_time > $sign_count['sign_date']){
						if($sign_count['sign_count']==30){       
							$sign_count['sign_count'] =0;
						}
						$sign_log['sign_count'] = $sign_count['sign_count'] +1;
						if($sign_log['sign_count']==3){//连续签到3天
							$score+=10;
							$data["score"]=$score;
						}elseif($sign_log['sign_count']==7){//连续签到7天
							$score+=30;
							$data["score"]=$score;
						}
						$sign_log['sign_score_count'] = $sign_count['sign_score_count'] +$score;			
					}else{
						$sign_log['sign_count'] = 1;
						$sign_log['sign_score_count'] = $score;
					}
					if($sign_log['sign_count']==3||$sign_log['sign_count']==7){
						$sign_log['status']=1;
					}
					$resultSignLog = $GLOBALS['db']->autoExecute(DB_PREFIX."user_sign_log",$sign_log,"INSERT");
					//$resultSignLog = $GLOBALS['db'] ->query('insert into '.DB_PREFIX.'user_sign_log (user_id,sign_date,sign_count,sign_score_count) select '.$user_id.','.$sign_log['sign_date'].','.$sign_log['sign_count'].','.$sign_log['sign_score_count'] .' from dual where not exists(select id from '.DB_PREFIX.'user_sign_log where user_id='.$user_id.' and sign_date>='.$t_begin_time.' and sign_date<='.$t_end_time.') limit 1');
					$signcount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date>=".$t_begin_time." and sign_date<=".$t_end_time);
					if($signcount>=2){
						$GLOBALS['db']->rollback();
						$result['status'] = 0;
						$result['info'] = "您已经签到过了";
						ajax_return($result);
					}  
					if($resultSignLog){
						modify_account($data,$user_id,"每日签到",25);
						insert_red_log($user_id,1);
						$result['status'] = 1;
						$result['score'] = $score;
						$result['info'] = "签到成功";
						$GLOBALS['db']->commit();
						ajax_return($result);
					}else{
						$GLOBALS['db']->rollback();
						$result['status'] = 0;
						$result['info'] = "签到失败，请重试";
						ajax_return($result);	
					}
					
				}else{
					$GLOBALS['db']->rollback();
					$result['status'] = 0;
					$result['info'] = "签到失败，请重试";
					ajax_return($result);
				}
			}			
		}
		public function points_list(){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';
			$user_id = $GLOBALS['user_info']['id'];
			$score_type = intval($_REQUEST['score_type']);
			$GLOBALS['tmpl']->assign("score_type",$score_type);
			$page_args['score_type'] =  $score_type;
	    	$score = array(
				array(
					"name" => "获得",
				),
				array(
					"name" => "使用",
				),
			);
			foreach($score as $k=>$v){
				$tmp_args = $page_args;
				$tmp_args['score_type'] = $k;
				$score[$k]['url'] = url("index","score#points_list",$tmp_args);
			}	
			$GLOBALS['tmpl']->assign('score',$score);
		//积分获得记录
			if ($score_type == 1){
				$condition = " type = 22";
			}else{
				$condition = " type in(25,100,70,1,23,26)";//23为积分商城订单取消返回   26 充值出借任务
			}
			$get = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_score_log where ".$condition." and user_id = ".$user_id." order by id desc");
			foreach ($get as $k => $v) {
				$v['week']= week(date('N', $v['create_time']));
				$v['score'] = intval($v['score']);
				$v['time'] = date('H:i',$v['create_time']);
				$v['create_time_ymd'] = date('Y-m-d',$v['create_time']);
				$sign_get[] = $v;
			}		
			$GLOBALS['tmpl']->assign('sign_get',$sign_get);
		/*//积分使用记录
			$sign = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_score_log where type=22 and user_id = ".$user_id." order by id desc");
			foreach ($sign as $k => $v) {
				$v['week']= week(date('N', $v['create_time']));
				$v['time'] = date('H:i',$v['create_time']);
				$sign_use[] = $v;
			}*/
			//var_dump($sign_get);die;
			$GLOBALS['tmpl']->assign('cate_title',"我的积分");
			$GLOBALS['tmpl']->assign('scores',$GLOBALS['user_info']['score']);
			$GLOBALS['tmpl']->assign('sign_use',$sign_use);
			$GLOBALS['tmpl']->display("page/points_list.html");
		}
		public function check_sign(){
			foreach($_POST as $k=>$v)
			{
				$data[$k] = htmlspecialchars(addslashes($v));
			}
			$user_id = $GLOBALS['user_info']['id'];
			$data['score'] = trim($data['sign']);
			$type = trim($data['type']);
			$category = trim($data['category']);
			$user_money = trim($data['user_money']);
			switch ($category) {
				case 'invest':
					$msg = "当天累计出借-".$user_money."元";
					break;
				case 'recharge':
					$msg = "每日充值";
					break;
				case 'share':
					$msg = "每日分享";
					break;
				case 'recharge_lend':
					$msg = "每日充值出借";
					break;
				default:
					# code...
					break;
			}
			//require_once APP_ROOT_PATH."system/libs/user.php";
			//每日分享
			if($type){
				$GLOBALS['db']->startTrans();
				$t = time();
				$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
				$end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
				//$receive_rs = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_score_log where user_id = ".$user_id." and score=".$data['score']." and type=".$type." and create_time > ".$begin_time." and create_time <".$end_time." lock in share mode");
				$receive_rs = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."user_score_log where user_id = ".$user_id." and score=".$data['score']."  and type=".$type." and create_time > ".$begin_time." and create_time <".$end_time." limit 1 for update");
				$arr=array('100','101','102','103');
				if($type==26){
					$data['score'] = 20;
					$recharge = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."payment_notice where user_id = ".$GLOBALS['user_info']['id']." and is_paid = 1 and create_time between ".$begin_time." and ".$end_time.' and cunguan_tag=0');
					if(!$recharge){
						$GLOBALS['db']->rollback();
						ajax_return(array('status'=>0,'info'=>'您未充值，请先充值'));
					}
				}elseif($type==70){
					$data['score'] = 5;
					$share_rs= $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_share_log where user_id = ".$user_id." and share_time >= ".$begin_time." and share_time <=".$end_time);
					if(!$share_rs){
						$GLOBALS['db']->rollback();
						ajax_return(array('status'=>0,'info'=>'您未分享，请先分享'));
					}
				}elseif(in_array($type,$arr)){
					$deal_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id = ".$GLOBALS['user_info']['id']." and create_time between ".$begin_time." and ".$end_time);
					if(!$deal_data){
						$GLOBALS['db']->rollback();
						ajax_return(array('status'=>0,'info'=>'您未出借，请先出借'));
					}else{
						$z_money = 0;
						foreach ($deal_data as $k => $v) {
							$deal = get_deal($v['deal_id']);
							$z_money+= $v['money'] * $deal['repay_time'] /12;//当天出借的折标金额
						}
						if($z_money<1000){
							$GLOBALS['db']->rollback();
							ajax_return(array('status'=>0,'info'=>'出借金额不足'));
						}elseif($z_money>=1000&&$type==100){
							$data['score']=40;
						}elseif($z_money>=5000&&$type==101){
							$data['score']=180;
						}elseif($z_money>=10000&&$type==102){
							$data['score']=330;
						}elseif($z_money>=50000&&$type==103){
							$data['score']=1500;
						}else{
							$GLOBALS['db']->rollback();
							ajax_return(array('status'=>0,'info'=>'请稍后重试'));
						}
						
					}
				}else{
					$GLOBALS['db']->rollback();
					ajax_return(array('status'=>0,'info'=>'请稍后重试'));
				}
			    //$receive_rs = is_receive_share_score($user_id,$type,$data['score']);
			    if($receive_rs){
			    	ajax_return(array('status'=>0,'info'=>'今日已领取'));
			    }else{
					$t= time();
			    	modify_account($data,$user_id,$msg,$type);
					//$share_rs = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_score_log where user_id = ".$user_id." and score=".$sign." and type=".$type."order by id desc limit 1");
					$t = time();
					$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
					$end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
					$share_rs = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_score_log where user_id = ".$user_id." and score=".$data['score']." and type=".$type." and create_time > ".$begin_time." and create_time <".$end_time);
					if($share_rs>=2){
						$GLOBALS['db']->rollback();
						ajax_return(array('status'=>0,'info'=>'请稍后重试！'));
					}else{
						$GLOBALS['db']->commit();
						$return["status"] = 1;
						$return["info"] = "累计".$msg.$data['score']."积分领取成功";
						ajax_return($return);
					}
			    }
			}
			
		}
		//分享成功后回调
		public function share_callback($code){
		    header('Access-Control-Allow-Origin: http://wxglcs.jiuchengjr.com');
		    $phone = floor(floatval(base64_decode($_REQUEST['code'])));
		    if(empty($phone) || !is_numeric($phone)) ajax_return(array('status'=>-1,'msg'=>'参数错误'));
		    $data['user_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where mobile=".$phone);
		    if(empty($data['user_id'])) ajax_return(array('status'=>-1,'msg'=>'参数错误'));
		    $data['share_time'] = time();
		    //查询的当日是否已分享
	        $share_rs = is_share($data['user_id']);
			$activity_id = intval($_REQUEST['activity_id']);
			$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
			$end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
			if($activity_id){
				$act_info = $GLOBALS['db']->getOne('select id from '.DB_PREFIX.'user_share_log where user_id='.$data['user_id'].' and activity_id='.$activity_id.' and share_time>'.$begin_time.' and share_time<'.$end_time);
				if($act_info){
					$rs = $GLOBALS['db']->query("update ".DB_PREFIX."user_share_log set share_count = share_count+1 where id=".$act_info);
				}else{
					$datas['share_count'] = 1;
					$datas['user_id'] = $data['user_id'];
					$datas['activity_id'] = $activity_id;
					$datas['share_time'] = TIME_UTC;
					$rs = $GLOBALS['db']->autoExecute(DB_PREFIX."user_share_log",$datas);
				}
			}else{
				$id =strim($_POST['id']);
				if($id==46){
					insert_red_log($GLOBALS['user_info']['id'],2);
				}
				//查询的当日是否已分享
				$share_rs = is_share($data['user_id']);
				if($share_rs){
					$rs = $GLOBALS['db']->query("update ".DB_PREFIX."user_share_log set share_count = share_count+1 where id=".		$share_rs['id']);
				}else{
					$data['share_count'] = 1;
					$rs = $GLOBALS['db']->autoExecute(DB_PREFIX."user_share_log",$data);
				}
			}
	        
	        if($rs){
	            $result['msg'] = '分享成功';
	            $result['status'] = '101';
	        }else{
	            $result['msg'] = '分享失败';
	            $result['status'] = '-1';
	        }
	        echo json_encode($result);exit;
		}
		public function user_red_log(){
			$user_id = $GLOBALS['user_info']['id'];
			if(!$user_id){
				app_redirect(url("index","user#login"));
			}	
			//jumpUrl("jump_url_info");
			/*移动端交互处理*/
			if($_GET['MachineInfo']){
				es_session::set('MachineInfo',$_GET['MachineInfo']);
			}
			$jump = machineInfo();
			$GLOBALS['tmpl']->assign('jump',$jump);
			$t_begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
			$t_end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
			//签到
			$user_sign = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=1');
			//分享
			$user_share = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=2');
			//抽奖
			$user_award = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=3');
			//积分兑换
			$user_score = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=4');
			//邀请好友并抢红包
			$user_invite = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and status=0 and type=5');
			$invite_count = $GLOBALS['db']->getOne('select count(*) from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=5');
			if($invite_count==3&&!$user_invite){
				$user_invite['status'] =1;
			}elseif($invite_count<=3&&$user_invite){
			}elseif($invite_count<3&&!$user_invite){
				$user_invite =0;
			}
			//出借
			$user_invest = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type in(6,7,8,9) and status=0 order by id asc limit 1');
			$user_invest_six = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=6 limit 1');
			$user_invest_seven = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=7 limit 1');
			$user_invest_eight = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=8  limit 1');
			$user_invest_nighe = $GLOBALS['db']->getRow('select id,status from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type=9  limit 1');
			if(!$user_invest_six){
				$user_invest_six['status']=0;
			}
			if(!$user_invest_seven){
				$user_invest_seven['status']=0;
			}
			if(!$user_invest_eight){
				$user_invest_eight['status']=0;
			}
			if(!$user_invest_nighe){
				$user_invest_nighe['status']=0;
			}
			$invest_count = $GLOBALS['db']->getOne('select count(*) from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and create_time>='.$t_begin_time.' and create_time<='.$t_end_time.' and type in(6,7,8,9)');
			if($invest_count==4&&!$user_invest){
				$user_invest['status'] =1;
			}elseif($invest_count<=4&&$user_invest){
			}elseif($invest_count<4&&!$user_invest){
				$user_invest =0;
			}
			//签到，分享
			$wap_nav ="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=33&phone=".$GLOBALS['user_info']['mobile'];
			//抽奖
			$award_url="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=45";
			/* //邀请好友
			$invite_url="https://jctwapcg.9caitong.com/index.php?ctl=find&act=W645&code=".$GLOBALS['user_info']['phone']."&id=".$user_id; */
			$red_packet_url="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=46&code=".$GLOBALS['user_info']['mobile'];
			//积分
			$score_url="https://jctwapcg.9caitong.com/index.php?ctl=find&act=new_mall";
			$GLOBALS['tmpl']->assign("phone",$GLOBALS['user_info']['phone']);
			$GLOBALS['tmpl']->assign("user_id",$user_id);
			$GLOBALS['tmpl']->assign('cate_title',"增加抢红包次数");
			$GLOBALS['tmpl']->assign('wap_nav',$wap_nav);
			$GLOBALS['tmpl']->assign('user_sign',$user_sign);
			$GLOBALS['tmpl']->assign('user_share',$user_share);
			$GLOBALS['tmpl']->assign('user_award',$user_award);
			$GLOBALS['tmpl']->assign('award_url',$award_url);
			$GLOBALS['tmpl']->assign('user_score',$user_score);
			$GLOBALS['tmpl']->assign('score_url',$score_url);
			$GLOBALS['tmpl']->assign('red_packet_url',$red_packet_url);
			$GLOBALS['tmpl']->assign('user_invite',$user_invite);
			$GLOBALS['tmpl']->assign('invite_url',$invite_url);
			$GLOBALS['tmpl']->assign('user_invest',$user_invest);
			$GLOBALS['tmpl']->assign('user_invest_six',$user_invest_six);
			$GLOBALS['tmpl']->assign('user_invest_seven',$user_invest_seven);
			$GLOBALS['tmpl']->assign('user_invest_nighe',$user_invest_nighe);
			$GLOBALS['tmpl']->assign('user_invest_eight',$user_invest_eight);
			$GLOBALS['tmpl']->display("page/red_packet_increase.html");
		}
		public function red_increase(){
			foreach($_POST as $k=>$v)
			{
				$data[$k] = htmlspecialchars(addslashes($v));
			}
			$user_id = $GLOBALS['user_info']['id'];
			if(!$user_id){
				app_redirect(url("index","user#login")."&jumpUrl=".url("index","score#user_red_log"));
			}
			if(!$data['id']){
				$root['status']=0;
				$root['info']="请稍后重试";
				ajax_return($root);
			}else{
				$GLOBALS['db']->startTrans();
				//查询是否存在
				$infos = $GLOBALS['db']->getRow('select status,type,increase from '.DB_PREFIX.'user_red_log where user_id='.$user_id.' and id='.$data['id'].' and status=0 for update');
				if($infos){
					$redis = new Redis();
					$redis->connect(REDIS_HOST, REDIS_PORT);
					$redis->auth(REDIS_PWD);
					$redis->select(8);
					if($infos['type']==5){//如果是分享
						$redis_user = $redis->get(REDIS_PREFIX.'share_num'.$user_id);
						$redis->setex(REDIS_PREFIX.'share_num'.$user_id,strtotime(date('Y-m-d 23:59:59'))-time(),$redis_user+$infos['increase']);
					}else{
						$redis_user = $redis->get(REDIS_PREFIX.'reward'.$user_id);
						if($redis_user){
							$redis->setex(REDIS_PREFIX.'reward'.$user_id,strtotime(date('Y-m-d 23:59:59'))-time(),$redis_user+$infos['increase']);
						}else{
							$increase =$redis->setex(REDIS_PREFIX.'reward'.$user_id,strtotime(date('Y-m-d 23:59:59'))-time(),$infos['increase']);
						}
					}
					
					$GLOBALS['db']->query('update '.DB_PREFIX.'user_red_log set status=1,update_time='.time().' where id='.$data['id']);
					$GLOBALS['db']->commit();
					$root['status']=1;
					$root['info']=$infos['increase'];
					ajax_return($root);
				}else{
					$GLOBALS['db']->rollback();
					$root['status']=0;
					$root['info']="您已领取过了";
					ajax_return($root);
				}
			}
		}
		//新版签到
		public function signs(){
			$user_id = $GLOBALS['user_info']['id'];
			if(!$user_id){
				app_redirect(url("index","user#login"));
			}	
			if($_GET['MachineInfo']){
				es_session::set('MachineInfo',$_GET['MachineInfo']);
			}
			$jump = machineInfo();
			$GLOBALS['tmpl']->assign('jump',$jump);
			$start_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
			$end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
			$user_sign = $GLOBALS['db']->getRow('select id,sign_count,status from '.DB_PREFIX.'user_sign_log where user_id='.$user_id.' and sign_date>='.$start_time.' and sign_date<='.$end_time.' limit 1');
			$score = $GLOBALS['user_info']['score']?$GLOBALS['user_info']['score']:0;
			$GLOBALS['tmpl']->assign('score',$score);
			$GLOBALS['tmpl']->assign('user_sign',$user_sign);
			$GLOBALS['tmpl']->assign('cate_title',"每日签到");
			$GLOBALS['tmpl']->display('page/sign.html');
		}//新版签到
		public function do_sign(){
			$user_id = $GLOBALS['user_info']['id'];
			if(!$user_id){
				app_redirect(url("index","user#login"));
			}	
			foreach($_POST as $k=>$v)
			{
				$data[$k] = intval(htmlspecialchars(addslashes($v)));
			}
			if(!$data['showYear']||!$data['showMonth']){
				$sign_data = false;
				$res['signList'] =$sign_data;
				echo json_encode($res);
			}
			//当前月份
			$date = $data['showYear']."-".$data['showMonth'].'-1';
			//当前月份天数
			$days = date('t',strtotime($date));
			$end = $data['showYear']."-".$data['showMonth']."-".$days." 23:59:59";
			$start_time = strtotime($date);
			$end_time = strtotime($end);
			$sign_data = $GLOBALS['db']->getAll("select FROM_UNIXTIME(create_time,'%e') as signDay from ".DB_PREFIX."user_score_log where type=25 and user_id=".$user_id." and create_time>=$start_time and create_time<=$end_time");
			if(!$sign_data){
				$sign_data=false;
			}
			$res['signList'] =$sign_data;
			echo json_encode($res);
		}
		public function get_award(){
			$user_id = $GLOBALS['user_info']['id'];
			if(!$user_id){
				app_redirect(url("index","user#login"));
			}	
			foreach($_POST as $k=>$v)
			{
				$data[$k] = intval(htmlspecialchars(addslashes($v)));
			}
			/* $arr = array(3,7,14,30);
			if(!in_array($data['type'],$arr)){
				$res['status'] = 0;
				$res['info'] ="非法访问";
			} */
			if(!$data['id']){
				$res['status'] = 0;
				$res['info'] ="异常";
				echo json_encode($res);exit;
			}
			$start_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
			$end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
			$GLOBALS['db']->startTrans();
			$sign_count = $GLOBALS['db']->getRow('select sign_count,id,status from '.DB_PREFIX.'user_sign_log where user_id='.$user_id.'  and sign_date>='.$start_time.' and sign_date<='.$end_time.' and id='.$data['id'].' for update ');
			if(!$sign_count){
				$GLOBALS['db']->rollback();
				$res['status'] = 0;
				$res['info'] ="您访问的数据异常";
				echo json_encode($res);exit;
			}
			if($sign_count['status']==1){
				$GLOBALS['db']->rollback();
				$res['status'] = 0;
				$res['info'] ="奖励已领取";
				echo json_encode($res);exit;
			}
			
			/* if($sign_count['sign_count']!=$data['type']){
				$res['status'] = 0;
				$res['info'] ="您未达到领取时间";
				echo json_encode($res);exit;
			} */
			$GLOBALS['db']->query('update '.DB_PREFIX.'user_sign_log set status=1,update_time='.time().' where id='.$sign_count['id']);
			if($sign_count['sign_count']==14){
				$datas['user_id']=$user_id;
				$datas['use_limit']=7;
				$datas['begin_time']=time();
				$datas['end_time']=strtotime("+7 day",strtotime(date("Y-m-d 00:00:00")))-1;
				$datas['red_type_id']=11;
				$datas['money']=15;
				$datas['content']="签到红包";
				$datas['packet_type']=1;
				$datas['create_time']=time();
				$GLOBALS['db']->autoExecute(DB_PREFIX.'red_packet',$datas);
				$res['text'] ="连续签到14天～";
				$res['info'] ="获得15元出借红包～";
			}elseif($sign_count['sign_count']==30){
				$redis = new Redis();
				$redis->connect(REDIS_HOST, REDIS_PORT);
				$redis->auth(REDIS_PWD);
				$redis->select(8);
				$redis_user = $redis->get(REDIS_PREFIX.'reward'.$user_id);
				if($redis_user){
					$redis->setex(REDIS_PREFIX.'reward'.$user_id,strtotime(date('Y-m-d 23:59:59'))-time(),$redis_user+10);
				}else{
					$increase =$redis->setex(REDIS_PREFIX.'reward'.$user_id,strtotime(date('Y-m-d 23:59:59'))-time(),10);
				}
				$res['text'] ="连续签到30天～";
				$res['info'] ="获得10次抢红包机会～";
			}else{
				$GLOBALS['db']->rollback();
				$res['status'] = 0;
				$res['info'] ="请稍后重试";
				echo json_encode($res);exit;
			}  
			$GLOBALS['db']->commit();
			$res['status'] = 1;
			echo json_encode($res);exit;
		}
	}
?>

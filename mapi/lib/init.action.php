<?php
class init{
	public function index()
	{
		$root = get_baseroot();
		$root['response_code'] = 1;
		$url = WAP_SITE_DOMAIN;
		$root['sign'] = $url."/index.php?ctl=score&act=earn_points";
		$root['session_id'] = es_session::id();
        $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
        if($MachineInfo[0]=='iOS'){
            $cunguan_status = $GLOBALS['db']->getOne("select cunguan_status from ".DB_PREFIX."switch_conf where status=1 and is_del=1 and switch_id=9 limit 1");
            if($cunguan_status==1){
                $root['default_status'] = $GLOBALS['db']->getOne("select default_status from ".DB_PREFIX."switch_conf where status=1 and is_del=1 and switch_id=9 limit 1");
            }
        }
        $user = $GLOBALS['user_info'];
        if(intval($user['id'])>0&&$user['user_type']=='1'){
        	$root['cunguan_status']='3';
        }
		$stats = site_statics();
		$statistics = $GLOBALS['db']->getAll("select id,title,fake_data,icon from ".DB_PREFIX."statistics_conf where is_effect =1");
		foreach ($statistics as $k=>$v){
		    $arr[] = $v ;
        }
		$root['statistics'] = $arr;
		$root['register_title'] = "注册用户（人）";
		$root['registered_user'] = strip_tags($stats['user_count']);//注册用户数;
		//$root['total_load'] =  strip_tags(number_format(intval($stats['total_load'])));  //累计出借
		$root['total_load_title'] = "累计出借（元）";
		$root['total_load'] =  strip_tags(intval($stats['total_load']));  //累计出借
		$root['kefu'] = array('400-650-8706');
		$root['kefu_time'] = '工作日8:30-22:00，周末9:00-22:00';
		$root['kefu2']='400-650-8706';
		$index_list = $GLOBALS['cache']->get("MOBILE_INDEX_ADVS");
		if($index_list===false)
		{
// 			//首页轮播图片
// 			$advs = $GLOBALS['db']->getAll(" select id,app_page,img,name,type,url from ".DB_PREFIX."app_nav_cg where is_effect =1 and device != 1 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc ");
// 			$adv_list = array();
// 			foreach($advs as $k=>$v)
// 			{
//                 $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
//                 if($MachineInfo[0]=='iOS'){
//                     if($MachineInfo[3]<'2.1.2'){
//                         if($v['app_page']==14){
//                             unset($advs[$k]);
//                             continue;
//                         }

//                     }
//                 }elseif($MachineInfo[0]=='Android'){
//                     if($MachineInfo[1]<'2.1.2'){
//                         if($v['app_page']==14){
//                             unset($advs[$k]);
//                             continue;
//                         }

//                     }
//                 }
// 				if ($v['img'] != ''){
// 					$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));
// 				}
// 				if($v['type']==2){
// 					$v['url']=$GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$v['app_page']);
// 				}
// 				if($GLOBALS['request']['MachineInfo']){
// 					$MachineInfo = explode("|||",$GLOBALS['request']['MachineInfo']); //设备信息
// 	            	$v['url'].="&MachineInfo=iOS";
// 	            }
// 				$adv_list[] = $v;
// 			}
            $adv_list[] = array('app_page'=>1,'id'=>39,'img'=>'https://oss.9caitong.com/Ossimg/2017/11/10/18/5a057ec8c8e8c.jpg','name'=>'抢幸运数字，发翻倍红包，赢双11好礼','type'=>1,'url'=>'https://wapcg.9caitong.com/index.php?ctl=uc_set&act=news&id=162&device=1&MachineInfo=iOS');
            $adv_list[] = array('app_page'=>14,'id'=>33,'img'=>'https://oss.9caitong.com/Ossimg/2017/09/07/23/59b161166ee71.jpg','name'=>'燃爆红包 即刻开抢','type'=>2,'url'=>'&MachineInfo=iOS');
            $adv_list[] = array('app_page'=>1,'id'=>38,'img'=>'https://oss.9caitong.com/Ossimg/2017/10/24/18/59ef1129d25d5.png','name'=>'全民一起来健身','type'=>1,'url'=>'https://wapcg.9caitong.com/index.php?ctl=find&act=W650&MachineInfo=iOS');
            $adv_list[] = array('app_page'=>1,'id'=>36,'img'=>'https://oss.9caitong.com/Ossimg/2017/10/20/10/59e95ed9bc1fa.jpg','name'=>'9积分大转盘','type'=>1,'url'=>'https://wapcg.9caitong.com/index.php?ctl=find&act=W651&MachineInfo=iOS');
            $adv_list[] = array('app_page'=>1,'id'=>24,'img'=>'https://oss.9caitong.com/Ossimg/2017/07/14/16/59687e51013f0.jpg','name'=>'新手福利','type'=>1,'url'=>'https://wapcg.9caitong.com/index.php?ctl=find&act=W642&MachineInfo=iOS');
            $adv_list[] = array('app_page'=>1,'id'=>25,'img'=>'https://oss.9caitong.com/Ossimg/2017/07/17/09/596c19150e352.jpg','name'=>'新手指引','type'=>1,'url'=>'https://wap.9caitong.com/index.php?ctl=find&act=W_Contract_depository_cgzy&MachineInfo=iOS');
            $adv_list[] = array('app_page'=>1,'id'=>23,'img'=>'https://oss.9caitong.com/Ossimg/2017/07/14/16/59687cb6cdcd4.jpg','name'=>'存管银行','type'=>1,'url'=>'https://wapcg.9caitong.com/index.php?ctl=about&act=yindaoye&MachineInfo=iOS');
			$index_list['adv_list'] = $adv_list;

			//输出公告
			$index_list['notice_list'] = get_mobile_notice(0);
			foreach ($index_list['notice_list'] as $k=>$v){
				$index_list['notice_list'][$k]['url'] = $url.'/member.php?ctl=uc_set&act=news&id='.$v['id'];
			}
			/****最新一条消息***/
			$news_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."app_msg  WHERE  is_effect = 1  order by id desc limit 0,1");
			$root['news_id'] = $news_id;
			//===========特别推荐================
			//publish_wait 0:已审核 1:等待审核;deal_status 0待等材料，1进行中，2满标，3流标，4还款中，5已还清
			//根据用户出借情况推荐标的，推荐条件后台可配置
			//未出借用户：推荐新手标
			//只出借1笔的用户：推荐3月标
			//多笔出借，出借总额不满10万的用户：推荐6月、12月标
			//多笔出借，出借总额不满10万的用户：推荐秒杀标
			$uid = $GLOBALS['user_info']['id'];
			if($uid > 0){
				/*用户登录*/
				$is_deal =$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 and deal_status = 1 and type_id !=12 limit 1");//查询是否有借款中的项目
				$deal = $GLOBALS['db']->getRow("SELECT count(money) as num,sum(money) as money FROM ".DB_PREFIX."deal_load WHERE cunguan_tag=1 and user_id = ".$uid." order by id ASC limit 1"); //查询用户出借状态
				if($deal['num'] == 0){
					// 未出借用户：优先推荐新手标 未有新手标 出借期限从小到大
					$is_new =$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and type_id !=12 and is_new = 1 and deal_status = 1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 limit 1"); //查询是否存在新手标
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
				$is_deal =$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 and deal_status = 1 and type_id !=12 limit 1");//查询是否有借款中的项目
				$is_new =$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal WHERE cunguan_tag=1 and type_id !=12 and is_new = 1 and deal_status = 1 and is_effect=1 and is_delete=0 and publish_wait=0 and is_hidden=0 limit 1 "); //查询是否存在新手标
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
			$beginner = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."beginner_welfare where status =1 limit 1");
			if($beginner && !$uid){
				$beginner_list['is_beginner'] = 1;
				$beginner_list['img'] =  get_abs_img_root(get_spec_image($beginner['page_path'],0,0,1));
				$beginner_list['url'] =  $beginner['url'];
			}else{
				$beginner_list['is_beginner'] = 0;
			}
			$root['beginner'] = $beginner_list; //活动推广图片
			require APP_ROOT_PATH.'app/Lib/deal.php';
			$limit = "0,1";
			$result = get_deal_list($limit,0,$condition,$orderby);
			foreach ( $result ['list'] as $m => $v )
			{
				if($v['is_new'] == 1){
					$result ['list'][$m]['type'] ="新手专享";
				}else{
					$result ['list'][$m]['type'] ="热门推荐";
				}
				$deal = $GLOBALS['db']->getRow("SELECT count(money) as num,sum(money) as money FROM ".DB_PREFIX."deal_load WHERE cunguan_tag=1 and user_id = ".$uid." order by id ASC limit 1");
				if($deal['num'] > 0){
					$result['list'][$m]['is_deal'] = "1";
				}else{
					$result['list'][$m]['is_deal'] = "0";
				}
				if($v['debts'] == 1){
					$last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$v['old_deal_id']." order by repay_time desc limit 1");
					$remin = strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time()));
					if($remin>0){
						$v['repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
					}else{
						$v['repay_time']=0;
					}
				}
				if($v['repay_time_type'] == 0){
					$result['list'][$m]['repay_time_dm'] = $v['repay_time']."天";
				}else{
					$result['list'][$m]['repay_time_dm'] = $v['repay_time']."个月";	
				}
				$result['list'][$m]['min_loan_money'] = "起投金额:".$v['min_loan_money']."元";
				$result ['list'][$m]['rate'] = sprintf("%.1f",$v["rate"]);
				$cate_info_icon = get_abs_wap_url_root(get_abs_img_root($result['list'][$m]['cate_info']['icon']));
				$result ['list'][$m]['cate_info']['icon'] = $cate_info_icon;
				$result ['list'][$m]['need_money'] = intval($v['need_money']);
				$result ['list'][$m]['information_status'] = $GLOBALS['db']->getOne("select information_status from ".DB_PREFIX."deal_loan_type where id=".$v['type_id']);

			}
			$index_list['rec_deal_list'] = $result['list'];
			$GLOBALS['cache']->set("MOBILE_INDEX_ADVS",$index_list);
		}
		$root['index_list'] = $index_list;
		//活动推送
		$activity = $GLOBALS['db']->getRow("select id,app_page,img,name,type,url from ".DB_PREFIX."app_popup_cg where is_effect =1 and position=1 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
		//活动不存在时
		if($activity){
			$act_list = $activity;
			$act_list['is_code'] = 1;
			$act_list['img'] =  get_abs_img_root(get_spec_image( $activity['img'],0,0,1));
            if($activity['type']==2){
                $act_list['url'] = $GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$activity['app_page']);
            }
		}else{
			$act_list['is_code'] = 0;
		}
		$root['activity'] = $act_list; //活动推广图片
		$startup = $GLOBALS['db']->getRow(" select * from ".DB_PREFIX."app_splash_screen_cg where is_effect = 1 order by sort desc limit 1");
		$root['startup'] = get_abs_img_root(get_spec_image($startup['img'],0,0,1));
		$root['webview'][] = $GLOBALS['db']->getRow("select name,height,is_effect,url from ".DB_PREFIX."app_web_view where name='首页' limit 1");
		$icons = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."app_cloumn_cg where clo_type = 'index' order by id ASC ");
		$todayBegin = strtotime(date("Y-m-d")."00:00:00"); 	//今天开始时间
		$todayEnd = strtotime(date("Y-m-d")."23:59:59"); 	//今天开始结束
		$sign_count_day = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_score_log where type = 25 and create_time >=".$todayBegin." and create_time < ".$todayEnd." limit 1");

        $hour = date('H');
        if($hour > 3){
            $sign_count_day = "931" + $sign_count_day;
        }


		$security_list = array();
		foreach($icons as $k=>$v)
		{
			if($v['id'] == 3){
				//$sign_count_day = $v['title'] + $sign_count_day;
				$v['title'] = "当日签到:".$sign_count_day."人";
			}
			$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));
            if($v['type']==2){
                $v['url']=$GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$v['app_page']." limit 1");
            }
			$security_list[] = $v;
		}
		$root['middle_menu'] = $security_list; //首页栏目图标

        /* 新消息 更新 做红点标记 2017-12-11 */
        $seventime =time() +7*86400;
        $new_invest_record_id =$GLOBALS['db']->getOne("SELECT dl.id FROM ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal as d ON dl.deal_id =d.id WHERE dl.user_id=".$user['id']."  and d.deal_status =1 order by dl.create_time desc limit 1");
        $new_borrow_manager_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."deal_repay where user_id =".$user['id']. " and has_repay in(0,3) order by id desc limit 1");
        $borrow_manager_alert =$GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."deal_repay where  user_id =".$user['id']. " and repay_time <=$seventime and has_repay in(0,3) ");
        $new_cash_red_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."red_packet where user_id =".$user['id'] ." and  packet_type=3 order by id desc limit 1");
        $new_coupon_red_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."red_packet where user_id =".$user['id']." and  packet_type=1 order by id desc limit 1");
        $new_jiaxi_card_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."interest_card where user_id =".$user['id']. " order by id desc limit 1");
        $new_notification_id =$GLOBALS['db']->getOne("SELECT a.id,a.title,ac.title as name,ac.icon,a.content,a.create_time,a.img FROM ".DB_PREFIX."app_msg_cate ac left join ".DB_PREFIX."app_msg a on a.cate_id=ac.id WHERE  a.is_effect = 1 and a.is_delete=0 and ac.id=37  order by id desc limit 1");
        $new_report_id =$GLOBALS['db']->getOne("SELECT a.id,a.title,ac.title as name,ac.icon,a.content,a.create_time,a.img FROM ".DB_PREFIX."app_msg_cate ac left join ".DB_PREFIX."app_msg a on a.cate_id=ac.id WHERE  a.is_effect = 1 and a.is_delete=0 and ac.id=38  order by id desc limit 1 ");
        $new_dynamic_id =$GLOBALS['db']->getOne("SELECT a.id,a.title,ac.title as name,ac.icon,a.content,a.create_time,a.img FROM ".DB_PREFIX."app_msg_cate ac left join ".DB_PREFIX."app_msg a on a.cate_id=ac.id WHERE  a.is_effect = 1 and a.is_delete=0 and ac.id=36  order by id desc limit 1");

        $root['redpoint_array']['new_invest_record_id']=$new_invest_record_id;
        $root['redpoint_array']['new_borrow_manager_id']=$new_borrow_manager_id;
        if($borrow_manager_alert){
            $root['redpoint_array']['borrow_manager_alert']= implode(",",$borrow_manager_alert);
        }else{
            $root['redpoint_array']['borrow_manager_alert']= "";
        }
        $root['redpoint_array']['new_cash_red_id']=$new_cash_red_id;
        $root['redpoint_array']['new_coupon_red_id']=$new_coupon_red_id;
        $root['redpoint_array']['new_jiaxi_card_id']=$new_jiaxi_card_id;
        $root['redpoint_array']['new_notification_id']=$new_notification_id;
        $root['redpoint_array']['new_report_id']=$new_report_id;
        $root['redpoint_array']['new_dynamic_id']=$new_dynamic_id;
        /* 新消息 更新 做红点标记 2017-12-11   结束*/
        $ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips_cg where code = 1 limit 1" );
		$ips['img'] = get_abs_img_root(get_spec_image($ips['img'],0,0,1));
		if($ips['type']==2){
		    $ips['url'] = $GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$ips['app_page']." limit 1");
        }
        $ips['warningText'] = "市场有风险，出借需谨慎";
		$root['ips'][] = $ips;


        //活动广告位
        $hd =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_hpad where is_effect=1 limit 1" );
        if($hd){
            $hd['code'] = 1;
            if($hd['type'] == 2){
                $hd['url'] = $GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal_attribute where is_effect=1 and id=".$ips['app_page']." limit 1");
            }
        }else{
            $hd['code'] = 0;
        }

        $root['hd']= $hd;
		/*//获取移动端设备信息 存入session
		if($GLOBALS['request']['MachineInfo']){
			es_session::set('MachineInfo',base64_decode($GLOBALS['request']['MachineInfo']));
			$GLOBALS['MachineInfo'] = es_session::get('MachineInfo');
			if($GLOBALS['MachineInfo'] != base64_decode($GLOBALS['request']['MachineInfo'])){
				es_session::delete('MachineInfo');
			}
		}*/
		$root['about']['yuhuxuzhi'] = WAP_SITE_DOMAIN."/member.php?ctl=uc_set&act=userxuzhi";
		$root['about']['yunying'] = WAP_SITE_DOMAIN."/member.php?ctl=find&act=p201703";
		$root['about']['guanyu'] = WAP_SITE_DOMAIN."/member.php?ctl=uc_set&act=aboutus";
		$root['about']['video'] = WAP_SITE_DOMAIN."/index.php?ctl=find&act=wap_video";
		/*$root['about']['video'] = "https://wapcg.9caitong.com/index.php?ctl=find&act=wap_video";*/

		$root['kaiguan'] = 1;//动画开关
		output($root);
	}
}
?>
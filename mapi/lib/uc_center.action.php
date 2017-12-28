<?php
class uc_center
{
	public function index(){
		$weixin_header_url = trim(base64_decode($GLOBALS['request']['weixin_header_url']));
		$root = get_baseroot();
		$user =  $GLOBALS['user_info']; //user_check($email,$pwd);
		$root['session_id'] = es_session::id();
		$root['mobile'] = $GLOBALS['user_info']['mobile'];
		$user_id  = intval($user['id']);
		if ($user_id >0){

			//风险评估
			$user_score = $GLOBALS['db']->getOne("select score from ".DB_PREFIX."wenjuan_user_answer_record where user_id=".$user_id);
			if($user_score){
				if($user_score >=12 && $user_score <=24){
					$root['user_score'] = "保守型";
				}elseif($user_score >=25 && $user_score <=36){
					$root['user_score'] = "稳健型";
				}elseif($user_score >=37 && $user_score <=60){
					$root['user_score'] = "积极型";
				}
			}else{
				$root['user_score'] = "";
			}
			require_once APP_ROOT_PATH."system/user_level/Level.php";
        	$level=new Level();
        	$level_data=$level->get_user_vip_level($user_id);
        	$root['user_level']="V".$level_data['user_level']."会员";
        	$root['url']=WAP_SITE_DOMAIN.'/member.php?ctl=memberlevel';
			require APP_ROOT_PATH.'app/Lib/uc.php';
			$user_statics = get_user_money_info($user_id);
			require_once APP_ROOT_PATH."system/libs/user.php";
			$edition = $GLOBALS['db']->getRow("select edition,header_url from ".DB_PREFIX."user where id = ".$user_id);
			if(!$edition['header_url'] && !empty($weixin_header_url)){
                $GLOBALS['db']->query("update ".DB_PREFIX."user set header_url='".$weixin_header_url."' where id = ".$user['id']);
			}
			//查询是否存管绑卡
			$bkinfos = $GLOBALS['db']->getRow("select bankcard from ".DB_PREFIX."user_bank where user_id = ".$user_id." and status=1 and cunguan_tag = 1");
			if($user['cunguan_tag'] == 0){
				//存管未开户
				$root['three_go_code'] = 1;
				$root['three_go_msg']='您有信息尚未填写完整，是否前去填写？';
				$root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
			}else if($user['cunguan_pwd'] == 0 && $user['cunguan_tag'] == 1){
				//存管未设置交易密码
				$root['three_go_code'] = 2;
				$root['three_go_msg']='您有信息尚未填写完整，是否前去填写？';
				$root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=setpaypassword';
			}else if(($user['cunguan_tag'] == 1 && $user['cunguan_pwd'] == 1 && !$bkinfos)&&!$user['user_type']=='1'){
				//存管未绑卡
				$root['three_go_code'] = 3;
				$root['three_go_msg']='您有信息尚未填写完整，是否前去填写？';
				$root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=wap_check_pwd';
				$root['change_paypwd'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=changepaypassword';
				$root['reset_paypwd'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=resetpaypassword';
			}else{
				$root['three_go_code'] = 4;
				$root['three_go_msg']='';
				$root['three_go_url'] ='';
				if ($user['user_type']=='1') {
					$root['change_paypwd'] = WAP_SITE_DOMAIN.'/member.php?ctl=find&act=warning';
					$root['reset_paypwd'] = WAP_SITE_DOMAIN.'/member.php?ctl=find&act=warning';
				}else{
					$root['change_paypwd'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=changepaypassword';
					$root['reset_paypwd'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=resetpaypassword';
				}
				
			}

			/********************添加代码三步走判断结束***************************/
			//充值,提现H5连接
			$root['recharge_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_money&act=incharge';
			$root['cash_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_money&act=bank';
			$root['risk_url'] = WAP_SITE_DOMAIN.'/index.php?ctl=agreement&act=questionnaire';
			if($edition['edition'] == 0){
				$root['edition'] = "0";
			}else{
				$root['edition'] = "1";
			}
			$root['user_login_status'] = 1;
			//总资产
			$root['all'][0]['user_money'] = sprintf('%.2f',$user["money"]+$user['cunguan_money']);	//可用余额
			$root['all'][0]['user_money_format'] = format_price(sprintf('%.2f',$user["money"]+$user['cunguan_money']));//用户金额
			$root['all'][0]['total_money'] = sprintf('%.2f',$user_statics["total_money"]+$user_statics["cunguan_total_money"]);  //总金额  		总资产
			$root['all'][0]['yesterday_invert'] = sprintf('%.2f',$user_statics["load_wait_earnings"]+$user_statics["cunguan_load_wait_earnings"]);  //昨日金额    代收收益
			$root['all'][0]['cum_money'] = sprintf('%.2f',$user_statics["load_repay_money"]+$user_statics["cunguan_load_repay_money"]); //累计收益      已收收益
			//存管账户
			$root['all'][1]['user_money'] = sprintf('%.2f',$user["cunguan_money"]);
			$root['all'][1]['user_money_format'] = format_price(sprintf('%.2f',$user["cunguan_money"]));//用户金额
			$root['all'][1]['total_money'] = $user_statics["cunguan_total_money"];  //总金额
			$root['all'][1]['yesterday_invert'] = $user_statics["cunguan_load_wait_earnings"];  //昨日金额
			$root['all'][1]['cum_money'] = $user_statics["cunguan_load_repay_money"]; //累计收益
			//普通账户
			$root['all'][2]['user_money'] = sprintf('%.2f',$user["money"]);
			$root['all'][2]['user_money_format'] = format_price(sprintf('%.2f',$user["money"]));//用户金额
			$root['all'][2]['total_money'] = $user_statics["total_money"];  //总金额
			$root['all'][2]['yesterday_invert'] = $user_statics["load_wait_earnings"];  //昨日金额
			$root['all'][2]['cum_money'] = $user_statics["load_repay_money"]; //累计收益

			$root['bank_card'] = $bkinfos['bankcard']?$bkinfos['bankcard']:'';
			//是否开启自动投标
			$autoinvest_count = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."auto_invest_config where user_id=".$user_id." and status=1 and is_delete=0");
			$root['is_entrust'] = count($autoinvest_count) > 0 ? 1 : 0;
			/**************失效的代码******************/									
			//签到数据
			$t_begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
			$t_end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
			$y_begin_time = $t_begin_time - (24*3600); //昨天开始
			$y_end_time = $t_end_time - (24*3600);  //昨天结束
			$group_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_group where id = ".$user['group_id']." ");
			$user['group_name'] = $group_name ?$group_name:"";
			$root['vip_id'] = $user['vip_id'];
			$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$t_begin_time." and ".$t_end_time);
			if($t_sign_data)
			{			
				$root['t_sign_data'] = $t_sign_data;
			}
			if($user['vip_id'] == 0){
				$user['vip_grade'] = "您还不是VIP会员";
			}else{
				$user['vip_grade'] = $GLOBALS['db']->getOne("select vip_grade from ".DB_PREFIX."vip_type where id = ".$user['vip_id']." ");
			}
			
			$credit_info =  $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_credit_file where type='credit_identificationscanning' and user_id =".$user_id);
			if($credit_info){
				if($credit_info['status'] == 0){
					$root['credit_status'] = 2;
					$root['credit_show'] = "待审核";
				}elseif($credit_info['status'] == 1){
					if($credit_info['passed'] == 1){
						$root['credit_status'] = 1;
						$root['credit_show'] = "已认证";
					}elseif($credit_info['passed'] == 2){
						$root['credit_status'] = 3;
						$root['credit_show'] = "审核失败";
					}
				}
			}else{
				$root['credit_status'] = 0;
				$root['credit_show'] = "未认证";
			}
						
			$province_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$user['province_id']);
			$city_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$user['city_id']);
			if($province_str.$city_str=='')
				$user_location = $GLOBALS['lang']['LOCATION_NULL'];
			else
				$user_location = $province_str." ".$city_str;
			
			$user['user_location'] = $user_location;
			$user['money_format'] = format_price($user['money']);//可用资金			
			$user['lock_money_format'] = format_price($user['lock_money']);//冻结资金			
			//$user['total_money'] = strval($user['money'] + $user['lock_money']);//资金余额
			$user['total_money_format'] = format_price($user['total_money']);//资金余额					
			$user['create_time_format'] = to_date($user['create_time'],'Y-m-d'); //注册时间
			
			$root['id'] = $user_id;
			$root['response_code'] = 1;
			$root['vip_grade'] = $user['vip_grade'];
			$root['user_location'] = $user['user_location'];
			$root['user_name'] = $user['user_name'];
			$root['group_name'] = $user['group_name'];
			$root['money_format'] = $user['money_format'];
			$root['money'] = $user['money'];
			$root['lock_money_format'] = $user['lock_money_format'];
			$root['lock_money'] = $user['lock_money'];
			//$root['total_money'] = $user['total_money'];
			$root['total_money_format'] = $user['total_money_format'];
			$root['create_time_format'] = $user['create_time_format'];
			$root['score'] = $user['score'];
			$root['idno'] = $user['idno']?$user['idno']:"";
			$idcard=$user['idno']?$user['idno']:"";
			$address_info=$GLOBALS['db']->getRow("select profession,graduation,sex from ".DB_PREFIX."address where user_id=".$user_id);
			if($idcard){
			$root['gender']= substr($idcard, (strlen($idcard)==15 ? -1 : -2), 1) % 2 ? '男' : '女'; //1为男 2为女
// 				$root("gender")="男";
			}else if(!empty($address_info[sex])){
				if($address_info[sex]==1){
					$root['gender']='男';
				}else{
					$root['gender']='女';
				}
			}else{
				$root['gender']="";
			}
			if($user['user_type']=='1'){
				$root['real_name'] = ' ';
				$root['idno'] ="              ";
			}else{
				$root['real_name'] = $user['real_name']?$user['real_name']:"";
			}
			
			$root['header_url'] = $user['header_url']?$user['header_url']:"";
			$root['point'] = $user['point'];
			$root['quota'] = $user['quota'];
			//学位
			if(!empty($address_info['graduation'])){
				$root['degree']=$address_info['graduation'];
			}else{
				$root['degree']='';
			}
			//职业
			if(!empty($address_info['profession'])){
				$root['profession']=$address_info['profession'];
			}else{
				$root['profession']="";
			}
			
			$shipping_address= APP_ROOT.'/index.php?ctl=find&act=mall_address_app&id=22&goods_id=106&app_tag=mobile';
			$root['shipping_address'] = str_replace("/mapi", "", WAP_SITE_DOMAIN.$shipping_address);
			//我的红包
			$root['voucher_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."ecv WHERE user_id=".$user_id." AND if(end_time > 0, (end_time+24*3600-1) > ".TIME_UTC.",1=1) AND if(use_limit > 0,(use_limit - use_count) > 0,1=1)");
		
			//我的加息券
			$root['interestrate_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."interestrate i left join  ".DB_PREFIX."interestrate_type it on i.ecv_type_id = it.id WHERE ((i.user_id=".$user_id." and i.to_user_id = 0) or i.to_user_id = ".$user_id.") AND if(i.end_time > 0, (i.end_time+24*3600-1) > ".TIME_UTC.",1=1) AND if(i.use_limit > 0,(i.use_limit - i.use_count) > 0,1=1) and it.use_type = 1");
		
			
			if(intval(app_conf("OPEN_IPS")) > 0){
				$app_url = APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$user_id."&from=".base64_decode($GLOBALS['request']['from']);
				//申请
				$root['app_url'] = str_replace("/mapi", "", WAP_SITE_DOMAIN.$app_url);
				$root['acct_url'] = $root['app_url'];				
			}		
			
			$root['ips_acct_no'] = $user['ips_acct_no'];
			$root['open_ips'] = intval(app_conf("OPEN_IPS"));
			
			//第三方托管标
			if (!empty($user['ips_acct_no']) && intval(app_conf("OPEN_IPS")) > 0){
				$result = GetIpsUserMoney($user_id,0);
					
				$root['ips_balance'] = $result['pBalance'];//可用余额
				$root['ips_lock'] = $result['pLock'];//冻结余额
				$root['ips_needstl'] = $result['pNeedstl'];//未结算余额
			}else{
				$root['ips_balance'] = 0;//可用余额
				$root['ips_lock'] = 0;//冻结余额
				$root['ips_needstl'] = 0;//未结算余额
			}

			/****最新一条消息***/
			$news_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."app_msg  WHERE  is_effect = 1  order by id desc limit 0,1");
			$root['news_id'] = $news_id;
			/*********格式化数据********/
			$root['ips_balance_format'] = strip_tags(number_format(floatval($root['ips_balance']),2)); 
			$root['ips_lock_format'] = strip_tags(number_format(floatval($root['ips_lock']),2));
			$root['ips_needstl_format'] = strip_tags(number_format(floatval($root['ips_needstl']),2));
			$root['webview'] = $GLOBALS['db']->getRow("select name,height,is_effect,url from ".DB_PREFIX."app_web_view where name='我的'");
			$root['program_title'] = "会员中心";
			/**************失效的代码******************/

			/* 新消息 更新 做红点标记 2017-12-11 */
			$seventime =time() +7*86400;
			$new_invest_record_id =$GLOBALS['db']->getOne("SELECT dl.id FROM ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."deal as d ON dl.deal_id =d.id WHERE dl.user_id=".$user['id']."  and d.deal_status =1 order by dl.create_time desc limit 1");
			$new_borrow_manager_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."deal_repay where user_id =".$user['id']. "  and has_repay in(0,3) order by id desc limit 1");
			$borrow_manager_alert =$GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."deal_repay where  user_id =".$user['id']. " and repay_time <=$seventime  and has_repay in(0,3)");
			$new_cash_red_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."red_packet where user_id =".$user['id'] ." and  packet_type=3  order by id desc limit 1");
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




		}else{
			// //栏目菜单
			// $column[] = array("title"=>"头像","icon"=>$url."/app/Tpl/wap/images/wap2/my/head_img.png");
			// $group = intval($GLOBALS['db']->getOne(" select id from ".DB_PREFIX."icon_group where name = '我的' "));
			// $icons = $GLOBALS['db']->getAll(" select name,title,url,img,sort,type from ".DB_PREFIX."icon where is_effect = 1 and group_id =".$group." order by id ASC ");
			// $column = array();
			// foreach($icons as $k=>$v)
			// {
			// 	if ($v['img'] != ''){
			// 		$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));
			// 		$column[] = $v;	
			// 	}
			// }
			// $root['column'] = $column;
			$root['response_code'] = 1;
			//$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
			$root['program_title'] = "登录";
		}
		
		output($root);		
	}
}
?>

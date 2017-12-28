<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class experience_ok
{
	public function index(){

		$root = array();
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;	
			$gold_id = intval(base64_decode($GLOBALS['request']['id']));//体验金的id
        	$id= intval(base64_decode($GLOBALS['request']['pid'])); //标的id
        	//$gold_money=$_REQUEST['ebidmoney'];//体验金的钱 
        	$bbin_money =$GLOBALS['db']->getRow("select sum(money)as money from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and use_status = 0  and end_time >" . time() . " and user_id = " . intval($user_id)." and id in (".$gold_id.")");
			$deal_id = intval($id);  //标的id	
			$deal =exper_deal($deal_id);
	
            //先开通存管用户
            if($GLOBALS['user_info']['cunguan_tag']!=1){            
            	$root['response_code'] = 0;
                $root['show_err'] = '请先开通为存管用户！';
                $root['url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';    
                output($root);         
            }

            // 存管版只能投存管标
            if($deal['cunguan_tag']!=1){
            	$root['response_code'] = 0;
                $root['show_err'] = '请选择存管标的！';      
                output($root);  
            }

			if(!$GLOBALS['user_info']['cunguan_pwd']){
				$root['response_code'] = 0;
                $root['show_err'] = '请先设置存管交易密码！';
                $root['url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=pc_setpaypassword';       	
				output($root);  
			}

            $cg_bank=$GLOBALS['db']->getOne("select bankcard from ".DB_PREFIX."user_bank where user_id=".$user_id." and cunguan_tag=1");
			if(!$cg_bank){
				$root['response_code'] = 0;
                $root['show_err'] = '请先绑定存管银行卡！';
                $root['url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=check_pwd';  
                output($root);  			
			}
			


			//第二判断阶梯，判断标的状态与出借金额的合规
			if(!$deal){
				$root['response_code'] = 0;
                $root['show_err'] = '标的不存在';
                output($root);  			
			}

			if($deal['user_id'] == $GLOBALS['user_info']['id']){
				$root['response_code'] = 0;
                $root['show_err'] = $GLOBALS['lang']['CANT_BID_BY_YOURSELF'];//不能投自己发放的标
                output($root);  
			
			}

			//判断是否是新手专享
			$deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$user_id." and is_new=1");
			if($deal['is_new']==1 && $deal_load_count > 0){
				$root['response_code'] = 0;
                $root['show_err'] = '此标为新手专享，只有新手才可以出借哦';	
                output($root);  					
			}

			//判断是否为存管标 
			$deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$user_id."  and cunguan_tag=1 and publish_wait=0");
			if($deal['cunguan_tag']==1 && $deal_load_count > 0){	
				$root['response_code'] = 0;
                $root['show_err'] = '此标为存管吧标,只有存管可以出借哦';		
                output($root);  				
			}

			$deal['need_money'] = $deal['borrow_amount'] - $deal['load_money'];//剩余可投金额
			$weibiao_yes = intval($deal['need_money'])<intval($deal['min_loan_money'])?1:0;//判断是否为尾标
			if($weibiao_yes){				
				if($bbin_money['money']!=$deal['need_money']){
					$root['response_code'] = 0;
                	$root['show_err'] = '尾标金额不可变更';
                	output($root);  	

				}
			}else{
				if($bbin_money['money']< $deal['min_loan_money'] ){
					$root['response_code'] = 0;
                	$root['show_err'] = "起投金额为".$deal['min_loan_money']."元";	
                	output($root);  						
				}
				if($deal['max_loan_money'] > 0 && $bbin_money['money']>$deal['max_loan_money']){
					$root['response_code'] = 0;
                	$root['show_err'] = "最大出借金额为".$deal['max_loan_money']."元";		
                	output($root);  					
				}
			}

			if($gold_id) {
					$ecv_count = 0;
					$ecv_id = explode(',',$gold_id);
                    $ecv_count =$GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and use_status = 0  and end_time >" . time() . " and user_id = " . intval($user_id)." and id in (".$gold_id.")");
					if (count($ecv_id) != $ecv_count) {
						$root['response_code'] = 0;
                		$root['show_err'] = '选用体验金已过期或存管不可用，请重新选择！';	
                		output($root);  							
					}
			}
			
			
			// if ($gold_money > 0) {
			// 	$cash_money = 0;
   //              $cash_money = $GLOBALS['db']->getOne("select sum(money) from " . DB_PREFIX . "taste_cash  where cunguan_tag=0 and user_id = " . intval($GLOBALS['user_info']['id'])." and id in(".$gold_id.")");
			// 	if ($cash_money != $gold_money) {
			// 		$return["status"] = 3;
			// 		$return["info"] = "体验金金额不匹配，请重新选择";
			// 		ajax_return($return);
			// 	}
			// }

			if(floatval($deal['borrow_amount']) <= floatval($deal['load_money'])){
				$root['response_code'] = 0;
                $root['show_err'] = '已满标';
                output($root);  			
			}	

			if($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
				$root['response_code'] = 0;
                $root['show_err'] = "此标为第三方托管标，请先绑定第三方托管账户,<a href=\"".url("index","uc_center")."\" target='_blank'>点这里设置</a>";							
				output($root);  	
			}

			if(floatval($deal['deal_status']) != 1 ){
				$root['response_code'] = 0;
                $root['show_err'] = $GLOBALS['lang']['DEAL_FAILD_OPEN'];
               output($root);  				
			}

			if($deal['need_money']<$bbin_money['money']){
				$root['response_code'] = 0;
                $root['show_err'] = "出借总额大于可投金额";
                output($root);  	
			}
			

			$label['user_id'] = $GLOBALS['user_info']['id'];
			$label['user_name'] = $GLOBALS['user_info']['user_name'];
			$label['deal_id'] = $deal_id;
			$label['money'] = $bbin_money['money'];
			$label['total_money'] = $bbin_money['money'];
			$label['add_ip'] = $_SERVER['REMOTE_ADDR'];
			$label['create_time'] = TIME_UTC;
			$label['create_date'] = to_date(TIME_UTC);
			$label['repay_time']  = strtotime("+ 1 day", $label['create_time']);
			$label['has_repay'] = 0;
			$label['raise_money'] = 0;	
			$label['repay_id'] = 0;
			$label['t_user_id'] = 0;
			$label['learn_id'] = $gold_id;
			$label['learn_money'] = $bbin_money['money'];

			$GLOBALS['db']->startTrans();//开始事务			
			$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."experience_deal where id=".$deal_id." FOR UPDATE");
			$deal_con = $GLOBALS['db']->getRow("SELECT borrow_amount,load_money,buy_count,create_time,rate,name FROM ".DB_PREFIX."experience_deal where id=".$deal_id);								
			$user_raise_time=1;
			$label['experience_money'] = round($label['total_money'] * $deal_con['rate'] / 100 /365 * $user_raise_time,2);
			
			$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."experience_deal where id=".$deal_id."");
			if(($deal_con['borrow_amount']-$deal_con['load_money'])<$label['total_money']){
				$GLOBALS['db']->rollback();
				$root['response_code'] = 0;
                $root['show_err'] = "出借总额大于可投金额";
                output($root);  									
			}
			$new_load_money = $deal_con['load_money']+$label['total_money'];
			$buy_count = $deal_con['buy_count']+1;
			$res1 = $GLOBALS['db']->query("update ".DB_PREFIX."experience_deal set load_money = ".intval($new_load_money).",buy_count = ".$buy_count." where id =".$deal_id." and buy_count=".$deal_con['buy_count']);
			$res2 = $GLOBALS['db']->autoExecute(DB_PREFIX."experience_deal_load",$label,"INSERT");			
			$load_id = $GLOBALS['db']->insert_id();

			if($res1 && $res2){
				$deal_con_two = $GLOBALS['db']->getRow("SELECT borrow_amount,load_money,buy_count,repay_time,rate,loantype FROM ".DB_PREFIX."experience_deal where id=".$deal_id);				
				if($deal_con_two['borrow_amount']<$deal_con_two['load_money']){
					$GLOBALS['db']->rollback();
					$root['response_code'] = 0;
                	$root['show_err'] = "出借总额大于可出借金额";	
                	output($root);  				
				}elseif($deal_con_two['borrow_amount']==$deal_con_two['load_money']){
					$GLOBALS['db']->query("update ".DB_PREFIX."experience_deal set success_time = ".$label['create_time'].",deal_status = 2 where id =".$deal_id);
				}
			}else{
				$GLOBALS['db']->rollback();
				$root['response_code'] = 0;
                $root['show_err'] = "出借失败,请重试";	
                output($root);  								
			}

			//$root['deal']['url']="/member.php?ctl=experdeals&id=$deal_id";

			if($load_id>0){ 
				//更改资金记录
				
				$time=time();
				$with=$GLOBALS['db']->query("UPDATE ".DB_PREFIX."taste_cash SET use_status = 1,use_time =".$time." WHERE  id in (".$gold_id.") AND user_id=".$GLOBALS['user_info']['id']);										
				$user_id =$GLOBALS['user_info']['id']; 
				$device= intval(base64_decode($GLOBALS['request']['MachineInfo'])); //标的id
				//$device = explode("|||",es_session::get('MachineInfo'));
				
				if($with){
					$bbin =$GLOBALS['db']->getAll("select *  from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and use_status = 1  and end_time >" . time() . " and user_id = " . intval($user_id)." and id in (".$gold_id.")");
					foreach ($bbin as $k => $v) {					
						$taste['user_id'] = $v['user_id'];
						$taste['deal_id'] = $deal_id;
						$taste['taste_cash_id'] = $v['taste_cash_id'];
						$taste['create_time'] =TIME_UTC;
						$taste['change'] = -$v['money'];
						$taste['add_ip'] = get_client_ip();
						$taste['cunguan_tag'] = 1;
						$taste['detail'] = '使用-'.$v['disc'];
						$taste['device'] = $device[0];
						$taste['taste_id'] = $deal_id;
						$res3 = $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$taste,"INSERT");		
					}
				}

	
				 $wx_openid = $GLOBALS['db']->getOne("select wx_openid from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
	        	if($wx_openid){
	            	if(app_conf('WEIXIN_TMPL')){
		                $tmpl_url =app_conf('WEIXIN_TMPL_URL');
		                $tmpl_datas = array();
		                $tmpl_datas['first'] = '尊敬的用户，您已成功购买以下标的。';
		                $tmpl_datas['keyword1'] = $root['deal']['name'];
		                $tmpl_datas['keyword2'] = $bid_money.'元';
		                $tmpl_datas['keyword3'] = $root['deal']['repay_time'].'个月';
		                $tmpl_datas['keyword4'] = $root['deal']['rate'].'%';
		                $tmpl_datas['keyword5'] = date('Y-m-d H:i:s');
		                $tmpl_datas['remark'] = "请登录玖财通查看详情~\r\n\r\n下载客户端，理财更简单。推荐您下载玖财通APP！";
		                $tmpl_data = create_request_data('2',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
		                $resl = request_curl($tmpl_url,$tmpl_data);

		                $tmpl_msg['dest'] = $wx_openid;
		                $tmpl_msg['send_type'] = 2;
		                $tmpl_msg['content'] = serialize($tmpl_datas);
		                $tmpl_msg['send_time'] = time();
		                $tmpl_msg['create_time'] = time();
		                $tmpl_msg['user_id'] = $GLOBALS['user_info']['id'];
			                $tmpl_msg['title'] = '出借成功';
		                if($resl=='true'){
		                    $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$GLOBALS['user_info']['id']);
		                    $tmpl_msg['is_send'] = 1;
		                    $tmpl_msg['result'] = '发送成功';
		                    $tmpl_msg['is_success'] = 1;
		                }else{
		                    $tmpl_msg['is_send'] = 0;
		                    $tmpl_msg['result'] = $resl['message'];
		                    $tmpl_msg['is_success'] = 0;
		                }
		                $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');
	            	}
	       		 }

        		/************出借成功后微信模板消息结束*********************/

		        if(app_conf('SMS_ON')==1 && app_conf('SMS_DEAL_LOAD')==1){
		            //发送投标短信
		            $load_tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_DEAL_LOAD'",false);
		            $tmpl_content = $load_tmpl['content'];
		            $notice['user_name'] = $GLOBALS['user_info']['user_name'];
		            $notice['deal_name'] = $root['deal']['name'];
		            $notice['money'] = number_format($bid_money);
		            $notice['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
		            $notice['site_name'] = app_conf("SHOP_TITLE");

		            $GLOBALS['tmpl']->assign("notice",$notice);

		            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		            $msg_data['dest'] = $GLOBALS['user_info']['mobile'];
		            $msg_data['send_type'] = 0;
		            $msg_data['title'] = $root['deal']['name']."投标短信通知";
		            $msg_data['content'] = addslashes($msg);
		            $msg_data['send_time'] = 0;
		            $msg_data['is_send'] = 0;
		            $msg_data['create_time'] = TIME_UTC;
		            $msg_data['user_id'] =  $GLOBALS['user_info']['id'];
		            $msg_data['is_html'] = $load_tmpl['is_html'];
		            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		        }

				$GLOBALS['db']->commit();
				$root['response_code'] = 1;
                $root['show_err'] = "投标成功";	
			}else{ 
				$GLOBALS['db']->rollback();
				$root['response_code'] = 0;
                $root['show_err'] = "出借失败,请重试";					
			}

		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
	
		output($root);		
	}
}
?>

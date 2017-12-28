<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
define(ACTION_NAME,"experdeals");
define(MODULE_NAMEN,"index");
class experdealsModule extends SiteBaseModule
{
	public function index(){
   
        //判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		$id = intval($_REQUEST['id']);	
		$deals_time = TIME_UTC;
		$deal = $GLOBALS['db']->getRow("select  *,(start_time + repay_time*24*3600 - ".$deals_time.") as remain_time,(load_money/borrow_amount*100) as progress_point from ".DB_PREFIX."experience_deal where id = ".intval($id)."");	
	
		$deal['need_money'] = format_price($deal['borrow_amount'] - $deal['load_money']);
		$need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."experience_deal_load where deal_id=".$id);
		$progress = sprintf("%.2f",floatval($need_money/$deal['borrow_amount']*100));
		if($need_money>=$deal['borrow_amount']){
			$deal['progress_point'] = '100';
		}elseif($progress*100>=9999&&$need_money<$deal['borrow_amount']){
			$deal['progress_point'] = '99.99';
		}else{
			$deal['progress_point'] = $progress;
		}
	
		require APP_ROOT_PATH.'app/Lib/page.php';
		$count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."experience_deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE deal_id = ".$id);
		$page = new Page($count,10);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$pages = intval($_REQUEST['p']);
		if($pages==0)
			$pages = 1;
		if(WAP == 1){
			$limit = "0,100";
		}else{
			$limit = (($pages-1)*10).",10";
		}
		//借款列表
        $load_list = $GLOBALS['db']->getAll("SELECT ub.user_id,ub.money,b.mobile,ub.user_name,ub.total_money,ub.create_time FROM ".DB_PREFIX."experience_deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE ub.deal_id = ".$id." order by ub.id desc limit ".$limit);           
		//可用额度
		$can_use_quota = get_can_use_quota($deal['user_id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);		
		$credit_file = get_user_credit_file($deal['user_id'],$u_info);
		$deal['is_faved'] = 0;

		$GLOBALS['tmpl']->assign("load_list",$load_list);	
		
		if($deal['type_match_row'])
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		else
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];
		
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");
	
        /*************项目资料详情开关开始**************/

        $switch = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."switch_conf where switch_id = 1 or switch_id = 8");
        foreach ($switch as $k=>$v){
            if($v['status']!=1){
                //抵押物资料
                $deal['mortgage_brand']= '暂无';
                $deal['mortgage_year'] = '暂无';
                $deal['mortgage_color'] = '暂无';
                $deal['mortgage_insurance'] = '暂无';
                $deal['mortgage_info'] = '暂无';
                //图片信息
                $img = [];
                $mortgage_infos = [];
            }else{
                //抵押物资料
                $deal['mortgage_brand']= $deal['mortgage_brand']?$deal['mortgage_brand']:'暂无';
                $deal['mortgage_year'] = $deal['mortgage_year'] ?$deal['mortgage_year']:'暂无';
                $deal['mortgage_color'] = $deal['mortgage_color'] ?$deal['mortgage_color']:'暂无';
                $deal['mortgage_insurance'] = $deal['mortgage_insurance'] ?$deal['mortgage_insurance']:'暂无';
                $deal['mortgage_info'] = $deal['mortgage_info'] ?$deal['mortgage_info'] : '暂无';
				$img = unserialize($deal['mortgage_infos']);//抵押图片
                $mortgage_infos = unserialize($deal['mortgage_infos']);
            }
        }

        /*************项目资料详情开关结束**************/
		$deal['weibiao_need_money'] = $deal['borrow_amount'] - $deal['load_money'];
		$deal['weibiao_yes'] = intval(str_replace(',','',$deal['need_money']))<intval($deal['min_loan_money'])?1:0;
		
		//jumpUrl("jump_url_depository");
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign('ajax',$ajax);
		$GLOBALS['tmpl']->assign("ips",$ips);
		$GLOBALS['tmpl']->assign("img",$img);
		$GLOBALS['tmpl']->assign("yes",$yes);
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->assign("money",floatval($GLOBALS['user_info']['cunguan_money']));
		
		$GLOBALS['tmpl']->assign("config",$config);
		$GLOBALS['tmpl']->assign("ACTION_NAME","experdeals");
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/experience_deal.html");
	}

		public function ebid(){
			/*status状态码
			* 0 提示错误信息，无任何操作
			* 1 投标成功
			* 2 未登录，跳转登录页面
			* 3 提示错误信息，修改输入的出借金额
			* 4 提示错误信息，跳转指定页面
			* 5 判断是否尾标
			*****/
						
        	$gold_id= intval($_REQUEST['FictitiousMoney_ids']); //体验金的
        
        	//$gold_money=$_REQUEST['ebidmoney'];//体验金的钱 
			$deal_id = intval($_REQUEST['deal_id']);  //标的id	
			$user_id=$GLOBALS['user_info']['id'];
			$gold_money =$GLOBALS['db']->getRow("select sum(money)as money from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and use_status = 0  and end_time >" . time() . " and user_id = " . intval($user_id)." and id in (".$gold_id.")");
			$deal = $this->exper_deal($deal_id);			
			//第一判断阶梯，判断用户状态
			if(!$GLOBALS['user_info']){
				$return["status"] = 0;
				$return["info"] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];//请先登录
				$return["jump"] = url("index","user#login"); 
				ajax_return($return);
			}

            //先开通存管用户
            if($GLOBALS['user_info']['cunguan_tag']!=1){
                $return["status"] = 0;
                $return["info"] = '请先开通为存管用户！';//请先开通存管
                $return["jump"] = url("index","uc_depository_account#index");
                ajax_return($return);
            }
            // 存管版只能投存管标
            if($deal['cunguan_tag']!=1){
                $return["status"] = 0;
                $return["info"] = '请选择存管标的！';
                ajax_return($return);
            }
			if(!$GLOBALS['user_info']['cunguan_pwd']){
				$return["status"] = 0;
				$return["info"] = '请先设置存管交易密码！';
				$return["jump"] = url("member","uc_depository_paypassword#pc_setpaypassword");
				ajax_return($return);
			}
            $cg_bank=$GLOBALS['db']->getOne("select bankcard from ".DB_PREFIX."user_bank where user_id=".$GLOBALS['user_info']['id']." and cunguan_tag=1");
			if(!$cg_bank){
				$return["status"] = 0;
				$return["info"] = '请先绑定存管银行卡！';
				$return["jump"] = url("member","uc_depository_addbank#check_pwd");
				ajax_return($return);
			}

			//第二判断阶梯，判断标的状态与出借金额的合规
			if(!$deal){
				$return["status"] = 0;
				$return["info"] = "标的不存在";
				ajax_return($return);
			}

			if($deal['user_id'] == $GLOBALS['user_info']['id']){
				$return["status"] = 0;
				$return["info"] = $GLOBALS['lang']['CANT_BID_BY_YOURSELF'];//不能投自己发放的标
				ajax_return($return);
			}

			//判断是否是新手专享
			$deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$GLOBALS['user_info']['id']." and is_new=1");
			if($deal['is_new']==1 && $deal_load_count > 0){
				$return["status"] = 0;
				$return["info"] = "此标为新手专享，只有新手才可以出借哦";
				ajax_return($return);
			}

			//判断是否为存管标 
			$deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$GLOBALS['user_info']['id']."  and cunguan_tag=1 and publish_wait=0");
			if($deal['cunguan_tag']==1 && $deal_load_count > 0){
				$return["status"] = 0;
				$return["info"] = "此标为存管吧标,只有存管可以出借哦";
				ajax_return($return);
			}

			$deal['need_money'] = $deal['borrow_amount'] - $deal['load_money'];//剩余可投金额
			$weibiao_yes = intval($deal['need_money'])<intval($deal['min_loan_money'])?1:0;//判断是否为尾标
			if($weibiao_yes){				
				if($gold_money['money']!=$deal['need_money']){
					$return["status"] = 0;
					$return["info"] = "尾标金额不可变更";
					ajax_return($return);
				}
			}else{

				
				if($gold_money['money']< $deal['min_loan_money'] ){
					$return["status"] = 0;
					$return["info"] = "请选择体验金";
					ajax_return($return);
				}
				if($deal['max_loan_money'] > 0 && $gold_money['money']>$deal['max_loan_money']){
					$return["status"] = 0;
					$return["info"] = "最大出借金额为".$deal['max_loan_money']."元";
					ajax_return($return);
				}
			}

			if ($gold_id) {
					$ecv_count = 0;
					$ecv_id = explode(',',$gold_id);
                    $ecv_count =$GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and use_status = 0  and end_time >" . time() . " and user_id = " . intval($GLOBALS['user_info']['id'])." and id in (".$gold_id.")");
					if (count($ecv_id) != $ecv_count) {
						$return["status"] = 0;
						$return["info"] = "选用体验金已过期或存管不可用，请重新选择！";
						ajax_return($return);
					}
			}
			
			/*	
			if ($gold_money > 0) {
				$cash_money = 0;
                $cash_money = $GLOBALS['db']->getOne("select sum(money) from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and user_id = " . intval($GLOBALS['user_info']['id'])." and id in(".$gold_id.")");
				if ($cash_money != $gold_money) {
					$return["status"] = 3;
					$return["info"] = "体验金金额不匹配，请重新选择";
					ajax_return($return);
				}
			}*/

			if(floatval($deal['borrow_amount']) <= floatval($deal['load_money'])){
				$return["status"] = 0;
				$return["info"] = "已满标";
				ajax_return($return);
			}	

			if($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
				$return["status"] = 0;
				$return["info"] = "此标为第三方托管标，请先绑定第三方托管账户,<a href=\"".url("index","uc_center")."\" target='_blank'>点这里设置</a>";
				ajax_return($return);
			}

			if(floatval($deal['deal_status']) != 1 ){
				$return["status"] = 0;
				$return["info"] = $GLOBALS['lang']['DEAL_FAILD_OPEN'];
				ajax_return($return);
			}

			if($deal['need_money']<$gold_money['money']){
				$return["status"] = 0;
				$return["info"] = "出借总额大于可投金额";
				ajax_return($return);
			}

			$label['user_id'] = $GLOBALS['user_info']['id'];
			$label['user_name'] = $GLOBALS['user_info']['user_name'];
			$label['deal_id'] = $deal_id;
			$label['money'] = $gold_money['money'];
			$label['total_money'] = $gold_money['money'];
			$label['add_ip'] = $_SERVER['REMOTE_ADDR'];
			$label['create_time'] = TIME_UTC;
			$label['create_date'] = to_date(TIME_UTC);				
			$label['repay_time']  = strtotime("+ 1 day", $label['create_time']);
			$label['has_repay'] = 0;
			$label['raise_money'] = 0;	
			$label['user_id'] = $GLOBALS['user_info']['id'];
			$label['repay_id'] = 0;
			$label['t_user_id'] = 0;
			$label['learn_id'] = $gold_id;
			$label['learn_money'] = $gold_money['money'];
				
			$GLOBALS['db']->startTrans();//开始事务			
			$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."experience_deal where id=".$deal_id." FOR UPDATE");
			$deal_con = $GLOBALS['db']->getRow("SELECT borrow_amount,load_money,buy_count,create_time,rate,name FROM ".DB_PREFIX."experience_deal where id=".$deal_id);			
			$user_raise_time=1;
			$label['experience_money'] = $label['total_money'] * $deal_con['rate'] / 100 /365 * $user_raise_time;		
			$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."experience_deal where id=".$deal_id."");
			if(($deal_con['borrow_amount']-$deal_con['load_money'])<$label['total_money']){
				$GLOBALS['db']->rollback();
				$return["status"] = 0;
				$return["info"] = "出借总额大于可投金额";
				ajax_return($return);				
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
					$return["status"] = 0;
					$return["info"] = "出借总额大于可出借金额";
					ajax_return($return);	
				}elseif($deal_con_two['borrow_amount']==$deal_con_two['load_money']){
					$GLOBALS['db']->query("update ".DB_PREFIX."experience_deal set success_time = ".$label['create_time'].",deal_status = 2 where id =".$deal_id);
				}
			}else{
				$GLOBALS['db']->rollback();
				$return["status"] = 0;
				$return["info"] = "出借失败,请重试";
				ajax_return($return);					
			}

			$root['deal']['url']="/member.php?ctl=experdeals&id=$deal_id";

			if($load_id>0){ 
				//更改资金记录
					$time=time();						
					$with=$GLOBALS['db']->query("UPDATE ".DB_PREFIX."taste_cash SET use_status = 1,use_time =".$time." WHERE  id in (".$gold_id.") AND user_id=".$GLOBALS['user_info']['id']);															
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
							$taste['device'] = 'PC';
							$taste['taste_id'] = $deal_id;
							$res3 = $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$taste,"INSERT");		
						}
					}
				
					/*	
					$msg = '[<a href="'.$root['deal']['url'].'" target="_blank">'.$deal_con['name'].'</a>]的出借';
					$brief = '出借成功';
					require_once APP_ROOT_PATH."system/libs/user.php";
					//$data['money'] =-($label['total_money']);
					$data['money'] =-($label['total_money']);		
					//$data['lock_money'] = $data['total_money'];
					modify_account($label,$GLOBALS['user_info']['id'],$msg,2,$brief);
					*/
			

				/************出借成功后微信模板消息开始*********************/
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
		        	if(WAP ==1){ 
		        		$return["status"] = 1;
						$return["info"] = "投标成功";	
						ajax_return($return);	
		        	}else{
		        		$return["status"] = 1;
						$return["info"] = $GLOBALS['tmpl']->fetch("page/success_ebid.html");
						ajax_return($return);							
		        	}

			}else{ 				
				$GLOBALS['db']->rollback();
				if(WAP ==1){ 
					$return["status"] = 0;
					$return["info"] = "出借失败,请重试";
					ajax_return($return);	
				}else{ 
					$return["status"] = 0;
					$return["info"] = "出借失败,请重试";
					ajax_return($return);	
				}
			
			}


		//end			
		}


		//获取体验金 
		public function experience_get_interestrate(){
			$user_id = intval($GLOBALS['user_info']['id']);
			$ecv_list = $GLOBALS['db']->getAll("select id,end_time,money from ".DB_PREFIX."taste_cash where cunguan_tag=1 and use_status = 0  and end_time >".time()." and user_id = ".$user_id."  order by end_time asc");
			foreach ($ecv_list as $k => $v) {			
				$ecv_list[$k]['time'] = date("Y-m-d",$v['end_time']);
			}

			if(count($ecv_list)<1){
				$data['info']="您的账户暂无当前出借金额可匹配的体验金!";
				$data['status']=0;
				ajax_return($data,0);
			}
			
			$GLOBALS['tmpl']->assign("interestrate_list",$ecv_list);
			$data["page"] = $GLOBALS['tmpl']->fetch("page/experience_deal_interestrate.html");
			
			$data['status'] = 1;
	        ajax_return($data);
		}


		//加息卡
		public function get_interest_card(){
				$data['status'] = 1;
				ajax_return($data);			
		}


		//红包 
		public function get_red_packet(){ 
			$data['status'] = 1;
			ajax_return($data);	
		}


	    /**
		 * 获取指定的投标
		 */
	    public function exper_deal($id){ 
		    $time = TIME_UTC;	
			if($id==0)  //有ID时不自动获取
			{
				return false;		
			}
			else{
				$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."experience_deal where id = ".intval($id)."  and is_effect = 1 and publish_wait=1 and is_hidden=0 and is_effect=1");
			}		
			return $deal;	
	    }
	//end
	}
?>

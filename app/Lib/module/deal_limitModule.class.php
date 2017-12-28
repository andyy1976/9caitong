<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/uc_func.php';
define(ACTION_NAME,"deal");
define(MODULE_NAMEN,"index");
class deal_limitModule extends SiteBaseModule
{
	public function index(){
        if($GLOBALS['user_info']['debts']!=1){
            showErr("对不起，您没有权限访问此页面！");
        }
		$id = intval($_REQUEST['id']);		
		$deal = get_deal($id,0);
        //  预售标的是否开始
        if($deal['is_advance']==1 && $deal['start_time']<time()){
            $deal['is_advance_start']=1;
        }
		$server_time = time();
		$start_time = $deal['start_time'];
		$GLOBALS['tmpl']->assign('server_time',$server_time);
		$GLOBALS['tmpl']->assign('start_time',$start_time);
		if($deal['deal_status']==1) {
			$deal['residual_time'] = ceil($deal['remain_time']/86400);
			
		}else{
			$deal['residual_time'] = 0;
		}
//        echo $deal['deal_status'];die;
//        if($deal['deal_status']==0){
//            $deal['deal_status']=1;
//        }
		/*
        $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$id);
        $need_money_re = $deal['borrow_amount'] - $need_money;
		$progress = floatval($need_money/$deal['borrow_amount']*100);
        if($need_money_re<0){
            $need_money_re = '0';
        }

        if($progress>100){
            $progress = '100';
        }
		if( floatval(99.994) < $progress && $progress < 100){
			$progress_point = "99.9";
		}else{
			$progress_point = sprintf("%.1f",round($need_money/$deal['borrow_amount']*100,2));
		}
		if(WAP==1){
			$deal['progress_point']= substr_replace($progress_point, '', strpos($progress_point, '.') + 2);
		}else{
			$deal['progress_point'] = round($need_money/$deal['borrow_amount']*100,2);
		}
		*/
		$need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$id);
		$progress = sprintf("%.2f",floatval($need_money/$deal['borrow_amount']*100));
		if($need_money>=$deal['borrow_amount']){
			$deal['progress_point'] = '100';
		}elseif($progress*100>=9999&&$need_money<$deal['borrow_amount']){
			$deal['progress_point'] = '99.99';
		}else{
			$deal['progress_point'] = $progress;
		}
		
		if(!$deal)
			app_redirect(url("index"));		
		send_deal_contract_email($id,$deal,$deal['user_id']);
		
		//==========wap端与pc端请求区分开始================
		require APP_ROOT_PATH.'app/Lib/page.php';
		$count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE deal_id = ".$id);
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
        $load_list = $GLOBALS['db']->getAll("SELECT ub.deal_id,ub.user_id,ub.user_name,ub.money,ub.is_auto,ub.create_time,ub.ecv_money,ub.red,ub.total_money,b.mobile FROM ".DB_PREFIX."deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE deal_id = ".$id." order by ub.id desc limit ".$limit);
        /*
        $pages=$this->investment_list($id);
        $load_list = $pages['list'];
        */
        $u_info = $deal['user'];
      
		if($deal['view_info']!=""){
			$view_info_list = unserialize($deal['view_info']);
			$GLOBALS['tmpl']->assign('view_info_list',$view_info_list);
		}		
		//可用额度
		$can_use_quota = get_can_use_quota($deal['user_id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);		
		$credit_file = get_user_credit_file($deal['user_id'],$u_info);
		$deal['is_faved'] = 0;
		
		
		if($GLOBALS['user_info']){
			if($u_info['user_type']==1)
				$company = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_company WHERE user_id=".$u_info['id']);
			
			$deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
			
			if($deal['deal_status'] >=4){
				//还款列表
				$loan_repay_list = get_deal_load_list($deal);
				
				$GLOBALS['tmpl']->assign("loan_repay_list",$loan_repay_list);
				
				if($loan_repay_list){
					$temp_self_money_list = $GLOBALS['db']->getAll("SELECT sum(self_money) as total_money,u_key FROM ".DB_PREFIX."deal_load_repay WHERE has_repay=1 AND deal_id=".$id." group by u_key ");
					$self_money_list = array();
					foreach($temp_self_money_list as $k=>$v){
						$self_money_list[$v['u_key']]= $v['total_money'];
					}
					
					foreach($load_list as $k=>$v){
						$load_list[$k]['remain_money'] = $v['money'] -$self_money_list[$k];
						if($load_list[$k]['remain_money'] <=0){
							$load_list[$k]['remain_money'] = 0;
							$load_list[$k]['status'] = 1;
						}
					}
				}				
			}	
			// $user_statics = sys_user_status($deal['user_id'],true);
			// $GLOBALS['tmpl']->assign("user_statics",$user_statics);
			$GLOBALS['tmpl']->assign("company",$company);			
			$user_info = $GLOBALS['db']->getRow("select vip_id,level_id from ".DB_PREFIX."user where id= ".$GLOBALS['user_info']['id']." ");
			

			$type_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_loan_type WHERE id=".$deal['type_id']);

			if($type_info['costsetting'] && $user_info['vip_id']>0){
				$vo_list = explode("\n",$type_info['costsetting']);
				foreach($vo_list as $k=>$v){
					$vo_list[$k] = explode("|",$v);
					if($vo_list[$k]['0'] == $user_info['vip_id']){
						if($vo_list[$k]['1']>0 || $vo_list[$k]['2']>0 || $vo_list[$k]['3']>0 || $vo_list[$k]['4']>0 || $vo_list[$k]['5']>0 || $vo_list[$k]['6']>0 ){
							$deal['user_loan_manage_fee'] = $vo_list[$k]['3'];
							$deal['user_loan_interest_manage_fee'] = $vo_list[$k]['4'];
						}
					}
				}
			}else{
				if($type_info['levelsetting']){
					$vol_list = explode("\n",$type_info['levelsetting']);
					foreach($vol_list as $kl=>$vl){
						$vol_list[$kl] = explode("|",$vl);
						if($vol_list[$kl]['0'] == $user_info['level_id']){
							if($vol_list[$kl]['1']>0 || $vol_list[$kl]['2']>0 || $vol_list[$kl]['3']>0 || $vol_list[$kl]['4']>0 || $vol_list[$kl]['5']>0 || $vol_list[$kl]['6']>0){
								$deal['user_loan_manage_fee'] = $vol_list[$kl]['3'];
								$deal['user_loan_interest_manage_fee'] = $vol_list[$kl]['4'];
							}
						}
					}
				}
			}			
			if($deal['uloadtype'] == 1){
				$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
				$GLOBALS['tmpl']->assign("has_bid_money",$has_bid_money);
				$GLOBALS['tmpl']->assign("has_bid_portion",intval($has_bid_money)/($deal['borrow_amount']/$deal['portion']));
			}
		}
		
		foreach($load_list as $k=>$v){
			$load_list[$k]['money'] = $v['total_money'];
		}
		
		$GLOBALS['tmpl']->assign("load_list",$load_list);	
		$GLOBALS['tmpl']->assign("credit_file",$credit_file);
		$GLOBALS['tmpl']->assign("u_info",$u_info);

		if($deal['type_match_row'])
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		else
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];

		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");
		
		//留言
		/*require APP_ROOT_PATH.'app/Lib/message.php';*/
		/*
		$rel_table = 'deal';
		
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;

		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
		else 
		{
			if($message_type['is_effect']==0)
			{
				$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
			}
		}
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',$rel_table);		*/

        //==========wap端与pc端请求区分结束================
        /*
        $msg_condition = $condition." AND is_effect = 1 ";
		$message = get_message_list($limit,$msg_condition);
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		foreach($message['list'] as $k=>$v){
			$msg_sub = get_message_list("","pid=".$v['id'],false);
			$message['list'][$k]["sub"] = $msg_sub["list"];
		}		
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}
        */
		//==========wap端与pc端请求区分开始================
		
		
		$xs = 10;
		$user_id = intval($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$deal["rate"] = sprintf("%.1f",$deal["rate"]); //统一预期年化收益格式
		$deal['ymb'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'ymb'"); 
		$deal['bank'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config where code = 'bank'");
		
		if($deal['rate'] >= 8)$deal['rate_progress'] = 80;
		else $deal['rate_progress'] = $deal['rate']*$xs;
		if($deal['bank'] < 2)$deal['bank_progress'] = 20;
 		else $deal['bank_progress'] = $deal['bank']*$xs;
		$deal['ymb_progress'] = $deal['ymb']*$xs;	
		
		$deal['uid'] = intval($GLOBALS['user_info']['id']);
		$deal['over_amount'] = $deal['borrow_amount'] - $deal['load_money'];

		if(es_session::get('coupon_id') || es_session::get('red_id')){
			es_session::delete('red_id');
			es_session::delete('coupon_id');
			es_session::delete('lend_money');
			es_session::delete('repay_time');
		}
		

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
		//剩余募集天数
		if($deal['deal_status']==1) {
			$deal['surplus_enddate'] = ceil($deal['remain_time']/86400);
		}else{
			$deal['surplus_enddate'] = 0;
		}

		$GLOBALS['tmpl']->assign( 'mortgage_infos',$mortgage_infos);
		$ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips_cg where code = 2" );
		$userinfo = $GLOBALS['user_info'];
		$user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where cunguan_tag = 1 and user_id= ".$userinfo['id']);
		//存管出借 验证
		if($userinfo['cunguan_tag'] == 0){
			$ajax['code'] = 0;
			$ajax['url'] = url("index","uc_depository_account"); //判断存管是否开户
		}else if($userinfo['cunguan_tag'] == 1 && $userinfo['cunguan_pwd'] == 0){
			$ajax['code'] = 1;
			$ajax['url'] = url("index","uc_depository_paypassword#setpaypassword"); //判断存管是否设置交易密码
		}else if($userinfo['cunguan_tag'] == 1 && $userinfo['cunguan_pwd'] == 1 && $user_status < 1){
			$ajax['code'] = 1;
			$ajax['url'] = url("index","uc_depository_addbank#wap_check_pwd"); //判断存管是否设置交易密码
		}else{
			$ajax['code'] = 4;
		}
		/*$usinfos = $GLOBALS['db']->getRow("select AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,u.paypassword,b.bankcard,b.status from ".DB_PREFIX."user u left join ".DB_PREFIX."user_bank b on u.id = b.user_id where u.id= ".$userinfo['id']);
		$user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where  user_id= ".$userinfo['id']);
		if($userinfo['id'] && !$usinfos['bankcard'] && !$usinfos['idno']){
			$ajaxurl = url("index","uc_center#identity");
		}else if($userinfo['id'] && !$usinfos['bankcard'] && $usinfos['idno']){
			$ajaxurl = url("index","uc_account#bind_bank");
		}else if($usinfos['bankcard'] && $user_status == 0){
			$ajaxurl = url("index","uc_account#bind_bank");
		}else if(!$usinfos['paypassword']){
			$ajaxurl = url("index","uc_account#wappaypassword");
		}*/
		//判断预售标
		$deal['timer'] = $deal['start_time'] - time();
		$hour = floor($deal['timer']/3600);
   		if($hour<10){
   			$hour = "0".$hour;
   		}
        $minutes = floor($deal['timer']/60%60);
        if($minutes<10){
   			$minutes = "0".$minutes;
   		}
        $seconds = floor($deal['timer']%60);
        if($seconds<10){
   			$seconds = "0".$seconds;
   		}
		$deal['initial_time'] = $hour.":".$minutes.":".$seconds;
        if($deal['debts']==1){
            $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal['old_deal_id']." order by repay_time desc limit 1");
            $deal['debts_repay_time']=  (strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24;
        }
		jumpUrl("jump_url_depository");
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign('ajax',$ajax);
		$GLOBALS['tmpl']->assign("ips",$ips);
		$GLOBALS['tmpl']->assign("img",$img);
		$GLOBALS['tmpl']->assign("yes",$yes);
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->assign("money",floatval($GLOBALS['user_info']['cunguan_money']));
		//==========wap端与pc端请求区分结束================
		$GLOBALS['tmpl']->assign("config",$config);
		$GLOBALS['tmpl']->assign("ACTION_NAME","deal");
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/deal_limit.html");
	}

    /*
     * PC 分页
    */
    public function investment_list($id){
        $page = intval($_REQUEST['p']);
        if($page==0)
            $page = 1;
        $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
        //借款列表
        $load_list = $GLOBALS['db']->getAll("SELECT ub.deal_id,ub.user_id,ub.user_name,ub.money,ub.is_auto,ub.create_time,ub.ecv_money,ub.red,b.mobile FROM ".DB_PREFIX."deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE deal_id = ".$id." limit ".$limit);
        $sql_count = "SELECT count(deal_id) FROM ".DB_PREFIX."deal_load WHERE deal_id = ".$id." order by id desc ";
        $count_list = $GLOBALS['db']->getOne($sql_count);
        return array("list"=>$load_list,"count"=>$count_list);
    }
	
	public function cash(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));		
		}
		$res['id'] = intval($_REQUEST['id']);		
		$res['money'] = $_REQUEST['money'];
		$res['cash_money'] = $_REQUEST['cash_money'];
		$res['red'] = $_REQUEST['red'];
		if($_REQUEST['cash_id']){			
			$cash_ids=explode(",",$_REQUEST['cash_id']);
		}
		$result = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ecv where cunguan_tag=1 and status = 0  and end_time >".time()." and user_id = ".$user_id." order by money asc");
		foreach ($result as $k => $v) {
			foreach ($cash_ids as $key => $value) {
				if($v['id'] == $value){
					$v['status'] = 1;
				}
			}
				$v['type'] = $v['money'] * 50;
				$v['money'] = intval($v['money']);
				$list[] = $v;		
		}
		/****优惠券使用说明*****/
		$voucher_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'voucher_explain'"));
		$GLOBALS['tmpl']->assign("voucher_explain",$voucher_explain);	
		$GLOBALS['tmpl']->assign("res",$res);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->display("page/deal_cash.html");
	}
	public function success(){ //投标成功
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));		
		}
		$id = intval($_REQUEST['id']);
//		$deal = get_deal($id);
		$deal['start_time'] = date("d-m-Y",time());
		$time = intval($deal["repay_time"]);
		$deal['end_time'] =  date("d-m-Y",strtotime("+$time month"));
//		$GLOBALS['tmpl']->assign("deal",$deal);
		$current_time = date("Y-m-d H:i:s",time());
		$popup = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."app_popup where is_effect =1 and position =2 and (add_time <= '$current_time' and end_time > '$current_time')");
		$GLOBALS['tmpl']->assign("popup",$popup);
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->display("page/invest_success.html");
	}
	//回款明细
	/*public function payment(){
		if(!$_POST)
		{
			app_redirect("404.html");
			exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$id = $_REQUEST['id'];
		$deal = get_deal($id);
		$c_money=$_POST['money']+$_POST['cash_money']+$_POST['red'];
		if($deal['loantype'] == "1"){
			if($deal['repay_time_type'] != 0){
				$i=1;
				for($i;$i<=$deal['repay_time'];$i++){
					if($i != $deal['repay_time']){
						$data['benxi'] = sprintf("%.2f", ($c_money*$deal['rate'])/12/100);
						$data['interest'] = sprintf("%.2f",($c_money*$deal['rate'])/12/100);
						$data['benjin'] = "0.00";
					}else{
						$data['benxi'] = sprintf("%.2f", (($c_money*$deal['rate'])/12/100)+$c_money);
						$data['interest'] = sprintf("%.2f",($c_money*$deal['rate'])/12/100);
						$data['benjin'] = sprintf("%.2f",$c_money);
					}				
					
					$data['time'] = date("Y-m-d",strtotime("+$i month"));
					$list[] = $data;
				}
			}
		}
		if($deal['loantype'] == "3"){ //本金均摊，利息固定
			if($deal['repay_time_type'] != 0){
				$i=1;
				for($i;$i<=$deal['repay_time'];$i++){					
					$data['benxi'] = sprintf("%.2f", ((($_REQUEST['money'] + $_REQUEST['r_money'] + $_REQUEST['red'])*$deal['rate'])/12/100)+(($_REQUEST['money'] + $_REQUEST['r_money'] + $_REQUEST['red'])/$deal['repay_time']));
					$data['interest'] = sprintf("%.2f",(($_REQUEST['money'] + $_REQUEST['r_money'] + $_REQUEST['red'])*$deal['rate'])/12/100);
					$data['benjin'] = sprintf("%.2f",($_REQUEST['money'] + $_REQUEST['r_money'] + $_REQUEST['red'])/$deal['repay_time']);
					$data['time'] = date("Y-m-d",strtotime("+$i month"));
					$list[] = $data;
				}

			}
		}
		echo json_encode($list);
	}*/
	
	public function mobile(){

		/*if(!$GLOBALS['user_info']){
			set_gopreview();
			app_redirect(url("index","user#login"));
		}*/

		$id = intval($_REQUEST['id']);
		
		$deal = get_deal($id);
		
		if(!$deal)
			app_redirect(url("index")); 
		
		send_deal_contract_email($id,$deal,$deal['user_id']);
		
		$deal['description'] = format_html_content_image($deal['description'],300,300);

		//借款列表
		$load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,is_auto,create_time FROM ".DB_PREFIX."deal_load WHERE deal_id = ".$id." order by id ASC ");
		
		$u_info = $deal['user'];
		
		if($deal['view_info']!=""){
			$view_info_list = unserialize($deal['view_info']);
			$GLOBALS['tmpl']->assign('view_info_list',$view_info_list);
		}
		
		
		//可用额度
		$can_use_quota = get_can_use_quota($deal['user_id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
		
		$credit_file = get_user_credit_file($deal['user_id'],$u_info);
		$deal['is_faved'] = 0;
		if($GLOBALS['user_info']){
			if($u_info['user_type']==1)
				$company = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_company WHERE user_id=".$u_info['id']);
			
			$deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
			
			if($deal['deal_status'] >=4){
				//还款列表
				$loan_repay_list = get_deal_load_list($deal);
				$GLOBALS['tmpl']->assign("loan_repay_list",$loan_repay_list);
				
				if($loan_repay_list){
					$temp_self_money_list = $GLOBALS['db']->getAll("SELECT sum(self_money) as total_money,u_key FROM ".DB_PREFIX."deal_load_repay WHERE has_repay=1 AND deal_id=".$id." group by u_key ");
					$self_money_list = array();
					foreach($temp_self_money_list as $k=>$v){
						$self_money_list[$v['u_key']]= $v['total_money'];
					}
					
					foreach($load_list as $k=>$v){
						$load_list[$k]['remain_money'] = $v['money'] -$self_money_list[$k];
						if($load_list[$k]['remain_money'] <=0){
							$load_list[$k]['remain_money'] = 0;
							$load_list[$k]['status'] = 1;
						}
					}
				}
				
				
			}	
			$user_statics = sys_user_status($deal['user_id'],true);
			$GLOBALS['tmpl']->assign("user_statics",$user_statics);
			$GLOBALS['tmpl']->assign("company",$company);
			
			
			if($deal['uloadtype'] == 1){
				$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id);
				$GLOBALS['tmpl']->assign("has_bid_money",$has_bid_money);
				$GLOBALS['tmpl']->assign("has_bid_portion",intval($has_bid_money)/($deal['borrow_amount']/$deal['portion']));
			}
		}

		$GLOBALS['tmpl']->assign("load_list",$load_list);
		$GLOBALS['tmpl']->assign("credit_file",$credit_file);
		$GLOBALS['tmpl']->assign("u_info",$u_info);

		//工作认证是否过期
		//$GLOBALS['tmpl']->assign('expire',user_info_expire($u_info));

		if($deal['type_match_row'])
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		else
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];

		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");

		//留言
		require APP_ROOT_PATH.'app/Lib/message.php';
		require APP_ROOT_PATH.'app/Lib/page.php';
		$rel_table = 'deal';
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;

		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
		else
		{
			if($message_type['is_effect']==0)
			{
				$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
			}
		}

		//message_form 变量输出
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',"deal");

		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$msg_condition = $condition." AND is_effect = 1 ";
		$message = get_message_list($limit,$msg_condition);

		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		foreach($message['list'] as $k=>$v){
			$msg_sub = get_message_list("","pid=".$v['id'],false);
			$message['list'][$k]["sub"] = $msg_sub["list"];
		}

		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}

		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("deal_mobile.html");
	}
	
	public function addmessage(){
		$user_info = $GLOBALS['user_info'];
			$rel_id = intval($_REQUEST['rel_id']);   //deal_id  出借项目id

			$data['user_id'] = $user_info['id'];
			$data['rel_id'] = $rel_id;
			$data['title'] = $_REQUEST['title'];
			$data['content'] = $_REQUEST['content'];
			
			$data['create_time'] = TIME_UTC;
			$data['rel_table'] = strim($_REQUEST['rel_table']);
			$data['is_effect'] = 1;
			
			if ($data['rel_id'] > 0){
				$GLOBALS['db']->autoExecute(DB_PREFIX."message",$data,"INSERT");
			}
			if($GLOBALS['db']->affected_rows()){
				$return['status'] = 1;
				showSuccess("操作成功",url("index","deal#mobile&id=$rel_id"));
			}
			else{
				$return["status"] = 0;
				showErr("操作失败",url("index","deal#mobile&id=$rel_id"));
			}
			
			
			
		}

		function preview(){
			$deal['id'] = 'XXX';

			$deal_loan_type_list = load_auto_cache("deal_loan_type_list");
			if(intval($_REQUEST['quota'])==1){
				$deal = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_quota_submit WHERE status=1 and user_id = ".$GLOBALS['user_info']['id']." ORDER BY id DESC");
				$type_id = intval($deal['type_id']);
				$data['view_info'] = unserialize($deal['view_info']);
				if($deal['cate_id'] > 0){
					$deal['cate_info'] = $GLOBALS['db']->getRow("select id,name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']." and is_effect = 1 and is_delete = 0");
				}

			}
			else{
				$deal['name'] = strim($_REQUEST['borrowtitle']);
				$type_id = intval($_REQUEST['borrowtype']);

				$icon_type = strim($_REQUEST['imgtype']);

				$icon_type_arr = array(
					'upload' =>1,
					'userImg' =>2,
					'systemImg' =>3,
					);
				$data['icon_type'] = $icon_type_arr[$icon_type];

				switch($data['icon_type']){
					case 1 :
					$deal['icon'] = replace_public(strim($_REQUEST['icon']));
					break;
					case 2 :
					$deal['icon'] = replace_public(get_user_avatar($GLOBALS['user_info']['id'],'big'));
					break;
					case 3 :
					$deal['icon'] = $GLOBALS['db']->getOne("SELECT icon FROM ".DB_PREFIX."deal_loan_type WHERE id=".intval($_REQUEST['systemimgpath']));
				}


				$deal['description']= replace_public(valid_str(btrim($_REQUEST['borrowdesc'])));


				$user_view_info = $GLOBALS['user_info']['view_info'];
				$user_view_info = unserialize($user_view_info);

				$new_view_info_arr = array();	
				for($i=1;$i<=intval($_REQUEST['file_upload_count']);$i++){
					$img_info = array();
					$img = replace_public(strim($_REQUEST['file_'.$i]));
					if($img!=""){
						$img_info['name'] = strim($_REQUEST['file_name_'.$i]);
						$img_info['img'] = $img;
						$img_info['is_user'] = 1;

						$user_view_info[] = $img_info;
						$ss = $user_view_info;
						end($ss);
						$key = key($ss);
						$new_view_info_arr[$key] = $img_info;
					}
				}


				$data['view_info'] = array();
				foreach($_REQUEST['file_key'] as $k=>$v){
					if(isset($user_view_info[$v])){
						$data['view_info'][$v] = $user_view_info[$v];
					}
				}

				foreach($new_view_info_arr as $k=>$v){
					$data['view_info'][$k] = $v;
				}

				if($deal['cate_id'] > 0){
					$deal['cate_info']['name'] = "借款预览标";
				}

			}


			$deal['rate_foramt'] = number_format(strim($_REQUEST['apr']),2);
			$deal['repay_time'] = strim($_REQUEST['repaytime']);
			$deal['repay_time_type'] = intval($_REQUEST['repaytime_type']);
			$deal['loantype'] = intval($_REQUEST['loantype']);

			$deal['borrow_amount'] = strim($_REQUEST['borrowamount']);
			$deal['borrow_amount_format'] = format_price($deal['borrow_amount']/10000)."万";

			$GLOBALS['tmpl']->assign('view_info_list',$data['view_info']);
			unset($data['view_info']);

			foreach($deal_loan_type_list as $k=>$v){
				if($v['id'] == $type_id){
					$deal['type_info'] = $v;
				}
			}


			$deal['min_loan_money'] = 50;
			$deal['need_money'] = $deal['borrow_amount_format'];



		//本息还款金额
			$deal['month_repay_money'] = format_price(pl_it_formula($deal['borrow_amount'],strim($deal['rate'])/12/100,$deal['repay_time']));


			if($deal['agency_id'] > 0){
				$deal['agency_info'] = get_user_info("*","id = ".$deal['agency_id']." and is_effect = 1");
			}

			$deal['progress_point'] = 0;
			$deal['buy_count'] = 0;
			$deal['voffice'] = 1;
			$deal['vjobtype'] = 1;


			$deal['is_delete'] = 2;

			$u_info = get_user("*",$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("u_info",$u_info);

			$can_use_quota=get_can_use_quota($GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);

			$credit_file = get_user_credit_file($GLOBALS['user_info']['id'],$u_info);
			$GLOBALS['tmpl']->assign("credit_file",$credit_file);
			$user_statics = sys_user_status($GLOBALS['user_info']['id'],true);
			$GLOBALS['tmpl']->assign("user_statics",$user_statics);


			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];

			$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");

			$GLOBALS['tmpl']->assign("deal",$deal);
			$GLOBALS['tmpl']->display("page/deal.html");
		}

		function bid(){
			/*status状态码
			* 0 提示错误信息，无任何操作
			* 1 投标成功
			* 2 未登录，跳转登录页面
			* 3 提示错误信息，修改输入的出借金额
			* 4 提示错误信息，跳转指定页面
			* 5 判断是否尾标
			*****/
			$switch = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 1 and status = 1"); //总开关
			$switch2 = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 7 and status = 1");  //出借开关
			if(empty($switch)||empty($switch2)){
				$return["status"] = 3;
				$return["info"] = '系统正在升级，请稍后再试';
				ajax_return($return);
			}

			$deal_id = intval($_REQUEST['deal_id']);
			$map['deal_id'] = $deal_id;
			$map['bidmoney'] = intval(strim($_REQUEST['bid_money']));
            $map['red_id'] = strim($_REQUEST['red_id']);
            //  红包金额
            if ($map['red_id']) {
            	$map['red_money'] =  $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."red_packet where id in(".$map['red_id'].")");
            }
            $map['total_money']=$map['bidmoney']+$map['red_money'];
            //  加息券
            $map['interestrate_id'] = strim($_REQUEST['interestrate_id']);
            if($map['interestrate_id']){
            	$map['interestrate_money'] = get_interestrate_money($map['interestrate_id'],$map['total_money'],$map['deal_id']);    //加息收益
            }
            // 获取已选择红包列表
            if($map['red_id']){
                $condition=" and rp.id in(".$map['red_id'].")";
                $choose_red_list=get_uc_red_list("0,1000",$GLOBALS['user_info']['id'],$condition);
                $max_money=0;
                foreach($choose_red_list['list'] as $k=>$v){
                    $choose_red_list['list'][$k]['max_use_money']=$v['ratio'];
                    $choose_red_list['list'][$k]['begin_date']=date("Y-m-d",$v['begin_time']);
                    $choose_red_list['list'][$k]['end_date']=date("Y-m-d",$v['end_time']);
                    $max_money+=$choose_red_list['list'][$k]['max_use_money'];
                }
                if($max_money>$map['bidmoney']){
                    $return["status"] = 3;
                    $return["info"] = "红包超限，请重新选择！";
                    ajax_return($return);
                }
                $GLOBALS['tmpl']->assign("choose_red_list",$choose_red_list['list']);
            }
            // 获取已选加息卡
            if($map['interestrate_id']){
                $condition=" and ic.id=".$map['interestrate_id'];
                $choose_interest_list=get_uc_interest_card_list('0,1',$GLOBALS['user_info']['id'],$condition);
                foreach($choose_interest_list['list'] as $kk=>$vv){
                    $choose_interest_list['list'][$kk]['begin_date']=date("Y-m-d",$vv['begin_time']);
                    $choose_interest_list['list'][$kk]['end_date']=date("Y-m-d",$vv['end_time']);
                    if($vv['interest_time_type']==0&&$vv['interest_time']!=0){
                        $choose_interest_list['list'][$kk]['interest_time_info']="加息时长".$vv['interest_time']."天";
                    }elseif($vv['interest_time']==0){
                        $choose_interest_list['list'][$kk]['interest_time_info']="全程加息";
                    }
                }
                $GLOBALS['tmpl']->assign("choose_interest_list",$choose_interest_list['list']);
            }
            $GLOBALS['tmpl']->assign("bid_money",$map['bidmoney']);
            //$min_loan_money = strim($_REQUEST['min_loan_money']);
			$return = array("status"=>0,"info"=>"");
            $deal = get_deal($deal_id);
			//第一判断阶梯，判断用户状态
			if(!$GLOBALS['user_info']){
				$return["status"] = 2;
				$return["info"] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];//请先登录
				$return["jump"] = url("index","user#login"); 
				ajax_return($return);
			}
            //先开通存管用户
            if($GLOBALS['user_info']['cunguan_tag']!=1){
                $return["status"] = 2;
                $return["info"] = '请先开通为存管用户！';//请先开通存管
                $return["jump"] = url("index","uc_depository_account#index");
                ajax_return($return);
            }
            // 存管版只能投存管标
            if($deal['cunguan_tag']!=1){
                $return["status"] = 3;
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
			if($map['red_money']<0){
				$return["status"] = 3;
				$return["info"] = "红包金额不能为负数";
				ajax_return($return);
			}
            /*
			if($map['interestrate_money']<0){
				$return["status"] = 3;
				$return["info"] = "代金券金额不能为负数";
				ajax_return($return);
			}


            if($deal['cunguan_tag']==0){
                // 非存管标的非存管资金
                if(($GLOBALS['user_info']['money']-$GLOBALS['user_info']['recharge_money']) < $map['bidmoney']){
                    $return["status"] = 4;
                    $return["info"] = "账户余额不足，请充值";
                    $return["jump"] = url("index","uc_money#incharge");
                    ajax_return($return);
                }
            }else{
                //判断用户是否开通存管
                if($GLOBALS['user_info']['cunguan_tag']==0){
                    $return["status"] = 3;
                    $return["info"] = "请先开通为资金存管账户！";
                    ajax_return($return);
                }
            **/
             /*
			 * 此判断为存管上线后用于区别存管资金，上线存管后开启，然后把上面判断账户余额的注释掉
			 **/
            $cunguan_money=$GLOBALS['user_info']['cunguan_money']?$GLOBALS['user_info']['cunguan_money']:0;
            if ($map['bidmoney'] > $cunguan_money) {
                $return["status"] = 4;
                $return["info"] = "账户余额不足，请充值";
                ajax_return($return);
            }
            /*
			if($GLOBALS['user_info']['money'] > $GLOBALS['user_info']['cunguan_money']) {
				if ($map['bidmoney'] > $GLOBALS['user_info']['cunguan_money']) {
					$return["status"] = 3;
					$return["info"] = "存管可用余额为" . $GLOBALS['user_info']['cunguan_money'] . "元。";
					ajax_return($return);
				}
			}elseif($GLOBALS['user_info']['money'] == $GLOBALS['user_info']['recharge_money']){
				if($GLOBALS['user_info']['money'] < $map['bidmoney']){
					$return["status"] = 4;
					$return["info"] = "存管账户余额不足，请充值";
					$return["jump"] = url("index","uc_money#incharge");
					ajax_return($return);
				}
			}else{
				$return["status"] = 3;
				$return["info"] = "资金账户出错，请联系客服处理";
				ajax_return($return);
			}

            }
            */
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
			if($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
				$return["status"] = 0;
				$return["info"] = "此标为第三方托管标，请先绑定第三方托管账户";
				$return["jump"] = url("index","uc_center");
				ajax_return($return);
			}
			//判断是否是新手专享
			$deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$GLOBALS['user_info']['id']." ");
			if($deal['is_new']==1 && $deal_load_count > 0){
				$return["status"] = 0;
				$return["info"] = "此标为新手专享，只有新手才可以出借哦";
				ajax_return($return);
			}
			/*
			 * 目前标第只有按金额出借，所以注释此段代码
			$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE user_id=".$GLOBALS['user_info']['id']." and  deal_id=".$id);
			if($deal['uloadtype'] == 1 ){
				if(floatval($map['bidmoney'])%intval($map['bidmoney'])!=0){
					$return["status"] = 0;
					$return["info"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];//请输入正确金额
					ajax_return($return);
				}
				else{
					$has_bid_portion = intval($has_bid_money)/($deal['borrow_amount']/$deal['portion']);
					if ($deal['max_portion'] > 0 ){
						if($map['bidmoney'] > ($deal['max_portion'] - $has_bid_portion)){
							$return["status"] = 0;
							$return["info"] = "您已经购买了{$has_bid_portion}份，还能购买".($deal['max_portion'] - $has_bid_portion)."份";
							ajax_return($return);
						}
					}
					elseif($map['bidmoney'] > $deal['need_portion']){
						$return["status"] = 0;
						$return["info"] = "您已经购买了{$has_bid_portion}份，还能购买".($deal['need_portion'])."份";
						ajax_return($return);
					}
				}
				$map['bidmoney'] = $map['bidmoney']*($deal['borrow_amount']/$deal['portion']);
			}
			elseif($deal['uloadtype'] == 0)
			{
				//if(((int)app_conf('DEAL_BID_MULTIPLE') > 0 && floatval($bidmoney)%app_conf('DEAL_BID_MULTIPLE')!= 0) || floatval($bidmoney)< $deal['min_loan_money'] || ($deal['max_loan_money'] > 0 && floatval($bidmoney)>$deal['max_loan_money'])){
				if($deal['need_money'] > $deal['min_loan_money']){
					if($map['bidmoney']< $deal['min_loan_money'] ){
						$return["status"] = 3;
						$return["info"] = "起投金额为".$deal['min_loan_money']."元";
						ajax_return($return);
					}
					if($deal['max_loan_money'] > 0 && $map['bidmoney']>$deal['max_loan_money']){
						$return["status"] = 3;
						$return["info"] = "最大出借金额为".$deal['max_loan_money']."元";
						ajax_return($return);
					}
				}
			}
			*/
			$deal['need_money'] = $deal['borrow_amount'] - $deal['load_money'];//剩余可投金额
			$weibiao_yes = intval($deal['need_money'])<intval($deal['min_loan_money'])?1:0;//判断是否为尾标
			if($weibiao_yes){
				if($map['red_money']||$map['interestrate_id']){
					$return["status"] = 3;
					$return["info"] = "尾标不能使用加息卡和红包";
					ajax_return($return);
				}
				if($map['bidmoney']!=$deal['need_money']){
					$return["status"] = 3;
					$return["info"] = "尾标金额不可变更";
					ajax_return($return);
				}
			}else{
				if($map['bidmoney']< $deal['min_loan_money'] ){
					$return["status"] = 3;
					$return["info"] = "起投金额为".$deal['min_loan_money']."元";
					ajax_return($return);
				}
				if($deal['max_loan_money'] > 0 && $map['bidmoney']>$deal['max_loan_money']){
					$return["status"] = 3;
					$return["info"] = "最大出借金额为".$deal['max_loan_money']."元";
					ajax_return($return);
				}
			}
			if($deal['need_money'] < ($map['bidmoney']+$map['red_money'])){
				$return["status"] = 0;
				$return["info"] = "出借总额大于可投金额";
				ajax_return($return);
			}
			if($deal['use_ecv'] !=1 && $map['red_money']){
				$return["status"] = 3;
				$return["info"] = "此标不能使用红包";
				ajax_return($return);
			}
//			if($deal['use_ecv'] == 1){
//				//红包抵用
//				/*
//				$user_id = intval($GLOBALS['user_info']['id']);
//				$sql = "select e.*,et.name from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id." AND if(e.use_limit > 0 ,(e.use_limit - e.use_count) > 0,1=1) AND if(e.begin_time >0 , e.begin_time < ".TIME_UTC.",1=1) AND if(e.end_time>0,(e.end_time + 24*3600 - 1) > ".TIME_UTC.",1=1) AND et.use_type !=2 order by e.id desc ";
//				$ecv_list = $GLOBALS['db']->getAll($sql);
//				$GLOBALS['tmpl']->assign("ecv_list",$ecv_list);
//				*/
//				$red =  $GLOBALS['db']->getOne("select cunguan_red_money from ".DB_PREFIX."user  where id = ".$GLOBALS['user_info']['id']);
//				$GLOBALS['tmpl']->assign("red",$red);
//
//			}
//			if($map['red_money']>$GLOBALS['user_info']['cunguan_red_money']){
//				$return["status"] = 3;
//				$return["info"] = "红包余额不足";
//				ajax_return($return);
//			}
			if($deal['use_interestrate'] !=1){
				if($map['interestrate_id']) {
					$return["status"] = 3;
					$return["info"] = "此标不能使用加息卡";
					ajax_return($return);
				}
			}else {
				if ($map['interestrate_id']) {
//					$ecv_count = 0;
//					$interest_card_id = explode(',', $map['interestrate_id']);
                    $interest_card_id =$GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "interest_card  where status = 0  and end_time >" . time() . " and user_id = " . intval($GLOBALS['user_info']['id'])." and id =".$map['interestrate_id']);
//					foreach ($ecv_id as $k => $v) {
//						$ecv_count += $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "ecv  where cunguan_tag=1 and id =" . $v . " and status = 0  and end_time >" . time() . " and user_id = " . intval($GLOBALS['user_info']['id']));
//					}
					if (!$interest_card_id) {
						$return["status"] = 3;
						$return["info"] = "选用加息卡已过期或存管不可用，请重新选择！";
						ajax_return($return);
					}
				}
//				if ($map['interestrate_money'] > 0) {
//					$cash_money = 0;
//					foreach ($ecv_id as $k => $v) {
//						$cash_money += $GLOBALS['db']->getOne("select money from " . DB_PREFIX . "ecv  where cunguan_tag=1 and id =" . $v . " and user_id = " . intval($GLOBALS['user_info']['id']));
//					}
//                    $cash_money = $GLOBALS['db']->getOne("select sum(money) from " . DB_PREFIX . "ecv  where cunguan_tag=1 and user_id = " . intval($GLOBALS['user_info']['id'])." and id in(".$map['interestrate_id'].")");
//					if ($cash_money != $map['interestrate_money']) {
//						$return["status"] = 3;
//						$return["info"] = "代金券金额不匹配，请重新选择";
//						ajax_return($return);
//					}
//				}
//				if (($map['bidmoney'] / 50) < $map['interestrate_money']) {
//					$return["status"] = 3;
//					$return["info"] = "代金券超出使用限额";
//					ajax_return($return);
//				}
			}
//			$GLOBALS['tmpl']->assign("deal",$deal);
//			$GLOBALS['tmpl']->assign("bidmoney",$map['bidmoney']);//出借金额
//			$GLOBALS['tmpl']->assign("red_money",$map['red_money']);//红包金额
//			$GLOBALS['tmpl']->assign("interestrate_money",$map['interestrate_money']);//代金券金额

			$return["status"] = 1;
			$return["info"] = $GLOBALS['tmpl']->fetch("page/deal_bid.html");
			$return["pc_con"] = json_encode($map);
			$return["jump"] = url("index","deal#deals");
			ajax_return($return);
		}
	function dobidstepone(){
		if(!$GLOBALS['user_info'])
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],1);
		
		if(strim($_REQUEST['name'])==""){
			showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['URGENTCONTACT'],1);
		}
		
		$data['real_name_encrypt'] = "AES_ENCRYPT('".strim($_REQUEST['name'])."','".AES_DECRYPT_KEY."')";
		if($GLOBALS['user_info']['idcardpassed'] == 0){
			if(strim($_REQUEST['idno'])==""){
				showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['IDNO'],1);
			}
			
			if(getIDCardInfo(strim($_REQUEST['idno']))==0){  //身份证正则表达式
				showErr($GLOBALS['lang']['FILL_CORRECT_IDNO'],1);
			}
			
			if(get_user_info("count(*)","idno_encrypt = AES_ENCRYPT('".strim($_REQUEST['idno'])."','".AES_DECRYPT_KEY."') and id <> ".intval($GLOBALS['user_info']['id']),"ONE")>0)
			{
				showErr(sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$GLOBALS['lang']['IDNO']),1);
			}
			if(strim($_REQUEST['idno'])!=strim($_REQUEST['idno_re'])){
				showErr($GLOBALS['lang']['TWO_ENTER_IDNO_ERROR'],1);
			}
			$data['idno_encrypt'] = "AES_ENCRYPT('".strim($_REQUEST['idno'])."','".AES_DECRYPT_KEY."')";
			$data['idcardpassed'] = 0;
		}
		
		/*手机*/
		if($GLOBALS['user_info']['mobilepassed'] == 0){
			if(strim($_REQUEST['phone'])==""){
				showErr($GLOBALS['lang']['MOBILE_EMPTY_TIP'],1);
			}
			if(!check_mobile(strim($_REQUEST['phone']))){
				showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'],1);
			}
			if(strim($_REQUEST['validateCode'])==""){
				showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['VERIFY_CODE'],1);
			}
			if(strim($_REQUEST['validateCode'])!=$GLOBALS['user_info']['bind_verify']){
				showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],1);
			}
			
			$data['mobile_encrypt'] = "AES_ENCRYPT('".strim($_REQUEST['phone'])."','".AES_DECRYPT_KEY."')";
			$data['mobilepassed'] = 1;
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
		
		showSuccess($GLOBALS['lang']['SUCCESS_TITLE'],1);
	}

	function dobid(){
		$switch = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 1 and status = 1"); //总开关
		$switch2 = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 7 and status = 1");  //出借开关
		if(empty($switch)||empty($switch2)){
			$return["status"] = 0;
			$return["info"] = '系统正在升级，请稍后再试';
			ajax_return($return);
		}
		$data['ajax'] = intval($_REQUEST["ajax"]);
		$pc_con = json_decode(trim($_REQUEST['pc_con']),true);
		$data['deal_id'] = intval($pc_con["deal_id"]);
		$data['bid_money'] = intval(strim($pc_con['bidmoney']));
		$data['red_money'] = intval(strim($pc_con['red_money']));//使用红包金额
		$data['red_id'] = strim($pc_con['red_id']);//使用红包id      逗号隔开
		$data['interestrate_id'] = intval($pc_con['interestrate_id']);//使用加息卡ID
		$data['interestrate_money'] = trim($pc_con['interestrate_money']);//加息收益
		$data['is_pc'] = 1;
		$data['learn_id'] = 0;
		$status = dobid2($data);
		if($status['status'] == 0){
			showErr($status['show_err'],$data['ajax']);
		}elseif($status['status'] == 2){
			ajax_return($status);
		}elseif($status['status'] == 3){
			showSuccess("余额不足，请先去充值",$data['ajax'],url("index","uc_money#incharge"));
		}elseif($status['status'] == 4){
            ajax_return($status);
        }else{
			//showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$ajax,url("index","deal",array("id"=>$id)));
            //get_deal($data['deal_id']);
			showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$data['ajax'],url("index","uc_invest"));
		}
	}
    // 存管版投资 wap pc  第三方交易密码成功后到这里
    function cg_dobid(){
        $seqno=$_GET['businessSeqNo'];
        $flag=$_GET['flag'];
        if($flag==2&&WAP==0){
            showErr('验密失败',0,url("index","deals"));
        }elseif($flag==2&&WAP==1){
            $GLOBALS['tmpl']->assign('msg','验密失败');
            $GLOBALS['tmpl']->display("page/deal_fail.html");
        }elseif($flag==3&&WAP==0){
            showErr('正在处理中...',0,url("index","deals"));
        }elseif($flag==3&&WAP==1){
            $GLOBALS['tmpl']->assign('msg','正在处理中...');
            $GLOBALS['tmpl']->display("page/deal_fail.html");
        }
        $data=$GLOBALS['db']->getRow("select deal_id,user_id,money as bid_money,total_money,red as red_money,add_ip,xuni_seqno,load_seqno,cunguan_tag,red_id,interestrate_id,interestrate_money,increase_interest from ".DB_PREFIX."deal_load_temp where load_seqno='".$seqno."'");
        $data['is_pc'] = 1;
        $data['learn_id'] = 0;
        $status=dobid2($data);
        $jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
        if($status['status']==1&&WAP==0){
            showSuccess('投资成功',0,url("index","uc_invest"));
//            app_redirect(url("index","uc_invest"));
        }elseif($status['status']==1&&WAP==1){
        	//投资成功清除session
			es_session::delete('red_id');
			es_session::delete('coupon_id');
			es_session::delete('lend_money');
			es_session::delete('repay_time');
//            showSuccess('投资成功',0,url("index","uc_invest"));
            app_redirect(url("index","deal#success&id=".$data['deal_id']));
        }elseif($status['status']!=1&&WAP==0){
            showErr($status['show_err'],0,url("index","deal&id=".$data['deal_id']));
        }elseif($status['status']!=1&&WAP==1){
        	es_session::delete('red_id');
			es_session::delete('coupon_id');
			es_session::delete('lend_money');
			es_session::delete('repay_time');
            $GLOBALS['tmpl']->assign('deal_id',$data['deal_id']);
            $GLOBALS['tmpl']->assign('msg',$status['show_err']);
            $GLOBALS['tmpl']->display("page/deal_fail.html");
        }
//        if($status['status'] == 0){
//            showErr($status['show_err'],$data['ajax']);
//        }elseif($status['status'] == 2){
//            ajax_return($status);
//        }elseif($status['status'] == 3){
//            showSuccess("余额不足，请先去充值",$data['ajax'],url("index","uc_money#incharge"));
//        }else{
//            //showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$ajax,url("index","deal",array("id"=>$id)));
//            //get_deal($data['deal_id']);
//            showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$data['ajax'],url("index","uc_invest"));
//        }
    }
// // 存管版投资app  第三方交易密码成功后到这里
    function appcg_dobid(){
        $seqno=$_GET['businessSeqNo'];
        $flag=$_GET['flag'];
        if($flag==2){
            $GLOBALS['tmpl']->assign('msg','验密失败');
            $GLOBALS['tmpl']->display("page/deal_fail.html");
        }elseif($flag==3){
            $GLOBALS['tmpl']->assign('msg','正在处理中...');
            $GLOBALS['tmpl']->display("page/deal_fail.html");
        }
        $jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
        $data=$GLOBALS['db']->getRow("select deal_id,user_id,money as bid_money,total_money,red as red_money,add_ip,xuni_seqno,load_seqno,cunguan_tag,red_id,interestrate_id,interestrate_money,increase_interest from ".DB_PREFIX."deal_load_temp where load_seqno='".$seqno."'");
        $data['is_pc'] = 1;
        $data['learn_id'] = 0;
        $status=dobid_app($data);
        if($status['status']==1){
            app_redirect(url("index","deal#success&id=".$data['deal_id']));
        }else{
//            $GLOBALS['tmpl']->assign('deal_id',$data['deal_id']);
            $GLOBALS['tmpl']->assign('msg',$status['show_err']);
            $GLOBALS['tmpl']->display("page/deal_fail.html");
        }
    }
	function wapdobid(){
		$switch = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 1 and status = 1"); //总开关
		$switch2 = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 7 and status = 1");  //出借开关
		if(empty($switch)||empty($switch2)){
			$return["status"] = 0;
			$return["info"] = '系统正在升级，请稍后再试';
			ajax_return($return);
		}
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$data['ajax'] = intval($user_data["ajax"]);
		$data['deal_id'] = intval($user_data["id"]);
		$data['bid_money'] = intval(strim($user_data["bid_money"]));
//		$data['bid_paypassword'] = MD5(trim(FW_DESPWD($user_data['bid_paypassword'])));
		$data['red_money'] = intval(strim($user_data["red"]));//使用红包金额
		$data['ecv_money'] = intval(strim($user_data["ecv_money"]));;//使用代金券金额
		$data['ecv_id'] = $user_data["ecv_id"];//使用代金券ID
		$data['is_pc'] = 0;
		$data['learn_id'] = 0;
		$status = dobid2($data);
		if($status['status'] == 0){
			showErr($status['show_err'],$data['ajax']);
		}elseif($status['status'] == 2){
			ajax_return($status);
		}elseif($status['status'] == 3){
			showSuccess("余额不足，请先去充值",$data['ajax'],url("index","uc_money#incharge"));
		}elseif($status['status'] == 4){
            ajax_return($status);
        }else{
			showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$data['ajax'],url("index","uc_invest"));
		}
	}
	//获取代金券
	function get_interestrate(){
		$user_id = intval($GLOBALS['user_info']['id']);
		$money = intval($_REQUEST['bid_money']/50);
		$i_money = intval($_REQUEST['i_money']);
		$i_id = $_REQUEST['i_id'];
		$ecv_id = explode(',',$i_id);
		$ecv_list = $GLOBALS['db']->getAll("select id,end_time,money from ".DB_PREFIX."ecv where cunguan_tag=1 and status = 0  and end_time >".time()." and user_id = ".$user_id." and money<=".$money." order by end_time asc");
		foreach ($ecv_list as $k => $v) {
			for($i=0;$i<count($ecv_id);$i++){
				if($v['id']==$ecv_id[$i]){
					$ecv_list[$k]['yes'] = 1;
				}
			}
			$ecv_list[$k]['cont'] = "满".($v['money'] * 50)."元可用";
			$ecv_list[$k]['time'] = date("Y-m-d",$v['end_time']);
		}
		if(count($ecv_list)<1){
			$result['info']="您的账户暂无当前出借金额可匹配的代金券!";
			$result['status']=0;
			ajax_return($result,0);
		}
		$GLOBALS['tmpl']->assign("interestrate_list",$ecv_list);
		$result["page"] = $GLOBALS['tmpl']->fetch("page/deal_interestrate.html");
		// $result['info']=$res["page"];
		$result['status']=1;
		ajax_return($result);
	}


	//加息卡
	public function plus_interest(){
        $limit=' 0,1000';
        $user_id=$GLOBALS['user_info']['id'];
        $deal_id=intval($_REQUEST['deal_id']);
        // 投资可用红包
        $red_list=get_interest_card_list($limit,$user_id,$deal_id);
        foreach($red_list['list'] as $k=>$v){
            $red_list['list'][$k]['begin_date']=date("Y-m-d",$v['begin_time']);
            $red_list['list'][$k]['end_date']=date("Y-m-d",$v['end_time']);
        }
        $GLOBALS['tmpl']->assign('interest_list',$red_list['list']);
        $GLOBALS['tmpl']->assign('count',$red_list['count']);
		$result['status']=1;
		$result["page"] = $GLOBALS['tmpl']->fetch("page/deal_plus_interest.html");
		ajax_return($result);
	}


	//红包
	public function red_envelope (){
        $limit=' 0,1000';
        $user_id=$GLOBALS['user_info']['id'];
        $deal_id=intval($_REQUEST['deal_id']);
        // 投资可用红包
        $red_list=get_red_list($limit,$user_id,$deal_id);

        foreach($red_list['list'] as $k=>$v){
            $red_list['list'][$k]['max_use_money']=$v['ratio'];
            $red_list['list'][$k]['begin_date']=date("Y-m-d",$v['begin_time']);
            $red_list['list'][$k]['end_date']=date("Y-m-d",$v['end_time']);
        }
        $GLOBALS['tmpl']->assign('red_list',$red_list['list']);
        $GLOBALS['tmpl']->assign('count',$red_list['count']);
		$result['status']=1;
		$result["page"] = $GLOBALS['tmpl']->fetch("page/deal_red_envelope.html");
		ajax_return($result);
	}



	public function down_contract(){
		$pid = intval($_REQUEST['id']);
		if(!is_numeric($pid) || empty($pid)) {
			$this->error("参数错误");
		}
		$is_debts = $GLOBALS['db']->getOne("select debts from ".DB_PREFIX."deal where id=".$pid);
		$contract_id = $is_debts ? 13 : 11 ;
		$title = $is_debts ? "债权转让及受让协议" : "出借协议";
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($contract_id));
		require APP_ROOT_PATH.'app/Lib/contract.php';
		$pdf = new contract();
		$file_name = $title.".pdf";
		$pdf->contractOutputByHtml($contract,$file_name,'I',$title);
	}
	public function cg_pass(){
		require APP_ROOT_PATH."system/utils/Depository/Require.php";
		$publics = new Publics();
		$load_seqno=$_REQUEST['load_seqno'];
		$html =  $publics ->verify_trans_password('deal','appcg_dobid',$GLOBALS['user_info']['id'],4,$load_seqno,"_self");
        echo $html;die;
	}


    public function experience_deal(){
//        $deal=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."experience_deal where is_effect=1 and is_delete=0 limit 1");
//        // loantype  0 等额本息    1 按月付息,到期还本  2到期还本息  3本金均摊，利息固定
//        if($deal['loantype']==0){
//            $deal['repay_type']="等额本息";
//        }elseif($deal['loantype']==1){
//            $deal['repay_type']="按月付息,到期还本";
//        }elseif($deal['loantype']==2){
//            $deal['repay_type']="到期还本息";
//        }elseif($deal['loantype']==3){
//            $deal['repay_type']="本金均摊，利息固定";
//        }
//
//        $GLOBALS['tmpl']->assign("deal",$deal);
//        $GLOBALS['tmpl']->display("page/experience_deal.html");

        $id = 6832;
        $deal = get_deal($id);

        if($deal['deal_status']==1) {
            $deal['residual_time'] = ceil($deal['remain_time']/86400);
        }else{
            $deal['residual_time'] = 0;
        }
        /*
        $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$id);
        $need_money_re = $deal['borrow_amount'] - $need_money;
        $progress = floatval($need_money/$deal['borrow_amount']*100);
        if($need_money_re<0){
            $need_money_re = '0';
        }

        if($progress>100){
            $progress = '100';
        }
        if( floatval(99.994) < $progress && $progress < 100){
            $progress_point = "99.9";
        }else{
            $progress_point = sprintf("%.1f",round($need_money/$deal['borrow_amount']*100,2));
        }
        if(WAP==1){
            $deal['progress_point']= substr_replace($progress_point, '', strpos($progress_point, '.') + 2);
        }else{
            $deal['progress_point'] = round($need_money/$deal['borrow_amount']*100,2);
        }
        */
        $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$id);

        $progress = sprintf("%.2f",floatval($need_money/$deal['borrow_amount']*100));
        if($need_money>=$deal['borrow_amount']){
            $deal['progress_point'] = '100';
        }elseif($progress*100>=9999&&$need_money<$deal['borrow_amount']){
            $deal['progress_point'] = '99.99';
        }else{
            $deal['progress_point'] = $progress;
        }
        if(!$deal)
            app_redirect(url("index"));
        send_deal_contract_email($id,$deal,$deal['user_id']);

        //==========wap端与pc端请求区分开始================
        require APP_ROOT_PATH.'app/Lib/page.php';
        $count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE deal_id = ".$id);
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
        $load_list = $GLOBALS['db']->getAll("SELECT ub.deal_id,ub.user_id,ub.user_name,ub.money,ub.is_auto,ub.create_time,ub.ecv_money,ub.red,ub.total_money,b.mobile FROM ".DB_PREFIX."deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE deal_id = ".$id." order by ub.id desc limit ".$limit);
        /*
        $pages=$this->investment_list($id);
        $load_list = $pages['list'];
        */
        $u_info = $deal['user'];
        if($deal['view_info']!=""){
            $view_info_list = unserialize($deal['view_info']);
            $GLOBALS['tmpl']->assign('view_info_list',$view_info_list);
        }
        //可用额度
        $can_use_quota = get_can_use_quota($deal['user_id']);
        $GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
        $credit_file = get_user_credit_file($deal['user_id'],$u_info);
        $deal['is_faved'] = 0;
        if($GLOBALS['user_info']){
            if($u_info['user_type']==1)
                $company = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_company WHERE user_id=".$u_info['id']);

            $deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".intval($GLOBALS['user_info']['id']));

            if($deal['deal_status'] >=4){
                //还款列表
                $loan_repay_list = get_deal_load_list($deal);

                $GLOBALS['tmpl']->assign("loan_repay_list",$loan_repay_list);

                if($loan_repay_list){
                    $temp_self_money_list = $GLOBALS['db']->getAll("SELECT sum(self_money) as total_money,u_key FROM ".DB_PREFIX."deal_load_repay WHERE has_repay=1 AND deal_id=".$id." group by u_key ");
                    $self_money_list = array();
                    foreach($temp_self_money_list as $k=>$v){
                        $self_money_list[$v['u_key']]= $v['total_money'];
                    }

                    foreach($load_list as $k=>$v){
                        $load_list[$k]['remain_money'] = $v['money'] -$self_money_list[$k];
                        if($load_list[$k]['remain_money'] <=0){
                            $load_list[$k]['remain_money'] = 0;
                            $load_list[$k]['status'] = 1;
                        }
                    }
                }
            }
            /*$user_statics = sys_user_status($deal['user_id'],true);
            $GLOBALS['tmpl']->assign("user_statics",$user_statics);*/
            $GLOBALS['tmpl']->assign("company",$company);
            $user_info = $GLOBALS['db']->getRow("select vip_id,level_id from ".DB_PREFIX."user where id= ".$GLOBALS['user_info']['id']." ");
            $type_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_loan_type WHERE id=".$deal['type_id']);

            if($type_info['costsetting'] && $user_info['vip_id']>0){
                $vo_list = explode("\n",$type_info['costsetting']);
                foreach($vo_list as $k=>$v){
                    $vo_list[$k] = explode("|",$v);
                    if($vo_list[$k]['0'] == $user_info['vip_id']){
                        if($vo_list[$k]['1']>0 || $vo_list[$k]['2']>0 || $vo_list[$k]['3']>0 || $vo_list[$k]['4']>0 || $vo_list[$k]['5']>0 || $vo_list[$k]['6']>0 ){
                            $deal['user_loan_manage_fee'] = $vo_list[$k]['3'];
                            $deal['user_loan_interest_manage_fee'] = $vo_list[$k]['4'];
                        }
                    }
                }
            }else{
                if($type_info['levelsetting']){
                    $vol_list = explode("\n",$type_info['levelsetting']);
                    foreach($vol_list as $kl=>$vl){
                        $vol_list[$kl] = explode("|",$vl);
                        if($vol_list[$kl]['0'] == $user_info['level_id']){
                            if($vol_list[$kl]['1']>0 || $vol_list[$kl]['2']>0 || $vol_list[$kl]['3']>0 || $vol_list[$kl]['4']>0 || $vol_list[$kl]['5']>0 || $vol_list[$kl]['6']>0){
                                $deal['user_loan_manage_fee'] = $vol_list[$kl]['3'];
                                $deal['user_loan_interest_manage_fee'] = $vol_list[$kl]['4'];
                            }
                        }
                    }
                }
            }
            if($deal['uloadtype'] == 1){
                $has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
                $GLOBALS['tmpl']->assign("has_bid_money",$has_bid_money);
                $GLOBALS['tmpl']->assign("has_bid_portion",intval($has_bid_money)/($deal['borrow_amount']/$deal['portion']));
            }
        }
        foreach($load_list as $k=>$v){
            $load_list[$k]['money'] = $v['total_money'];
        }
        $GLOBALS['tmpl']->assign("load_list",$load_list);
        $GLOBALS['tmpl']->assign("credit_file",$credit_file);
        $GLOBALS['tmpl']->assign("u_info",$u_info);
        if($deal['type_match_row'])
            $seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
        else
            $seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];

        $GLOBALS['tmpl']->assign("page_title",$seo_title);
        $seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
        $GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
        $seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
        $GLOBALS['tmpl']->assign("seo_description",$seo_description.",");

        //留言
        /*require APP_ROOT_PATH.'app/Lib/message.php';*/
        /*
        $rel_table = 'deal';

        $message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
        $condition = "rel_table = '".$rel_table."' and rel_id = ".$id;

        if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
        {
            $condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
        }
        else
        {
            if($message_type['is_effect']==0)
            {
                $condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
            }
        }
        $GLOBALS['tmpl']->assign('rel_id',$id);
        $GLOBALS['tmpl']->assign('rel_table',$rel_table);		*/

        //==========wap端与pc端请求区分结束================
        /*
        $msg_condition = $condition." AND is_effect = 1 ";
		$message = get_message_list($limit,$msg_condition);
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		foreach($message['list'] as $k=>$v){
			$msg_sub = get_message_list("","pid=".$v['id'],false);
			$message['list'][$k]["sub"] = $msg_sub["list"];
		}
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}
        */
        //==========wap端与pc端请求区分开始================
        $xs = 10;
        $user_id = intval($GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign("user_id",$user_id);
        $deal["rate"] = sprintf("%.1f",$deal["rate"]); //统一预期年化收益格式
        $deal['ymb'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'ymb'");
        $deal['bank'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config where code = 'bank'");

        if($deal['rate'] >= 8)$deal['rate_progress'] = 80;
        else $deal['rate_progress'] = $deal['rate']*$xs;
        if($deal['bank'] < 2)$deal['bank_progress'] = 20;
        else $deal['bank_progress'] = $deal['bank']*$xs;
        $deal['ymb_progress'] = $deal['ymb']*$xs;

        $deal['uid'] = intval($GLOBALS['user_info']['id']);
        $deal['over_amount'] = $deal['borrow_amount'] - $deal['load_money'];

        //$deal['over_amount'] = $need_money_re;


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
        //剩余募集天数
        if($deal['deal_status']==1) {
            $deal['surplus_enddate'] = ceil($deal['remain_time']/86400);
        }else{
            $deal['surplus_enddate'] = 0;
        }

        $GLOBALS['tmpl']->assign( 'mortgage_infos',$mortgage_infos);
        $ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips where code = 2" );
        $userinfo = $GLOBALS['user_info'];
        $user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where cunguan_tag = 1 and user_id= ".$userinfo['id']);
        //存管出借 验证
        if($userinfo['cunguan_tag'] == 0){
            $ajax['code'] = 0;
            $ajax['url'] = url("index","uc_depository_account"); //判断存管是否开户
        }else if($userinfo['cunguan_tag'] == 1 && $userinfo['cunguan_pwd'] == 0){
            $ajax['code'] = 1;
            $ajax['url'] = url("index","uc_depository_paypassword#setpaypassword"); //判断存管是否设置交易密码
        }else if($userinfo['cunguan_tag'] == 1 && $userinfo['cunguan_pwd'] == 1 && $user_status < 1){
            $ajax['code'] = 1;
            $ajax['url'] = url("index","uc_depository_addbank#wap_check_pwd"); //判断存管是否设置交易密码
        }else{
            $ajax['code'] = 4;
        }
        /*$usinfos = $GLOBALS['db']->getRow("select AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,u.paypassword,b.bankcard,b.status from ".DB_PREFIX."user u left join ".DB_PREFIX."user_bank b on u.id = b.user_id where u.id= ".$userinfo['id']);
        $user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where  user_id= ".$userinfo['id']);
        if($userinfo['id'] && !$usinfos['bankcard'] && !$usinfos['idno']){
            $ajaxurl = url("index","uc_center#identity");
        }else if($userinfo['id'] && !$usinfos['bankcard'] && $usinfos['idno']){
            $ajaxurl = url("index","uc_account#bind_bank");
        }else if($usinfos['bankcard'] && $user_status == 0){
            $ajaxurl = url("index","uc_account#bind_bank");
        }else if(!$usinfos['paypassword']){
            $ajaxurl = url("index","uc_account#wappaypassword");
        }*/
        jumpUrl("jump_url_depository");
        $GLOBALS['tmpl']->assign("user_id",$user_id);
        $GLOBALS['tmpl']->assign('ajax',$ajax);
        $GLOBALS['tmpl']->assign("ips",$ips);
        $GLOBALS['tmpl']->assign("img",$img);
        $GLOBALS['tmpl']->assign("yes",$yes);
        $GLOBALS['tmpl']->assign("deal",$deal);
        $GLOBALS['tmpl']->assign("money",floatval($GLOBALS['user_info']['cunguan_money']));
        //==========wap端与pc端请求区分结束================
        $GLOBALS['tmpl']->assign("config",$config);
        $GLOBALS['tmpl']->assign("ACTION_NAME","deal");
        $GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
        $GLOBALS['tmpl']->display("page/experience_deal.html");
    }
    //==========wap端与pc端请求区分================
	public function deals(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));
		}

		/*获取session参数 输出到页面*/
		$deal_id = es_session::get('deal_id');       	//出借标id
		$lend_money = es_session::get('lend_money');	//出借金额
		$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		$GLOBALS['tmpl']->assign("lend_money",$lend_money);
		/*获取session参数*/
		if(es_session::get('red_id')){
			$red_id = rtrim(es_session::get('red_id'),",");
			$red_money=intval($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."red_packet where id in(".$red_id.")"));
		}
		$GLOBALS['tmpl']->assign("red_id",$red_id);
		$GLOBALS['tmpl']->assign("red_money",$red_money);
		//获取红包张数
		$red_packets = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet where user_id = $user_id and packet_type < 3 and status = 0 and end_time >".time());
		$GLOBALS['tmpl']->assign("red_packets",$red_packets);
		//获取加息卡张数
		$raise_interes = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id = $user_id and status = 0 and end_time >".time());
		$GLOBALS['tmpl']->assign("raise_interes",$raise_interes);
		if(es_session::get('coupon_id')){
			$coupon_id = rtrim(es_session::get('coupon_id'));
			$coupon_rate = "+".$GLOBALS['db']->getOne("select rate as money from ".DB_PREFIX."interest_card where id = $coupon_id");
		}
		$GLOBALS['tmpl']->assign("coupon_id",$coupon_id);
		$GLOBALS['tmpl']->assign("coupon_rate",$coupon_rate);
		$GLOBALS['tmpl']->assign("cungaun_money",$GLOBALS['user_info']['cunguan_money']);
		$deal = get_deal($deal_id);//min_loan_money
		$deal['need_money'] = $deal['borrow_amount'] - $deal['load_money'];
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/deal_deals.html");
	}
    //出借使用红包
    public function red_packet(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));
		}
		$lend_money = es_session::get('lend_money');
		$repay_time = es_session::get('repay_time');
		$GLOBALS['tmpl']->assign("lend_money",$lend_money);
		if(es_session::get('red_id')){
			$reds_id=explode(",",es_session::get('red_id'));
		}
		$red_packet = $GLOBALS['db']->getAll("select rp.id,rp.begin_time,rp.end_time,rp.content,rp.money,rpn.use_condition,rpn.amount,rpn.ratio from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id = rpn.id where rp.user_id = $user_id and rp.packet_type < 3 and rp.status = 0 and rp.end_time >".time());
		foreach ($red_packet as $k => $v) {
			foreach ($reds_id as $key => $value) {
				if($value == $v['id']){
					$v['status'] = 1;
				}
			}
			if($v['ratio'] > $lend_money){
				$v['code'] = 1;
			}
			$in = in_array($repay_time,explode(",",$v['use_condition'])); //是否可用
			if(!$in){
				$v['code'] = 1;
			}
			$v['money'] = intval($v['money']);
			$v['use_condition'] = str_replace(",","、",$v['use_condition']);
			$v['amount'] = $v['ratio'];
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		/****优惠券使用说明*****/
		$red_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'red_explain'"));
		$GLOBALS['tmpl']->assign("red_explain",$red_explain);
		$GLOBALS['tmpl']->display("page/deal_red_packet.html");
	}
	//出借使用加息卡
    public function raise_interes(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));
		}
		$lend_money = es_session::get('lend_money');
		$repay_time = es_session::get('repay_time');
		$GLOBALS['tmpl']->assign("lend_money",$lend_money);
		if(es_session::get('red_id')){
			$reds_id=explode(",",es_session::get('red_id'));
		}
		$raise_interes = $GLOBALS['db']->getAll("select i.*,c.use_condition,c.one_month,c.three_month,c.six_month,c.twelve_month,c.interest_time from ".DB_PREFIX."interest_card i left join ".DB_PREFIX."coupon c on i.coupon_id = c.id where i.user_id = $user_id  and i.status = 0 and i.end_time >".time());
		foreach ($raise_interes as $k => $v) {
			if($v['interest_time']  >0){
				$v['interest_time'] = "加息".$v['interest_time']."天";
			}else{
				$v['interest_time'] = "全程加息";
			}
			if(es_session::get('coupon_id')){
				$coupon_id=es_session::get('coupon_id');
				if($coupon_id == $v['id']){
					$v['status'] = 1;
				}
			}
			$in = in_array($repay_time,explode(",",$v['use_condition'])); //是否可用
			if(!$in){
				$v['code'] = 1;
			}
			$v['use_condition'] = str_replace(",","、",$v['use_condition']);
			$v['money'] = intval($v['money']);
			$v['amount'] = $v['amount'] * $v['ratio'];
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		/****加息卡使用说明*****/
		$pluscard_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'pluscard_explain'"));
		$GLOBALS['tmpl']->assign("pluscard_explain",$pluscard_explain);
		$GLOBALS['tmpl']->display("page/deal_raise_interes.html");
	}
}
?>

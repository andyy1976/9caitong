<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class experience_dealModule extends SiteBaseModule
{
	public function index(){
		
		$user_id =$GLOBALS['user_info']['id']; 
		$FictitiousMoney_ids=$_REQUEST['FictitiousMoney_ids']; //体验金的id
		$FictitiousMoney_ids=rtrim($FictitiousMoney_ids,',');
		$ecv_money =$GLOBALS['db']->getOne("select sum(money)as money from " . DB_PREFIX . "taste_cash  where cunguan_tag=1 and use_status = 0  and end_time >" . time() . " and user_id = " . intval($user_id)." and id in (".$FictitiousMoney_ids.")");
		$ecv_money =strstr($ecv_money,'.',true);	
		$id = intval($_REQUEST['id']);
		$experience_deal = $GLOBALS['db']->getRow("select id,name,invest_notice,description,rate,borrow_amount,load_money,loantype,min_loan_money,repay_time,deal_status,FORMAT(load_money/borrow_amount*100,2) as progress_point from ".DB_PREFIX."experience_deal where id = ".$id);
		if($experience_deal){
			$experience_deal['loantype_format'] = loantypename($experience_deal['loantype'],1);
			$experience_deal['rate'] = sprintf("%.1f",$experience_deal['rate']);	
			$experience_deal['need_money'] = sprintf("%.2f",($experience_deal["borrow_amount"]-$experience_deal["load_money"]));		
		}

		$xs = 10;
		$user_id = intval($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$experience_deal['ymb'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'ymb'"); 
		$experience_deal['bank'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config where code = 'bank'");
		$need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."experience_deal_load where deal_id=".$id);
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
		$load_list = $GLOBALS['db']->getAll("SELECT ub.user_id,ub.money,b.mobile,ub.user_name,ub.total_money,ub.create_time FROM ".DB_PREFIX."experience_deal_load ub  LEFT JOIN ".DB_PREFIX."user b on ub.user_id=b.id WHERE ub.deal_id = ".$id." order by ub.id desc limit ".$limit); 		
		
		if($experience_deal['rate'] >= 8)$experience_deal['rate_progress'] = 80;
		else $experience_deal['rate_progress'] = $experience_deal['rate']*$xs;
		if($experience_deal['bank'] < 2)$experience_deal['bank_progress'] = 20;
 		else $experience_deal['bank_progress'] = $experience_deal['bank']*$xs;
		$experience_deal['ymb_progress'] = $experience_deal['ymb']*$xs;

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
		$GLOBALS['tmpl']->assign('ajax',$ajax);
		$GLOBALS['tmpl']->assign("need_money",intval($need_money));
		$GLOBALS['tmpl']->assign("ecv_money",$ecv_money); //体验金的钱
		$GLOBALS['tmpl']->assign("FictitiousMoney_ids",$FictitiousMoney_ids);//体验金的id
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign("deal",$experience_deal);	
		$GLOBALS['tmpl']->assign("load_list",$load_list);	
		$GLOBALS['tmpl']->display("page/experience_deal.html");
	}

	public function jump_gold(){ 
		$user_id = intval($GLOBALS['user_info']['id']);
		$deal_id = $_REQUEST['id']; //标的id
		$ecv_list = $GLOBALS['db']->getAll("select id,create_time,end_time,money,use_status from ".DB_PREFIX."taste_cash where cunguan_tag=1 and use_status = 0  and end_time >".time()." and user_id = ".$user_id."  order by end_time asc");
		foreach ($ecv_list as $k => $v) {			
			$ecv_list[$k]['money']=  $v['money'] =strstr($v['money'],'.',true);
			$ecv_list[$k]['voucher_explain'] =   '看产品怎么写了';
		}
		// if(count($ecv_list)<3){	
		// 	$data['info']="您的账户暂无当前出借金额可匹配的体验金!";
		// 	$data['status']=0;
		// 	ajax_return($data,0);
		// }
		$taste_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'cg_explain'"));
		$GLOBALS['tmpl']->assign("taste_explain",$taste_explain);
		$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		$GLOBALS['tmpl']->assign("interestrate_list",$ecv_list);
		$GLOBALS['tmpl']->display("page/glod_cash.html");
	}
}

?>

<?php

require_once APP_ROOT_PATH.'app/Lib/uc.php';
require_once APP_ROOT_PATH."app/Lib/deal.php";
require_once APP_ROOT_PATH."system/libs/user.php";
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
class uc_dealModule extends SiteBaseModule
{
	public function refund(){
		$user_id = $GLOBALS['user_info']['id'];
		
		$status = intval($_REQUEST['status']);
		
		$GLOBALS['tmpl']->assign("status",$status);
		
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		$deal_status = 4;
		if($status == 1){
			$deal_status = 5;
		}
		
		$result = get_deal_list($limit,0,"deal_status =$deal_status AND user_id=".$user_id,"id DESC");
		$deal_ids = array();
		foreach($result['list'] as $k=>$v){
			if($v['repay_progress_point'] >= $v['generation_position'])
				$result['list'][$k]["can_generation"] = 1;
			
			$deal_ids[] = $v['id'];
		}
		if($deal_ids){
			$temp_ids = $GLOBALS['db']->getAll("SELECT `deal_id`,`status` FROM ".DB_PREFIX."generation_repay_submit WHERE deal_id in(".implode(",",$deal_ids).") ");
			$deal_g_ids = array();
			foreach($temp_ids as $k=>$v){
				$deal_g_ids[$v['deal_id']] = $v;
			}
		
		
			foreach($result['list'] as $k=>$v){
				if(isset($deal_g_ids[$v['id']])){
					//申请中
					$result['list'][$k]['generation_status'] = $deal_g_ids[$v['id']]['status'] + 1; 
				}
			}
		}
		
		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	
//电子合同
	public function contract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal){
			showErr("操作失败！");
		}
		$load_user_id = $GLOBALS['db']->getOne("select user_id FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
		if($load_user_id == 0  && $deal['user_id']!=$GLOBALS['user_info']['id'] ){
			showErr("操作失败！");
		}
				
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		$loan_list = $GLOBALS['db']->getAll("select * FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." ORDER BY create_time ASC");
		foreach($loan_list as $k=>$v){
			$vv_deal['borrow_amount'] = $v['money'];
			$vv_deal['rate'] = $deal['rate'];
			$vv_deal['repay_time'] = $deal['repay_time'];
			$vv_deal['loantype'] = $deal['loantype'];
			$vv_deal['repay_time_type'] = $deal['repay_time_type'];
			
			$deal_rs =  deal_repay_money($vv_deal);
			$loan_list[$k]['get_repay_money'] = $deal_rs['month_repay_money'];
			if(is_last_repay($deal['loantype'])==1)
				$loan_list[$k]['get_repay_money'] = $deal_rs['remain_repay_money'];
		}
		
		$GLOBALS['tmpl']->assign('loan_list',$loan_list);
		
		if($deal['user']['sealpassed'] == 1){
			$credit_file = get_user_credit_file($deal['user_id']);
			$GLOBALS['tmpl']->assign('user_seal_url',$credit_file['credit_seal']['file_list'][0]);
		}
		
		
		$GLOBALS['tmpl']->assign('SITE_URL',str_replace(array("https://","http://"),"",SITE_DOMAIN));
		$GLOBALS['tmpl']->assign('SITE_TITLE',app_conf("SITE_TITLE"));
		$GLOBALS['tmpl']->assign('CURRENCY_UNIT',app_conf("CURRENCY_UNIT"));
		
		
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($deal['contract_id']));
		
		
		$GLOBALS['tmpl']->assign('contract',$contract);
		
		$GLOBALS['tmpl']->display("inc/uc/uc_deal_contract.html");	
	}
	
	
	//电子合同
	public function dcontract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal){
			showErr("操作失败！");
		}
		$load_user_id = $GLOBALS['db']->getOne("select user_id FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
		if($load_user_id == 0  && $deal['user_id']!=$GLOBALS['user_info']['id'] ){
			showErr("操作失败！");
		}
				
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		$loan_list = $GLOBALS['db']->getAll("select * FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." ORDER BY create_time ASC");
		foreach($loan_list as $k=>$v){
			$vv_deal['borrow_amount'] = $v['money'];
			$vv_deal['rate'] = $deal['rate'];
			$vv_deal['repay_time'] = $deal['repay_time'];
			$vv_deal['loantype'] = $deal['loantype'];
			$vv_deal['repay_time_type'] = $deal['repay_time_type'];
			
			$deal_rs =  deal_repay_money($vv_deal);
			$loan_list[$k]['get_repay_money'] = $deal_rs['month_repay_money'];
			if(is_last_repay($deal['loantype'])==1)
				$loan_list[$k]['get_repay_money'] = $deal_rs['remain_repay_money'];
		}
		
		$GLOBALS['tmpl']->assign('loan_list',$loan_list);
		
		if($deal['user']['sealpassed'] == 1){
			$credit_file = get_user_credit_file($deal['user_id']);
			$GLOBALS['tmpl']->assign('user_seal_url',$credit_file['credit_seal']['file_list'][0]);
		}
		
		$GLOBALS['tmpl']->assign('SITE_URL',str_replace(array("https://","http://"),"",SITE_DOMAIN));
		$GLOBALS['tmpl']->assign('SITE_TITLE',app_conf("SITE_TITLE"));
		$GLOBALS['tmpl']->assign('CURRENCY_UNIT',app_conf("CURRENCY_UNIT"));
		
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($deal['contract_id']));
	
	
		$GLOBALS['tmpl']->assign('contract',$contract);
		/*header("Content-type:text/html;charset=utf-8");
		header("Content-Disposition: attachment; filename=借款协议.html");
		
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
		echo '<html>';
		echo '<head>';
		echo '<title>借款协议</title>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo '<meta http-equiv="X-UA-Compatible" content="IE=7" />';
		echo  $GLOBALS['tmpl']->fetch("inc/uc/uc_deal_contract.html");
		echo '</body>';
		echo '</html>';*/
		require APP_ROOT_PATH."/system/utils/word.php";
    	$word = new word(); 
   		$word->start(); 
   		$wordname = "借款协议.doc"; 
   		echo  $GLOBALS['tmpl']->fetch("inc/uc/uc_deal_contract.html");
   		$word->save($wordname); 
		
	}
	//全部还款操作界面
	public function  all_quick_refund(){
		$deal_id = intval($_REQUEST['deal_id']);
		if($deal_id == 0){
			showErr("操作失败！");
		}

		if($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where has_repay=3 and deal_id=".$deal_id) > 0){
			showErr("全部还款失败,有代偿未还款",0,url("index","uc_deal#borrowed"));
		}

		
		$repay = $GLOBALS['db']->getAll("SELECT repay_money,l_key+1 as l_key,interest_money,repay_time,self_money from ".DB_PREFIX."deal_repay where deal_id=".$deal_id." and has_repay=0");
		$repay_time = $GLOBALS['db']->getOne("SELECT repay_time FROM " . DB_PREFIX . "deal_repay WHERE deal_id =".$deal_id." and has_repay=1 order by id desc limit 1");
		$list = array();
		foreach($repay as $k=>$v){
			$l_key[] = $v['l_key'];
			if($k==0){
				if(time()>strtotime(date("Y-m-d 23:59:59",$v['repay_time']))||time()>strtotime(date("Y-m-d 23:59:59",$repay_time))){
					$list['repay_money'] += $v['repay_money'];
				}else{
					$list['repay_money'] += $v['repay_money']-$v['interest_money'];
				}
			}else {
				$list['repay_money'] += $v['repay_money'] - $v['interest_money'];
			}
		}
		$list['l_key'] = implode(',',$l_key);
		$list['cunguan_money'] = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'__JIUCAITONGAESKEY__') as cunguan_money from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
		$deal = get_deal($deal_id);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where deal_id=".$deal_id." and has_repay=3")>0){
			$deal['deal_status']=4;
		}
		if($deal['deal_status']!=4){
			showErr("借款不是还款状态！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_all_quick_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//全部还款验密
	public function all_repay_borrow_money(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			$status['status'] = 1;
			$status['jump'] = url("index","user#login");
			$status['info'] = "请先登录！";
			ajax_return($status);
		}
		$deal_id = intval($_REQUEST['deal_id']);
		if(!$deal_id){
			$status['status'] = 0;
			$status['info'] = "请稍后重试！";
			ajax_return($status);
		}
		if($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where has_repay=3 and deal_id=".$deal_id) > 0){
			$status['status'] = 0;
			$status['info'] = "有代偿未还款";
			ajax_return($status);
		}
		$repay = $GLOBALS['db']->getAll("SELECT repay_money,interest_money from ".DB_PREFIX."deal_repay where deal_id=".$deal_id." and has_repay=0");
		$list = array();
		foreach($repay as $k=>$v){
			if($k==0){
				$list['repay_money'] += $v['repay_money'];
			}else {
				$list['repay_money'] += $v['repay_money'] - $v['interest_money'];
			}
		}
		$list['cunguan_money'] = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'__JIUCAITONGAESKEY__') as cunguan_money from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
		if($list['repay_money']>$list['cunguan_money']){
			$status['status'] = 1;
			$status['info'] ="余额不足！请先充值";
			$status['jump'] =url("member","uc_money#incharge");
			ajax_return($status);
		}else {
			$status['status'] = 2;
			$status['jump'] = url("member", "uc_deal#all_verify_trans_password&deal_id=$deal_id");
			ajax_return($status);
		}
	}
	//全部还款第一步:验证交易密码
	public function all_verify_trans_password(){
		$is_mobile = isMobile();
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			if($is_mobile){
				$this->all_quick_refund_fail('请稍后重试！');
			}else{
				showErr("请稍后重试！",0,url("index","user#login"));
			}
		}
		$deal_id = intval($_REQUEST['deal_id']);
		if(!$deal_id){
			if($is_mobile){
				$this->all_quick_refund_fail('请稍后重试！');
			}else{
				showErr("请稍后重试！",0,url("member","uc_deal#borrowed"));
			}
		}
		if($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where has_repay=3 and deal_id=".$deal_id) > 0){
			if($is_mobile){
				$this->all_quick_refund_fail("全部还款失败,有代偿未还款".$res1['respHeader']['respMsg']);
			}else{
				showErr("全部还款失败,有代偿未还款",0,url("index","uc_deal#borrowed"));
			}
		}
		$repay = $GLOBALS['db']->getOne("SELECT count(id) from ".DB_PREFIX."deal_repay where deal_id=".$deal_id." and cunguan_tag=1 and has_repay=0");
		if(!$repay){
			if($is_mobile){
				$this->all_quick_refund_fail('请稍后重试！');
			}else{
				showErr("请稍后重试！",0,url("index","uc_deal#borrowed"));
			}
			
		}
		
		if($GLOBALS['user_info']['user_type']==1){
			$this->all_repay_money($deal_id);
		}else {
			$Publics = new Publics();
			$seqno = $Publics->seqno();
			$data['seqno'] = $seqno;
			$GLOBALS['db']->autoExecute(DB_PREFIX . "deal_repay", $data, "UPDATE", "deal_id=" . $deal_id);
			$html = $Publics->verify_trans_password('uc_deal', 'all_repay_money', $user_id, '4', $seqno, "_self");
			echo $html;
			die;
		}
	}
	//全部还款：开始还款
	public function all_repay_money($deal_id=''){
		$is_mobile = isMobile();
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){//判断用户是否登陆
			if($is_mobile){
				$this->all_quick_refund_fail('请先登录');
			}else{
				showErr("请先登录",0,url("index","user#login"));
			}
		}

		if($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where has_repay=3 and deal_id=".$deal_id) > 0){
			showErr("有代偿未还款",0,url("index","uc_deal#borrowed"));
		}
		if($GLOBALS['user_info']['user_type']==1){
			$Publics = new Publics();
			$seqno = $Publics->seqno();
			$repay_info = $GLOBALS['db']->getAll("select dr.id as repay_id,dr.deal_id,dr.user_id,dr.repay_time,dr.repay_money,dr.interest_money,dr.self_money,d.objectaccno,u.accno from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id left join ".DB_PREFIX."user u on u.id = d.user_id where dr.deal_id='".$deal_id."' and dr.has_repay=0 and dr.cunguan_tag=1");
		}else {
			$flag = $_GET['flag'];
			if($flag!=1){
				if($is_mobile){
					$this->all_quick_refund_fail('请稍后重试');
				}else{
					showErr("请稍后重试！",0,url("member","uc_deal#borrowed"));
				}
			}
			$seqno = strim($_GET['businessSeqNo']);
			if(!$seqno){
				if($is_mobile){
					$this->all_quick_refund_fail('请稍后重试');
				}else{
					showErr("请稍后重试！",0,url("member","uc_deal#borrowed"));
				}
			}
			$repay_info = $GLOBALS['db']->getAll("select dr.id as repay_id,dr.deal_id,dr.user_id,dr.repay_time,dr.repay_money,dr.interest_money,dr.self_money,d.objectaccno,u.accno from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id left join ".DB_PREFIX."user u on u.id = d.user_id where dr.seqno='$seqno' and dr.has_repay in(0,3) and dr.cunguan_tag=1");
		}
		if(!$repay_info){
			if($is_mobile){
				$this->all_quick_refund_fail('此还款计划不存在');
			}else{
				showErr("此还款计划不存在",0,url("member","uc_deal#borrowed"));
			}
		}
		$deal = new Deal();
		$GLOBALS['db']->startTrans();   //开始事务
		$money = 0;
		$oderNo=0;
		$count_repay = count($repay_info);
		$s = 1;
		$repay_time = $GLOBALS['db']->getOne("SELECT repay_time FROM " . DB_PREFIX . "deal_repay WHERE user_id=" . $user_id . " and deal_id =".$repay_info[0]['deal_id']." and has_repay=1 order by id desc limit 1");
		foreach($repay_info as $k=>$v){
			$deal_repay_info = $GLOBALS['db']->getAll("select u.accno,u.id as user_id,dlr.id,dlr.load_id,dlr.virtual_info,dlr.repay_money,dlr.interest_money,dlr.self_money,dlr.debts_deal_id,dlr.raise_money,dlr.interestrate_money,dlr.increase_interest  from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."user u on u.id=dlr.user_id where dlr.repay_id = ".$v['repay_id']." and dlr.has_repay=0 and dlr.cunguan_tag=1");
			if($k==0){
				if(time()>strtotime(date("Y-m-d 23:59:59",$v['repay_time']))||time()>strtotime(date("Y-m-d 23:59:59",$repay_time))){
					//$this->all_quick_refund_fail("11");die;
					//融资人还款记录
					$money += $v['repay_money'];
					$repay_data['true_repay_money'] = $v['repay_money'];
					$repay_data['true_interest_money'] = $v['interest_money'];
					//投资人记录
					foreach ($deal_repay_info as $key => $value) {
						$load_repay_data['user_id'] = $value['user_id'];
						$load_repay_data['accno'] = $value['accno'];
						$load_repay_data['repay_money'] = $value['repay_money'];
						$load_repay_datas[] = $load_repay_data;
						$oderNo++;
						if ($value['debts_deal_id']) {
							$deal_id = $value['debts_deal_id'];
						}
						$load_repay['oderNo'] = $oderNo;
						$load_repay['debitAccountNo'] = $v['objectaccno'];
						$load_repay['cebitAccountNo'] = $value['accno'];
						$load_repay['currency'] = "CNY";
						$load_repay['amount'] = floatval($value['repay_money']);
						$load_repay['otherAmounttype'] = "";
						$load_repay['otherAmount'] = "";
						$load_repay['summaryCode'] = "T05";
						$load_repays[] = $load_repay;
						//修改状态
						$statusArray['calculate_status'] = 1;
						$statusArray['has_repay'] = 1; //设置已还款标志
						$statusArray['status'] = 4; //区分全部还款
						$statusArray['true_repay_time'] = strtotime(date('Y-m-d'));
						$statusArray['true_repay_date'] = date('Y-m-d', time());
						$statusArray['true_repay_money'] = $value['repay_money'];
						$statusArray['true_self_money'] = $value['self_money'];
						$statusArray['true_interest_money'] = $value['interest_money'];
						if ($count_repay == $s) {
							$statusArray['raise_money'] = $value['raise_money'];
							$statusArray['interestrate_money'] = $value['interestrate_money'];
							$statusArray['increase_interest'] = $value['increase_interest'];
						}
						$statusArray['virtual_info'] = $value['virtual_info'];
						$statusArray['load_id'] = $value['load_id'];
						$statusArray['user_id'] = $value['user_id'];
						$statusArray['accno'] = $value['accno'];
						$statusArray['id'] = $value['id'];
						$statusArrays[] = $statusArray;
					}
				}else{
					if($v['self_money']>0){
						//$this->all_quick_refund_fail("22");die;
						//融资人还款记录
						$money += $v['repay_money'] - $v['interest_money'];
						$repay_data['true_repay_money'] = $v['repay_money'] - $v['interest_money'];
						$repay_data['true_interest_money'] = 0;
						//投资人记录
						foreach($deal_repay_info as $key => $value){
							$oderNo++;
							if($value['debts_deal_id']){
								$deal_id = $value['debts_deal_id'];
							}
							$load_repay_money = $value['repay_money'] - $value['interest_money'];
							if($load_repay_money>0) {
								$load_repay_data['user_id'] = $value['user_id'];
								$load_repay_data['accno'] = $value['accno'];
								$load_repay_data['repay_money'] = $load_repay_money;
								$load_repay_datas[] = $load_repay_data;
								$load_repay['oderNo'] = $oderNo;
								$load_repay['debitAccountNo'] = $v['objectaccno'];
								$load_repay['cebitAccountNo'] = $value['accno'];
								$load_repay['currency'] = "CNY";
								$load_repay['amount'] = floatval($load_repay_money);
								$load_repay['otherAmounttype'] = "";
								$load_repay['otherAmount'] = "";
								$load_repay['summaryCode'] = "T05";
								$load_repays[] = $load_repay;
							}
							//修改状态
							$statusArray['calculate_status'] = 1;
							$statusArray['has_repay'] = 1; //设置已还款标志
							$statusArray['status'] = 4; //区分全部还款
							$statusArray['true_repay_time'] = strtotime(date('Y-m-d'));
							$statusArray['true_repay_date'] = date('Y-m-d', time());
							$statusArray['true_repay_money'] = $load_repay_money;
							$statusArray['true_self_money'] = $value['self_money'];
							$statusArray['true_interest_money'] = 0;
							if($count_repay==$s) {
								$statusArray['raise_money'] = $value['raise_money'];
								$statusArray['interestrate_money'] = $value['interestrate_money'];
								$statusArray['increase_interest'] = $value['increase_interest'];
							}
							$statusArray['virtual_info'] = $value['virtual_info'];
							$statusArray['load_id'] = $value['load_id'];
							$statusArray['user_id'] = $value['user_id'];
							$statusArray['accno'] = $value['accno'];
							$statusArray['id'] = $value['id'];
							$statusArrays[] = $statusArray;
						}
					}else{
						//$this->all_quick_refund_fail("33");die;
						//融资人还款记录
						$repay_data['true_repay_money'] = 0;
						$repay_data['true_interest_money'] = 0;
						//投资人记录
						foreach ($deal_repay_info as $key => $value) {
							if ($value['debts_deal_id']) {
								$deal_id = $value['debts_deal_id'];
							}
							//修改状态
							$statusArray['calculate_status'] = 1;
							$statusArray['has_repay'] = 1; //设置已还款标志
							$statusArray['status'] = 4; //区分全部还款
							$statusArray['true_repay_time'] = strtotime(date('Y-m-d'));
							$statusArray['true_repay_date'] = date('Y-m-d', time());
							$statusArray['true_repay_money'] = 0;
							$statusArray['true_self_money'] = 0;
							$statusArray['true_interest_money'] = 0;
							$statusArray['virtual_info'] = $value['virtual_info'];
							$statusArray['load_id'] = $value['load_id'];
							$statusArray['user_id'] = $value['user_id'];
							$statusArray['accno'] = $value['accno'];
							$statusArray['id'] = $value['id'];
							$statusArrays[] = $statusArray;
						}
					}
				}
			}else {
				//融资人还款记录
				$money += $v['self_money'];
				$repay_data['true_repay_money'] = $v['self_money'];
				$repay_data['true_interest_money'] = 0;
				//投资人记录
				foreach($deal_repay_info as $key => $value){
					$oderNo++;
					if($value['debts_deal_id']){
						$deal_id = $value['debts_deal_id'];
					}
					$load_repay_money = $value['repay_money'] - $value['interest_money'];
					if($load_repay_money>0) {
						$load_repay_data['user_id'] = $value['user_id'];
						$load_repay_data['accno'] = $value['accno'];
						$load_repay_data['repay_money'] = $load_repay_money;
						$load_repay_datas[] = $load_repay_data;
						$load_repay['oderNo'] = $oderNo;
						$load_repay['debitAccountNo'] = $v['objectaccno'];
						$load_repay['cebitAccountNo'] = $value['accno'];
						$load_repay['currency'] = "CNY";
						$load_repay['amount'] = floatval($load_repay_money);
						$load_repay['otherAmounttype'] = "";
						$load_repay['otherAmount'] = "";
						$load_repay['summaryCode'] = "T05";
						$load_repays[] = $load_repay;
					}
					//修改状态
					$statusArray['calculate_status'] = 1;
					$statusArray['has_repay'] = 1; //设置已还款标志
					$statusArray['status'] = 4; //区分全部还款
					$statusArray['true_repay_time'] = strtotime(date('Y-m-d'));
					$statusArray['true_repay_date'] = date('Y-m-d', time());
					$statusArray['true_repay_money'] = $load_repay_money;
					$statusArray['true_self_money'] = $value['self_money'];
					$statusArray['true_interest_money'] = 0;
					if($count_repay==$s) {
						$statusArray['raise_money'] = $value['raise_money'];
						$statusArray['interestrate_money'] = $value['interestrate_money'];
						$statusArray['increase_interest'] = $value['increase_interest'];
					}
					$statusArray['virtual_info'] = $value['virtual_info'];
					$statusArray['load_id'] = $value['load_id'];
					$statusArray['user_id'] = $value['user_id'];
					$statusArray['accno'] = $value['accno'];
					$statusArray['id'] = $value['id'];
					$statusArrays[] = $statusArray;
				}
			}
			$s++;
			$repay_data['has_repay'] = 1;
			$repay_data['true_repay_date'] = date('Y-m-d', time());
			$repay_data['true_repay_time'] = strtotime();
			$repay_data['true_self_money'] = $v['self_money'];
			$repay_data['status'] = 4;//全部还款
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$v['repay_id']);
		}
		if($money>0){
			$data['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>$repay_info[0]['accno'],"cebitAccountNo"=>$repay_info[0]['objectaccno'],"currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T04"));
			$data['accNo'] = $repay_info[0]['accno'];
			$data['objectaccNo'] = $repay_info[0]['objectaccno'];
			$data['user_id'] = $repay_info[0]['user_id'];
			$data['money'] = $money;
			$data['deal_id'] = (string)$repay_info[0]['deal_id'];
			$res = $deal->do_repay($seqno,'T04',$data);//还款
			$res_code =$res['respHeader']['respCode'];
			if($res_code=="P2P0000"){
				$GLOBALS['db']->commit();
				$map['cunguan_money'] = -$money;
				modify_account($map, $repay_info[0]['user_id'], "偿还本息", 4, "偿还本息",1);
				$pub = new Publics();
				$seqno = $pub ->seqno();
				$data1['accountList'] = $load_repays;
				$data1['deal_repay_info'] = $load_repay_datas;
				$data1['objectaccno'] = $repay_info[0]['objectaccno'];
				$data1['deal_id'] = (string)$repay_info[0]['deal_id'];
				$res1 = $deal ->do_repay($seqno,'T05',$data1);//出款
				$res1_code =$res1['respHeader']['respCode'];
				if($res1_code=="P2P0000"){
					foreach($statusArrays as $ke=>$va) {
						if($va['true_self_money']>0){ //还本还息才需要解冻投资资金
							$datas['cunguan_lock_money'] = -$va['true_self_money']; //解冻步骤  投资资金+虚拟货币---------------------
						}
						if($va['true_repay_money']>0) {
							//资金增加++++++++++++++++++++++
							$datas['cunguan_money'] = $va['true_repay_money'];
							$datas['create_time'] = time(); //还款时间
							$datas['brief'] = $va['true_self_money']>0?"还本还息":"还息";
							$datas['deal_id'] = $repay_info[0]['deal_id'];
							$datas['load_repay_id'] = $va['id'];
							$datas['load_id'] = $va['load_id'];
							$msg = $va['true_self_money']>0?"还本还息":"还息";
							modify_account($datas, $va['user_id'], $msg, 5,$datas['brief'],1);
						}
						if($va['interestrate_money']>0){//加息卡收益
							$inte = $va;
							unset($inte['increase_interest']);
							unset($inte['raise_money']);
							$interestrate[] = $inte;
						}
						if($va['increase_interest']>0){//奖励加息收益
							$inc = $va;
							unset($inc['interestrate_money']);
							unset($inc['raise_money']);
							$inc_interest[] = $inc;
						}
						if($va['raise_money']>0){//募集期收益
							$rai = $va;
							unset($rai['interestrate_money']);
							unset($rai['increase_interest']);
							$raise_arr[] = $rai;
						}
						$id = $va['id'];
						unset($va['id']);
						$GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load_repay", $va, "UPDATE", "id=" . $id);
					}
					//更改标第状态为完成
					$seqno1 = $pub ->seqno();
					$res2 = $deal -> save_deal($repay_info[0]['deal_id'],$seqno1,"07",0);
					$res2_code = $res2['respHeader']['respCode'];
					if($res2_code!="P2P0000"){
						if($is_mobile){
							$this->all_quick_refund_fail($res2['respHeader']['respMsg']);
						}else{
							showErr($res2['respHeader']['respMsg'],0,url("index","uc_deal#borrowed"));
						}
					}
					$deal_data['cunguan_status']="07";
					$deal_data['deal_status']=5;
					if($deal_id>0){
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal_id);
					}
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$repay_info[0]['deal_id']);
					if($inc_interest){//奖励加息
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$inc_interest);//奖励加息
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail("平台奖励划款失败：".$res1['respHeader']['respMsg']);
							}else{
								showErr("平台奖励划款失败：".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
							}
						}else{
							foreach($inc_interest as $key=>$value){
								modify_account(array('cunguan_money'=>$value['increase_interest']), $value['user_id'], "奖励加息收益", 59, "奖励加息收益",1);
							}
						}
					}
					if($interestrate){//加息卡加息
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$interestrate);//加息卡收益
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail("平台奖励划款失败：".$res1['respHeader']['respMsg']);
							}else{
								showErr("平台奖励划款失败：".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
							}
						}else{
							foreach($interestrate as $key=>$value){
								modify_account(array('cunguan_money'=>$value['interestrate_money']), $value['user_id'], "加息卡收益", 60, "加息卡收益",1);
							}
						}
					}
					if($raise_arr){//是否有募集期收益
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$raise_arr);//募集期收益
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail("平台奖励划款失败：".$res1['respHeader']['respMsg']);
							}else{
								showErr("平台奖励划款失败：".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
							}
						}else{
							foreach($raise_arr as $key=>$value){
								modify_account(array('cunguan_money'=>$value['raise_money']), $value['user_id'], "募集期收益", 58, "募集期收益",1);
							}
						}
					}
					app_redirect(url("member","uc_deal#all_quick_refund_success",array("deal_id"=>$repay_info[0]['deal_id'])));
				}else{
					if($is_mobile){
						$this->all_quick_refund_fail("出款失败,".$res1['respHeader']['respMsg']);
					}else{
						showErr("出款失败,".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
					}
				}
			}else{
				$GLOBALS['db']->rollback();
				if($is_mobile){
					$this->all_quick_refund_fail("还款失败");
				}else{
					showErr("还款失败！",0,url("member","uc_deal#borrowed"));
				}
			}
		}else{
			$GLOBALS['db']->rollback();
			if($is_mobile){
				$this->all_quick_refund_fail("请稍后重试");
			}else{
				showErr("请稍后重试！",0,url("member","uc_deal#borrowed"));
			}
		}
	}
	//还款成功页面
	public function all_quick_refund_success(){
		$deal_id = intval($_REQUEST['deal_id']);
		if($deal_id==0){
			showErr("非法访问");
		}
		$repay = $GLOBALS['db']->getAll("SELECT true_repay_money,l_key+1 as l_key from ".DB_PREFIX."deal_repay where deal_id=".$deal_id." and status=4");
		$list = array();
		foreach($repay as $k=>$v){
			$l_key[] = $v['l_key'];
			$list['repay_money'] += $v['true_repay_money'];
		}
		$list['l_key'] = implode(',',$l_key);
		$map = $GLOBALS['db']->getRow("SELECT name,repay_time from ".DB_PREFIX."deal where id=".$deal_id);
		$list['name'] = $map['name'];
		$list['repay_time'] = $map['repay_time'];
		$list['deal_id'] = $deal_id;
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("page_title","还款成功");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_all_quick_refund_success.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//还款失败页面
	public function all_quick_refund_fail($msg){
		$list['msg'] = $msg;
		$list['deal_id'] = '';
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("page_title","还款失败");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_all_quick_refund_fail.html");
		$GLOBALS['tmpl']->display("page/uc.html");die;
	}
	//正常还款操作界面
	public function quick_refund(){
		$repay_id = intval($_REQUEST['repay_id']);
		if($repay_id == 0){
			showErr("操作失败！");
		}
		$repay = $GLOBALS['db']->getRow("SELECT deal_id,user_id,repay_money,l_key,repay_time,has_repay,do_repay_type from ".DB_PREFIX."deal_repay where id=".$repay_id);
		$repay['l_key']++;
		$cunguan_money =$GLOBALS['db']->getRow("SELECT d.name as name,AES_DECRYPT(u.cunguan_money_encrypt,'__JIUCAITONGAESKEY__') as cunguan_money,d.repay_time,d.company_userid from ".DB_PREFIX."deal d left join ".DB_PREFIX."user u on u.id=d.user_id where d.id = ".$repay['deal_id']." and d.is_delete <> 1  and d.is_effect = 1 and u.cunguan_tag=1");
		$deal = get_deal($repay['deal_id']);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_repay where id=".$repay_id." and has_repay=3")>0){
			$deal['deal_status']=4;
		}
		if($deal['deal_status']!=4){
			showErr("借款不是还款状态！");
		}
		if($repay['has_repay'] == 3 && $repay['do_repay_type'] == 2 && $GLOBALS['user_info']['user_type'] == 1){
			if($deal['company_userid']){
				$url = url("index","uc_deal#company_over_repay");
			}
		}elseif($repay['has_repay'] == 3 && $repay['do_repay_type'] == 2 && $GLOBALS['user_info']['user_type'] == 0){
				$url = url("index","uc_deal#repay_check_pwd");
		}elseif($repay['has_repay'] == 3 && $repay['do_repay_type'] == 1 && $GLOBALS['user_info']['user_type'] == 0){
				$url = url("index","uc_deal#repay_borrow_money");
		}
		if($repay['has_repay'] == 0){
			$url = url("index","uc_deal#repay_borrow_money");
		}
		
		$now_time = strtotime(date("Y-m-d"));
		if($repay['repay_time']<$now_time){//如果当前时间大于还款时间 则为逾期
			$day = floor(($now_time-$repay['repay_time'])/86400);
			//$over_fee = round($day*$repay['repay_money']*0.0005,2);//逾期手续费
		}
		if($over_fee>0){
			//应还总额
			$total_money = $repay['repay_money'];
			//$data['impose_money'] = $over_fee;//罚息
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$data,"UPDATE","id=".$repay_id);
		}else{
			$total_money = $repay['repay_money'];
			
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign('url',$url);
		$GLOBALS['tmpl']->assign("repay",$repay);
		$GLOBALS['tmpl']->assign("repay_id",$repay_id);
		$GLOBALS['tmpl']->assign("over_fee",$over_fee);
		$GLOBALS['tmpl']->assign("day",$day);
		$GLOBALS['tmpl']->assign("total_money",$total_money);
		$GLOBALS['tmpl']->assign("cunguan_money",$cunguan_money);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	//正常还款执行界面
	/* public function repay_borrow_money(){
		var_dump($_REQUEST);die;
		$id = intval($_REQUEST['id']);
		$ids = strim($_REQUEST['ids']);
		$paypassword = strim(FW_DESPWD($_REQUEST['paypassword']));
		if($paypassword==""){
			showErr($GLOBALS['lang']['PAYPASSWORD_EMPTY'],1);
		}
	
		if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
			showErr($GLOBALS['lang']['PAYPASSWORD_ERROR'],1);
		}
		
		$status = getUcRepayBorrowMoney($id,$ids);
		if ($status['status'] == 2){
			ajax_return($status);
			die();
		}
		elseif ($status['status'] == 0){
			showErr($status['show_err'],1);
		}else{
			showSuccess($status['show_err'],1);
		}
				
	} */
	//第一步:验证交易密码
   public function verify_trans_password(){
		$type = $_REQUEST['type'];
		$do_repay_type = $_REQUEST['do_repay_type'];
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			showErr("请稍后重试！",0,url("index","user#login"));
		}
		$repay_id = intval($_REQUEST['repay_id']);
		if(!$repay_id){
			showErr("请稍后重试！",0,url("index","deals"));
		}
		$repay = $GLOBALS['db']->getRow("SELECT repay_time from ".DB_PREFIX."deal_repay where id=".$repay_id." and cunguan_tag=1 and has_repay in(0,2,3)");
		if(!$repay){
			showErr("请稍后重试！",0,url("index","deals"));
		}
	   	if($GLOBALS['user_info']['user_type']==1){
		   	$this->$type($repay_id);
	   	}else {
			$Publics = new Publics();
			$seqno = $Publics->seqno();
			$data['seqno'] = $seqno;
			$GLOBALS['db']->autoExecute(DB_PREFIX . "deal_repay", $data, "UPDATE", "id=" . $repay_id);
			$html = $Publics->verify_trans_password('uc_deal', $type, $user_id, '4', $seqno, "_self");
			echo $html;
			die;
		}
   }
   //个人用户企业代偿后还款验密
	public function repay_check_pwd(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			showErr("请稍后重试！",0,url("index","user#login"));
		}
		$repay_id = intval($_REQUEST['bid']);
		if($repay_id==0){
			showErr("请稍后重试！",0,url("index","deals"));
		}
		$repay = $GLOBALS['db']->getRow("SELECT deal_id,user_id,repay_money,l_key,repay_time,has_repay,do_repay_type from ".DB_PREFIX."deal_repay where id=".$repay_id." and cunguan_tag=1 and has_repay in(0,3)");
		if(!$repay){
			showErr("请稍后重试！",0,url("index","deals"));
		}
		$repay['l_key']++;
		$cunguan_tag = $GLOBALS['db']->getOne("select cunguan_tag from ".DB_PREFIX."deal_repay where id=".$repay_id." and user_id =".$user_id);
		$cunguan_money =$GLOBALS['db']->getRow("SELECT d.name as name,AES_DECRYPT(u.cunguan_money_encrypt,'__JIUCAITONGAESKEY__') as cunguan_money,d.repay_time from ".DB_PREFIX."deal d left join ".DB_PREFIX."user u on u.id=d.user_id where d.id = ".$repay['deal_id']." and d.is_delete <> 1  and d.is_effect = 1 and u.cunguan_tag=1");
		if($cunguan_tag ==1){
			$now_time = strtotime(date("Y-m-d"));
			/* if($repay['repay_time']<$now_time){//如果当前时间大于还款时间 则为逾期
				$day = floor(($now_time-$repay['repay_time'])/86400);
				$over_fee = round($day*$repay['repay_money']*0.0005,2);//逾期手续费
			} */
			if($repay['repay_time']<$now_time){
				//应还总额
				$total_money =$repay['repay_money'];
				//$data['impose_money'] = $over_fee;//罚息
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$data,"UPDATE","id=".$repay_id);
			}else{
				$total_money = $repay['repay_money'];
			}
			if($total_money>$cunguan_money['cunguan_money']){
				$status['status'] = 0;
				$status['info'] ="余额不足！请先充值";
				$status['jump'] =url("index","uc_money#incharge");
				ajax_return($status);
			}
			/* if($repay['repay_time']<$now_time){//逾期
				$status['status']=1;
				$status['jump'] =url("index","uc_deal#verify_trans_password&type=over_repay&repay_id=$repay_id");
			}else{
				$status['status']=1;
				$status['jump'] =url("index","uc_deal#verify_trans_password&type=repay_money&repay_id=$repay_id");
			} */
			$status['status']=2;
			$status['jump'] =url("index","uc_deal#verify_trans_password&type=company_over_repay&repay_id=$repay_id&do_repay_type=".$repay['do_repay_type']);
			ajax_return($status);
		}
	}
	//还款验密
	public function repay_borrow_money(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			showErr("请稍后重试！",0,url("index","user#login"));
		}
		$repay_id = intval($_REQUEST['bid']);
		if($repay_id==0){
			showErr("请稍后重试！",0,url("index","deals"));
		}
		$repay = $GLOBALS['db']->getRow("SELECT deal_id,user_id,repay_money,l_key,repay_time,has_repay,do_repay_type from ".DB_PREFIX."deal_repay where id=".$repay_id." and cunguan_tag=1 and has_repay in(0,3)");
		if(!$repay){
			showErr("请稍后重试！",0,url("index","deals"));
		}
		$repay['l_key']++;
		$cunguan_tag = $GLOBALS['db']->getOne("select cunguan_tag from ".DB_PREFIX."deal_repay where id=".$repay_id." and user_id =".$user_id);
		$cunguan_money =$GLOBALS['db']->getRow("SELECT d.name as name,AES_DECRYPT(u.cunguan_money_encrypt,'__JIUCAITONGAESKEY__') as cunguan_money,d.repay_time from ".DB_PREFIX."deal d left join ".DB_PREFIX."user u on u.id=d.user_id where d.id = ".$repay['deal_id']." and d.is_delete <> 1  and d.is_effect = 1 and u.cunguan_tag=1");
		if($cunguan_tag ==1){
			$now_time = strtotime(date("Y-m-d"));
			/* if($repay['repay_time']<$now_time){//如果当前时间大于还款时间 则为逾期
				$day = floor(($now_time-$repay['repay_time'])/86400);
				$over_fee = round($day*$repay['repay_money']*0.0005,2);//逾期手续费
			} */
			if($repay['repay_time']<$now_time){
				//应还总额
				$total_money =$repay['repay_money'];
				//$data['impose_money'] = $over_fee;//罚息
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$data,"UPDATE","id=".$repay_id);
			}else{
				$total_money = $repay['repay_money'];
			}
			if($total_money>$cunguan_money['cunguan_money']){
				$status['status'] = 1;
				$status['info'] ="余额不足！请先充值";
				$status['jump'] =url("index","uc_money#incharge");
				ajax_return($status);
			}
			if($repay['do_repay_type'] == 1){//逾期
				$status['status']=2;
				$status['jump'] =url("index","uc_deal#verify_trans_password&type=over_repay&repay_id=$repay_id&do_repay_type=".$repay['do_repay_type']);
			}else{
				$status['status']=2;
				$status['jump'] =url("index","uc_deal#verify_trans_password&type=repay_money&repay_id=$repay_id");
			} 
			// $status['status']=1;
			// $status['jump'] =url("index","uc_deal#verify_trans_password&type=repay_money&repay_id=$repay_id");
			ajax_return($status);
		}
	}
	 //还款
	public function repay_money($repay_id=''){
			$is_mobile = isMobile();
			$user_id = $GLOBALS['user_info']['id'];
			if(!$user_id){//判断用户是否登陆
				if($is_mobile){
					$this->all_quick_refund_fail('请先登录');
				}else{
					showErr("请先登录",0,url("index","user#login"));
				}
			}
			if($GLOBALS['user_info']['user_type']==1){
				$Publics = new Publics();
				$seqno = $Publics->seqno();
				$repay_info = $GLOBALS['db']->getRow("select dr.id as id,dr.deal_id as deal_id,d.user_id as user_id,dr.repay_money as repay_money,dr.raise_money as raise_money,dr.interest_money as interest_money,dr.self_money as self_money,d.objectaccno as objectaccno,d.old_deal_id,d.repay_time as repay_time,dr.l_key as l_key from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.id='".$repay_id."' and dr.has_repay=0 and dr.cunguan_tag=1");
			}else {
				$flag = $_GET['flag'];
				if($flag!=1){
					if($is_mobile){
						$this->all_quick_refund_fail('操作错误');
					}else{
						showErr("操作错误！",0,url("index","deals"));
					}
				}
				$seqno = strim($_GET['businessSeqNo']);
				if(!$seqno){
					if($is_mobile){
						$this->all_quick_refund_fail('操作错误');
					}else{
						showErr("操作错误！",0,url("index","deals"));
					}
				}
				$repay_info = $GLOBALS['db']->getRow("select dr.id as id,dr.deal_id as deal_id,d.user_id as user_id,dr.repay_money as repay_money,dr.raise_money as raise_money,dr.interest_money as interest_money,dr.self_money as self_money,d.objectaccno as objectaccno,d.old_deal_id,d.repay_time as repay_time,dr.l_key as l_key from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.seqno='$seqno' and dr.has_repay in(0,3) and dr.cunguan_tag=1");
			}
			if(!$repay_info){
				if($is_mobile){
					$this->all_quick_refund_fail('此还款计划不存在');
				}else{
					showErr("此还款计划不存在",0,url("index","uc_deal#borrowed"));
				}
			}
			$deal = new Deal();
			//$repay_info = $GLOBALS['db']->getRow("select dr.deal_id as deal_id,d.user_id as user_id,dr.repay_money as repay_money,d.objectaccno as objectaccno,d.repay_time as repay_time,dr.l_key as l_key from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.id = ".$repay['id']." and dr.has_repay=0 and dr.cunguan_tag=1");
			$money = floatval($repay_info['repay_money']);
			$user_info =$GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id = ".$repay_info['user_id']);
			$data['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>$user_info['accno'],"cebitAccountNo"=>$repay_info['objectaccno'],"currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T04"));
			//$objectaccno = $repay_info['objectaccno'];
			//$accno = $user_info['accno']; 
			$data['accNo'] = $user_info['accno'];
			$data['objectaccNo'] = $repay_info['objectaccno'];
			$data['user_id'] = $repay_info['user_id'];
			$data['money'] = $money;
			$data['deal_id'] = (string)$repay_info['deal_id'];
			$res = $deal->do_repay($seqno,'T04',$data);//还款
			$res_code =$res['respHeader']['respCode'];
			if($res_code=="P2P0000"){
				$repay_info['cunguan_money'] = -$repay_info['repay_money'];
				if($repay_info['self_money']>0){//还款之后进行资金明细操作
					modify_account($repay_info, $repay_info['user_id'], "偿还本息", 4, "偿还本息",1);
				}else{
					modify_account($repay_info, $repay_info['user_id'], "偿还利息", 4, "偿还利息",1);
				}	
				$repay_data['has_repay'] = 1;
				$repay_data['true_repay_date'] = date('Y-m-d', time());
				$repay_data['true_repay_time'] = strtotime();
				$repay_data['true_repay_money'] = $repay_info['repay_money'];
				$repay_data['true_self_money'] = $repay_info['self_money'];
				$repay_data['true_interest_money'] = $repay_info['interest_money'];
				$time = strtotime(to_date(time(),"Y-m-d"));//当前日期的时间戳
				if($time<$repay_info['repay_time']){//如果小于预期还款时间
					$repay_data['status'] = 0;//提前还款
				}elseif($time==$repay_info['repay_time']){//如果等于预期还款时间
					$repay_data['status'] = 1;//准时还款
				}else{
					$repay_data['status'] = 2;//逾期还款
				}
					
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$repay_info['id']);
				$pub = new Publics();
				$deal_repay_info = $GLOBALS['db']->getAll("select u.accno,u.id as user_id,dlr.repay_money,dlr.debts_deal_id  from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."user u on u.id=dlr.user_id where dlr.repay_id = ".$repay_info['id']." and dlr.has_repay=0 and dlr.cunguan_tag=1");
				$oderNo=0;
				foreach($deal_repay_info as $key => $value){
					$oderNo++;
					if($value['debts_deal_id']){
						$deal_id = $value['debts_deal_id'];
					}
					//$user_info1 =$GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id=".$value['user_id']);
					$repay1['oderNo'] = $oderNo;
					$repay1['debitAccountNo']=$repay_info['objectaccno'];
					$repay1['cebitAccountNo']=$value['accno'];
					$repay1['currency']="CNY";
					$repay1['amount']=floatval($value['repay_money']);
					$repay1['otherAmounttype']="";
					$repay1['otherAmount']="";
					$repay1['summaryCode']="T05";
					$repays[]=$repay1;
				}
				$seqno = $pub ->seqno();
				$data1['accountList'] = $repays;
				$data1['deal_repay_info'] = $deal_repay_info;
				$data1['objectaccno'] = $repay_info['objectaccno'];
				$data1['deal_id'] = (string)$repay_info['deal_id'];
				$res1 = $deal ->do_repay($seqno,'T05',$data1);//出款
				$res1_code =$res1['respHeader']['respCode'];
				if($res1_code=="P2P0000"){
					$infos = $GLOBALS['db']->getAll("select dlr.id,u.id as user_id,u.accno,dlr.raise_money,dlr.repay_money,dlr.virtual_info,dlr.deal_id,dlr.repay_id as load_repay_id,dlr.load_id,dlr.self_money,dlr.interestrate_money,dlr.increase_interest  from ".DB_PREFIX."deal_load_repay dlr left join ".DB_PREFIX."user u on u.id=dlr.user_id  where dlr.repay_id=".$repay_info['id']." and dlr.has_repay =0  and dlr.cunguan_tag=1");
					if(!$infos){
						if($is_mobile){
							$this->all_quick_refund_fail('请稍后重试');
						}else{
							showErr('请稍后重试！',0,url("index","uc_deal#borrowed"));
						}
					}
					foreach($infos as $key=>$value){
						if($value['self_money']>0){ //还本还息才需要解冻投资资金
							$datas['cunguan_lock_money'] = -$value['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
						}
						$datas['cunguan_money']=$value['repay_money'];
						if($value['interestrate_money']>0){//加息卡收益
							$interestrate_money = $value;
							unset($interestrate_money['increase_interest']);
							unset($interestrate_money['raise_money']);
							$interestrate[] = $interestrate_money;
						}
						if($value['increase_interest']>0){//奖励加息收益
							$increase_interest =$value;
							unset($increase_interest['interestrate_money']);
							unset($increase_interest['raise_money']);
							$inc_interest[] = $increase_interest;
						}
						if($value['raise_money']>0){//募集期收益
							$raise_money = $value;
							unset($raise_money['increase_interest']);
							unset($raise_money['interestrate_money']);
							$raise_arr[] = $raise_money;
						}
						 //资金增加++++++++++++++++++++++
						$datas['create_time'] = time(); //还款时间
						$datas['brief'] = $value['virtual_info']; //虚拟货币消息
						$datas['deal_id'] = $value['deal_id'];
						$datas['load_repay_id'] = $value['load_repay_id'];
						$datas['load_id'] = $value['load_id'];
						$msg = $value['self_money']>0?"还本还息":"还息";	
						modify_account($datas, $value['user_id'], $msg, 5,$datas['brief'],1);
						//添加资金记录
						$statusArray['calculate_status'] = 1;
						$statusArray['has_repay'] = 1; //设置已还款标志
						$statusArray['true_repay_time'] = strtotime();
						$statusArray['true_repay_date'] = date('Y-m-d', time());
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$statusArray,"UPDATE","id=".$value['id']);
					}
					 if($inc_interest){//奖励加息
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$inc_interest);//奖励加息
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail("平台奖励划款失败：".$res1['respHeader']['respMsg']);
							}else{
								showErr("平台奖励划款失败：".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
							}
						}else{
							foreach($inc_interest as $key=>$value){
								modify_account(array('cunguan_money'=>$value['increase_interest']), $value['user_id'], "奖励加息收益", 59, "奖励加息收益",1);
							}
						}
					} 
					if($interestrate){//加息卡加息
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$interestrate);//加息卡收益
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail("平台奖励划款失败：".$res1['respHeader']['respMsg']);
							}else{
								showErr("平台奖励划款失败：".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
							}
						}else{
							foreach($interestrate as $key=>$value){
								modify_account(array('cunguan_money'=>$value['interestrate_money']), $value['user_id'], "加息卡收益", 60, "加息卡收益",1);
							}
						}
					}
					if($repay_info['self_money']>0){
						if($repay_info['raise_money']>0){//是否有募集期收益
							$seqno = $pub ->seqno();
							$res1 = $deal ->earning_money($seqno,'T10',$infos);//募集期收益
							$res1_code =$res1['respHeader']['respCode'];
							if($res1_code!="P2P0000"){
								if($is_mobile){
									$this->all_quick_refund_fail("平台奖励划款失败：".$res1['respHeader']['respMsg']);
								}else{
									showErr("平台奖励划款失败：".$res1['respHeader']['respMsg'],0,url("member","uc_deal#borrowed"));
								}
							}else{
								foreach($raise_arr as $key=>$value){
									modify_account(array('cunguan_money'=>$value['raise_money']), $value['user_id'], "募集期收益", 58, "募集期收益",1);
								}
							}
						}
						$last_repay = ($repay_info['repay_time']-$repay_info['l_key'])==1?true:false;//是否是最后一期
						if($last_repay){
							$seqno1 = $pub ->seqno();
							$res2 = $deal -> save_deal($repay_info['deal_id'],$seqno1,"07",0);
							$res2_code = $res2['respHeader']['respCode'];
							if($res2_code!="P2P0000"){
								if($is_mobile){
									$this->all_quick_refund_fail($res2['respHeader']['respMsg']);exit;
								}else{
									showErr($res2['respHeader']['respMsg'],0,url("index","uc_deal#borrowed"));
								}
							}
							$deal_data['cunguan_status']="07";
							$deal_data['deal_status']=5;
							if($deal_id>0){
								$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$deal_id);
							}
							$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$repay_info['deal_id']);
						}
					}
					app_redirect(url("index","uc_deal#quick_refund_success",array("repay_id"=>$repay_info['id'])));
				}else{
					if($is_mobile){
						$this->all_quick_refund_fail('出款失败,'.$res1['respHeader']['respMsg']);
					}else{
						showErr('出款失败,'.$res1['respHeader']['respMsg'],0,url("index","uc_deal#borrowed"));
					}
				}
			}else{
				if($is_mobile){
					$this->all_quick_refund_fail('还款失败,'.$res['respHeader']['respMsg']);
				}else{
					showErr('还款失败,'.$res['respHeader']['respMsg'],0,url("index","uc_deal#borrowed"));
				}
			}
			
			
		//}
	} 

	//逾期还款
	public function over_repay(){
		$is_mobile = isMobile();
		$flag = $_GET['flag'];
		if($flag!=1){
			if($is_mobile){
				$this->all_quick_refund_fail('操作错误！');
			}else{
				showErr("操作错误！",0,url("index","deals"));
			}
		}
		$seqno = strim($_GET['businessSeqNo']);
		if(!$seqno){
			if($is_mobile){
				$this->all_quick_refund_fail('操作错误！');
			}else{
				showErr("操作错误！",0,url("index","deals"));
			}
		}
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){//判断用户是否登陆
			if($is_mobile){
				$this->all_quick_refund_fail('请先登陆！');
			}else{
				showErr("请先登陆！",0,url("index","user#login"));
			}
		}
		$repay_info = $GLOBALS['db']->getRow("select dr.id,dr.deal_id,d.user_id,dr.repay_money,dr.raise_money,dr.interest_money,dr.self_money,d.objectaccno,d.company_userid,d.repay_time,dr.l_key from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.seqno='$seqno' and dr.has_repay=3 and dr.cunguan_tag=1");
		//$repay = $GLOBALS['db']->getRow("select id,user_id,deal_id,repay_money,repay_time,self_money,interest_money,impose_money,cunguan_tag from ".DB_PREFIX."deal_repay where seqno='$seqno' and has_repay in(0,2,3) and cunguan_tag=1");
		if(!$repay_info){
			if($is_mobile){
				$this->all_quick_refund_fail('此还款计划已逾期');
			}else{
				showErr("此还款计划已逾期",0,url("index","uc_deal#borrowed"));
			}
		} 
		//if($repay_info['cunguan_tag']==1){
			$deal = new Deal();
			//$repay_info = $GLOBALS['db']->getRow("select dr.deal_id as deal_id,d.user_id as user_id,dr.repay_money as repay_money,d.objectaccno as objectaccno from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.id = ".$repay['id']." and dr.has_repay=3 and dr.cunguan_tag=1");
			$user_info =$GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id = ".$repay_info['user_id']);
			$money = floatval($repay_info['repay_money']);
			$data['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>$user_info['accno'],"cebitAccountNo"=>'JCTPR20170712',"currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>"","summaryCode"=>"T09"));
			$data['user_id'] = $repay_info['user_id'];
			$data['accNo'] = $user_info['accno'];
			$data['deal_id'] = $repay_info['deal_id'];
			$data['money'] = $money;
			$res = $deal->do_repay($seqno,'T09',$data);//还款
			$res_code =$res['respHeader']['respCode'];
			if($res_code=="P2P0000"){
				$repay_info['cunguan_money'] = -$repay_info['repay_money'];
				if($repay_info['self_money']>0){//还款之后进行资金明细操作
					modify_account($repay_info, $repay_info['user_id'], "偿还本息", 4, "偿还本息",1);
				}else{
					modify_account($repay_info, $repay_info['user_id'], "偿还利息", 4, "偿还利息",1);
				}	
				$repay_data['has_repay'] = 1;
				$repay_data['true_repay_date'] = date('Y-m-d', time());
				$repay_data['true_repay_time'] = strtotime();
				$repay_data['true_repay_money'] = $repay_info['repay_money'];
				$repay_data['true_self_money'] = $repay_info['self_money'];
				$repay_data['true_interest_money'] = $repay_info['interest_money'];
				$time = strtotime(to_date(time(),"Y-m-d"));
				if($time<$repay_info['repay_time']){
					$repay_data['status'] = 0;
				}elseif($time==$repay_info['repay_time']){
					$repay_data['status'] = 1;
				}else{
					$repay_data['status'] = 2;
				}
					
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$repay_info['id']);
				if($repay_info['self_money']>0){
					$last_repay = ($repay_info['repay_time']-$repay_info['l_key'])==1?true:false;//是否是最后一期
					if($last_repay){
						$pub = new Publics();
						$seqno1 = $pub ->seqno();
						$res2 = $deal -> save_deal($repay_info['deal_id'],$seqno1,"07",0);
						$res2_code = $res2['respHeader']['respCode'];
						$deal_data['cunguan_status']="07";
						$deal_data['deal_status']=5;
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$repay_info['deal_id']);
						if($res2_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail($res2['respHeader']['respMsg']);
							}else{
								showErr($res2['respHeader']['respMsg'],0,url("index","uc_deal#borrowed"));
							}
						}
					}
				}
				app_redirect(url("index","uc_deal#quick_refund_success",array("repay_id"=>$repay_info['id'])));
			}else{
				if($is_mobile){
					$this->all_quick_refund_fail($res['respHeader']['respMsg']);
				}else{
					showErr($res['respHeader']['respMsg'],0,url("index","uc_deal#borrowed"));
				}
			}
		//}
			
	}
	//企业用户代偿后还款
	public function company_over_repay($repay_id=''){
		// $flag = $_GET['flag'];
		// if($flag!=1){
		// 	showErr("操作错误！",0,url("index","deals"));
		// }
		// $seqno = strim($_GET['businessSeqNo']);
		// if(!$seqno){
		// 	showErr("操作错误！",0,url("index","deals"));
		// }
		$is_mobile = isMobile();
		$seqno = strim($_REQUEST['businessSeqNo']);
		if(isset($seqno) && !empty($seqno)){
			$data['seqno'] = $seqno;
			$ajax = 0;
		}else{
			$Publics = new Publics();
			$seqno = $Publics->seqno();
			$data['seqno'] = $seqno;
			$ajax = 1;
		}
		$repay_id = strim($_REQUEST['bid'])?strim($_REQUEST['bid']):$repay_id;
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){//判断用户是否登陆
			if($is_mobile){
				$this->all_quick_refund_fail('请先登陆！');
			}else{
				showErr("请先登陆！",$ajax,url("index","user#login"));
			}
			
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX . "deal_repay", $data, "UPDATE", "id=" . $repay_id);

		
		$repay_info = $GLOBALS['db']->getRow("select dr.id,dr.deal_id,d.user_id,dr.repay_money,dr.raise_money,dr.interest_money,dr.self_money,dr.do_repay_type,d.objectaccno,d.company_userid,d.repay_time,dr.l_key from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.seqno='$seqno' and dr.has_repay=3 and dr.cunguan_tag=1");

		//$repay = $GLOBALS['db']->getRow("select id,user_id,deal_id,repay_money,repay_time,self_money,interest_money,impose_money,cunguan_tag from ".DB_PREFIX."deal_repay where seqno='$seqno' and has_repay in(0,2,3) and cunguan_tag=1");
		if(!$repay_info){
			if($is_mobile){
				$this->all_quick_refund_fail('此还款已逾期！');
			}else{
				showErr("此还款已逾期",$ajax,url("index","uc_deal#borrowed"));
			}
		} 
		if(isset($repay_info['company_userid']) && !empty($repay_info['company_userid']) && $repay_info['do_repay_type']==2){
			$debitAccountNo = $repay_info['company_userid'];
		}elseif(isset($repay_info['company_userid']) && !empty($repay_info['company_userid']) && $repay_info['do_repay_type']==1){
			$debitAccountNo = 'JCTPR20170712';
		}else{
			if($is_mobile){
				$this->all_quick_refund_fail('担保户不存在！');
			}else{
				showErr("担保户不存在",$ajax,url("index","uc_deal#borrowed"));
			}
		}
		//if($repay_info['cunguan_tag']==1){
			$deal = new Deal();
			//$repay_info = $GLOBALS['db']->getRow("select dr.deal_id as deal_id,d.user_id as user_id,dr.repay_money as repay_money,d.objectaccno as objectaccno from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."deal d on d.id = dr.deal_id where dr.id = ".$repay['id']." and dr.has_repay=3 and dr.cunguan_tag=1");
			$user_info =$GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id = ".$repay_info['user_id']);
			$money = floatval($repay_info['repay_money']);
			$data['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>$user_info['accno'],"cebitAccountNo"=>$debitAccountNo,"currency"=>"CNY","amount"=>$money,"summaryCode"=>"T09","otherAmounttype"=>"","otherAmount"=>""));
			$data['user_id'] = $repay_info['user_id'];
			$data['accNo'] = $user_info['accno'];
			$data['deal_id'] = $repay_info['deal_id'];
			$data['money'] = $money;
			$res = $deal->do_repay($seqno,'T09',$data);//还款
			$res_code =$res['respHeader']['respCode'];
			// echo "<pre>"; 
			// print_r($res);die;
			if($res_code=="P2P0000"){
				
				$repay_info['cunguan_money'] = -$repay_info['repay_money'];
				if($repay_info['self_money']>0){//还款之后进行资金明细操作
					modify_account($repay_info, $repay_info['user_id'], "偿还本息", 4, "偿还本息",1);
				}else{
					modify_account($repay_info, $repay_info['user_id'], "偿还利息", 4, "偿还利息",1);
				}	
				$repay_data['has_repay'] = 1;
				$repay_data['true_repay_date'] = date('Y-m-d', time());
				$repay_data['true_repay_time'] = strtotime();
				$repay_data['true_repay_money'] = $repay_info['repay_money'];
				$repay_data['true_self_money'] = $repay_info['self_money'];
				$repay_data['true_interest_money'] = $repay_info['interest_money'];
				$time = strtotime(to_date(time(),"Y-m-d"));
				if($time<$repay_info['repay_time']){
					$repay_data['status'] = 0;
				}elseif($time==$repay_info['repay_time']){
					$repay_data['status'] = 1;
				}else{
					$repay_data['status'] = 2;
				}
					
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$repay_info['id']);
				if($repay_info['self_money']>0){
					$last_repay = ($repay_info['repay_time']-$repay_info['l_key'])==1?true:false;//是否是最后一期
					if($last_repay){
						$pub = new Publics();
						$seqno1 = $pub ->seqno();
						$res2 = $deal -> save_deal($repay_info['deal_id'],$seqno1,"07",0);
						$res2_code = $res2['respHeader']['respCode'];
						$deal_data['cunguan_status']="07";
						$deal_data['deal_status']=5;
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"UPDATE","id=".$repay_info['deal_id']);
						if($res2_code!="P2P0000"){
							if($is_mobile){
								$this->all_quick_refund_fail($res2['respHeader']['respMsg']);
							}else{
								showErr($res2['respHeader']['respMsg'],$ajax,url("index","uc_deal#borrowed"));
							}
							
						}
					}
				}
				if($repay_info['do_repay_type']==2){
					// 企业代偿  企业账户资金变更
						$datas['create_time'] = time(); //代偿时间
						$datas['brief'] = '用户还款'; 
						$datas['deal_id'] = $repay_info['deal_id'];
						$datas['cunguan_money'] = $repay_info['repay_money'];
						$msg = '借款人已还款';	
						modify_account($datas, $repay_info['company_userid'], $msg, 70, $datas['brief'],1);
				}
				if(WAP == 1){
					app_redirect(url("index","uc_deal#quick_refund_success",array("repay_id"=>$repay_info['id'])));
				}else{
					showSuccess($res['respHeader']['respMsg'],$ajax,url("index","uc_deal#borrowed"));
				}
				
				
			}else{
				if($is_mobile){
					$this->all_quick_refund_fail($res['respHeader']['respMsg']);
				}else{
					showErr($res['respHeader']['respMsg'],$ajax,url("index","uc_deal#borrowed"));
				}
				
			}
		//}
			
	}
	//还款成功页面
	public function quick_refund_success(){
		$repay_id = intval($_REQUEST['repay_id']);
		if($repay_id==0){
			showErr("非法访问");
		}
		$repay = $GLOBALS['db']->getRow("select id,deal_id,repay_money,l_key,cunguan_tag from ".DB_PREFIX."deal_repay where id =".$repay_id." and has_repay=1");
		if($repay['cunguan_tag']!=1){
			showErr("非法访问");
		}
		$repay['l_key']=$repay['l_key']+1;
		$list['l_key'] = $repay['l_key'];
		$list['repay_money'] = $repay['repay_money'];
		$deal = $GLOBALS['db']->getRow("select name,repay_time from ".DB_PREFIX."deal where id=".$repay['deal_id']);
		$list['repay_time'] = $deal['repay_time'];
		$list['deal_id'] = $repay['deal_id'];
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign("repay",$repay);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("page_title","还款成功");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refund_success.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	//提前还款操作界面
	public function inrepay_refund(){
		$id = intval($_REQUEST['id']);		
		
		
		$status = getUcInrepayRefund($id);
		if ($status['status'] == 1){		
			//$deal = $status['deal'];
			$GLOBALS['tmpl']->assign("deal",$status['deal']);
			$GLOBALS['tmpl']->assign("true_all_manage_money",$status['true_all_manage_money']);
			$GLOBALS['tmpl']->assign("true_all_mortgage_fee",$status['true_all_mortgage_fee']);
            
			$GLOBALS['tmpl']->assign("impose_money",$status['impose_money']);
			$GLOBALS['tmpl']->assign("total_repay_money",$status['total_repay_money']);
						
			$GLOBALS['tmpl']->assign("true_total_repay_money",$status['true_total_repay_money']);
			
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_inrepay_refund.html");
			$GLOBALS['tmpl']->display("page/uc.html");	
		}else{
			showErr($status['show_err']);
		}
	}
	//提前还款执行程序
	public function inrepay_repay_borrow_money(){
		$id = intval($_REQUEST['id']);
		$paypassword = strim(FW_DESPWD($_REQUEST['paypassword']));
		if($paypassword==""){
			showErr($GLOBALS['lang']['PAYPASSWORD_EMPTY'],1);
		}
	
		if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
			showErr($GLOBALS['lang']['PAYPASSWORD_ERROR'],1);
		}
		$status = getUCInrepayRepayBorrowMoney($id);
		if ($status['status'] == 0){
			showErr($status['show_err'],1);
		}else{
			showSuccess($status['show_err'],1);
		}
				
	}
	
	public function refdetail(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($deal['deal_status']!=5){
			showErr("借款状态不正确！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		//还款列表
		$loan_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay where deal_id=$id ORDER BY repay_time ASC");
		$manage_fee = 0;
		$impose_money = 0;
		$repay_money = 0;
		$manage_impose_fee = 0;
		$mortgage_fee = 0;
		foreach($loan_list as $k=>$v){
			$manage_fee += $v['true_manage_money'];
            $mortgage_fee += $v['true_mortgage_fee'];
			$impose_money += $v['impose_money'];
			$repay_money += $v['true_repay_money'];
			$manage_impose_fee +=$v['manage_impose_money'];
			
			//还款日
			$loan_list[$k]['repay_time_format'] = to_date($v['repay_time'],'Y-m-d');
			$loan_list[$k]['true_repay_time_format'] = to_date($v['true_repay_time'],'Y-m-d');
	
			//已还本息
			$loan_list[$k]['repay_money_format'] = format_price($v['true_repay_money']);
			
			//逾期费用
			$loan_list[$k]['impose_money_format'] = format_price($v['impose_money']);
			
			//借款管理费
			$loan_list[$k]['manage_money_format'] = format_price($v['true_manage_money']);
			
			$loan_list[$k]['manage_impose_money_format'] = format_price($v['manage_impose_money']);
			
			//抵押物管理费
            $loan_list[$k]['mortgage_fee_format'] = format_price($v['true_mortgage_fee']);
			 
			
			//状态
			if($v['status'] == 0){
				$loan_list[$k]['status_format'] = '提前还款';
			}elseif($v['status'] == 1){
				$loan_list[$k]['status_format'] = '正常还款';
			}elseif($v['status'] == 2){
				$loan_list[$k]['status_format'] = '逾期还款';
			}elseif($v['status'] == 3){
				$loan_list[$k]['status_format'] = '严重逾期';
			}
			
		}
		
		
		$GLOBALS['tmpl']->assign("manage_fee",$manage_fee);
        $GLOBALS['tmpl']->assign("mortgage_fee",$mortgage_fee);
		$GLOBALS['tmpl']->assign("impose_money",$impose_money);
		$GLOBALS['tmpl']->assign("repay_money",$repay_money);
		$GLOBALS['tmpl']->assign("manage_impose_fee",$manage_impose_fee);
		$GLOBALS['tmpl']->assign("loan_list",$loan_list);
		
		$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
		$GLOBALS['tmpl']->assign("inrepay_info",$inrepay_info);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refdetail.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	
	public function mrefdetail(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
	
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($deal['deal_status']!=5){
			showErr("借款状态不正确！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
	
		//还款列表
		$loan_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay where deal_id=$id ORDER BY repay_time ASC");
		$manage_fee = 0;
		$impose_money = 0;
		$repay_money = 0;
		$manage_impose_fee = 0;
        $mortgage_fee=0;
		foreach($loan_list as $k=>$v){
			$manage_fee += $v['true_manage_money'];
            $mortgage_fee += $v['true_mortgage_fee'];
			$impose_money += $v['impose_money'];
			$repay_money += $v['true_repay_money'];
			$manage_impose_fee +=$v['manage_impose_money'];
			
			//还款日
			$loan_list[$k]['repay_time_format'] = to_date($v['repay_time'],'Y-m-d');
			$loan_list[$k]['true_repay_time_format'] = to_date($v['true_repay_time'],'Y-m-d');
	
			//已还本息
			$loan_list[$k]['repay_money_format'] = format_price($v['true_repay_money']);
			
			//逾期费用
			$loan_list[$k]['impose_money_format'] = format_price($v['impose_money']);
			
			//借款管理费
			$loan_list[$k]['manage_money_format'] = format_price($v['true_manage_money']);
			
			$loan_list[$k]['manage_impose_money_format'] = format_price($v['manage_impose_money']);
            
            //抵押物管理费
            $loan_list[$k]['mortgage_fee_format'] = format_price($v['true_mortgage_fee']);
			 
			
			//状态
			if($v['status'] == 0){
				$loan_list[$k]['status_format'] = '提前还款';
			}elseif($v['status'] == 1){
				$loan_list[$k]['status_format'] = '正常还款';
			}elseif($v['status'] == 2){
				$loan_list[$k]['status_format'] = '逾期还款';
			}elseif($v['status'] == 3){
				$loan_list[$k]['status_format'] = '严重逾期';
			}
			
		}
		
		$GLOBALS['tmpl']->assign("manage_fee",$manage_fee);
        $GLOBALS['tmpl']->assign("mortgage_fee",$mortgage_fee);
		$GLOBALS['tmpl']->assign("impose_money",$impose_money);
		$GLOBALS['tmpl']->assign("repay_money",$repay_money);
		$GLOBALS['tmpl']->assign("manage_impose_fee",$manage_impose_fee);
		$GLOBALS['tmpl']->assign("loan_list",$loan_list);
	
		$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
		$GLOBALS['tmpl']->assign("inrepay_info",$inrepay_info);
	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refdetail.html");
		$GLOBALS['tmpl']->display("uc_deal_mrefdetail.html");
	}
	public function borrowed(){
		$this->getList("borrowed");
	}
	public function loans(){
		$this->getList("loans");
	}
	public function over(){
		$this->getList("over");
	}
	public function getList($mode="borrowed"){
		$result = getLoansList($mode,intval($GLOBALS['user_info']['id']),intval($_REQUEST['p']));
		$user_id = $GLOBALS['user_info']['id'];
		$seven_time = strtotime('+7 days');
		$time = time();
		// 近七日待还
		$money_log["invest_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 0 and cunguan_tag=1 and repay_time<=".$seven_time.""));
		// 剩余待还
		$money_log["repay_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 0 and cunguan_tag=1"));
		// 累计借款
		$money_log["loans_money"]=$GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE user_id=".$GLOBALS['user_info']['id']." and cunguan_tag=1");
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$GLOBALS['tmpl']->assign("list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('money_log',$money_log);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROWED']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrowed.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	//还款计划
	public function borrowed_info(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			showErr("请先登陆！",0,url("index","user#login"));
		}
		$deal_id = intval($_REQUEST['deal_id']);
		$repay_log = $GLOBALS['db']->getAll("SELECT id,deal_id,status,has_repay,interest_money,self_money,repay_money,repay_time,raise_money,true_repay_time,l_key FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id." and user_id=".$user_id." order by repay_time asc");
		if(!$repay_log){
			showErr("操作错误！",0,url("index","deals"));
		}
		//应还金额
		$repay_money = sprintf('%.2f',$GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id." and user_id=".$user_id." and cunguan_tag=1"));
		//剩余应还
		$has_repay_money = sprintf('%.2f',$GLOBALS['db']->getOne("SELECT sum(repay_money) as money FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id." and user_id=".$user_id." and has_repay=0 and cunguan_tag=1"));
		$deal = $GLOBALS['db']->getRow("SELECT id,name,repay_time,repay_start_time,borrow_amount,rate,deal_status from ".DB_PREFIX."deal where id = ".$deal_id."  and is_delete <> 1  and is_effect = 1  and cunguan_tag=1");
		$deal['repay_start_time'] = to_date($deal['repay_start_time'],"Y-m-d");
		$type_status = 0;
		$t_time = strtotime(date("Y-m-d"));//当前日期的时间戳
		foreach($repay_log as $k=>$v){
			if($v['has_repay']==1){//已还款
				$repay_log[$k]['type_status'] = 1;
				if($v['true_repay_time']>$v['repay_time']){//如果逾期
						$repay_log[$k]['has_repay']=3;
					}
										
			}else{
				if($type_status==0){
					$type_status = 1;
					if($v['repay_time']>=$t_time){//如果等于当前时间或提前一天 == 0||$v['repay_time']-$t_time == 86400
					$repay_log[$k]['type_status'] = 0;
					}elseif($v['repay_time']<$t_time){//如果逾期
					$repay_log[$k]['has_repay']=2;
					$repay_log[$k]['type_status'] = 0;
					}else{
						$repay_log[$k]['type_status'] = 1;
					}
				}else{
						$repay_log[$k]['type_status'] = 1;
					}
			}
			$repay_log[$k]['repay_time'] =  to_date($v['repay_time'],"Y-m-d");
			if($v['self_money']>0){
				$repay_log[$k]['interest_money'] = round($v['repay_money'] - $v['self_money'],2);
			}else{
				$repay_log[$k]['interest_money'] = round($v['repay_money'],2);
			}
			
			$repay_log[$k]['l_key'] =  $v['l_key']+1;
			$repay_log[$k]['total_money'] = round($v['repay_money'],2);
		}
		//存管账户余额
		$cunguan_money = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'__JIUCAITONGAESKEY__') FROM ".DB_PREFIX."user  WHERE id=".$user_id." and cunguan_tag=1");
		$GLOBALS['tmpl']->assign("day_repay_list",$day_repay_list['list']);
		$GLOBALS['tmpl']->assign('repay_log',$repay_log);
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign('repay_money',$repay_money);
		$GLOBALS['tmpl']->assign('has_repay_money',$has_repay_money);
		$GLOBALS['tmpl']->assign("page_title","借款详情");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrowed_info.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function borrow_stat(){
		$user_statics = sys_user_status($GLOBALS['user_info']['id'],false,true);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROW_STAT']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrow_stat.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	public function mborrow_stat(){
		$user_statics = sys_user_status($GLOBALS['user_info']['id'],false,true);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROW_STAT']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrow_stat.html");
		$GLOBALS['tmpl']->display("uc_deal_mborrow_stat.html");
	}
	
	function generation(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$is_ajax = intval($_REQUEST['is_ajax']);
	
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在",$is_ajax);
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款",$is_ajax);
		}
		if($deal['repay_progress_point'] < $deal['generation_position']){
			showErr("已还金额不足够续约",$is_ajax);
		}
		$GLOBALS['tmpl']->assign("deal",$deal);
		echo $GLOBALS['tmpl']->fetch("inc/uc/uc_deal_generation.html");
		
	}
	
	function dogeneration(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$is_ajax = intval($_REQUEST['is_ajax']);
	
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在",$is_ajax);
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款",$is_ajax);
		}
		if($deal['repay_progress_point'] < $deal['generation_position']){
			showErr("已还金额不足够续约",$is_ajax);
		}
		
		$data['deal_id'] = $id;
		$data['user_id'] = $GLOBALS['user_info']['id'];
		$data['money'] = $deal['need_remain_repay_money'];
		$data['create_time'] = TIME_UTC; 
		
		$rs_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."generation_repay_submit WHERE deal_id=".$id." AND user_id=$user_id");
		
		if(!$rs_id){
			$GLOBALS['db']->autoExecute(DB_PREFIX."generation_repay_submit",$data);
			if($GLOBALS['db']->insert_id() > 0){
				showSuccess("申请续约成功",$is_ajax);
			}
			else{
				showErr("申请续约失败",$is_ajax);
			}
		}
		else{
			$GLOBALS['db']->autoExecute(DB_PREFIX."generation_repay_submit",$data,"UPDATE","id=".$rs_id);
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess("申请续约成功",$is_ajax);
			}
			else{
				showErr("申请续约失败",$is_ajax);
			}
		}
	}
	
	/**
	 * 删除草稿
	 */
	function removesave(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$is_ajax = intval($_REQUEST['is_ajax']);
		if($id==0){
			showErr("参数错误",$is_ajax);
		}
		$deal = $GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."deal WHERE id=".$id." AND user_id=$user_id and is_delete = 2");
		if(!$deal){
			showErr("草稿不存在",$is_ajax);
		}
		
		 $GLOBALS['db']->query("DELETE FROM  ".DB_PREFIX."deal WHERE id=".$id." AND user_id=$user_id and is_delete = 2");
		 if($GLOBALS['db']->affected_rows() > 0){
			showSuccess("删除成功",$is_ajax);
		}
		else{
			showErr("删除失败",$is_ajax);
		}
	}
	
	public function export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$id = intval($_REQUEST['id']);
		$deal = get_deal($id);
		//定义条件
		if($id == 0){
			showErr("操作失败！");
		}
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}

		$list = get_deal_load_list($deal);
		foreach($list as $k=>$v){
			$result_min = get_deal_user_load_list($deal,0,$list[$k]['l_key'],-1,0,0,1,$limit);
			$list[$k]['item'] = $result_min['item'];
		}
		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv_repay'), $page+1);
            if($deal['is_mortgage']==1)
                $repay_value = array('l_key_index'=>'""','repay_day_format'=>'""','month_has_repay_money_all_format'=>'""','month_need_all_repay_money_format'=>'""','month_repay_money_format'=>'""','month_manage_money_format'=>'""','impose_all_money_format'=>'""','mortgage_fee_format'=>'""','status_format'=>'""');
            else
                $repay_value = array('l_key_index'=>'""','repay_day_format'=>'""','month_has_repay_money_all_format'=>'""','month_need_all_repay_money_format'=>'""','month_repay_money_format'=>'""','month_manage_money_format'=>'""','impose_all_money_format'=>'""','status_format'=>'""');
			//if($page == 1)
			$content = "";
	
			foreach($list as $k=>$v)
			{
			    if($deal['is_mortgage']==1)
                    $contentss = iconv("utf-8","gbk","还款期数,还款日,已还总额,待还总额,待还本息,管理费,逾期/违约金,抵押物管理费,状态");
                else
                    $contentss = iconv("utf-8","gbk","还款期数,还款日,已还总额,待还总额,待还本息,管理费,逾期/违约金,状态");
				$content  .= $contentss . "\n";
				$repay_value = array();
				$repay_value['l_key_index'] = iconv('utf-8','gbk','" 第' . $v['l_key_index'] . '期"');
				$repay_value['repay_day_format'] = iconv('utf-8','gbk','"' . $v['repay_day_format'] . '"');
				$repay_value['month_has_repay_money_all_format'] = iconv('utf-8','gbk','"' . $v['month_has_repay_money_all_format'] . '"');
				$repay_value['month_need_all_repay_money_format'] = iconv('utf-8','gbk','"' . $v['month_need_all_repay_money_format'] . '"');
				$repay_value['month_repay_money_format'] = iconv('utf-8','gbk','"' . $v['month_repay_money_format'] . '"');
				$repay_value['month_manage_money_format'] = iconv('utf-8','gbk','"' . $v['month_manage_money_format'] . '"');
				$repay_value['impose_all_money_format'] = iconv('utf-8','gbk','"' . $v['impose_all_money_format'] . '"');
                if($deal['is_mortgage']==1){
                    $repay_value['mortgage_fee_format'] = iconv('utf-8','gbk','"' . $v['mortgage_fee_format'] . '"');
                }
				$repay_value['status_format'] = iconv('utf-8','gbk','"' . $v['status_format'] . '"');
				$content .= implode(",", $repay_value) . "\n";
				
				$repay_value_item = array('empty'=>'""','id'=>'""','t_user_name'=>'""','month_repay_money_formats'=>'""','impose_money_format'=>'""','status_formats'=>'""');
				$contents = iconv("utf-8","gbk",",借款单号,会员,还款本息 ,逾期/违约金,状态");
				$content .= $contents . "\n";
					
				foreach($list[$k]['item'] as $kk=>$vv)
				{
					$repay_value_item['empty'] = iconv('utf-8','gbk','""');
					$repay_value_item['id'] = iconv('utf-8','gbk','"' . $list[$k]['item'][$kk]['id'] . '"');
					if($list[$k]['item'][$kk]['t_user_id']>0){
						$repay_value_item['t_user_name'] = iconv('utf-8','gbk','"' . $list[$k]['item'][$kk]['t_user_name'] . '"');
					}else{
						$repay_value_item['t_user_name'] = iconv('utf-8','gbk','"' . $list[$k]['item'][$kk]['user_name'] . '"');
					}
					
					$repay_value_item['month_repay_money_formats'] = iconv('utf-8','gbk','"' . $list[$k]['item'][$kk]['month_repay_money_format'] . '"');
					$repay_value_item['impose_money_format'] = iconv('utf-8','gbk','"' . $list[$k]['item'][$kk]['impose_money_format'] . '"');
					$repay_value_item['status_formats'] = iconv('utf-8','gbk','"' . $list[$k]['item'][$kk]['status_format'] . '"');
					$content .= implode(",", $repay_value_item) . "\n";
				}
				$content .= "\n";
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
	
	
	
	
}
?>
<?php
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
require_once APP_ROOT_PATH."system/libs/user.php";
require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_loansModule extends SiteBaseModule
{
	/* public function index(){
		$this->getlist("index");
	}
	public function loans(){
		$this->getlist("loans");
	}
	public function flow(){
		$this->getlist("flow");
	}
	public function ing(){
		$this->getlist("ing");
	}
	public function over(){
		$this->getlist("over");
	}
	public function bad(){
		$this->getlist("bad");
		
	} */
	
    /* private function getlist($mode = "index") {
    	
    	$result = getLoansList($mode,intval($GLOBALS['user_info']['id']),intval($_REQUEST['p']));
    	// $money_log= get_user_money_info($GLOBALS['user_info']['id']);
    	$loans_sql = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 0"));
		$invest_deal = $GLOBALS['db']->getRow($invest_sql);
		$seven_time = strtotime('-7 days');
		$time = time();
		// 近七日待还
		$money_log["seven_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 0 and repay_time>".$seven_time." and repay_time<=$time"));
		// 剩余待还
		$money_log["repay_money"] = sprintf('%.2f', $GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE user_id=".$GLOBALS['user_info']['id']." and has_repay = 0"));
		// 累计借款
		$money_log["loans_money"]=$GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE user_id=".$GLOBALS['user_info']['id']);
    	$GLOBALS['tmpl']->assign('money_log',$money_log);
    	$list = $result['list'];
    	$count = $result['count'];
    	$GLOBALS['tmpl']->assign("list",$list);
    	$page = new Page($count,app_conf("PAGE_SIZE"));   //初始化分页对象
    	$p  =  $page->show();
    	$GLOBALS['tmpl']->assign('pages',$p);
    	
		$GLOBALS['tmpl']->assign('user_id', $GLOBALS['user_info']['id']);
		
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_INVEST']);
  
    	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_loans.html");
    	$GLOBALS['tmpl']->display("page/uc.html");
    } */
	/* public function repay_info(){
		$user_id = $GLOBALS['user_info']['id'];
		$deal_id = intval($_REQUEST['deal_id']);
		$repay_log = $GLOBALS['db']->getAll("SELECT id,deal_id,status,has_repay,interest_money,self_money,repay_time,raise_money,l_key FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id." and user_id=".$user_id." order by repay_time asc");
		//应还金额
		$repay_money = sprintf('%.2f',$GLOBALS['db']->getOne("SELECT sum(repay_money) FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id." and user_id=".$user_id));
		//剩余应还
		$has_repay = sprintf('%.2f',$GLOBALS['db']->getOne("SELECT sum(repay_money) as money FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal_id." and user_id=".$user_id." and has_repay=0"));
		$deal = $GLOBALS['db']->getRow("SELECT repay_time,repay_start_time,borrow_amount,rate from ".DB_PREFIX."deal where id = ".$deal_id."  and is_delete <> 1  and is_effect = 1 ");
		foreach($repay_log as $k=>$v){
			$repay_log[$k]['repay_time'] =  to_date($repay_log[$k]['repay_time'],"Y-m-d");
			$repay_log[$k]['l_key'] =  $repay_log[$k]['l_key']+1;
			$repay_log[$k]['total_money'] = $repay_log[$k]['interest_money']+$repay_log[$k]['self_money'];
		}
		//存管账户余额
		$cunguan_money = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'__JIUCAITONGAESKEY__') FROM ".DB_PREFIX."user  WHERE id=".$user_id);
		$GLOBALS['tmpl']->assign("day_repay_list",$day_repay_list['list']);
		$GLOBALS['tmpl']->assign('repay_log',$repay_log);
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign('repay_money',$repay_money);
		$GLOBALS['tmpl']->assign('has_repay',$has_repay);
		$GLOBALS['tmpl']->assign("page_title","出借详情");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_repay_info.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	} */

    //还款验密
	public function do_repay(){
		$user_id = $GLOBALS['user_info']['id'];
		$repay_id = intval($_REQUEST['repay_id']);
		$repay = $GLOBALS['db']->getOne("select cunguan_tag from ".DB_PREFIX."deal_repay where id=".$repay_id." and user_id =".$user_id);
		if($repay ==1){
			$pub = new Publics();
			$seqno = $pub ->seqno();
			$data['seqno'] = $seqno;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$data,"UPDATE","id=".$repay_id);
			$html = $pub ->verify_trans_password('uc_loans',"repay_money",$user_id,'4',$seqno);
			echo $html;die;
		}
	}
	//还款
	public function repay_money(){
		$ip = get_client_ip();
		if($ip!='111.198.16.92'){
			showSuccess("非法访问！");
		}
		if($_GET['flag']!=1){
			echo "验密失败！";die;
		}
		$seqno = strim($_GET['businessSeqNo']);
		$repay = $GLOBALS['db']->getRow("select id,user_id,repay_money,repay_time,self_money,interest_money,raise_money from ".DB_PREFIX."deal_repay where seqno='$seqno'");
		//$ip = get_client_ip();
		//if($ip =="36.110.98.254:19001"){
			$deal = new Deal();
			$res = $deal->repay_money($seqno,'T04',$repay['id']);//还款
			$res_code =$res['respHeader']['respCode'];
			if($res_code=="P2P0000"){
				modify_account(array('cunguan_money'=>-($repay['repay_money'])), $repay['user_id'], "偿还本息", 4, "偿还本息",1);
				$pub = new Publics();
				$seqno = $pub ->seqno();
				$res1 = $deal ->repay_money($seqno,'T05',$repay['id']);//出款
				$res1_code =$res1['respHeader']['respCode'];
				if($res1_code=="P2P0000"){
					
					$infos = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load_repay where repay_id=".$repay['id']);
					if($repay['raise_money']>0){
						foreach($infos as $key=>$value){
							$arr['user_id'] = $value['user_id'];
							$arr['raise_money'] = $value['raise_money'];
							$list[]=$arr;
						}
						$seqno = $pub ->seqno();
						$res1 = $deal ->earning_money($seqno,'T10',$list);//募集期收益
						$res1_code =$res1['respHeader']['respCode'];
						if($res1_code!="P2P0000"){
							return false;
						}
					}
					
					
						foreach($infos as $key=>$value){
							if($infos[$key]['self_money']>0){ //还本还息才需要解冻投资资金
								$data['cunguan_lock_money'] = -$value['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
							}
						
							$data['cunguan_money']=$value['repay_money'];
							
							 //资金增加++++++++++++++++++++++
							$data['create_time'] = time(); //还款时间
							$data['brief'] = $value['virtual_info']; //虚拟货币消息
							$data['deal_id'] = $value['deal_id'];
							$data['load_repay_id'] = $value['load_repay_id'];
							$data['load_id'] = $value['load_id'];
							$msg = $value['self_money']>0?"还本还息":"还息";	
							if($value['raise_money']>0){
								$data['cunguan_money']=$value['raise_money'];
								modify_account($data, $value['user_id'], "募集期收益", 57, $data['brief'],1);
							}
							modify_account($data, $value['user_id'], $msg, 5, $data['brief'],1);
								//添加资金记录
							$statusArray['calculate_status'] = 1;
							$statusArray['has_repay'] = 1; //设置已还款标志
							$statusArray['true_repay_time'] = time();
							$statusArray['true_repay_money'] = $value['repay_money'];
							$statusArray['true_self_money'] = $value['self_money'];
							$statusArray['true_interest_money'] = $value['interest_money'];
							$statusArray['true_repay_date'] = date('Y-m-d', time());
							$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$statusArray,"UPDATE","id=".$infos[$key]['id']);
						}
						$repay_data['has_repay'] = 1;
						$repay_data['true_repay_date'] = date('Y-m-d', time());
						$repay_data['true_repay_money'] = $repay['repay_money'];
						$repay_data['true_self_money'] = $repay['self_money'];
						$repay_data['true_interest_money'] = $repay['interest_money'];
						$time = strtotime(to_date(time(),"Y-m-d"));
						if($time<$repay['repay_time']){
							$repay_data['status'] = 0;
						}elseif($time==$repay['repay_time']){
							$repay_data['status'] = 1;
						}else{
							$repay_data['status'] = 2;
						}
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$repay['id']);
				}else{
					var_dump("1111");die;
				}
				
			//}
		}else{
			var_dump("2222");die;
		}
	}
	
}
?>
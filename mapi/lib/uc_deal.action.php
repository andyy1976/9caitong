<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_deal
{
	public function index(){
		/*
		Id:贷款单ID
		load_id：出借记录id
		*/
		$root = get_baseroot();
		$page = intval(base64_decode($GLOBALS['request']['page']));
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			$status = intval(base64_decode($GLOBALS['request']['status']));
			if(isset($status) && $status == "1"){
				$sql ="select id,name,borrow_amount,repay_start_time,deal_status from ".DB_PREFIX."deal where is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and cunguan_tag=1 and user_id = ".$user_id." and deal_status=4 order by create_time desc limit ".$limit."";
				$result['list'] = $GLOBALS['db']->getAll($sql);
				$result['count'] = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal where is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and cunguan_tag=1 and user_id = ".$user_id." and deal_status=4");
			}elseif(isset($status) && $status=="2"){
				$sql ="select id,name,borrow_amount,repay_start_time,deal_status from ".DB_PREFIX."deal where is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and cunguan_tag=1 and user_id = ".$user_id." and deal_status=5 order by create_time desc limit ".$limit."";
				$result['list'] = $GLOBALS['db']->getAll($sql);
				$result['count'] = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal where is_effect=1 and is_delete=0 and publish_wait = 0 and debts=0 and cunguan_tag=1 and user_id = ".$user_id." and deal_status=5");
			}
			/*******筛选可用字段********/
			foreach ($result['list'] as $k => $v) {
				$deal['id'] = $v['id'];
				if(mb_strlen($v['name'],'utf8')>15){
					$deal['name'] = cut_str(strval($v['name']),15).'...';
				}else{
					$deal['name'] = strval($v['name']);
				}
				//本期应还
				if($v['deal_status']==5){
					$deal['rebate_money'] = '0.00';
					$deal['borrow_manager_alert']=0;	//新加 2017-12-11
				}else {

					 $repay = $GLOBALS['db']->getRow("SELECT repay_money,repay_time FROM " . DB_PREFIX . "deal_repay WHERE user_id=" . $user_id . " and deal_id =" . $v['id'] . " and has_repay in(0,3) order by id asc limit 1");

					$repay_before = ($repay['repay_time'] - time())/86400; //到期还款天数
					if($repay_before <= 7)
						$deal['borrow_manager_alert']=1; //新加 2017-12-11
					else
						$deal['borrow_manager_alert']=0;
					$deal['rebate_money'] = $repay['repay_money'];

				}

				$deal['repay_time'] = date("Y-m-d",$v['repay_start_time']);
				$deal['money'] = strval(ceil($v['borrow_amount']));
				$list[] = $deal;
			}

			/* 新消息 更新 做红点标记 2017-12-11 */
			$seventime =time() +7*86400;
			$nowtime =time();
			$new_borrow_manager_id =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."deal_repay where user_id =".$user_id. " and has_repay=0 order by id desc limit 1");
			$root['new_borrow_manager_id'] =$new_borrow_manager_id;
			/* 新消息 更新 做红点标记 2017-12-11 结束 */
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$root['item'] = $list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "回款列表";
		output($root);		
	}
}
?>

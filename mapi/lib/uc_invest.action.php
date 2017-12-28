<?php
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class uc_invest
{
	public function index(){
		
		$root = get_baseroot();		
		$page = intval(base64_decode($GLOBALS['request']['page']));
		$order = " order by dl.id desc";
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
				//$condition = " and (de.deal_status = 1 or de.deal_status = 2 or de.deal_status = 4) and de.cunguan_tag =0";
				//$result = get_invest_log($user_id,$condition,$order,$limit);
				$result = get_app_invest_list($mode = "invite",$user_id,$page);	//进行中
			}elseif(isset($status) && $status=="2"){
				//$condition = " and de.deal_status = 5 and de.cunguan_tag =0";
				//$result = get_invest_log($user_id,$condition,$order,$limit);
				$result = get_app_invest_list($mode = "over",$user_id,$page); //已还清
			}
			/*******筛选可用字段********/
			foreach ($result['list'] as $k => $v) {
				$deal['id'] = $v['bid'];
                if(mb_strlen($v['sub_name'],'utf8')>10){
                    $deal['name'] = cut_str(strval($v['sub_name']),15).'...';
                }else{
                    $deal['name'] = strval($v['sub_name']);
                }
				//预期收益
				$intterest_money= $GLOBALS['db']->getOne("SELECT sum(interest_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$v['bid']." and load_id=".$v['load_id']);
				if($intterest_money){
					$deal['rebate_money'] = $GLOBALS['db']->getOne("select sum(interest_money) as interest_money from ".DB_PREFIX."deal_load_repay  where load_id =".$v['load_id']);
					$deal['repay_time'] = $GLOBALS['db']->getOne("SELECT repay_date FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$user_id." and deal_id =".$v['bid']." and load_id=".$v['load_id']." order by id desc limit 1");

				}else{
					$deal['rebate_money'] ="审核中";
					$deal['repay_time'] = "审核中";
				}
				$deal['money'] = strval(ceil($v['total_money']));
				$deal['load_id'] = $v['load_id'];
				$list[] = $deal;
			}
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$root['item'] = $list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));

			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "我的出借";
		output($root);		
	}
}
?>

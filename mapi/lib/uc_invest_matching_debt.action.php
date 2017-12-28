<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_invest_matching_debt
{
	public function index(){
		/*
		Id:贷款单ID
		load_id：出借记录id
		*/
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$load_id = intval(base64_decode($GLOBALS['request']['load_id']));
        $information_status = intval(base64_decode($GLOBALS['request']['information_status']));
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$deals=$GLOBALS['db']->getAll("select d.id,dl.id as deal_load_id,d.name,d.borrow_amount as total_money from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on d.id=dl.deal_id where dl.plan_load_id=".$load_id." and dl.user_id=".$user_id);
			foreach ($deals as $k => $v) {
				$deals[$k]['total_money'] = $v['total_money']."元";
			}
			$deal = $deals?$deals:"";

			$root['item'] = $deal;
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "匹配债权";
		output($root);
	}
}
?>

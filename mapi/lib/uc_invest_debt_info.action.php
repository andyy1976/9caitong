<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_invest_debt_info
{
	public function index(){
		/*
		Id:贷款单ID
		load_id：出借记录id
		*/
		$root = get_baseroot();		
		$user =  $GLOBALS['user_info'];
		$id = intval(base64_decode($GLOBALS['request']['id'])); //匹配债权id
		$deal_load_id = intval(base64_decode($GLOBALS['request']['deal_load_id'])); //匹配投资记录id
        $information_status = intval(base64_decode($GLOBALS['request']['information_status']));
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			$deal_info=$GLOBALS['db']->getRow("select name,borrow_amount,rate,repay_time,repay_time_type,loantype from ".DB_PREFIX."deal where id=".$id);
			 switch ($deal_info['loantype']) {
			 	case '0':
			 		$deal_info['loantype'] = "等额本息";
			 		break;
			 	case '1':
			 		$deal_info['loantype'] = "按月付息,到期还本";
			 		break;
			 	case '2':
			 		$deal_info['loantype'] = "到期还本息";
			 		break;
			 	default:
			 		$deal_info['loantype'] = "本金均摊，利息固定";
			 		break;
			 }
			$deal_info['borrow_amount'] = $deal_info['borrow_amount']."元";
			$deal_info['rate'] = $deal_info['rate']."%";
			if($deal_info['repay_time_type'] == 1)
				$deal_info['repay_time'] = $deal_info['repay_time']."个月";
			else
				$deal_info['repay_time'] = $deal_info['repay_time']."天";
			$deal_info['hetong']['title'] = "查看合同";
			$deal_info['hetong']['url'] = WAP_SITE_DOMAIN . "/member.php?ctl=uc_invest&act=licai_down_contract&id=".$id."&load_id=".$deal_load_id;
			$root['item'] = $deal_info;
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "债权详情";
		output($root);
	}
}
?>

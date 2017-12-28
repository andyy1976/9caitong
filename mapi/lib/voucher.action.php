<?php
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class voucher
{
	public function index(){
		
		$root = array();
		/*
		Id:贷款单ID
		buy_money：投标金额
		*/
		$page = intval(base64_decode($GLOBALS['request']['page']));
		if($page==0)
			$page = 1;
		$root['session_id'] = es_session::id();
		$user_id = $GLOBALS['user_info']['id'];
		$buy_money = intval(base64_decode($GLOBALS['request']["buy_money"]));
		$limit = "0,10000";
		$result = get_voucher_list($limit,$user_id);
		foreach ($result['list'] as $k => $v) {
			if($v['status'] == 0 && $v['end_time'] >time()){
				$v['rmoney'] = strval($v['money'] * 50);
				$v['begin_time'] = date("Y-m-d",$v['begin_time']);
				$v['end_time'] = date("Y-m-d",$v['end_time']);
				if($v['rmoney'] > $buy_money){
					$v['type'] = 1;					
				}else{
					$v['type'] = 0;
				}
				$v['money'] = $v['money'];
				$list[] = $v;
				
			}
		}
		$root['item'] = $list;
		$voucher_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'red_explain'"));
		$root['voucher_explain'] = str_replace("。","。\n\n" ,$voucher_explain);
		$root['response_code'] = 1;
		output($root);		
	}
}
?>

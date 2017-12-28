<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_voucher
{
	public function index(){
		
		$root = get_baseroot();
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';			
			$root['user_login_status'] = 1;		
			$root['response_code'] = 1;
			$user_statics = get_user_money_info($user_id);
			$root['red_money'] = strval($user_statics['red_money']);
			$page = intval(base64_decode($GLOBALS['request']['page']));
			if($page==0)
				$page = 1;
				
			/*$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");*/			
			$limit = "0,1000";

			$result = get_voucher_list($limit,$user_id);
			foreach ($result['list'] as $k => $v) {
				if($v['status'] == 0 && $v['end_time'] >time()){
					$v['use_limit'] = $v['money']*50;
					$v['limit_time'] =to_date($v['begin_time'],"Y-m-d")." ". $GLOBALS['lang']['TO'] ." ". to_date($v['end_time'],"Y-m-d");
					$v['status_format'] = "未使用";
					$not_use[] = $v;
				}
				if($v['status'] == 1){
					$v['use_limit'] = $v['money']*50;
					$v['limit_time'] =to_date($v['begin_time'],"Y-m-d")." ". $GLOBALS['lang']['TO'] ." ". to_date($v['end_time'],"Y-m-d");
					$v['status_format'] = "已使用";
					$use[] = $v;
				}
				if($v['status'] != 1 && $v['end_time'] < time()){
					$v['use_limit'] = $v['money']*50;
					$v['limit_time'] =to_date($v['begin_time'],"Y-m-d")." ". $GLOBALS['lang']['TO'] ." ". to_date($v['end_time'],"Y-m-d");
					$v['status_format'] = "已过期";
					$expired[] = $v;
				}
			}
			$root['response_code'] = 1;
			if($not_use == null){
				$not_use=array();
				$root['item']['not_use'] = $not_use;
			}else{
				$root['item']['not_use'] = $not_use;
			}
			if($expired == null){
				$expired=array();
				$root['item']['expired'] = $expired;
			}else{
				$root['item']['expired'] = $expired;
			}
			if($use == null){
				$use=array();
				$root['item']['use'] = $use;
			}else{
				$root['item']['use'] = $use;
			}
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$voucher_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'voucher_explain'"));
		$root['voucher_explain'] = str_replace("。","。\n\n" ,$voucher_explain);
		$red_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'red_explain'"));
		$root['red_explain'] = str_replace("。","。\n\n" ,$red_explain);
		$root['program_title'] = "代金券";
		output($root);		
	}
}
?>

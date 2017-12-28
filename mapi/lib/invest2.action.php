<?php
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class invest
{
	public function index(){
		
		$root = array();
		
		$email = strim(base64_decode($GLOBALS['request']['email']));//用户名或邮箱
		$pwd = strim(base64_decode($GLOBALS['request']['pwd']));//密码
		$page = intval(base64_decode($GLOBALS['request']['page']));
		
		$mode = strim(base64_decode($GLOBALS['request']['mode']));//index,invite,ing,over,bad
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			
			
			
			$result = getInvestList($mode,$user_id,$page);
			 
			$root['item'] = $result['list'];
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("DEAL_PAGE_SIZE")));
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);		
	}
}
?>

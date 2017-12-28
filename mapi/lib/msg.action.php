<?php

//require APP_ROOT_PATH.'app/Lib/deal.php';
class msg
{
	public function index(){
		$root = get_baseroot();
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);//user_id
		if ($user_id >0){
			$root['user_login_status'] = 1;
			$rel_id = intval(base64_decode($GLOBALS['request']['rel_id']));   //deal_id  出借项目id
		
			$data['user_id'] = (int)$user_id;
			$data['rel_id'] = (int)$rel_id;
			$data['title'] = base64_decode($GLOBALS['request']['title']) == "" ?  valid_str(strim(base64_decode($GLOBALS['request']['content'])))  :  valid_str(strim(base64_decode($GLOBALS['request']['title']))) ;
			$data['content'] = valid_str(strim(base64_decode($GLOBALS['request']['content'])));
			
			$data['create_time'] = TIME_UTC;
			$data['rel_table'] = strim(base64_decode($GLOBALS['request']['rel_table']));
			$data['is_effect'] = 1;
			
			if ($data['rel_id'] > 0){
				$GLOBALS['db']->autoExecute(DB_PREFIX."message",$data,"INSERT");
			}
			
			if($GLOBALS['db']->affected_rows()){
				$root['response_code'] = 1;
				$root['show_err'] = "留言成功";
				
				$root['user_name'] = $user['user_name'];
				$root['avatar'] = wap_user_avatar($user_id);
				$root['content'] = $data['content'] ;
				$root['title'] = $data['title'] ;
				$root['create_time'] = to_date($data['create_time'],"Y-m-d");
			}else{
				$root['response_code'] = 0;
				$root['show_err'] = "留言失败";
			}
			
			//$message_list = $GLOBALS['db']->getAll("SELECT title,content,a.create_time,rel_id,a.user_id,a.is_effect,b.user_name FROM ".DB_PREFIX."message as a left join ".DB_PREFIX."user as b on  a.user_id = b.id WHERE rel_id = ".$id);
			//$root['message']= $message_list;
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);		
	}
}
?>


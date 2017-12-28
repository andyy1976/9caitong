<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_do_address
{
	public function index(){
		
		$root = array();
		
		
		$address_id = intval(base64_decode($GLOBALS['request']['id']));
		
		
		$user_name = base64_decode($GLOBALS['request']['user_name']);
		$user_phone = base64_decode($GLOBALS['request']['user_phone']);
		$user_address = base64_decode($GLOBALS['request']['user_address']); 
		$user_provinces_cities = base64_decode($GLOBALS['request']['user_provinces_cities']); 
		$user_zip_code = base64_decode($GLOBALS['request']['user_zip_code']); 
		$is_default = base64_decode($GLOBALS['request']['is_default']);
		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
						
			//提交地址
			$addr_data['user_id'] = $user_id;
			$addr_data['name'] = $user_name;
			$addr_data['address'] = $user_address;
			$addr_data['phone'] = $user_phone;
			$addr_data['provinces_cities'] = $user_provinces_cities;
			$addr_data['zip_code'] = $user_zip_code;
			$addr_data['is_default'] = $is_default;
			
			if($address_id){
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_address",$addr_data,"UPDATE","id=".$address_id." ");
				if($is_default == 1){
					$GLOBALS['db']->query("update ".DB_PREFIX."user_address set is_default = 0 where id <> ".$address_id);
				}
			}else{
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_address",$addr_data,"INSERT");
				
				if($is_default == 1){
					$user_address_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user_address ORDER BY id DESC LIMIT 1 ");
					$GLOBALS['db']->query("update ".DB_PREFIX."user_address set is_default = 0 where id <> ".$user_address_id);
				}
			}
			$root['show_err'] ="编辑成功";
			$address_id = $GLOBALS['db']->insert_id();
			if(intval($address_id) == 0){
				$root['info'] = "地址保存失败";
				$root['status'] = 0;
			}
			
			$root['response_code'] = 1;
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "收货编辑";
		output($root);		
	}
}
?>

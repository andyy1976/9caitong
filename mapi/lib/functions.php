<?php
//输出接口数据
function output($data)
{
	header("Content-Type:text/html; charset=utf-8");
	$r_type = intval(base64_decode($_REQUEST['r_type']));//返回数据格式类型; 0:base64;1;json_encode;2:array
	$data['act'] = ACT;
	$data['act_2'] = ACT_2;
	ob_start();
	ob_end_clean();
	if ($r_type == 0)
	{
		echo base64_encode(json_encode($data));
	}else if ($r_type == 1)
	{
		print_r(json_encode($data));
	}else if ($r_type == 2){

		print_r($data);
	}else if($r_type == 4){
		require_once APP_ROOT_PATH.'/system/libs/crypt_aes.php';
		$aes = new CryptAES();
		$aes->set_key('__JIUCAITONGAESKEY__');
		$aes->require_pkcs5();
		$encText = $aes->encrypt(json_encode($data));
		//var_dump(json_encode($data));exit;
		echo $encText;
	}
	exit;
}



function getMConfig(){

	$m_config = $GLOBALS['cache']->get("m_config_cg");
	if(true || $m_config===false)
	{
		$m_config = array();
		$sql = "select code,val from ".DB_PREFIX."m_config_cg";
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $item){
			$m_config[$item['code']] = $item['val'];
		}

		$GLOBALS['cache']->set("m_config_cg",$m_config);
	}
	return $m_config;
}
function getVersion(){

	$version = $GLOBALS['cache']->get("version");
	if(true || $version===false)
	{
		$version = array();
		$sql = "select code,val from ".DB_PREFIX."app_version";
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $item){
			$version[$item['code']] = $item['val'];
		}

		$GLOBALS['cache']->set("version",$version);
	}
	return $version;
}


/**
 * 过滤SQL查询串中的注释。该方法只过滤SQL文件中独占一行或一块的那些注释。
 *
 * @access  public
 * @param   string      $sql        SQL查询串
 * @return  string      返回已过滤掉注释的SQL查询串。
 */
function remove_comment($sql)
{
	/* 删除SQL行注释，行注释不匹配换行符 */
	$sql = preg_replace('/^\s*(?:--|#).*/m', '', $sql);

	/* 删除SQL块注释，匹配换行符，且为非贪婪匹配 */
	//$sql = preg_replace('/^\s*\/\*(?:.|\n)*\*\//m', '', $sql);
	$sql = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql);

	return $sql;
}

function emptyTag($string)
{
	if(empty($string))
		return "";

	$string = strip_tags(trim($string));
	$string = preg_replace("|&.+?;|",'',$string);

	return $string;
}

//function get_abs_img_root($content)
//{
//	return str_replace ( "./public/", get_domain () . APP_ROOT . "/../public/", $content );
//	// return str_replace('/mapi/','/',$str);
//}
////
//function get_abs_img_root_wap($content)
//{
//	return str_replace ( "./public/", get_domain () . APP_ROOT . "/public/", $content );
//	// return str_replace('/mapi/','/',$str);
//}

function get_user_has($key,$value,$is_nav=0){
	$ext = "";
	if($is_nav==0){
		$ext = " `".$key."` = '".$value."'";
	}
	else{
		$ext = " `".$key."` = ".$value;
	}
	$row=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."user where $ext ");
	if($row){
		return $row;
	}else{
		return false;
	}
}

function get_abs_img_root($content)
{

	//return str_replace("./public/",WAP_SITE_DOMAIN.APP_ROOT."/../public/",$content);
	return $content;
}
function get_abs_url_root($content)
{
	$content = str_replace("./",WAP_SITE_DOMAIN.APP_ROOT."/../",$content);
	return $content;
}

function get_abs_wap_url_root($content)
{
	return str_replace("/mapi/../public","/public",$content);

}

function get_abs_wap_avatar_url_root($content)
{
	return str_replace("/mapi/public","/public",$content);

}

function user_check($username_email,$pwd)
{
//	$pwd = addslashes($pwd);
	if($username_email&&$pwd)
	{
		//$sql = "select *,id as uid from ".DB_PREFIX."user where (user_name='".$username_email."' or email = '".$username_email."' or mobile = '".$username_email."') and is_delete = 0";
		//$sql = "select *,id as uid from ".DB_PREFIX."user where (user_name='".$username_email."' or money_encrypt = AES_ENCRYPT('".$username_email."','".AES_DECRYPT_KEY."') or mobile_encrypt = AES_ENCRYPT('".$username_email."','".AES_DECRYPT_KEY."')) and is_delete = 0";
		$sql = "select *,id as uid,AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where mobile = '".$username_email."'";

		$user_info = $GLOBALS['db']->getRow($sql);

		$is_use_pass = false;
		if (strlen($pwd) != 32){
			if($user_info['user_pwd']==md5($pwd.$user_info['code']) || $user_info['user_pwd']==md5($pwd)){
				$is_use_pass = true;

			}
		}
		else{
			if($user_info['user_pwd']==$pwd){
				$is_use_pass = true;
			}
		}
		if($is_use_pass)
		{
			es_session::set("user_info",$user_info);
			$GLOBALS['user_info'] = $user_info;
			return $user_info;
		}
		else
			return null;
	}
	else
	{
		return null;
	}
}

function user_login($username_email,$pwd)
{
	require_once APP_ROOT_PATH."system/libs/user.php";
	if(check_ipop_limit(CLIENT_IP,"user_dologin",intval(app_conf("SUBMIT_DELAY")))){
		$result = do_login_user($username_email,$pwd);
	}
	else{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
		return $result;
	}

	if($result['status']){
		es_session::set("check_login_count",0);
		return $result;
	}else{
		$GLOBALS['user_info'] = null;
		unset($GLOBALS['user_info']);

		if($result['data'] == ACCOUNT_NO_EXIST_ERROR){
			$err = $GLOBALS['lang']['USER_NOT_EXIST'];
		}
		if($result['data'] == ACCOUNT_PASSWORD_ERROR){
			$err = $GLOBALS['lang']['PASSWORD_ERROR'];
		}
		if($result['data'] == ACCOUNT_NO_VERIFY_ERROR){
			$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
		}
		if(es_session::is_set("check_login_count")){
				$check_count = es_session::get("check_login_count");
		}else{
			$check_count = 0;
		}
		es_session::set("check_login_count",$check_count+1);

		$result['msg'] = $err;
		return $result;
	}
}

/** 获取支付方式并且处理实体 */
function getPayMentList(){
	$payment_list = $GLOBALS ['db']->getAll ( "select * from " . DB_PREFIX . "payment where is_effect = 1 and online_pay=2 order by sort asc " );
	foreach ( $payment_list as $k => $v )
	{
		$class_name = $v ['class_name'] . "_payment";
		if($v ['class_name']=='Wwxjspay')
		{
			array_splice($payment_list,$k,1);
			//unset($payment_list[$k]);
			continue;
		}
		require_once APP_ROOT_PATH . "system/payment/" . $class_name . ".php";
		$o = new $class_name ();
		$payment_list [$k] ['logo'] = get_abs_img_root ( $v ['logo'] );
		unset ( $payment_list [$k] ['config'] );
	}
	$key = array_search('Wwxjspay', $payment_list);
	if ($key !== false)
	{
		array_splice($payment_list, $key);
	}
	return $payment_list;
}


function get_baseroot(){
	$root['licai_open'] = (int)app_conf("LICAI_OPEN");
	$root['user_name'] = $GLOBALS['user_info']['user_name'];
	$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
	if($MachineInfo[0]=='iOS'){
		if($GLOBALS['user_info']['user_type']==1){
			$root['cunguan_status'] = 3;
		}else {
			$root['cunguan_status'] = $GLOBALS['db']->getOne("select cunguan_status from " . DB_PREFIX . "switch_conf where status=1 and is_del=1 and switch_id=9");
			if($root['cunguan_status']==1){
                $root['default_status'] = $GLOBALS['db']->getOne("select default_status from ".DB_PREFIX."switch_conf where status=1 and is_del=1 and switch_id=9");
            }
		}
	}elseif($MachineInfo[0]=='Android'){
		if($GLOBALS['user_info']['user_type']==1){
			$root['cunguan_status'] = 3;
		}else {
			$root['cunguan_status'] = $GLOBALS['db']->getOne("select cunguan_status from " . DB_PREFIX . "switch_conf where status=1 and is_del=1 and switch_id=10");
			if ($root['cunguan_status'] == 1) {
				$root['default_status'] = $GLOBALS['db']->getOne("select default_status from " . DB_PREFIX . "switch_conf where status=1 and is_del=1 and switch_id=10");
			}
		}
	}else{
		$root['cunguan_status'] = "1";
		$root['default_status'] = "1";
	}
	return $root;
}



?>
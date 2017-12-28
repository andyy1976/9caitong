<?php
/*
 *
 * zhangteng   1010
 *
 * 2016.08.14
 *
 */
chenyu
require_once 'common.php';
filter_injection($_REQUEST);
if(!defined("APP_INDEX"))
	define("APP_INDEX","index");
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/'))
{
	mkdir(APP_ROOT_PATH.'public/runtime/app/',0777);
}

$GLOBALS['tmpl']->assign("site_info",get_site_info());
//开始输出友情链接

$GLOBALS['tmpl']->assign("f_link_data",load_auto_cache("links"));

//输出根路径
$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);

//输出语言包的js
if(!file_exists(get_real_path()."public/runtime/app/lang.js"))
{			
		$str = "var LANG = {";
		foreach($lang as $k=>$lang_row)
		{
			$str .= "\"".$k."\":\"".str_replace("nbr","\\n",addslashes($lang_row))."\",";
		}
		$str = substr($str,0,-1);
		$str .="};";
		@file_put_contents(get_real_path()."public/runtime/app/lang.js",$str);
}
/***
* app同步登录
* 如果app传过session_id用户进行登录
****/
if($_REQUEST['session_id']){
	$session_id = session_id($_REQUEST['session_id']);
	session_start();
}elseif($_REQUEST['PHPSESSID']){
	$session_id = session_id($_REQUEST['PHPSESSID']);
	session_start();
}
//会员自动登录及输出
$cookie_uname = es_cookie::get("user_name")?es_cookie::get("user_name"):'';
$cookie_upwd = es_cookie::get("user_pwd")?es_cookie::get("user_pwd"):'';
if($cookie_uname!=''&&$cookie_upwd!=''&&!es_session::get("user_info"))
{
	require_once APP_ROOT_PATH."system/libs/user.php";
	auto_do_login_user($cookie_uname,$cookie_upwd);
}

if(strim($_REQUEST['ctl']) == "uc_invest" ||  strim($_REQUEST['ctl']) == "uc_deal"){
	$r_user_name=strim($_REQUEST['user_name']);
	$r_user_pwd=strim($_REQUEST['user_pwd']);
	
	if($r_user_name!=''&&$r_user_pwd!='')
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		auto_do_login_user($r_user_name,$r_user_pwd);
	}
}
$user_info = es_session::get('user_info');
//设备来源


//$user_info = es_session::get('user_info');
//var_dump($user_info);die;
if(intval($user_info['id']) > 0){
	$user_info = get_user_info("*","is_delete = 0 and is_effect = 1 and id = ".intval($user_info['id']));
	if($user_info)
	{	
		es_session::set('user_info',$user_info);
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		if(check_ipop_limit(CLIENT_IP,"auto_send_msg",30,$user_info['id']))  //自动检测收发件
		{
			//有会员登录状态时，自动创建消息
			$msg_systems = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."msg_system where (end_time = 0 or end_time > ".TIME_UTC.") and (user_ids = '' or user_ids like '%"."|".$user_info['id']."-".$user_info['user_name']."|"."%')");
			foreach($msg_systems as $msg)
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."msg_box where to_user_id = ".$user_info['id']." and system_msg_id = ".$msg['id'])==0)
				{
					send_user_msg($msg['title'],$msg['content'],0,$user_info['id'],$msg['create_time'],$msg['id'],true);
				}		
			}
		}
	}
}
else{
	es_session::set('user_info',array());
}

 
//保存来路
if(!es_cookie::get("referer_url"))
{	
	if(!preg_match("/".urlencode(SITE_DOMAIN.APP_ROOT)."/",urlencode($_SERVER["HTTP_REFERER"])))
	es_cookie::set("referer_url",$_SERVER["HTTP_REFERER"]);
}
$referer = es_cookie::get("referer_url");

$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$jumpUrl = $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
es_cookie::set("jump_url",$jumpUrl);
$GLOBALS['tmpl']->assign('jumpUrl',es_cookie::get("jump_url"));

$IMG_APP_ROOT = APP_ROOT;
//var_dump($jumpUrl);
//var_dump(APP_ROOT);
//var_dump($IMG_APP_ROOT);
if($_SERVER['HTTP_HOST']=="wap.anq360.com"||$_SERVER['HTTP_HOST']=="wap.9caitong.com"||$_SERVER['HTTP_HOST']=="192.168.100.161:81"||$_SERVER['HTTP_HOST']=="jctwapcg.9caitong.com"||$_SERVER['HTTP_HOST']=="wapcg.9caitong.com"){

	if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_wap_caches/'))
		mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_wap_caches/',0777);
	if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_wap_compiled/'))
		mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_wap_compiled/',0777);
	$GLOBALS['tmpl']->cache_dir      = APP_ROOT_PATH . 'public/runtime/app/tpl_wap_caches';
	$GLOBALS['tmpl']->compile_dir    = APP_ROOT_PATH . 'public/runtime/app/tpl_wap_compiled';
	$GLOBALS['tmpl']->template_dir   = APP_ROOT_PATH . 'app/Tpl/wap';
	//定义当前语言包
	$GLOBALS['tmpl']->assign("LANG",$lang);
	//定义模板路径
	//$tmpl_path = SITE_DOMAIN.APP_ROOT."/app/Tpl/";
	$tmpl_path = $http_type . $_SERVER['HTTP_HOST']."/app/Tpl/";
	
	
	
	
	
	$GLOBALS['tmpl']->assign("TMPL",$tmpl_path."wap");
	$GLOBALS['tmpl']->assign("TMPL_REAL",APP_ROOT_PATH."app/Tpl/wap"); 
	$GLOBALS['tmpl']->assign("MOBILE_DOWN_PATH",SITE_DOMAIN.url("index","mobile"));
}else{

	if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_caches/'))
		mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_caches/',0777);
	if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_compiled/'))
		mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_compiled/',0777);
	$GLOBALS['tmpl']->cache_dir      = APP_ROOT_PATH . 'public/runtime/app/tpl_caches';
	$GLOBALS['tmpl']->compile_dir    = APP_ROOT_PATH . 'public/runtime/app/tpl_compiled';
	$GLOBALS['tmpl']->template_dir   = APP_ROOT_PATH . 'app/Tpl/' . app_conf("TEMPLATE");
	//定义当前语言包
	$GLOBALS['tmpl']->assign("LANG",$lang);
	//定义模板路径
	//$tmpl_path = SITE_DOMAIN.APP_ROOT."/app/Tpl/";
	$tmpl_path = $http_type . $_SERVER['HTTP_HOST']."/app/Tpl/";
	//echo 3;
	//var_dump(APP_ROOT_PATH);
	//var_dump($tmpl_path);exit;
	$GLOBALS['tmpl']->assign("TMPL",$tmpl_path.app_conf("TEMPLATE"));
	$GLOBALS['tmpl']->assign("TMPL_REAL",APP_ROOT_PATH."app/Tpl/".app_conf("TEMPLATE")); 

	$GLOBALS['tmpl']->assign("MOBILE_DOWN_PATH",SITE_DOMAIN.url("index","mobile"));
	if(is_dir(APP_ROOT_PATH."/wap"))
		$GLOBALS['tmpl']->assign("WAP_SITE_PATH",SITE_DOMAIN.wap_url("index","index"));
} 	


if(app_conf("SHOP_OPEN")==0)
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SHOP_CLOSE']);
	$GLOBALS['tmpl']->assign("html",app_conf("SHOP_CLOSE_HTML"));
	$GLOBALS['tmpl']->display("shop_close.html");
	exit;
}

$DEAL_MSG_COUNT = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0 and (send_type = 0 or send_type = 1) ");
$GLOBALS['tmpl']->assign("DEAL_MSG_COUNT",$DEAL_MSG_COUNT);

//一个账号只可以在一台设备登录
/*if($user_info){
	$sess = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."session where user_id ='".$user_info['id']."'");
	$jump_get_out = machineInfo();
	if($jump_get_out['index'] == "AndroidToHomePage"){
		if(!$_REQUEST['PHPSESSID']){
			$sessid = es_session::id();
			es_cookie::set("PHPSESSID",$sessid);
		}else{
			$sessid = $_REQUEST['PHPSESSID'];
		}
		if($sess['session_id'] != $sessid){
			$session_data = date("H:i:s",$sess['session_time']);
			$GLOBALS['tmpl']->assign('session_data',$session_data);
			$GLOBALS['tmpl']->assign('jump_get_out',$jump_get_out);
	    }
	}else if($jump_get_out['index'] == "iosToHomePage"){
		if(!$_REQUEST['session_id']){
			$sessid = es_session::id();
			es_cookie::set("PHPSESSID",$sessid);
		}else{
			$sessid = $_REQUEST['session_id'];
		}
		if($sess['session_id'] != $sessid){
			$GLOBALS['tmpl']->assign('jump_get_out',$jump_get_out);
	    }
	}else{
		if(WAP == 1){
			$sessid = es_session::id();
			if($sess['session_id'] != $sessid){
		        $session_data = "当前账号于".$sess['session_data']."在其他设备上登录，请注意账户安全。您已被迫下线，是否重新登录？";
		        $GLOBALS['tmpl']->assign('session_data',$session_data);
		        $GLOBALS['tmpl']->assign('jump_get_out',$jump_get_out);
		       	es_session::delete('user_info');
		    }
		}
		
	}
	
} */
?>
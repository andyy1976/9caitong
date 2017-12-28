<?php


//此处理处理url中带两个?号问题，第二个?会被替换成&  为安全起见，只处理汇付宝回调地址 added by zhangteng 20170516 16:00
$the_host = $_SERVER['HTTP_HOST'];//取得当前域名
$the_urlr = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';//判断地址后面部分
if(substr_count($the_urlr,'?')>1)//判断是不是首页
{
	//$the_url="/";//如果是首页，赋值为“/”
	$the_url = str_replace("huifuzhifu_notify?result","huifuzhifu_notify&result",$the_urlr);
    if($the_url!=$the_urlr){
        header('HTTP/1.1 301 Moved Permanently');//发出301头部
        header('Location:https://www.9caitong.com'.$the_url);//跳转到带www的网址
        die;
    }

}

if(!defined('ROOT_PATH'))
	define('ROOT_PATH', str_replace('index.php', '', str_replace('\\', '/', __FILE__)));


require ROOT_PATH.'system/common.php';



if($_REQUEST['is_pc']==1)
	es_cookie::set("is_pc","1",24*3600*30);

if (intval($GLOBALS['pay_req']['is_sj']) == 1){
	$_REQUEST['is_sj'] = 1;
}

//在此检测是否来自渠道方 
$device = isMobile()?"WAP":"PC";
$device = $device?$device:"UndefinedWap"; 
es_session::set("device",$device);//来源设备
if(isset($_REQUEST['source_id'])&&($_REQUEST['source_id'])!==""){
    $source_id = intval($_REQUEST['source_id']);
	es_session::set("source_id",$source_id);//渠道商编号
}
//echo es_cookie::get("is_pc");
//require ROOT_PATH.'app/Lib/SiteApp.class.php';
	//实例化一个网站应用实例
	//$AppWeb = new SiteApp(); 
if (isMobile() && !isset($_REQUEST['is_pc']) && es_cookie::get("is_pc")!=1 && intval($_REQUEST['is_sj'])==0  && trim($_REQUEST['ctl'])!='collocation'  && trim($_REQUEST['ctl'])!='mobile'){
	define("WAP","1");
	require ROOT_PATH.'app/Lib/SiteApp.class.php';
	//实例化一个网站应用实例
	$AppWeb = new SiteApp(); 
	//app_redirect("http://192.168.100.161:81");
}else{	
	require ROOT_PATH.'app/Lib/SiteApp.class.php';
	define("WAP","0");
	//实例化一个网站应用实例
	$AppWeb = new SiteApp(); 
}
 
?>
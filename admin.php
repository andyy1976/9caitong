<?php 

if(!defined("ADMIN_ROOT"))
{
	header('HTTP/1.1 404 Not Found');
	header("status: 404 Not Found");
	exit;
}

define("FILE_PATH",""); //文件目录，空为根目录
require './system/common.php';
define('BASE_PATH','./');
define('THINK_PATH', './admin/ThinkPHP');
//定义项目名称和路径
define('APP_NAME', 'admin');
define('APP_PATH', './admin');

// 加载框架入口文件 
require(THINK_PATH."/ThinkPHP.php");

//实例化一个网站应用实例
$AppWeb = new App(); 
//应用程序初始化
$AppWeb->run();   
//测试aaaaaaa

?>
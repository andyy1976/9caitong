<?php
require APP_ROOT_PATH.'app/Lib/page.php';
	class reportsModule extends SiteBaseModule
{
	public function index()
	{
		$id = $_REQUEST['id'];
		if(file_exists("./app/Tpl/new/page/reports/".$id."/index.html")){
			$GLOBALS['tmpl']->display("page/reports/".$id."/index.html");
		}
	}
	public function mod()
	{
		if(file_exists("./app/Tpl/new/page/reports/mod.html")){
			$GLOBALS['tmpl']->display("page/reports/mod.html");
		}
	}
	public function mod_m()
	{
		if(file_exists("./app/Tpl/new/page/reports/mod_m.html")){
			$GLOBALS['tmpl']->display("page/reports/mod_m.html");
		}
	}
	public function p201603()
	{
		if(file_exists("./app/Tpl/new/page/reports/p201603.html")){
			$GLOBALS['tmpl']->display("page/reports/p201603.html");
		}
	}
	public function p201604()
	{
		if(file_exists("./app/Tpl/new/page/reports/p201604.html")){
			$GLOBALS['tmpl']->display("page/reports/p201604.html");
		}
	}
	public function p201701()
	{
		if(file_exists("./app/Tpl/new/page/reports/p201701.html")){
			$GLOBALS['tmpl']->display("page/reports/p201701.html");
		}
	}

	public function p201702()
	{
		if(file_exists("./app/Tpl/new/page/reports/p201702.html")){
			$GLOBALS['tmpl']->display("page/reports/p201702.html");
		}
	}
	public function p201703()
	{
		if(file_exists("./app/Tpl/new/page/reports/p201703.html")){
			$GLOBALS['tmpl']->display("page/reports/p201703.html");
		}
	}
}

?>
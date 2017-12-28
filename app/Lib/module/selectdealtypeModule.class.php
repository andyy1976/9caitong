<?php

require APP_ROOT_PATH.'app/Lib/page.php';
class selectdealtypeModule extends SiteBaseModule
{
	public function index()
	{
		$GLOBALS['tmpl']->display("page/selectdealtype.html");              
	}		
}
?>
<?php

require APP_ROOT_PATH.'app/Lib/page.php';
class helptypeModule extends SiteBaseModule
{
	public function index()
	{
		$GLOBALS['tmpl']->display("page/helptype.html");              
	}		
}
?>
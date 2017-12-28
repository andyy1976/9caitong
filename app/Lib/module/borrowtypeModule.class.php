<?php

require APP_ROOT_PATH.'app/Lib/page.php';
class borrowtypeModule extends SiteBaseModule
{
	public function index()
	{
		$GLOBALS['tmpl']->display("page/borrowtype.html");              
	}		
}
?>
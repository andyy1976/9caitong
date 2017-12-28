<?php
require APP_ROOT_PATH.'app/Lib/crowd_func.php';
class crowd_listModule extends SiteBaseModule
{
	
	public function index(){
		
		 
		$GLOBALS['tmpl']->display("page/crowd_list.html");
	}
 	
}
?>

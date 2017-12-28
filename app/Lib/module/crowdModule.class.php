<?php
require APP_ROOT_PATH.'app/Lib/crowd_func.php';
class crowdModule extends SiteBaseModule
{
	
	public function show(){
		
		 
		$GLOBALS['tmpl']->display("page/crowd.html");
	}
 	
}
?>

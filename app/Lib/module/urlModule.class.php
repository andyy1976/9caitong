<?php


class urlModule extends SiteBaseModule
{
	public function index()
	{
		$r = addslashes(htmlspecialchars(trim($_REQUEST['r'])));
		$r = base64_decode($r);
		$url = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."urls where id = ".intval($r));
		if($url)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."urls set count = count + 1 where id = ".intval($r));
			app_redirect($url['url']);
		}
		else
		{
			app_redirect(url("index"));
		}
		
	}
}
?>
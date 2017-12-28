<?php
define(ACTION_NAME,"about");
define(MODULE_NAMEN,"index");
class aboutModule extends SiteBaseModule
{
	public function index()
	{
		// app下载统计
		$download_total=number_format(5437569);
		$GLOBALS['tmpl']->assign("download_total",$download_total);

        $stats = site_statics();
        $registered_user = str_split(strip_tags(number_format($stats['user_count'])));//注册用户数;
        $wapregistered_user = strip_tags(number_format($stats['user_count']));//注册用户数;
		$limit = ("0,4");	
		$limit1 = ("0,4");
		$result = get_article_list($limit,25,'ac.type_id = 0','');
		$resultvideo = get_article_list($limit1,38,'ac.type_id = 4','');
		$GLOBALS['tmpl']->assign('wapregistered_user',$wapregistered_user); //累计注册用户
		$GLOBALS['tmpl']->assign("listvideo",$resultvideo['list']);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("page_title","关于我们");
		$GLOBALS['tmpl']->assign("page_keyword","关于我们,");
		$GLOBALS['tmpl']->assign("page_description","关于我们,");
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/about.html");
	}
	public function yindaoye()
	{
	    $GLOBALS['tmpl']->display("page/yindaoye.html");
	}
}
?>
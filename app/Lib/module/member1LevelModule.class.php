<?php
define(ACTION_NAME,"memberLevel");
define(MODULE_NAMEN,"index");
class member1LevelModule extends SiteBaseModule
{

	public function index()
	{
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("page_title","会员等级");
		$GLOBALS['tmpl']->assign("page_keyword","会员等级,");
		$GLOBALS['tmpl']->assign("page_description","会员等级,");
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/memberLevel.html");
	}
	public function GrowthMission()
	{

		$GLOBALS['tmpl']->assign("page_title","成长任务");
	    $GLOBALS['tmpl']->display("page/memberLevel_GrowthMission.html");

	}
	public function privilege()
	{
		$GLOBALS['tmpl']->assign("page_title","会员特权");
	    $GLOBALS['tmpl']->display("page/memberLevel_privilege.html");
	}
	function aa(){
      echo "aa";die;

        $GLOBALS['tmpl']->display("page/memberLevel_privilege.html");
    }

}
?>
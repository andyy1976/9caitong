<?php
class interestModule extends SiteBaseModule{

    public function index() {    	
		$GLOBALS['tmpl']->assign("page_title","计息方式");
		$GLOBALS['tmpl']->assign("cate_title","计息方式");
    	$GLOBALS['tmpl']->display("page/interest_index.html");
    }
}
?>
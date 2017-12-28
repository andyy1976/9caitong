<?php
define(ACTION_NAME,"guarantee");
define(MODULE_NAMEN,"index");
class guaranteeModule extends SiteBaseModule{

    public function index() {
    	
    	$article_cate = get_acate_tree(12);
    	foreach($article_cate as $k=>$v){
    		$article_cate[$k]['article'] = get_article_list(100,$v['id'],"","",true);
    	}
    	
    	$GLOBALS['tmpl']->assign("article_cate",$article_cate);
    	
		
    	//最新借款列表
    	require APP_ROOT_PATH.'app/Lib/deal.php';
		$deal_list =  get_deal_list(9,0,"deal_status in (1,2)"," sort DESC,  id DESC");
		
		$GLOBALS['tmpl']->assign("deal_list",$deal_list['list']);
		$GLOBALS['tmpl']->assign("page_title","安全保障");
		$GLOBALS['tmpl']->assign("page_keyword","安全保障,");
        $GLOBALS['tmpl']->assign('cate_title',"安全保障");
		$GLOBALS['tmpl']->assign("page_description","安全保障,");
		$GLOBALS['tmpl']->assign("ACTION_NAME","guarantee");
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
    	$GLOBALS['tmpl']->display("page/guarantee_index.html");
    }
    
     public function detail() {
    	$id =  intval($_REQUEST['id']);
    	$aid =  intval($_REQUEST['aid']);
    	if($id==0){
    		app_redirect(url("index","guarantee"));
    	}
    	$article_cate = get_acate_tree(12);
    	$info = null;
    	foreach($article_cate as $k=>$v){
    		if($id == $v['id'])
    		{
    			$info = $v;
    		}
    		$article_cate[$k]['article'] = get_article_list(100,$v['id'],"","",true);
    	}
    	$GLOBALS['tmpl']->assign("article_cate",$article_cate);
    	$GLOBALS['tmpl']->assign("id",$id);
    	$GLOBALS['tmpl']->assign("aid",$aid);
    	
    	
    	$seo_title = $info['seo_title']!=''?$info['seo_title']:$info['title'];
		
    	$GLOBALS['tmpl']->assign("page_title",$seo_title." - 安全保障");
    	
		$seo_keyword = $info['seo_keyword']!=''?$info['seo_keyword']:$info['title'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",安全保障,");
		
		$seo_description = $info['seo_description']!=''?$info['seo_description']:$info['title'];
		$GLOBALS['tmpl']->assign("page_description",$seo_description.",安全保障,");
         $GLOBALS['tmpl']->assign('cate_title',"安全保障");
    	$GLOBALS['tmpl']->display("page/guarantee_detail.html");
    }
    /**
     * 小玖课堂
     */
    public function xiaojiu(){
        $GLOBALS['tmpl']->display("page/activity/xiaojiu.html");
    }

    /*
     * 合规之路
    */
    public function hehui(){
        $GLOBALS['tmpl']->display("page/activity/compliance_index.html");
    }

    /*
     *  玖财通宜宾商业银行
     * */
    public function contract_depository(){
        $GLOBALS['tmpl']->display("page/activity/Contract_depository.html");
    }

    /*
     *
     * */
    public function registered_experience(){
        $GLOBALS['tmpl']->display("page/activity/Registered_experience.html");
    }


}
?>
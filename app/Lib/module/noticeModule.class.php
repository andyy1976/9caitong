<?php
define(ACTION_NAME,"notice");
define(MODULE_NAMEN,"index");
require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH.'app/Lib/deal.php';
class noticeModule extends SiteBaseModule
{
	private function side_right(){
		//推荐标
		$rec_deal_list = get_deal_list(5,0,"is_recommend=1");
		$GLOBALS['tmpl']->assign("rec_deal_list",$rec_deal_list['list']);
	}
	public function index()
	{	
		$id = intval($_REQUEST['id']);
		$uname = addslashes(trim($_REQUEST['id']));	
		$act = addslashes(trim($_REQUEST['act']));
		
		if($uname=='list'||$act=='list')
		{
			$this->list_notice(intval($_REQUEST['cate_id']));
			exit;
		}
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).$GLOBALS['deal_city']['id']);
		if (!$GLOBALS['tmpl']->is_cached('page/notice_index.html', $cache_id))	
		{		
			$cate_list = get_acate_tree();			
			$GLOBALS['tmpl']->assign('cate_list',$cate_list);		
			if($id==0&&$uname=='')
			{
				app_redirect(APP_ROOT."/");
			}
			elseif($id==0&&$uname!='')
			{
				$id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."article where uname = '".$uname."'")); 
			}		
	
			if($id==0)
			{
				app_redirect(APP_ROOT."/");
			}
			//========区分wap和pc访问===========
			if(WAP == 1){
				$article = get_mobile_article($id);
			}else{
				$article = get_article($id);
			}
			//========区分wap和pc访问===========			
			if(!$article||$article['type_id']!=2)
			{
				app_redirect(APP_ROOT."/");
			}		
			else
			{
				if(check_ipop_limit(CLIENT_IP,"article",60,$article['id']))
				{
					//每一分钟访问更新一次点击数
					$GLOBALS['db']->query("update ".DB_PREFIX."article set click_count = click_count + 1 where id =".$article['id']);
				}
				
				if($article['rel_url']!='')
				{
					if(!preg_match ("/http:\/\//i", $article['rel_url']))
					{
						if(substr($article['rel_url'],0,2)=='u:')
						{
							app_redirect(parse_url_tag($article['rel_url']));
						}
						else
						app_redirect(APP_ROOT."/".$article['rel_url']);
					}
					else
					app_redirect($article['rel_url']);
				}
			}	
			
			//开始输出当前的site_nav			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$GLOBALS['lang']['SHOP_NOTICE'],'url'=>url("shop","notice#list"));
			if($article['type_id']==1)
			{
				$module = "help";
			}
			elseif($article['type_id']==2)
			{
				$module = "notice";
			}
			elseif($article['type_id']==3)
			{
				$module = "sys";
			}
			else 
			{
				$module = 'article';
			}
			if($article['uname']!='')
			$aurl = url("shop",$module,array("id"=>$article['uname']));
			else
			$aurl = url("shop",$module,array("id"=>$article['id']));
			$site_nav[] = array('name'=>$article['title'],'url'=>$aurl);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			//========区分wap和pc访问===========
			if(WAP == 1){
				$article = get_mobile_article($id);
			}else{
				$article = get_article($id);
			}			
			$GLOBALS['tmpl']->assign("article",$article);
			//========区分wap和pc访问===========		
			$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article_cate where id = ".intval($article['cate_id']));
			$GLOBALS['tmpl']->assign('cate',$cate);			
			$seo_title = $article['seo_title']!=''?$article['seo_title']:$article['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $article['seo_keyword']!=''?$article['seo_keyword']:$article['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $article['seo_description']!=''?$article['seo_description']:$article['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			$GLOBALS['tmpl']->assign("all_list",url("index","notice#list_notice"));
			$GLOBALS['tmpl']->assign("relate_help",$cate_list);			
			$this->side_right();
		}
		$GLOBALS['tmpl']->display("page/notice_index.html",$cache_id);
	}
	
	public function list_notice($cate_id='')
	{
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME."list_notice".trim($_REQUEST['id']).intval($_REQUEST['p']).$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('page/notice_list.html', $cache_id))	
		{	
			$cate_list = get_acate_tree();
			
			$GLOBALS['tmpl']->assign('cate_list',$cate_list);
			
			//分页
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
		 	//========区分wap和pc访问===========
			if(WAP == 1){
				$limit = '0,1000';
				$result = get_mobile_article_list($limit,0,'ac.type_id = 2','',true);
				foreach ($result['list'] as $k => $v) {
					$v['content'] = mb_substr(strip_tags($v['content']), 0,100,'utf-8');
					$v['create_time'] = date("Y-m-d",$v['create_time']);
					$v['url'] = "/member.php?ctl=uc_set&act=news&id=".$v['id'];
					$result['list'][$k] = $v;
				}
			}else{
				$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
				$result = get_article_list($limit,0,'ac.type_id = 2 and a.cate_id = '.$cate_id,'',true);
			}
			//========区分wap和pc访问===========
			$GLOBALS['tmpl']->assign("list",$result['list']);
			$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			if(WAP == 1){
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."app_msg_cate where id = ".intval($result['list'][0]['cate_id']));
			}else{
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article_cate where id = ".$cate_id);
			}			
			$GLOBALS['tmpl']->assign('cate',$cate);
			
			//开始输出当前的site_nav			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$GLOBALS['lang']['SHOP_NOTICE'],'url'=>url("shop","notice#list"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['SHOP_NOTICE']);
			$GLOBALS['tmpl']->assign('page_keyword',$GLOBALS['lang']['SHOP_NOTICE']);
			$GLOBALS['tmpl']->assign('page_description',$GLOBALS['lang']['SHOP_NOTICE']);
			
			$this->side_right();
		}
		$GLOBALS['tmpl']->display("page/notice_list.html",$cache_id);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
	}
}
?>
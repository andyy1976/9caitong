<?php
class news_report_list
{
	public function index(){
		$page = intval(base64_decode($GLOBALS['request']['page']));
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$news_list = $GLOBALS['db']->getAll("SELECT a.id,a.title,ac.title as name,ac.icon,a.content,a.create_time,a.img FROM ".DB_PREFIX."app_msg_cate ac left join ".DB_PREFIX."app_msg a on a.cate_id=ac.id WHERE  a.is_effect = 1 and a.is_delete=0 and ac.id=38  order by id desc limit ".$limit);
		$news_count = $GLOBALS['db']->getOne("select count(*)  from ".DB_PREFIX."app_msg where cate_id = 38");
		foreach ($news_list as $k => $v) {
			$v['content'] = mb_substr(strip_tags(str_replace('&nbsp;','',$v['content'])), 0,100,'utf-8');
			$v['content'] = mb_substr(strip_tags(str_replace('\r\n','',$v['content'])), 0,100,'utf-8');
			//$v['content'] = mb_substr(strip_tags(trim($v['content'],'&nbsp;')), 0,100,'utf-8');
			$v['create_time'] = date("Y-m-d",$v['create_time']);
			$v['icon'] =  get_abs_img_root(get_spec_image($v['icon'],0,0,1));
			$v['content_url'] = WAP_SITE_DOMAIN."/index.php?ctl=uc_set&act=news&id=".$v['id'];

			$news_list[$k] = $v;
		}

		$root['new_report_id']=$GLOBALS['db']->getOne("SELECT a.id,a.title,ac.title as name,ac.icon,a.content,a.create_time,a.img FROM ".DB_PREFIX."app_msg_cate ac left join ".DB_PREFIX."app_msg a on a.cate_id=ac.id WHERE  a.is_effect = 1 and a.is_delete=0 and ac.id=38  order by id desc limit 1 ");
		$root['item'] = $news_list;
		$root['response_code'] = 1;
		$root['page'] = array("page"=>$page,"page_total"=>ceil($news_count/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));
		$root['program_title'] = "报道列表";
		output($root);		
	}
}
?>

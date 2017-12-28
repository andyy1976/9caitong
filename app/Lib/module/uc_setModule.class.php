<?php 
/*require APP_ROOT_PATH.'app/Lib/uc.php';*/
class  uc_setModule extends SiteBaseModule
{
	
	public function index(){
		jumpUrl("jump_url_info");
		$new['nid'] = $GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."app_msg order by id desc");
		$new['id'] = $GLOBALS['db']->getOne("SELECT new_id FROM ".DB_PREFIX."user where id =".$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("new",$new);	
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function news(){

		$id = intval($_REQUEST['id']);
		$type = intval($_REQUEST['type']);
		$GLOBALS['tmpl']->assign("type",$type);
		$news_url = array(
			array(
				"name" => "公告",
			),
			array(
				"name" => "报道",
			),
			array(
				"name" => "动态",
			),
		);
		foreach($news_url as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['type'] = $k;		
			$news_url[$k]['url'] = url("index","uc_set#news",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('news_url',$news_url);
		if($id){
			$news_info = $GLOBALS['db']->getRow("SELECT title,content,create_time FROM ".DB_PREFIX."app_msg WHERE  id=".$id);
			$news_info['create'] = date("Y-m-d H:i:s",$news_info['create_time']);
			$news_info['time'] = date("Y年m月d日",$news_info['create_time']);
			$GLOBALS['tmpl']->assign("cate_title",'详情');
			$GLOBALS['tmpl']->assign("news_info",$news_info);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_detail.html");

		}else{
			if($type == 1)
				$content = " and a.cate_id = 38";
			elseif($type == 2)
				$content = " and a.cate_id = 36";
			else
				$content = " and a.cate_id = 37";
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			$news_list = $GLOBALS['db']->getAll("select a.*,ac.type_id,ac.icon from ".DB_PREFIX."app_msg as a left join ".DB_PREFIX."app_msg_cate as ac on a.cate_id = ac.id where  ac.is_effect = 1 and ac.is_delete = 0 and a.is_effect = 1 and a.is_delete = 0 $content order by a.sort desc limit ".$limit);
			foreach ($news_list as $k => $v) {
				$v['content'] = mb_substr(strip_tags($v['content']), 0,100,'utf-8');
				$v['create_time'] = date("Y-m-d",$v['create_time']);
				$v['url'] = "/member.php?ctl=uc_set&act=news&id=".$v['id'];
				$news_list[$k] = $v;
			}
			//公告读取状态
			$data['new_id'] = $GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."app_msg order by id desc");
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id = ".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("news_list",$news_list);
			$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));		
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_news.html");
		}
		$GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_info"));
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function ajaxNews(){
		$type = intval($_REQUEST['type']);
		if($type == 1)
			$content = "and cate_id = 38";
		elseif($type == 2)
			$content = "and cate_id = 36";
		else
			$content = "and cate_id = 37";
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."app_msg where is_effect = 1 and is_delete = 0 and is_effect = 1 and is_delete = 0 $content");	
		echo $count;
	}
	public function news_list(){
		$page = intval($_REQUEST['page']);
		$type = intval($_REQUEST['type']);
		if($type == 1)
			$content = " and a.cate_id = 38";
		elseif($type == 2)
			$content = " and a.cate_id = 36";
		else
			$content = " and a.cate_id = 37";
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$news_list = $GLOBALS['db']->getAll("select a.*,ac.type_id,ac.icon from ".DB_PREFIX."app_msg as a left join ".DB_PREFIX."app_msg_cate as ac on a.cate_id = ac.id where  ac.is_effect = 1 and ac.is_delete = 0 and a.is_effect = 1 and a.is_delete = 0 $content order by a.sort desc limit ".$limit);
		foreach ($news_list as $k => $v) {
			$v['content'] = mb_substr(strip_tags($v['content']), 0,100,'utf-8');
			$v['create_time'] = date("Y-m-d",$v['create_time']);
			$v['url'] = "/member.php?ctl=uc_set&act=news&id=".$v['id'];
			$news_list[$k] = $v;
		}
		if (empty($news_list)) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('news_list',$news_list);
            $GLOBALS['tmpl']->assign('type',$type);
            $info = $GLOBALS['tmpl']->fetch("inc/uc/news.html");
            echo $info;
        }
	}
	public function help(){
		$id = intval($_REQUEST['id']);
		if($id){
			$cate = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."app_help_cate_cg WHERE id=".$id." and is_effect = 1 and is_delete=0");
			$GLOBALS['tmpl']->assign("cate",$cate);
			$list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."app_help_cg WHERE cate_id=".$id." and is_effect = 1 and is_delete=0");
			$GLOBALS['tmpl']->assign("list",$list);
			$GLOBALS['tmpl']->assign("cate_title",$cate['title']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_view.html");
		}else{
			$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
			switch ($MachineInfo[0]) {
				case 'iOS':
					$jump['popTel'] = 'iosToPopTel';
					break;
				case 'Android':
					$jump['popTel'] = 'androidToPopTel';
				break;
				default:
					$jump['popTel'] = 'popTel';
					break;
			}
			$help_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."app_help_cate_cg  WHERE  is_effect = 1 and is_delete=0");
			foreach ($help_list as $k => $v) {
				$v['url'] = "/member.php?ctl=uc_set&act=help&id=".$v['id'];
				$help_list[$k] = $v;
			}
			$GLOBALS['tmpl']->assign('jump',$jump);
			$GLOBALS['tmpl']->assign("cate_title",'帮助中心');
			$GLOBALS['tmpl']->assign("help_list",$help_list);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_help.html");
		}		
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function about(){
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_about.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function aboutus(){
		$GLOBALS['tmpl']->assign("cate_title",'关于玖财通');
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_set_aboutus.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function userxuzhi(){
	    $GLOBALS['tmpl']->assign("cate_title",'用户须知');
	    $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_userxuzhi.html");
	    $GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function userxuzhi1(){
	    $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_wap_userxuzhi.html");
	    $GLOBALS['tmpl']->display("page/uc.html");
	}
	public function waptraderule(){
	    $GLOBALS['tmpl']->display("inc/uc/uc_waptraderule.html");
	}
	
	public function wapinternetlending(){
	    $GLOBALS['tmpl']->display("inc/uc/uc_wapinternetlending.html");
	}
	
	public function yunying(){
	    $GLOBALS['tmpl']->display("inc/uc/uc_yunying.html");
	}
}

?>
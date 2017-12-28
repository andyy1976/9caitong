<?php
class news
{
	public function index(){
		$id = intval(base64_decode($GLOBALS['request']['id']));		
		$news = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."app_msg WHERE  id=".$id);
		$data['new_id'] = $news['id'];
		$data['title'] = $news['title'];
		$data['content'] = $news['content'];
		$data['add_time'] =  date("Y-m-d H:i:s",$news['create_time']);
		$root['content'] = $data;
		$root['response_code'] = 1;
		$root['program_title'] = "消息中心";
		output($root);		
	}
}
?>
<?php

require APP_ROOT_PATH.'app/Lib/deal.php';
class find_ago
{
	public function index(){
		$root['response_code'] = 1;
		//已经结束的活动
        //$activity = $GLOBALS['db']->getAll("select id,title,end_time,reward,wap_img,appwap_url from ".DB_PREFIX."activity where is_effect = 1 and disable =1 and is_delete=1 and  end_time < ".TIME_UTC." and use_way in(1,3) order by activity_id desc");
        $activity = $GLOBALS['db']->getAll("select id,title,end_time,app_page,is_login,img,name,type,url,act_type from ".DB_PREFIX."app_activity_cg where act_type=1 and device != 1 and is_effect =1 and UNIX_TIMESTAMP(end_time) < ".TIME_UTC." order by sort desc");
        foreach ($activity as $k => $v) {
            $v['is_login'] = 0;
            $v['end_time'] = intval((strtotime($v['end_time']) - TIME_UTC)/24/60/60);
            $v['wap_img'] =$v['img'];
            $v['appwap_url']=$v['url'];
            $act_list[] = $v;
        }
		$root['act_list'] = $act_list;
		output($root);
	}
}
?>


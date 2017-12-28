<?php
/*
*抢红包增加次数接口
*
*/
class red_packet_increase{
	public function index(){
		$log_id = strim(base64_decode($GLOBALS['request']['log_id']));
		$type = strim(base64_decode($GLOBALS['request']['type']));
        $root = get_baseroot();
		$user = $GLOBALS['user_info'];
		if(!$user){//用户未登陆
			$root['response_code'] = 0;
        	$root['show_err'] = '请先登陆，再领取';
        	output($root);
		}
		$begin_time=strtotime(date('Y-m-d'));
		$end_time = $begin_time+86400;
		$GLOBALS['db']->startTrans();
		$user = $GLOBALS['db']->getRow('select id from '.DB_PREFIX.'user where id='.$user['id'].' for update');
		$red_log = $GLOBALS['db']->getRow('select id,increase from '.DB_PREFIX.'user_red_log where create_time>='.$begin_time.' and create_time<'.$end_time.' and status=0 and user_id='.$user['id']);
		if($red_log){
			$res= $GLOBALS['db']->query('update '.DB_PREFIX.'user_red_log set status=1 where id='.$red_log['id']);
			if($res){
				$GLOBALS['db']->commit();
				$root['response_code'] = 1;
				$root['show_err'] = '恭喜您，抢红包次数+'.$red_log['increase'];
				output($root);
			}else{
				$GLOBALS['db']->rollback();
				$root['response_code'] = 1;
				$root['show_err'] = '请稍后重试';
				output($root);
			}	
		}else{
			$GLOBALS['db']->rollback();
			$root['response_code'] = 0;
        	$root['show_err'] = '您已领取过';
        	output($root);
			
		}


}
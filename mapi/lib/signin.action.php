<?php

require APP_ROOT_PATH.'app/Lib/deal.php';
class signin
{
	public function index(){
		
		$root = get_baseroot();
		
		$id = intval(base64_decode($GLOBALS['request']['id']));
	
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['result'] = $this->sign($user_id);
			$root['response_code'] = 1;
			$root['show_err'] = '';
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		
		$root['score'] = $user['score'] + $root['result']['score'];
		$root['program_title'] = "签到";
		output($root);		
	}
	public function sign($user_id){
		if(!$user_id){
			$return['status'] = 0;
			$return['info'] = "签到失败，请重新登录";
			return $return;
		}
		$t_begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));  //今天开始
		$t_end_time = to_timespan(to_date(TIME_UTC,"Y-m-d"))+ (24*3600 - 1);  //今天结束
		$y_begin_time = $t_begin_time - (24*3600); //昨天开始
		$y_end_time = $t_end_time - (24*3600);  //昨天结束
		$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date >= ".$t_begin_time." and sign_date<=".$t_end_time);
		if($t_sign_data){
			$result['status'] = 0;
			$result['info'] = "您已经签到过了";
			return $result;
		}else{
			$score = 3;
			if($score){
				if($score>0)
					$data["score"]=$score;
				//统计是否连续签到
				$sign_count = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." order by id desc");
				$sign_log['user_id'] = $user_id;
				$sign_log['sign_date'] = TIME_UTC;
				if($y_begin_time < $sign_count['sign_date'] && $y_end_time > $sign_count['sign_date']){
					if($sign_count['sign_count']==7){
						$sign_count['sign_count'] =0;
					}
					$sign_log['sign_count'] = $sign_count['sign_count'] +1;
					if($sign_log['sign_count']==3){//连续签到3天
						$score+=10;
						$data["score"]=$score;
					}elseif($sign_log['sign_count']==7){//连续签到7天
						$score+=30;
						$data["score"]=$score;
					}
					$sign_log['sign_score_count'] = $sign_count['sign_score_count'] +$score;			
				}else{
					$sign_log['sign_count'] = 1;
					$sign_log['sign_score_count'] = $score;
				}
				$resultSignLog = $GLOBALS['db']->autoExecute(DB_PREFIX."user_sign_log",$sign_log,"INSERT");
				if($resultSignLog){
					modify_account($data,$user_id,"每日签到",25);
					$result['status'] = 1;
					$result['score'] = $score;
					if($sign_log['sign_count']==2){
						$number = "明日签到，可获得13积分";
					}elseif($sign_log['sign_count']==6){
						$number = "明日签到，可获得33积分";
					}else{
						$number = "明日签到，可获得3积分";
					}
					$result['info'] = "签到成功\n".$number;
					return $result;
				}else{
					$result['status'] = 0;
					$result['info'] = "签到失败，请重试";
					return $result;
				}
				
			}else{
				$result['status'] = 0;
				$result['info'] = "签到失败，请重试";
				return $result;
			}
		}
	}
}
?>


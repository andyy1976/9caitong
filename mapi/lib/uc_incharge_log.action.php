<?php
class uc_incharge_log
{
	public function index(){
		
		$root = get_baseroot();
		$url = WAP_SITE_DOMAIN;
		$page = intval(base64_decode($GLOBALS['request']['page']));		
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';			
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;			
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			//$condition="1=1";
			$condition=" and cunguan_tag = 0";
			$result = get_user_incharge_log($limit,$user_id,$condition);
			$details = $result['list'];
			$month_time_start = to_date(TIME_UTC,"m");
			foreach($details as $key=>$val){
				if(date('m',$val['create_time']) == $month_time_start){
					$create_time=date('本月 Y年',$val['create_time']);
				}else{
					$create_time=date('m月 Y年',$val['create_time']);//将查出的每个年月日，以年月分离出来，做为新数组的下标
				}
			    $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
                if(date('Y-m-d', $val['create_time'])==date('Y-m-d',TIME_UTC)){
                    $val['week'] = '今天';
                }elseif(date("Y-m-d",strtotime("-1 day"))==date('Y-m-d',$val['create_time'])){
                    $val['week'] = '昨天';
                }else{
                    $val['week']= week(date('N', $val['create_time']));
                }

			    $val['time'] = date('H:i',$val['create_time']);
			    $val['account_money'] = $val['money'];
			    $val['memo'] = $val['is_paid_format'];
				if($val['is_paid']==1){
					$val['icon'] = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='充值成功'");
					//$val['icon'] = $url."/app/Tpl/wap/images/wap2/my/icon_cz_success.png";
					$val['money'] = "+" . $val['money'];
				}else{
					//$val['icon'] = $url."/app/Tpl/wap/images/wap2/my/icon_cz_fail.png";
                    $val['icon'] =$GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='充值失败'");
				}

			    $val['create_time'] = $details[$key]['create_time'];	      
			    $data[$create_time][]=$val; 			    
			}
			foreach ($data as $k => $v) {
				$bat['month'] = $k;
				$bat['weeks'] = $v;
				$list[] = $bat;
			}
			if($list == null){
				$list_row = array();
			}else{
				$list_row = $list;
			}
			$root['item'] = $list_row;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "充值日志";
		output($root);		
	}
}
?>

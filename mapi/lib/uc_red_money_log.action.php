<?php
class uc_red_money_log
{
	public function index(){		
		$root = get_baseroot();		
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
			$result = get_user_red_money_log($limit,$user_id,56,'');
			$details = $result['list'];
			$month_time_start = to_date(TIME_UTC,"m");
			foreach($details as $key=>$val){
				if(date('m',$val['create_time']) == $month_time_start){
					$create_time=date('本月 Y年',$val['create_time']);
				}else{
					$create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
				}
				if($val['money'] > 0){
                    $val['icon'] = WAP_SITE_DOMAIN.$GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='红包获得'");
					//$val['icon'] = get_domain().'/app/Tpl/wap/images/wap2/my/red_money_get.png';
				}else{
                    $val['icon'] = WAP_SITE_DOMAIN.$GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='红包使用'");
					//$val['icon'] = get_domain().'/app/Tpl/wap/images/wap2/my/red_money_use.png';
				}

                $val['memo']=strim(strip_tags($val['memo']));
                preg_match("/(?:\[)(.*)(?:\])/i",$val['memo'], $res);
                if($res){
                    $val['memo']=str_replace($res[0].'的',"",$val['memo']);
                    $val['memo']=str_replace($res[0].',的',"",$val['memo']);
                    $val['memo']=str_replace($res[0].',',"",$val['memo']);
                    if(strpos($val['memo'], '代金券')){
                        $val['memo']=substr($val['memo'],strpos($val['memo'], '代金券'));
                    }
                }
                $val['money'] =intval($val['money']);
                $val['memo'] = mb_substr($val['memo'],0,11,'utf-8');
                if($val['money']>0){
                	$val['money'] = '+'.intval($val['money']);
				}
			    $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
			    $val['week']= week(date('N', $val['create_time']));
			    $val['time'] = date('H:i',$val['create_time']);
			    $val['create_time'] = $details[$key]['create_time']; 			       
			    $data[$create_time][]=$val; 
			}
			foreach ($data as $k => $v) {
				$bat['month'] = $k;
				$bat['weeks'] = $v;
				$list[] = $bat;
			}
			$root['item'] = $list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "红包明细";
		output($root);		
	}
}
?>

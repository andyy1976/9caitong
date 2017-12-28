<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_money_log
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
			$p = 5;
			$limit = (($page-1)*$p).",".$p;	
			$condition = "type=1 or type=8";		
			$result = get_user_money_log($limit,$GLOBALS['user_info']['id'],$condition);
			$details = $result['list'];
			$month_time_start = to_date(TIME_UTC,"m");
			foreach($details as $key=>$val){
				if(date('m',$val['create_time']) == $month_time_start){
					$create_time=date('本月 Y年',$val['create_time']);
				}else{
					$create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
				}
			    $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
			    $val['week']= week(date('N', $val['create_time']));
			    $val['time'] = date('H:i',$val['create_time']);
			    $val['create_time'] = $details[$key]['create_time'];      
			    $list[$create_time][]=$val; //-->将查出的每个年月日，以年月分离出来，做为新数组的下标  
			    
			}
			if($result['list']){
				$root['response_code'] = 1;
			}else{
				$root['response_code'] = 0;
				$root['show_err'] = "请求数据失败";
			}
			$root['item'] = $list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/$p),"page_size"=>$p);
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "资金明细";
		output($root);		
	}
}
?>

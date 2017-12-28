<?php
class uc_money_logs
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
			$limit = (($page-1)*15).",".'15';
			//$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
            $condition = "and (type in(1,2,5,47,29,58) or brief= '提现成功') and cunguan_tag = 0";
			//$condition = "and type !=13 and type !=100";
			$result = get_user_money_log($limit,$user_id,-1,$condition);
			$root['sql'] = $result['sql'];
			$details = $result['list'];
			$month_time_start = to_date(TIME_UTC,"m");
			foreach($details as $key=>$val){
				if(date('m',$val['create_time']) == $month_time_start){
					$create_time=date('本月 Y年',$val['create_time']);
				}else{
					$create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
				}

                switch ($val['type']) {
                    case '1':
                        $val['memo'] ="充值成功";
                        $val['money'] = "+".sprintf("%.2f", floatval($val['money']));
                        break;
                    case '2':
                        $val['memo'] ="出借成功";
                        break;
                    case '5':
                        $val['money'] = "+".sprintf("%.2f", floatval($val['money']));
                        break;
                    case '8':
                        $val['memo'] ="提现成功";
                        if($val['money'] > 0){
                            $val['money'] = "-".sprintf("%.2f", floatval($val['money']));
                        }
                        break;
                    case '47':
                        $val['memo'] ="领取体验金收益";
                        $val['money'] = "+".sprintf("%.2f", floatval($val['money']));
                        break;
                    case '29':
                        $val['memo'] ='虚拟货币转换';
                        break;
                    case '58':
                        $val['memo'] ="募集期收益";
                        $val['money'] = "+".sprintf("%.2f", floatval($val['money']));
                        break;
                    default:
                        # code...
                        break;
                }

				$val['icon'] = $this->get_type_icon($val['type'],$val['money']);
			    $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
			    if(date('Y-m-d', $val['create_time'])==date('Y-m-d',TIME_UTC)){
                    $val['week'] = '今天';
				}elseif(date("Y-m-d",strtotime("-1 day"))==date('Y-m-d',$val['create_time'])){
                    $val['week'] = '昨天';
				}else{
                    $val['week']= week(date('N', $val['create_time']));
				}
                /*if($val['memo'] == '提现失败' || $val['brief']== '提现失败'){
                    $val['money'] = '-'.$val['money'];
                }
                if($val['money']>0){
                    $val['money'] = '+'.$val['money'];
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
				if($val['memo'] == '提现申请' || $val['brief']== '提现申请'){
					unset($val);
					continue;
				}

				$val['memo'] = empty($val['brief']) ?$val['memo']:$val['brief'];*/
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
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/15),"page_size"=>15);
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "资金明细";
		output($root);		
	}

	public function get_type_icon($type,$money){
		if($type==1 || $type==47 || $type == 58|| $type == 5){
            $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='充值成功'");
		}elseif($type==2 || $type==8 ){
			if($money>0){
                $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='提现失败'");
			}else{
                $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='提现成功'");
			}
		}elseif($type==56 || $type == 29){
			if($money>0){
                $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='红包获得'");
			}else{
                $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='红包使用'");
			}
		}elseif($type==57){
			if($money>0){
                $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='代金券获得'");
			}else{
                $icon = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."icon i left join ".DB_PREFIX."icon_group ig on i.group_id=ig.id where i.is_effect=1 and ig.is_effect=1 and ig.name='代金券使用'");
			}
		}
		return $icon;
	}
}
?>

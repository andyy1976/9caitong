<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_invest_refdetail
{
	public function index(){
		
		$root = get_baseroot();
		
		$id = intval(base64_decode($GLOBALS['request']['id']));
		$load_id = intval(base64_decode($GLOBALS['request']['load_id']));
			
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		//$user_id = $GLOBALS['user_info']['id'];
		if ($user_id >0){
			require_once APP_ROOT_PATH.'app/Lib/deal.php';
			require_once APP_ROOT_PATH."app/Lib/deal_func.php";
			$root['user_login_status'] = 1;
									
			$deal = get_deal($id);
			$root['deal'] = $deal;
			if(!$deal || $deal['deal_status'] < 4){				
				$root['show_err'] = "操作失败！";
				$root['response_code'] = 0;
			}else{				
				$temp_user_load = $GLOBALS['db']->getRow("SELECT dl.id,dl.deal_id,dl.user_id,dl.money,dlt.t_user_id FROM ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal_load_transfer dlt on dlt.load_id = dl.id WHERE dl.deal_id=".$id." and dl.id=".$load_id);
				$user_loads = $GLOBALS['db']->getRow("select dl.total_money as total_money,dl.create_time,dl.id as id,d.repay_time as repay_time,d.rate as rate from ".DB_PREFIX."deal_load as dl left join  ".DB_PREFIX."deal d on d.id = dl.deal_id  WHERE dl.id=".$id." and dl.user_id=".$user_id);
				$user_load_ids = array();
				if($temp_user_load){
					$u_key = $GLOBALS['db']->getOne("SELECT u_key FROM ".DB_PREFIX."deal_load_repay WHERE load_id=".$load_id." and (user_id=".$user_id." or t_user_id = ".$user_id.")");
					if(($temp_user_load["user_id"] == $user_id && intval($temp_user_load['t_user_id']) == 0 )|| $temp_user_load['t_user_id'] == $user_id){
						$temp_user_load['repay_start_time'] = $deal['repay_start_time'];
						$temp_user_load['repay_time'] = $deal['repay_time'];
						$temp_user_load['rate'] = $deal['rate'];
						$temp_user_load['u_key'] = $u_key;
						$temp_user_load['load'] = get_deal_user_load_list($deal, $user_id, -1 ,$u_key);
						$temp_user_load['impose_money'] =0;
						$temp_user_load['manage_fee'] = 0;
						$temp_user_load['repay_money'] = 0;
						$temp_user_load['manage_interest_money'] = 0;
						foreach($temp_user_load['load'] as $kk=>$vv){
							$temp_user_load['impose_money'] += $vv['impose_money'];
							$temp_user_load['manage_fee'] += $vv['manage_money'];
							$temp_user_load['repay_money'] += $vv['month_has_repay_money'];
							$temp_user_load['manage_interest_money'] += floatval($vv['manage_interest_money']);
							
							//预期收益
							$temp_user_load['load'][$kk]['yuqi_money']=format_price($vv['month_repay_money']-$vv['self_money'] - $vv['manage_money'] - $vv['manage_interest_money']);
							//实际收益
							if($vv['has_repay']==1){
								$temp_user_load['load'][$kk]['real_money']=format_price($vv['month_repay_money']- $vv['self_money']+$vv['impose_money'] - $vv['manage_money']- $vv['manage_interest_money']);
							}
						}
						$user_load_ids[] = $temp_user_load;
					}
				}else{
					$i=1;
					for($i;$i<=$user_loads['repay_time'];$i++){
						if($i != $user_loads['repay_time']){
							$data['benxi'] = sprintf("%.2f",($user_loads['total_money'] * $user_loads['rate'])/12/100);
							$data['interest_money'] = sprintf("%.2f",($user_loads['total_money'] * $user_loads['rate'])/12/100);
							$data['self_money'] = "0.00";
						}else{
							$data['benxi'] = sprintf("%.2f",(($user_loads['total_money'] * $user_loads['rate'])/12/100)+$user_loads['total_money']);
							$data['interest_money'] = sprintf("%.2f",($user_loads['total_money'] * $user_loads['rate'])/12/100);
							$data['self_money'] = $user_loads['total_money'];
						}				
						
						$data['repay_date'] = date("Y-m-d",strtotime("+$i month"));
						$user_load_ids[] = $data;
					}
				}
				
				$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
				$root['inrepay_info'] = $inrepay_info;
				$root['true_repay_time_format'] =to_date($inrepay_info['true_repay_time'],'Y-m-d');
				
				$root['user_load_ids'] = $user_load_ids;
				$root['load_id'] = $load_id;
				$root['agree_url'] = wap_url("index","deal_contract",array("id"=>$id));
				
				$root['response_code'] = 1;
				$root['show_err'] = '';
			}
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "回款详情";
		output($root);		
	}
}
?>

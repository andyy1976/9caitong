<?php
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class interest_card
{
    public function index(){

        $root = array();
        /*
        Id:贷款单ID
        buy_money：投标金额
        */
        $page = intval(base64_decode($GLOBALS['request']['page']));
        $deal_id = intval(base64_decode($GLOBALS['request']['deal_id']));
        $plan_id = intval(base64_decode($GLOBALS['request']['plan_id']));
        if($page==0)
            $page = 1;
        $root['session_id'] = es_session::id();
        $user_id = $GLOBALS['user_info']['id'];
        $limit = "0,10000";
		if($plan_id){
			$result = get_lcinterest_card_list($limit,$user_id,$plan_id);
		}else{
			$result = get_interest_card_list($limit,$user_id,$deal_id);
		}
        
        foreach ($result['list'] as $k => $v) {
            if($v['status'] == 0 && $v['end_time'] >time()){
                $v['begin_time']=date("Y-m-d",$v['begin_time']);
                $v['end_time']=date("Y-m-d",$v['end_time']);
				if($plan_id){
					$v['use_condition']="21天理财计划";
				}else{
					$v['use_condition']=$v['use_condition']."个月项目";
				}
                
                $v['rate']='+'.$v['rate'];
                if($v['interest_time']==0){
                    $v['interest_time']="全程加息";
                }else{
                    $v['interest_time']="加息".$v['interest_time']."天";
                }
                $list[] = $v;
            }
        }
        $root['item'] = $list;
        $voucher_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'pluscard_explain'"));
//        $root['voucher_explain'] = str_replace("。","。\n\n" ,$voucher_explain);
        $root['count']=$result['count'];
        $root['response_code'] = 1;
        $root['user_descript']=str_replace("。","。\n\n" ,$voucher_explain);
        output($root);
    }
}
?>

<?php
require APP_ROOT_PATH.'app/Lib/uc_func.php';
class red_deal
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
			$result = get_red_list($limit,$user_id,$plan_id,true);
		}else{
			$result = get_red_list($limit,$user_id,$deal_id);
		}
        
        foreach ($result['list'] as $k => $v) {
            $v['begin_time']=date("Y-m-d",$v['begin_time']);
            $v['end_time']=date("Y-m-d",$v['end_time']);
           if($plan_id){
				$v['use_condition']="可用于理财计划";
			}else{
				$v['use_condition']=$v['use_condition']."个月项目";
			}
            $v['max_use_money']=$v['ratio'];

            $v['is_increase']=0?"不可叠加":"可叠加";
            if($v['red_type']==0){
                $v['red_type']="注册红包";
            }elseif($v['red_type']==1){
                $v['red_type']="投资红包";
            }else{
                $v['red_type']="现金红包";
            }

            $list[] = $v;
        }
        $root['item'] = $list;
        $voucher_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'red_explain'"));
//        $root['voucher_explain'] = str_replace("。","。\n\n" ,$voucher_explain);
        $root['count']=$result['count'];
        $root['response_code'] = 1;
        $root['user_descript']=str_replace("。","。\n\n" ,$voucher_explain);
        output($root);
    }
}
?>
